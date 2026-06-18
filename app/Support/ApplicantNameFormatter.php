<?php

namespace App\Support;

class ApplicantNameFormatter
{
    public static function middleInitial(?string $middleName): ?string
    {
        $middle = trim((string) $middleName);

        if ($middle === '') {
            return null;
        }

        $letter = mb_substr(ltrim($middle, '.'), 0, 1);

        if ($letter === '') {
            return null;
        }

        return mb_strtoupper($letter).'.';
    }

    public static function buildFullName(string $firstName, string $middleName, string $lastName): string
    {
        return trim(implode(' ', array_filter([
            trim($firstName),
            self::middleInitial($middleName),
            trim($lastName),
        ])));
    }
}
