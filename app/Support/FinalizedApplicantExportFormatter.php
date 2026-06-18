<?php

namespace App\Support;

use App\Models\Applicant;

class FinalizedApplicantExportFormatter
{
    public static function fullName(Applicant $applicant): string
    {
        return self::uppercase($applicant->full_name);
    }

    public static function firstName(Applicant $applicant): string
    {
        return self::uppercase($applicant->first_name);
    }

    public static function middleName(Applicant $applicant): string
    {
        return self::uppercase($applicant->middle_name);
    }

    public static function lastName(Applicant $applicant): string
    {
        return self::uppercase($applicant->last_name);
    }

    public static function address(Applicant $applicant): string
    {
        return ApplicantAddressFormatter::build(
            (string) $applicant->address,
            $applicant->barangay,
        );
    }

    private static function uppercase(?string $value): string
    {
        return mb_strtoupper(trim((string) $value));
    }
}
