<?php

namespace App\Support;

class ApplicantAddressFormatter
{
    public const MAX_LENGTH = 60;

    public const LOCATION_SUFFIX = 'Manolo Fortich, Bukidnon';

    public static function build(string $address, ?string $barangay): string
    {
        $parts = array_values(array_filter([
            trim($address),
            filled($barangay) ? trim((string) $barangay) : null,
        ]));

        $built = implode(', ', $parts);

        if (! str_contains(strtolower($built), 'manolo fortich')) {
            $built = $built === ''
                ? self::LOCATION_SUFFIX
                : rtrim($built, ', ').', '.self::LOCATION_SUFFIX;
        }

        return $built;
    }

    public static function length(string $address, ?string $barangay): int
    {
        return mb_strlen(self::build($address, $barangay));
    }

    public static function isValidLength(string $address, ?string $barangay): bool
    {
        return self::length($address, $barangay) <= self::MAX_LENGTH;
    }
}
