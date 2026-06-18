<div>
    <x-admin.header title="Archive" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Archive</h2>
            <p class="mt-1 text-sm text-gray-600">
                Rejected applications are stored here. Search and review rejection history.
            </p>
        </div>

        <div class="mb-6">
            <label for="archive-search" class="sr-only">Search archived applications</label>
            <div class="relative max-w-xl">
                <svg class="pointer-events-none absolute top-1/2 left-3 h-5 w-5 -trangray-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    id="archive-search"
                    type="search"
                    placeholder="Search by name, email, barangay, or reason..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @if ($applicants->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-base font-medium text-gray-900">
                        @if ($search !== '')
                            No archived applications match your search.
                        @else
                            No archived applications
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        Rejected applications will appear here automatically.
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Full Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Barangay</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date Rejected</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Rejection Reason</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($applicants as $applicant)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $applicant->full_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->barangay }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">
                                        {{ $applicant->verified_at?->format('M d, Y g:i A') ?? '—' }}
                                    </td>
                                    <td class="max-w-xs truncate px-6 py-4 text-sm text-gray-700" title="{{ $applicant->rejection_reason }}">
                                        {{ $applicant->rejection_reason ?? '—' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a
                                            href="{{ route('admin.applications.show', $applicant) }}"
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                        >
                                            Review History
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
