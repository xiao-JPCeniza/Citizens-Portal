<?php

namespace App\Support;

use App\Models\Applicant;

class ApplicantEditToken
{
    public const EXPIRY_DAYS = 7;

    public static function generatePlainText(): string
    {
        return bin2hex(random_bytes(32));
    }

    public static function hash(string $plainToken): string
    {
        return hash_hmac('sha256', $plainToken, (string) config('app.key'));
    }

    public static function issue(Applicant $applicant): string
    {
        $plainToken = self::generatePlainText();

        $applicant->forceFill([
            'edit_token_hash' => self::hash($plainToken),
            'edit_token_expires_at' => now()->addDays(self::EXPIRY_DAYS),
        ])->save();

        return $plainToken;
    }

    public static function clear(Applicant $applicant): void
    {
        $applicant->forceFill([
            'edit_token_hash' => null,
            'edit_token_expires_at' => null,
        ])->save();
    }

    public static function isValid(Applicant $applicant, string $plainToken): bool
    {
        if (! $applicant->isRejected()) {
            return false;
        }

        if (blank($applicant->edit_token_hash) || blank($applicant->edit_token_expires_at)) {
            return false;
        }

        if ($applicant->edit_token_expires_at->isPast()) {
            return false;
        }

        if (! preg_match('/^[a-f0-9]{64}$/i', $plainToken)) {
            return false;
        }

        return hash_equals($applicant->edit_token_hash, self::hash($plainToken));
    }

    public static function url(Applicant $applicant, string $plainToken): string
    {
        return route('applications.edit', [
            'applicant' => $applicant->application_id,
            'token' => $plainToken,
        ]);
    }
}
