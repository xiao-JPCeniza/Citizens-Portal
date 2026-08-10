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

    /**
     * Resolve the selected reason from a stored rejection_reason value
     * ("Reason" or "Reason: remarks").
     */
    public static function fromStored(?string $stored): ?self
    {
        if (blank($stored)) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($stored === $case->value || str_starts_with($stored, $case->value.':')) {
                return $case;
            }
        }

        return null;
    }

    public function requiresPassportCorrection(): bool
    {
        return match ($this) {
            self::InvalidPassportPhoto,
            self::UnreadableDocuments => true,
            default => false,
        };
    }

    public function requiresGcashCorrection(): bool
    {
        return match ($this) {
            self::InvalidGcashScreenshot,
            self::UnreadableDocuments => true,
            default => false,
        };
    }

    public function allowsInformationCorrection(): bool
    {
        return $this === self::IncompleteInformation;
    }
}
