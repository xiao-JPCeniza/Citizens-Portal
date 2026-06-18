<?php

namespace App\Livewire\Public;

use App\Services\ApplicationEmailOtpService;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class EmailVerification extends Component
{
    public string $email = '';

    public string $otp = '';

    public bool $otpSent = false;

    public function mount(): void
    {
        if (request()->boolean('terms_accepted')) {
            session(['terms_accepted' => true]);
        }

        if (! session('terms_accepted')) {
            session()->flash('error', 'Please accept the Terms and Conditions before applying.');
            $this->redirect(route('welcome'), navigate: false);
        }
    }

    public function sendOtp(ApplicationEmailOtpService $otpService): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
        ]);

        $this->ensureSendIsNotRateLimited();

        $otpService->send($this->email);

        RateLimiter::hit($this->sendThrottleKey(), 60);

        $this->otpSent = true;
        $this->otp = '';
        session()->flash('status', 'A verification code has been sent to your email address.');
    }

    public function verifyOtp(ApplicationEmailOtpService $otpService): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'otp' => ['required', 'string', 'digits:6'],
        ]);

        $this->ensureVerifyIsNotRateLimited();

        if (! $otpService->verify($this->email, $this->otp)) {
            RateLimiter::hit($this->verifyThrottleKey(), 60);

            throw ValidationException::withMessages([
                'otp' => 'The verification code is invalid or has expired.',
            ]);
        }

        RateLimiter::clear($this->verifyThrottleKey());
        RateLimiter::clear($this->sendThrottleKey());

        session(['application_verified_email' => strtolower(trim($this->email))]);

        $this->redirect(route('apply'), navigate: false);
    }

    public function changeEmail(): void
    {
        $this->otpSent = false;
        $this->otp = '';
    }

    protected function ensureSendIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->sendThrottleKey(), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->sendThrottleKey());

        throw ValidationException::withMessages([
            'email' => __('Too many code requests. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    protected function ensureVerifyIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->verifyThrottleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->verifyThrottleKey());

        throw ValidationException::withMessages([
            'otp' => __('Too many verification attempts. Please try again in :seconds seconds.', [
                'seconds' => $seconds,
            ]),
        ]);
    }

    protected function sendThrottleKey(): string
    {
        return 'application-otp-send|'.strtolower($this->email).'|'.request()->ip();
    }

    protected function verifyThrottleKey(): string
    {
        return 'application-otp-verify|'.strtolower($this->email).'|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.public.email-verification')
            ->title('Verify Your Email');
    }
}
