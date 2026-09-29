<?php

namespace App\Services;

use App\Models\Applicant;
use App\Support\ApplicantNameFormatter;
use App\Support\ApplicantPhotoStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class ApplicantPassportZipService
{
    /**
     * @param  array<int>  $applicantIds
     */
    public function download(array $applicantIds): BinaryFileResponse
    {
        $applicants = Applicant::query()
            ->verified()
            ->whereIn('id', $applicantIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->get();

        if ($applicants->isEmpty()) {
            abort(404, 'No approved applicants were found for the selected IDs.');
        }

        $tempZipPath = tempnam(sys_get_temp_dir(), 'passport-zip-');

        if ($tempZipPath === false) {
            abort(500, 'Could not create zip file.');
        }

        $zip = new ZipArchive;
        $result = $zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($result !== true) {
            abort(500, 'Could not open zip file.');
        }

        $disk = ApplicantPhotoStorage::disk();
        $usedNames = [];
        $added = 0;

        foreach ($applicants as $applicant) {
            if (blank($applicant->passport_photo) || ! $disk->exists($applicant->passport_photo)) {
                continue;
            }

            $zipName = $this->uniqueZipEntryName($applicant, $usedNames);
            $zip->addFile($disk->path($applicant->passport_photo), $zipName);
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tempZipPath);

            abort(404, 'No passport photos were found for the selected applicants.');
        }

        $filename = 'passport-photos-'.now()->format('Y-m-d-His').'.zip';

        return response()->download($tempZipPath, $filename)->deleteFileAfterSend(true);
    }

    /**
     * @param  array<string, bool>  $usedNames
     */
    protected function uniqueZipEntryName(Applicant $applicant, array &$usedNames): string
    {
        $originalName = basename($applicant->passport_photo);
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $suffix = $extension !== '' ? '.'.strtolower($extension) : '';

        $stem = $this->sanitizeFileName((string) $applicant->full_name);

        if ($stem === '') {
            $stem = $this->sanitizeFileName(ApplicantNameFormatter::buildFullName(
                (string) $applicant->first_name,
                (string) $applicant->middle_name,
                (string) $applicant->last_name,
            ));
        }

        if ($stem === '') {
            $stem = pathinfo($originalName, PATHINFO_FILENAME);
        }

        if (isset($usedNames[strtolower($stem)])) {
            $discriminator = $applicant->application_id ?: $applicant->id;
            $stem = "{$stem} ({$discriminator})";
        }

        $usedNames[strtolower($stem)] = true;

        return $stem.$suffix;
    }

    protected function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]/u', '', $name) ?? '';
        $name = preg_replace('/\s+/u', ' ', $name) ?? '';

        return trim($name, ' .');
    }
}
