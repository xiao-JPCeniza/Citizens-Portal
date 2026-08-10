<?php

namespace App\Support;

class ApplicantPhotoStorage
{
    public const DIRECTORY = 'applicants';

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
}
