<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

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

        if ($extension === '') {
            $extension = 'jpg';
        }

        return self::DIRECTORY.'/'.$applicationId.'-gcash.'.$extension;
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
