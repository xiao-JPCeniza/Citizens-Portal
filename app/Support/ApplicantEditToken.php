<?php

namespace App\Support;

use App\Models\Applicant;

class ApplicantEditToken
{
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
            // Links do not expire; column kept nullable for production compatibility.
            'edit_token_expires_at' => null,
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
        // Correction links are issued while pending (awaiting documents).
        // Rejected + token remains valid for older archived correction links.
        if ($applicant->isApproved()) {
            return false;
        }

        if ($applicant->isPending() && blank($applicant->rejection_reason)) {
            return false;
        }

        if (! $applicant->isPending() && ! $applicant->isRejected()) {
            return false;
        }

        if (blank($applicant->edit_token_hash)) {
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
