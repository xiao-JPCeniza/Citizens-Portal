<div>
    <x-admin.header title="Finalized Applications" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-2xl font-bold tracking-tight text-gray-900">Finalized Applications</h2>
                <p class="mt-1 text-sm text-gray-600">
                    All approved Citizen ID applications ready for final processing.
                </p>
            </div>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <button
                    type="button"
                    wire:click="openEmailModal"
                    @disabled(count($selectedApplicants) === 0)
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-50"
                    title="Send distribution email to selected applicants"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                    </svg>
                    Send Email
                    @if (count($selectedApplicants) > 0)
                        <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700">
                            {{ count($selectedApplicants) }}
                        </span>
                    @endif
                </button>
                @if (count($selectedApplicants) > 0)
                    <a
                        href="{{ route('admin.finalized.passport-zip', ['ids' => $selectedApplicants]) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50"
                        title="Download passport photos for selected applicants as a zip file"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" />
                        </svg>
                        Download Zip ID
                        <span class="rounded-full bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-700">
                            {{ count($selectedApplicants) }}
                        </span>
                    </a>
                @else
                    <span
                        class="inline-flex items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm opacity-50"
                        title="Select applicants to download passport photos"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12M12 16.5V3" />
                        </svg>
                        Download Zip ID
                    </span>
                @endif
                <a
                    href="{{ route('admin.export', array_filter([
                        'scope' => 'finalized',
                        'q' => $search,
                        'barangay' => $barangay,
                        'from' => $date_from,
                        'to' => $date_to,
                    ])) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500"
                >
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
                    placeholder="Name, email, barangay..."
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
                            No finalized applications match your filters.
                        @else
                            No finalized applications yet
                        @endif
                    </p>
                    <p class="mt-1 text-sm text-gray-500">Approved applications will appear here.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Full Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Barangay</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Blood Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date Approved</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Approved By</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Action</th>
                                <th scope="col" class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-600">
                                    <label class="inline-flex cursor-pointer items-center gap-2">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleSelectAllOnPage"
                                            @checked($this->allOnPageSelected())
                                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                            aria-label="Select all applicants on this page"
                                        >
                                    </label>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($applicants as $applicant)
                                <tr class="transition hover:bg-gray-50" wire:key="applicant-{{ $applicant->id }}">
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
                                            class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                                        >
                                            View Details
                                        </a>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-4 text-center">
                                        <input
                                            type="checkbox"
                                            wire:model.live="selectedApplicants"
                                            value="{{ $applicant->id }}"
                                            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                                            aria-label="Select {{ $applicant->full_name }}"
                                        >
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

    @if ($showEmailModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            role="dialog"
            aria-modal="true"
            aria-labelledby="distribution-email-title"
        >
            <button
                type="button"
                wire:click="closeEmailModal"
                class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"
                aria-label="Close modal"
            ></button>

            <div class="relative z-10 w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl">
                <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
                    <div>
                        <h3 id="distribution-email-title" class="text-lg font-semibold text-gray-900">Send Distribution Email</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Sending to {{ count($selectedApplicants) }} selected applicant(s).
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeEmailModal"
                        class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600"
                        aria-label="Close"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form wire:submit="sendDistributionEmail" class="space-y-4 px-6 py-5">
                    <div>
                        <label for="distribution-when" class="block text-sm font-medium text-gray-700">When</label>
                        <input
                            wire:model="when"
                            id="distribution-when"
                            type="text"
                            placeholder="e.g. June 25, 2026, 9:00 AM"
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('when')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="distribution-where" class="block text-sm font-medium text-gray-700">Where</label>
                        <input
                            wire:model="where"
                            id="distribution-where"
                            type="text"
                            placeholder="e.g. Municipal Gymnasium, Tankulan"
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                        >
                        @error('where')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="distribution-what" class="block text-sm font-medium text-gray-700">What</label>
                        <textarea
                            wire:model="what"
                            id="distribution-what"
                            rows="3"
                            placeholder="e.g. Citizen ID card distribution and claiming"
                            class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20"
                        ></textarea>
                        @error('what')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="distribution-poster" class="block text-sm font-medium text-gray-700">Poster Photo</label>
                        <input
                            wire:model="poster_photo"
                            id="distribution-poster"
                            type="file"
                            accept="image/jpeg,image/jpg,image/png"
                            class="mt-1.5 block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100"
                        >
                        <div wire:loading wire:target="poster_photo" class="mt-1 text-sm text-gray-500">Uploading poster...</div>
                        @error('poster_photo')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    @error('selectedApplicants')
                        <p class="text-sm text-red-600">{{ $message }}</p>
                    @enderror

                    <div class="flex flex-col-reverse gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:justify-end">
                        <button
                            type="button"
                            wire:click="closeEmailModal"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="sendDistributionEmail, poster_photo"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-500 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.768 59.768 0 0 1 21.485 12 59.77 59.77 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                            </svg>
                            <span wire:loading.remove wire:target="sendDistributionEmail">Send</span>
                            <span wire:loading wire:target="sendDistributionEmail">Sending...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
