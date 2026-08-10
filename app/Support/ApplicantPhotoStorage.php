<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ApplicantPhotoStorage
{
    public const DIRECTORY = 'applicants';

    public const DISK = 'local';

    public static function disk(): Filesystem
    {
        return Storage::disk(self::DISK);
    }

    public static function passportPath(string $applicationId): string
    {
        return self::DIRECTORY.'/'.$applicationId.'.jpg';
    }

    public static function gcashPath(string $applicationId, string $extension): string
    {
        $extension = strtolower(ltrim($extension, '.'));

        if ($extension === '' || $extension === 'jpeg') {
            $extension = 'jpg';
        }

        return self::DIRECTORY.'/'.$applicationId.'-gcash.'.$extension;
    }

    public static function ensureDirectoryExists(): void
    {
        $disk = self::disk();

        if (! $disk->exists(self::DIRECTORY)) {
            $disk->makeDirectory(self::DIRECTORY);
        }
    }

    /**
     * Store a passport photo using the canonical naming convention.
     * Returns the relative path saved in the database (applicants/{id}.jpg).
     */
    public static function storePassport(UploadedFile $file, string $applicationId): string
    {
        $relativePath = self::passportPath($applicationId);

        self::writeUploadedFile($file, $relativePath);

        return $relativePath;
    }

    /**
     * Store a GCash screenshot using the canonical naming convention.
     * Returns the relative path saved in the database (applicants/{id}-gcash.{ext}).
     */
    public static function storeGcash(UploadedFile $file, string $applicationId): string
    {
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg'));
        $relativePath = self::gcashPath($applicationId, $extension);

        self::writeUploadedFile($file, $relativePath);

        return $relativePath;
    }

    /**
     * Persist an uploaded file to the private applicants disk and verify it exists.
     */
    public static function writeUploadedFile(UploadedFile $file, string $relativePath): void
    {
        self::ensureDirectoryExists();
        self::assertApplicantsDirectoryIsWritable();

        $disk = self::disk();
        $directory = trim(dirname($relativePath), '.');
        $filename = basename($relativePath);

        if ($directory === '') {
            $directory = self::DIRECTORY;
        }

        if ($disk->exists($relativePath)) {
            $disk->delete($relativePath);
        }

        $lastError = null;

        // Prefer storeAs — works reliably with Livewire TemporaryUploadedFile.
        try {
            $stored = $file->storeAs($directory, $filename, [
                'disk' => self::DISK,
            ]);

            if ($stored === $relativePath && $disk->exists($relativePath) && $disk->size($relativePath) > 0) {
                return;
            }

            $lastError = "storeAs returned [{$stored}] for [{$relativePath}]";
        } catch (\Throwable $exception) {
            $lastError = $exception->getMessage();
        }

        // Fallback: stream/copy from the uploaded file's real path.
        try {
            $realPath = $file->getRealPath();

            if ($realPath === false || ! is_readable($realPath)) {
                throw new RuntimeException('Uploaded temporary file is not readable.');
            }

            $stream = fopen($realPath, 'rb');

            if ($stream === false) {
                throw new RuntimeException('Could not open uploaded temporary file for reading.');
            }

            try {
                $written = $disk->writeStream($relativePath, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if (($written ?? true) && $disk->exists($relativePath) && $disk->size($relativePath) > 0) {
                return;
            }

            $lastError = 'writeStream did not persist a readable file';
        } catch (\Throwable $exception) {
            $lastError = $exception->getMessage();
        }

        $absolute = $disk->path($relativePath);

        throw new RuntimeException(
            "Failed to store applicant document at [{$relativePath}] (absolute: [{$absolute}]). ".
            'Last error: '.($lastError ?? 'unknown').'. '.
            'If this persists, fix storage permissions for the web server user.'
        );
    }

    public static function assertApplicantsDirectoryIsWritable(): void
    {
        self::ensureDirectoryExists();

        $directory = self::disk()->path(self::DIRECTORY);

        if (! is_dir($directory) && ! @mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new RuntimeException(
                "Could not create applicants storage directory at [{$directory}]. ".
                'Check that storage/app/private is owned by the web server user.'
            );
        }

        if (! is_writable($directory)) {
            throw new RuntimeException(
                "Applicants storage is not writable at [{$directory}]. ".
                'Run: sudo chown -R www-data:www-data storage bootstrap/cache && sudo chmod -R ug+rwx storage bootstrap/cache'
            );
        }

        $probe = rtrim($directory, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'.write_probe_'.uniqid('', true);

        if (@file_put_contents($probe, 'ok') === false) {
            throw new RuntimeException(
                "Applicants storage write probe failed at [{$directory}]. ".
                'Fix ownership/permissions for the web server user on the storage folder.'
            );
        }

        @unlink($probe);
    }

    /**
     * Normalize DB path values that may include legacy prefixes.
     */
    public static function normalizeRelativePath(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');

        $prefixes = [
            'storage/app/private/',
            'storage/app/public/',
            'storage/app/',
            'storage/',
            'app/private/',
            'app/public/',
            'app/',
            'private/',
            'public/',
        ];

        foreach ($prefixes as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
                break;
            }
        }

        return ltrim($normalized, '/');
    }

    /**
     * Resolve an applicant photo to an absolute filesystem path.
     * Checks the private local disk first, then common legacy locations used in older deploys.
     */
    public static function absolutePath(?string $path): ?string
    {
        $normalized = self::normalizeRelativePath($path);

        if ($normalized === null) {
            return null;
        }

        foreach ([self::DISK, 'public'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($normalized)) {
                return $disk->path($normalized);
            }
        }

        $legacyCandidates = [
            storage_path('app/'.$normalized),
            storage_path('app/private/'.$normalized),
            storage_path('app/public/'.$normalized),
            base_path($normalized),
            public_path($normalized),
            public_path('storage/'.$normalized),
        ];

        foreach ($legacyCandidates as $candidate) {
            if (is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Find a passport/GCash file by application id across known storage roots.
     */
    public static function absolutePathForApplication(string $applicationId, string $type): ?string
    {
        $applicationId = trim($applicationId);

        if ($applicationId === '') {
            return null;
        }

        $expected = $type === 'gcash'
            ? [
                self::gcashPath($applicationId, 'jpg'),
                self::gcashPath($applicationId, 'jpeg'),
                self::gcashPath($applicationId, 'png'),
                self::gcashPath($applicationId, 'pdf'),
            ]
            : [
                self::passportPath($applicationId),
                self::DIRECTORY.'/'.$applicationId.'.jpeg',
                self::DIRECTORY.'/'.$applicationId.'.png',
            ];

        foreach ($expected as $relative) {
            $absolute = self::absolutePath($relative);

            if ($absolute !== null) {
                return $absolute;
            }
        }

        $roots = [
            storage_path('app/private/'.self::DIRECTORY),
            storage_path('app/public/'.self::DIRECTORY),
            storage_path('app/'.self::DIRECTORY),
        ];

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $pattern = $type === 'gcash'
                ? $root.DIRECTORY_SEPARATOR.$applicationId.'-gcash.*'
                : $root.DIRECTORY_SEPARATOR.$applicationId.'.*';

            $matches = glob($pattern) ?: [];

            foreach ($matches as $match) {
                if (! is_file($match)) {
                    continue;
                }

                // Avoid matching "000001-gcash.jpg" when looking for passport "000001.*".
                if ($type !== 'gcash' && str_contains(basename($match), '-gcash.')) {
                    continue;
                }

                return $match;
            }
        }

        return null;
    }

    public static function exists(?string $path): bool
    {
        return self::absolutePath($path) !== null;
    }
}
