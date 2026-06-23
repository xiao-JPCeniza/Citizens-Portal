<?php

namespace App\Livewire\Admin;

use App\Services\AdminActivityLogService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin-guest')]
class Login extends Component
{
    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        if (Auth::guard('admin')->check()) {
            $this->redirect(route('admin.dashboard'), navigate: true);
        }
    }

    public function login(AdminActivityLogService $activityLogService): void
    {
        $this->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $this->ensureIsNotRateLimited();

        if (! Auth::guard('admin')->attempt(
            ['email' => $this->email, 'password' => $this->password],
            $this->remember,
        )) {
            RateLimiter::hit($this->throttleKey(), 600);

            throw ValidationException::withMessages([
                'email' => __('These credentials do not match our records.'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        session()->regenerate();

        $activityLogService->log(
            Auth::guard('admin')->user(),
            'Admin Login',
            'Administrator signed in to the portal.',
        );

        $this->redirect(route('admin.dashboard'), navigate: true);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        $minutes = (int) ceil($seconds / 60);

        throw ValidationException::withMessages([
            'email' => __('Too many login attempts. Please try again in :minutes minutes.', [
                'minutes' => $minutes,
            ]),
        ]);
    }

    protected function throttleKey(): string
    {
        return 'admin-login|'.strtolower($this->email).'|'.request()->ip();
    }

    public function render()
    {
        return view('livewire.admin.login');
    }
}
