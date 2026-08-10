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

    /**
     * Final / non-correctable rejections do not include a secure edit link.
     */
    public function allowsEditLink(): bool
    {
        return match ($this) {
            self::Other,
            self::NonResident,
            self::DuplicateApplication => false,
            default => true,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Other => 'Other (final — no edit link)',
            self::NonResident => 'Non-Resident of Manolo Fortich (no edit link)',
            self::DuplicateApplication => 'Duplicate Application (no edit link)',
            default => $this->value,
        };
    }
}
