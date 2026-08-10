<?php

namespace App\Http\Controllers\Admin;

use App\Models\Applicant;
use App\Support\ApplicantPhotoStorage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicantDocumentController
{
    public function __invoke(Applicant $applicant, string $type): BinaryFileResponse
    {
        $path = match ($type) {
            'passport' => $applicant->passport_photo,
            'gcash' => $applicant->gcash_screenshot,
            default => null,
        };

        $absolutePath = ApplicantPhotoStorage::absolutePath($path);

        if ($absolutePath === null) {
            abort(404);
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
