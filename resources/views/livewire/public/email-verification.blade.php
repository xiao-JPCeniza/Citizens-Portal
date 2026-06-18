<div>
    {{-- Header --}}
    <header class="mf-header">
        <div class="mx-auto flex max-w-5xl flex-col gap-4 px-4 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-4">
                <x-brand-logo variant="color" class="h-11 w-auto sm:h-12" />
                <div>
                    <p class="mf-header-subtitle text-xs font-medium uppercase tracking-wider">Municipality of Manolo Fortich</p>
                    <p class="mf-header-subtitle mt-0.5 text-sm">Province of Bukidnon, Philippines</p>
                </div>
            </div>
            <a href="{{ route('welcome') }}" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-medium text-gray-100 backdrop-blur-sm transition hover:bg-white/20">
                &larr; Back to Welcome
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-2xl px-4 py-10 sm:px-6 sm:py-12">
        <section class="mb-8 text-center">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary-700">Step 2 of 3</p>
            <h2 class="text-3xl font-bold tracking-tight text-gray-900">Verify Your Email</h2>
            <p class="mx-auto mt-4 max-w-xl text-base leading-relaxed text-gray-600">
                Enter your email address to receive a one-time verification code before continuing to the application form.
            </p>
        </section>

        @if (session('status'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('status') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            @if (! $otpSent)
                <form wire:submit="sendOtp" class="space-y-6">
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                        <input
                            wire:model="email"
                            type="email"
                            id="email"
                            autocomplete="email"
                            autofocus
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('email') border-red-400 @enderror"
                            placeholder="you@example.com"
                        >
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-primary-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:opacity-60"
                    >
                        <span wire:loading.remove wire:target="sendOtp">Send Verification Code</span>
                        <span wire:loading wire:target="sendOtp">Sending...</span>
                    </button>
                </form>
            @else
                <div class="mb-6 rounded-xl border border-primary-100 bg-primary-50 px-4 py-3 text-sm text-primary-900">
                    A 6-digit code was sent to <strong>{{ $email }}</strong>.
                    <button type="button" wire:click="changeEmail" class="ml-1 font-semibold text-primary-700 underline decoration-primary-700/40 underline-offset-2 hover:text-primary-800">
                        Use a different email
                    </button>
                </div>

                <form wire:submit="verifyOtp" class="space-y-6">
                    <div>
                        <label for="otp" class="mb-1.5 block text-sm font-medium text-gray-700">Verification Code <span class="text-red-500">*</span></label>
                        <input
                            wire:model="otp"
                            type="text"
                            id="otp"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="6"
                            autofocus
                            class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-center text-lg tracking-[0.35em] shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('otp') border-red-400 @enderror"
                            placeholder="000000"
                        >
                        @error('otp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="flex flex-col gap-3 sm:flex-row">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-xl bg-primary-700 px-6 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="verifyOtp">Verify &amp; Continue</span>
                            <span wire:loading wire:target="verifyOtp">Verifying...</span>
                        </button>

                        <button
                            type="button"
                            wire:click="sendOtp"
                            wire:loading.attr="disabled"
                            class="inline-flex flex-1 items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="sendOtp">Resend Code</span>
                            <span wire:loading wire:target="sendOtp">Sending...</span>
                        </button>
                    </div>
                </form>
            @endif
        </section>
    </main>
</div>
