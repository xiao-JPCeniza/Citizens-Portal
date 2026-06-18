<?php

namespace App\Support;

class ApplicantFieldConstraints
{
    public const PHONE_NUMBER_LENGTH = 11;

    public const EMERGENCY_CONTACT_PERSON_MAX_LENGTH = 30;

    public static function phoneNumberPattern(): string
    {
        return '/^09\d{9}$/';
    }
}
