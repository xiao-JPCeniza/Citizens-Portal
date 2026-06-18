<div class="flex min-h-screen flex-col items-center justify-center px-4 py-12">
    <div class="mb-8 text-center">
        <x-brand-logo variant="color" class="mx-auto mb-4 h-14 w-auto sm:h-16" />
        <p class="text-xs font-medium uppercase tracking-wider text-primary-300">Municipality of Manolo Fortich</p>
        <p class="mt-2 text-sm text-gray-400">Citizen ID Application Management</p>
    </div>

    <div class="w-full max-w-md rounded-2xl border border-gray-700/60 bg-gray-800 p-8 shadow-xl">
        <h2 class="text-lg font-semibold text-white">Sign in to your account</h2>
        <p class="mt-1 text-sm text-gray-400">Only authorized administrators may access this system.</p>

        <form wire:submit="login" class="mt-6 space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300">Email</label>
                <input
                    wire:model="email"
                    id="email"
                    type="email"
                    autocomplete="username"
                    required
                    autofocus
                    class="mt-1.5 block w-full rounded-lg border border-gray-600 bg-gray-900 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                    placeholder="user@gmail.com"
                >
                @error('email')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300">Password</label>
                <input
                    wire:model="password"
                    id="password"
                    type="password"
                    autocomplete="current-password"
                    required
                    class="mt-1.5 block w-full rounded-lg border border-gray-600 bg-gray-900 px-3.5 py-2.5 text-sm text-white placeholder-gray-500 shadow-sm transition focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/30"
                >
                @error('password')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <label for="remember" class="flex cursor-pointer items-center">
                <input
                    wire:model="remember"
                    id="remember"
                    type="checkbox"
                    class="h-4 w-4 cursor-pointer rounded border-gray-500 bg-gray-700 accent-primary-400 focus:ring-2 focus:ring-primary-500/30 focus:ring-offset-2 focus:ring-offset-gray-800"
                >
                <span class="ml-2 text-sm text-gray-400">Remember me</span>
            </label>

            <button
                type="submit"
                wire:loading.attr="disabled"
                class="flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-gray-800 disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="login">Sign In</span>
                <span wire:loading wire:target="login">Signing in...</span>
            </button>
        </form>
    </div>

    <a href="{{ route('welcome') }}" class="mt-8 text-sm text-gray-500 transition hover:text-gray-300">
        &larr; Back to Public Portal
    </a>
</div>
