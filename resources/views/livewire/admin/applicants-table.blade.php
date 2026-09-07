<div>
    <x-admin.header title="New Applicants Queue" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('success') }}
            </div>
        @endif

        @php
            $pageHasConflicts = $applicants->contains(fn ($applicant) => isset($conflictNameLookup[$applicant->full_name]));
        @endphp

        @if ($pageHasConflicts)
            <div class="mb-6 rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                <p class="font-semibold">Duplicate full name detected</p>
                <p class="mt-1">
                    One or more pending applicants share a full name with a finalized or card-delivered application.
                    Matching rows are highlighted in red — review carefully before approving.
                </p>
            </div>
        @endif

        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">New Applicants Queue</h2>
            <p class="mt-1 text-sm text-gray-600">
                Applications are processed first-in, first-out. Oldest submissions appear first.
            </p>
        </div>

        <div class="mb-6">
            <label for="applicants-search" class="sr-only">Search pending applications</label>
            <div class="relative max-w-xl">
                <svg class="pointer-events-none absolute top-1/2 left-3 h-5 w-5 -translate-y-1/2 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    id="applicants-search"
                    type="search"
                    placeholder="Search by application ID, name, email, or barangay..."
                    class="block w-full rounded-lg border border-gray-300 bg-white py-2.5 pr-4 pl-10 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                >
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @if ($applicants->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-base font-medium text-gray-900">
                        @if ($search !== '')
                            No pending applications match your search.
                        @else
                            No pending applications
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                        @if ($search !== '')
                            Try a different application ID, name, email, or barangay.
                        @else
                            New submissions will appear here for verification.
                        @endif
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Application ID</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Full Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Barangay</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date Submitted</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($applicants as $applicant)
                                @php
                                    $isDuplicateName = isset($conflictNameLookup[$applicant->full_name]);
                                @endphp
                                <tr class="transition {{ $isDuplicateName ? 'bg-red-50 hover:bg-red-100/80' : 'hover:bg-gray-50' }}">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-gray-900">{{ $applicant->application_id }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium {{ $isDuplicateName ? 'text-red-800' : 'text-gray-900' }}">
                                        <span class="inline-flex items-center gap-2">
                                            {{ $applicant->full_name }}
                                            @if ($isDuplicateName)
                                                <span class="rounded-full bg-red-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white">
                                                    Duplicate name
                                                </span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->barangay }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->created_at->format('M d, Y g:i A') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if ($applicant->awaitsDocumentCorrection())
                                            <span class="inline-flex rounded-full bg-sky-100 px-2.5 py-0.5 text-xs font-medium text-sky-800">
                                                Awaiting Documents
                                            </span>
                                        @else
                                            <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                                {{ $applicant->status->label() }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                        <a
                                            href="{{ route('admin.applications.show', $applicant) }}"
                                            class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-primary-500"
                                        >
                                            View Application
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
