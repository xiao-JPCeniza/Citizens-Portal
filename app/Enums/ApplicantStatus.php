<?php

namespace App\Enums;

enum ApplicantStatus: string
{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Verification',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }
}
