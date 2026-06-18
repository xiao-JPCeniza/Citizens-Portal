<div>
    <x-admin.header title="New Applicants Queue" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        @if (session('success'))
            <div class="mb-6 rounded-xl border border-accent-200 bg-accent-50 px-4 py-3 text-sm text-accent-800">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">New Applicants Queue</h2>
            <p class="mt-1 text-sm text-gray-600">
                Applications are processed first-in, first-out. Oldest submissions appear first.
            </p>
        </div>

        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            @if ($applicants->isEmpty())
                <div class="px-6 py-16 text-center">
                    <p class="text-base font-medium text-gray-900">No pending applications</p>
                    <p class="mt-1 text-sm text-gray-500">New submissions will appear here for verification.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Full Name</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Barangay</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Date Submitted</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($applicants as $applicant)
                                <tr class="transition hover:bg-gray-50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ $applicant->full_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->barangay }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-700">{{ $applicant->created_at->format('M d, Y g:i A') }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <span class="inline-flex rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                                            {{ $applicant->status->label() }}
                                        </span>
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
