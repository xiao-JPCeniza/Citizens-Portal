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
}
