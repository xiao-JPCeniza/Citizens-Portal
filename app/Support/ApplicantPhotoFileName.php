<?php

namespace App\Support;

use App\Models\Applicant;

class ApplicantPhotoFileName
{
    public static function for(Applicant $applicant): string
    {
        if (blank($applicant->passport_photo)) {
            return '';
        }

        return self::stem($applicant).self::suffix($applicant);
    }

    public static function stem(Applicant $applicant): string
    {
        $stem = self::sanitize((string) $applicant->full_name);

        if ($stem === '') {
            $stem = self::sanitize(ApplicantNameFormatter::buildFullName(
                (string) $applicant->first_name,
                (string) $applicant->middle_name,
                (string) $applicant->last_name,
            ));
        }

        if ($stem === '') {
            $stem = pathinfo(basename((string) $applicant->passport_photo), PATHINFO_FILENAME);
        }

        return $stem;
    }

    public static function suffix(Applicant $applicant): string
    {
        $extension = pathinfo(basename((string) $applicant->passport_photo), PATHINFO_EXTENSION);

        return $extension !== '' ? '.'.strtolower($extension) : '';
    }

    private static function sanitize(string $name): string
    {
        $name = preg_replace('/[\\\\\/:*?"<>|\x00-\x1F]/u', '', $name) ?? '';
        $name = preg_replace('/\s+/u', ' ', $name) ?? '';

        return trim($name, ' .');
    }
}
