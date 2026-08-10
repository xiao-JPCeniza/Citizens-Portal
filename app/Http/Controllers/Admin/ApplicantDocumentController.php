<?php

namespace App\Http\Controllers\Admin;

use App\Models\Applicant;
use App\Support\ApplicantPhotoStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicantDocumentController
{
    public function __invoke(Applicant $applicant, string $type): BinaryFileResponse
    {
        $storedPath = match ($type) {
            'passport' => $applicant->passport_photo,
            'gcash' => $applicant->gcash_screenshot,
            default => null,
        };

        $absolutePath = ApplicantPhotoStorage::absolutePath($storedPath);

        if ($absolutePath === null && filled($applicant->application_id)) {
            $absolutePath = ApplicantPhotoStorage::absolutePathForApplication(
                $applicant->application_id,
                $type,
            );
        }

        if ($absolutePath === null) {
            abort(404, 'Document file was not found on the server.');
        }

        $mime = mime_content_type($absolutePath) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($absolutePath).'"',
            'Cache-Control' => 'private, no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
