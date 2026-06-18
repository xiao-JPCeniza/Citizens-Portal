<?php

namespace App\Services;

use App\Models\Applicant;
use Illuminate\Support\Facades\Storage;
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
            ->finalized()
            ->whereIn('id', $applicantIds)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($applicants->isEmpty()) {
            abort(404, 'No finalized applicants were found for the selected IDs.');
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

        $disk = Storage::disk('public');
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
        $baseName = basename($applicant->passport_photo);
        $zipName = $baseName;

        if (isset($usedNames[$zipName])) {
            $extension = pathinfo($baseName, PATHINFO_EXTENSION);
            $stem = pathinfo($baseName, PATHINFO_FILENAME);
            $zipName = "{$stem}-{$applicant->id}".($extension !== '' ? ".{$extension}" : '');
        }

        $usedNames[$zipName] = true;

        return $zipName;
    }
}
