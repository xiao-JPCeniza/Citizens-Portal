<?php

namespace App\Services;

use App\Support\ApplicantPhotoStorage;
use App\Support\ApplicationIdGenerator;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;

class ApplicantPhotoNormalizationService
{
    public function normalizeExistingApplicants(?Filesystem $disk = null): void
    {
        $disk ??= ApplicantPhotoStorage::disk();
        $sequence = 1;

        DB::table('applicants')
            ->orderBy('id')
            ->select(['id', 'passport_photo', 'gcash_screenshot'])
            ->chunkById(100, function ($applicants) use ($disk, &$sequence) {
                foreach ($applicants as $applicant) {
                    $applicationId = ApplicationIdGenerator::format($sequence);
                    $sequence++;

                    $passportPath = $this->relocatePassportPhoto(
                        $disk,
                        $applicant->passport_photo,
                        $applicationId,
                    );

                    $gcashPath = $this->relocateGcashScreenshot(
                        $disk,
                        $applicant->gcash_screenshot,
                        $applicationId,
                    );

                    DB::table('applicants')
                        ->where('id', $applicant->id)
                        ->update([
                            'application_id' => $applicationId,
                            'passport_photo' => $passportPath,
                            'gcash_screenshot' => $gcashPath,
                        ]);
                }
            });
    }

    protected function relocatePassportPhoto(Filesystem $disk, ?string $currentPath, string $applicationId): string
    {
        $targetPath = ApplicantPhotoStorage::passportPath($applicationId);

        if (blank($currentPath) || $currentPath === $targetPath) {
            return $targetPath;
        }

        if ($disk->exists($currentPath)) {
            if ($disk->exists($targetPath)) {
                $disk->delete($targetPath);
            }

            $disk->move($currentPath, $targetPath);
            $this->deleteEmptyParentDirectories($disk, $currentPath);
        }

        return $targetPath;
    }

    protected function relocateGcashScreenshot(Filesystem $disk, ?string $currentPath, string $applicationId): string
    {
        $extension = filled($currentPath)
            ? (pathinfo($currentPath, PATHINFO_EXTENSION) ?: 'jpg')
            : 'jpg';

        $targetPath = ApplicantPhotoStorage::gcashPath($applicationId, $extension);

        if (blank($currentPath) || $currentPath === $targetPath) {
            return $targetPath;
        }

        if ($disk->exists($currentPath)) {
            if ($disk->exists($targetPath)) {
                $disk->delete($targetPath);
            }

            $disk->move($currentPath, $targetPath);
            $this->deleteEmptyParentDirectories($disk, $currentPath);
        }

        return $targetPath;
    }

    protected function deleteEmptyParentDirectories(Filesystem $disk, string $oldPath): void
    {
        $directory = dirname($oldPath);

        if ($directory === '.' || $directory === ApplicantPhotoStorage::DIRECTORY) {
            return;
        }

        $files = $disk->files($directory);
        $directories = $disk->directories($directory);

        if ($files === [] && $directories === []) {
            $disk->deleteDirectory($directory);
        }
    }
}
