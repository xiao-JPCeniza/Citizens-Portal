<div>
    <x-admin.header title="Citizen ID Dashboard" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">Dashboard</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Welcome back, {{ auth('admin')->user()->name }}. Overview of Citizen ID applications.
                </p>
            </div>
            <a
                href="{{ route('admin.export') }}"
                class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
            >
                Export to Excel
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-amber-800">Pending Applications</p>
                <p class="mt-2 text-3xl font-bold text-amber-900">{{ number_format($stats['pending']) }}</p>
            </div>

            <div class="rounded-xl border border-accent-200 bg-accent-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-accent-800">Approved Applications</p>
                <p class="mt-2 text-3xl font-bold text-accent-900">{{ number_format($stats['approved']) }}</p>
            </div>

            <div class="rounded-xl border border-red-200 bg-red-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-red-800">Rejected Applications</p>
                <p class="mt-2 text-3xl font-bold text-red-900">{{ number_format($stats['rejected']) }}</p>
            </div>

            <div class="rounded-xl border border-gray-300 bg-gray-50 p-5 shadow-sm">
                <p class="text-sm font-medium text-gray-700">Archived Applications</p>
                <p class="mt-2 text-3xl font-bold text-gray-900">{{ number_format($stats['archived']) }}</p>
            </div>

            <div class="rounded-xl border border-primary-200 bg-primary-50 p-5 shadow-sm sm:col-span-2 lg:col-span-1">
                <p class="text-sm font-medium text-primary-800">Total Applications</p>
                <p class="mt-2 text-3xl font-bold text-primary-900">{{ number_format($stats['total']) }}</p>
            </div>
        </div>

        @if ($stats['pending'] > 0)
            <div class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Applications awaiting verification</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ number_format($stats['pending']) }} application(s) in the verification queue.
                        </p>
                    </div>
                    <a
                        href="{{ route('admin.applications.index') }}"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                    >
                        Review Queue
                    </a>
                </div>
            </div>
        @endif
    </main>
</div>
