<div>
    <x-admin.header title="Verification Dashboard" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Verification Dashboard</h2>
            <p class="mt-1 text-sm text-gray-600">
                Accepted Citizen ID applications awaiting final verification. Review each application to approve, return, or reject it.
            </p>
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
