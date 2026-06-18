<?php

namespace App\Enums;

enum RejectionReason: string
{
    case InvalidPassportPhoto = 'Invalid Passport Photo';
    case InvalidGcashScreenshot = 'Invalid GCash Screenshot';
    case IncompleteInformation = 'Incomplete Information';
    case UnreadableDocuments = 'Unreadable Documents';
    case NonResident = 'Non-Resident of Manolo Fortich';
    case DuplicateApplication = 'Duplicate Application';
    case Other = 'Other';

    public function requiresRemarks(): bool
    {
        return $this === self::Other;
    }
}
