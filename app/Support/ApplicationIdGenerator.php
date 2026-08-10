<?php

namespace App\Support;

use App\Models\Applicant;
use RuntimeException;

class ApplicationIdGenerator
{
    public static function next(): string
    {
        $latest = Applicant::query()
            ->lockForUpdate()
            ->orderByDesc('application_id')
            ->value('application_id');

        $nextNumber = $latest === null ? 1 : ((int) $latest) + 1;

        if ($nextNumber > 999999) {
            throw new RuntimeException('Maximum application ID (999999) has been reached.');
        }

        return self::format($nextNumber);
    }

    public static function format(int $number): string
    {
        return str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }
}
