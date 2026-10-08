<div>
    <x-admin.header title="Verification Dashboard" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">Verification Dashboard</h2>
                <p class="mt-1 text-sm text-gray-600">
                    Accepted Citizen ID applications awaiting final verification. Review each application to approve, return, or reject it.
                </p>
            </div>
            <div class="flex shrink-0 flex-col gap-3 sm:flex-row sm:items-center">
                <button
                    type="button"
                    wire:click="approvePage(@js($applicants->pluck('id')->all()))"
                    wire:confirm="Approve all {{ $applicants->count() }} application(s) on this page and move them to Approved Applications? Each applicant will receive an approval email."
                    wire:loading.attr="disabled"
                    wire:target="approvePage"
                    @disabled($applicants->isEmpty())
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-accent-600 bg-accent-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-accent-700 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span wire:loading.remove wire:target="approvePage">Approve All on Page</span>
                    <span wire:loading wire:target="approvePage">Approving...</span>
                    @if ($applicants->isNotEmpty())
                        <span wire:loading.remove wire:target="approvePage" class="rounded-full bg-white/20 px-2 py-0.5 text-xs font-semibold text-white">
                            {{ $applicants->count() }}
                        </span>
                    @endif
                </button>
                <a
                    href="{{ route('admin.export', array_filter([
                        'scope' => 'finalized',
                        'format' => 'compilation',
                        'q' => $search,
                        'barangay' => $barangay,
                        'from' => $date_from,
                        'to' => $date_to,
                    ])) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" />
                    </svg>
                    Export for Compiling
                </a>
                <a
                    href="{{ route('admin.export', array_filter([
                        'scope' => 'finalized',
                        'q' => $search,
                        'barangay' => $barangay,
                        'from' => $date_from,
                        'to' => $date_to,
                    ])) }}"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" />
                    </svg>
                    Export to Excel
                </a>
            </div>
        </div>

        <div class="mb-6 grid gap-4 rounded-xl border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-4">
            <div class="sm:col-span-2">
                <label for="finalized-search" class="block text-sm font-medium text-gray-700">Search</label>
                <input
                    wire:model.live.debounce.300ms="search"
                    id="finalized-search"
                    type="search"
                    placeholder="Application ID, name, email, barangay..."
                    class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
            </div>

            <div>
                <label for="finalized-barangay" class="block text-sm font-medium text-gray-700">Barangay</label>
                <select
                    wire:model.live="barangay"
                    id="finalized-barangay"
                    class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
                    <option value="">All barangays</option>
                    @foreach ($barangays as $name)
                        <option value="{{ $name }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="finalized-from" class="block text-sm font-medium text-gray-700">Date From</label>
                    <input
                        wire:model.live="date_from"
                        id="finalized-from"
                        type="date"
                        class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                    >
                </div>
                <div>
                    <label for="finalized-to" class="block text-sm font-medium text-gray-700">Date To</label>
                    <input
                        wire:model.live="date_to"
                        id="finalized-to"
                        type="date"
                        class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                    >
                </div>
            </div>

            @if ($search !== '' || $barangay !== '' || $date_from !== '' || $date_to !== '')
                <div class="flex items-end sm:col-span-2 lg:col-span-4">
                    <button
                        type="button"
                        wire:click="clearFilters"
                        class="text-sm font-medium text-gray-600 transition hover:text-gray-900"
                    >
                        Clear filters
                    </button>
                </div>
            @endif
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @if ($applicants->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-base font-medium text-gray-900">
                        @if ($search !== '' || $barangay !== '' || $date_from !== '' || $date_to !== '')
                            No applications for verification match your filters.
                        @else
                            No applications awaiting verification
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500">Applications accepted in New Applicants will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Application ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Full Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Barangay</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Blood Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date Accepted</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Accepted By</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($applicants as $applicant)
                                <tr class="transition hover:bg-gray-50" wire:key="applicant-{{ $applicant->id }}">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">{{ $applicant->application_id }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $applicant->full_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->barangay }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->blood_type }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $applicant->verified_at?->format('M d, Y g:i A') ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->verifier?->name ?? '—' }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a
                                            href="{{ route('admin.applications.show', $applicant) }}"
                                            class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                                        >
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <x-admin.table-pagination :paginator="$applicants" />
            @endif
        </div>
    </main>
</div>
