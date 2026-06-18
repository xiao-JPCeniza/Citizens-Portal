<?php

namespace App\Services;

use App\Mail\ApplicationEmailOtpMail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class ApplicationEmailOtpService
{
    private const OTP_TTL_SECONDS = 600;

    public function send(string $email): void
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $code = (string) random_int(100000, 999999);

        Cache::put($this->cacheKey($normalizedEmail), $code, self::OTP_TTL_SECONDS);

        Mail::to($normalizedEmail)->send(new ApplicationEmailOtpMail($code));
    }

    public function verify(string $email, string $code): bool
    {
        $normalizedEmail = $this->normalizeEmail($email);
        $stored = Cache::get($this->cacheKey($normalizedEmail));

        if (! is_string($stored) || ! hash_equals($stored, $code)) {
            return false;
        }

        Cache::forget($this->cacheKey($normalizedEmail));

        return true;
    }

    protected function cacheKey(string $email): string
    {
        return 'application_email_otp:'.$this->normalizeEmail($email);
    }

    protected function normalizeEmail(string $email): string
    {
        return strtolower(trim($email));
    }
}
