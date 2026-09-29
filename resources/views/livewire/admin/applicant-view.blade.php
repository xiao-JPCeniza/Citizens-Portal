<div>
    <x-admin.header title="Review Application" />

    <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <div class="mb-6">
            @if ($applicant->isRejected() || $applicant->isCardDelivered())
                <a href="{{ route('admin.archive.index') }}" class="text-sm font-medium text-primary-700 transition hover:text-primary-800">
                    &larr; Back to Archive
                </a>
            @elseif ($applicant->isVerified())
                <a href="{{ route('admin.approved.index') }}" class="text-sm font-medium text-primary-700 transition hover:text-primary-800">
                    &larr; Back to Approved Applications
                </a>
            @elseif ($applicant->isApproved())
                <a href="{{ route('admin.finalized.index') }}" class="text-sm font-medium text-primary-700 transition hover:text-primary-800">
                    &larr; Back to Verification Dashboard
                </a>
            @else
                <a href="{{ route('admin.applications.index') }}" class="text-sm font-medium text-primary-700 transition hover:text-primary-800">
                    &larr; Back to New Applicants
                </a>
            @endif
        </div>

        <div class="mb-8">
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Review Application</h2>
            <p class="mt-1 text-sm text-gray-600">
                Application ID {{ $applicant->application_id }} — {{ $applicant->full_name }} — {{ $applicant->email }}
            </p>
        </div>

        @error('applicant')
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ $message }}
            </div>
        @enderror

        @if ($applicant->isForVerification())
            <div class="mb-6 rounded-xl border border-primary-200 bg-primary-50 px-4 py-3 text-sm text-primary-800">
                This application was accepted and is awaiting final verification.
                <span class="font-medium">{{ $applicant->stageLabel() }}</span>
                @if ($applicant->verified_at)
                    since {{ $applicant->verified_at->format('F d, Y g:i A') }}.
                @endif
            </div>
        @elseif (! $applicant->isPending())
            <div class="mb-6 rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                This application has already been processed.
                <span class="font-medium">{{ $applicant->stageLabel() }}</span>
                @if ($applicant->verified_at)
                    on {{ $applicant->verified_at->format('F d, Y g:i A') }}.
                @endif
            </div>
        @endif

        @if ($applicant->isRejected())
            <section class="mb-8 rounded-xl border border-red-200 bg-red-50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-red-900">Rejection History</h3>
                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-red-700">Rejection Reason</dt>
                        <dd class="mt-1 text-sm text-red-900">{{ $applicant->rejection_reason ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-red-700">Rejected By</dt>
                        <dd class="mt-1 text-sm text-red-900">{{ $applicant->verifier?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-red-700">Date Rejected</dt>
                        <dd class="mt-1 text-sm text-red-900">{{ $applicant->verified_at?->format('F d, Y g:i A') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>
        @elseif ($applicant->awaitsDocumentCorrection())
            <section class="mb-8 rounded-xl border border-amber-200 bg-amber-50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-amber-900">Awaiting Required Documents</h3>
                <p class="mt-1 text-sm text-amber-800">
                    This application remains pending. The applicant was emailed a secure link to submit the required corrections.
                </p>
                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-amber-700">Requested Correction</dt>
                        <dd class="mt-1 text-sm text-amber-900">{{ $applicant->rejection_reason ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-amber-700">Requested By</dt>
                        <dd class="mt-1 text-sm text-amber-900">{{ $applicant->verifier?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-amber-700">Date Requested</dt>
                        <dd class="mt-1 text-sm text-amber-900">{{ $applicant->verified_at?->format('F d, Y g:i A') ?? '—' }}</dd>
                    </div>
                </dl>
            </section>
        @endif

        @if ($applicant->isApproved())
            <section class="mb-8 rounded-xl border border-accent-200 bg-accent-50 p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-accent-900">{{ $applicant->isForVerification() ? 'Acceptance Details' : 'Approval Details' }}</h3>
                <dl class="mt-4 space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-accent-700">{{ $applicant->isForVerification() ? 'Accepted By' : 'Approved By' }}</dt>
                        <dd class="mt-1 text-sm text-accent-900">{{ $applicant->verifier?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-accent-700">{{ $applicant->isForVerification() ? 'Date Accepted' : 'Date Approved' }}</dt>
                        <dd class="mt-1 text-sm text-accent-900">{{ $applicant->verified_at?->format('F d, Y g:i A') ?? '—' }}</dd>
                    </div>
                    @if ($applicant->isCardDelivered())
                        <div>
                            <dt class="text-xs font-semibold uppercase tracking-wider text-accent-700">Card Status</dt>
                            <dd class="mt-1 text-sm font-semibold text-accent-900">Card Delivered (archived)</dd>
                        </div>
                    @endif
                </dl>
            </section>
        @endif

        <div class="grid gap-8 lg:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Applicant Information</h3>
                <dl class="mt-6 space-y-4">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Application ID</dt>
                        <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $applicant->application_id }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">First Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->first_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Middle Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->middle_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Last Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->last_name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Birthday</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->birthday?->format('F d, Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Email Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">GCash Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->gcash_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Barangay</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->barangay }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Complete Address</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->address }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Blood Type</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->blood_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Emergency Contact Person</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->emergency_contact_person }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Emergency Contact Number</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->emergency_contact_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-gray-500">Submission Date</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $applicant->created_at->format('F d, Y g:i A') }}</dd>
                    </div>
                </dl>
            </section>

            <section class="space-y-6">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">Passport Photo Preview</h3>
                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                        @if ($applicant->passportPhotoUrl())
                            <img
                                src="{{ $applicant->passportPhotoUrl() }}"
                                alt="Passport photo of {{ $applicant->full_name }}"
                                class="mx-auto max-h-80 w-full object-contain"
                            >
                        @else
                            <p class="px-4 py-8 text-center text-sm text-gray-500">No passport photo uploaded.</p>
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="text-lg font-semibold text-gray-900">GCash Screenshot Preview</h3>
                    <div class="mt-4 overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                        @if ($applicant->gcashScreenshotUrl())
                            @if ($applicant->isGcashPdf())
                                <div class="flex flex-col items-center gap-3 px-4 py-8">
                                    <svg class="h-12 w-12 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    <a
                                        href="{{ $applicant->gcashScreenshotUrl() }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="text-sm font-medium text-primary-700 hover:text-primary-800"
                                    >
                                        Open GCash Screenshot (PDF)
                                    </a>
                                </div>
                            @else
                                <img
                                    src="{{ $applicant->gcashScreenshotUrl() }}"
                                    alt="GCash screenshot of {{ $applicant->full_name }}"
                                    class="mx-auto max-h-80 w-full object-contain"
                                >
                            @endif
                        @else
                            <p class="px-4 py-8 text-center text-sm text-gray-500">No GCash screenshot uploaded.</p>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        @if ($applicant->isPending())
            <section class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Verify Application</h3>
                <p class="mt-1 text-sm text-gray-600">Accept or reject this application after reviewing all details and documents. Accepted applications move to the Verification Dashboard.</p>

                @if ($showRejectForm)
                    <form wire:submit="reject" class="mt-6 space-y-5">
                        <div>
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason</label>
                            <select
                                wire:model="rejection_reason"
                                id="rejection_reason"
                                class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                            >
                                <option value="">Select a reason</option>
                                @foreach ($rejectionReasons as $reason)
                                    <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                                @endforeach
                            </select>
                            @error('rejection_reason')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-xs text-gray-500">
                                Document-related reasons keep the application pending and email a correction link.
                                Reasons marked &ldquo;no edit link&rdquo; send a final rejection and move the application to Archive.
                            </p>
                        </div>

                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                            <p class="mt-0.5 text-xs text-gray-500">Optional comments. Required when selecting Other.</p>
                            <textarea
                                wire:model="remarks"
                                id="remarks"
                                rows="4"
                                class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                                placeholder="Additional comments for the applicant..."
                            ></textarea>
                            @error('remarks')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="reject"
                                class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="reject">Reject Application</span>
                                <span wire:loading wire:target="reject">Rejecting...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="cancelReject"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            wire:click="approve"
                            wire:confirm="Accept this application and move it to the Verification Dashboard?"
                            wire:loading.attr="disabled"
                            wire:target="approve"
                            class="inline-flex items-center rounded-lg bg-accent-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-accent-500 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="approve">Accept Application</span>
                            <span wire:loading wire:target="approve">Accepting...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="showReject"
                            class="inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50"
                        >
                            Reject Application
                        </button>
                    </div>
                @endif
            </section>
        @endif

        @if ($applicant->isForVerification())
            <section class="mt-8 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                <h3 class="text-lg font-semibold text-gray-900">Final Verification</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Approve to move this application to Approved Applications, return it to the applicant for correction, or reject it.
                </p>

                @if ($showReturnForm)
                    <form wire:submit="returnApplication" class="mt-6 space-y-5">
                        <div>
                            <label for="return_reason" class="block text-sm font-medium text-gray-700">What needs to be corrected?</label>
                            <select
                                wire:model="rejection_reason"
                                id="return_reason"
                                class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                            >
                                <option value="">Select a reason</option>
                                @foreach ($returnReasons as $reason)
                                    <option value="{{ $reason->value }}">{{ $reason->label() }}</option>
                                @endforeach
                            </select>
                            @error('rejection_reason')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-xs text-gray-500">
                                The applicant will be emailed your remarks and a secure link to submit the correction.
                                The application moves back to New Applicants until they resubmit.
                            </p>
                        </div>

                        <div>
                            <label for="return_remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                            <p class="mt-0.5 text-xs text-gray-500">Required. Explain to the applicant what needs to be corrected.</p>
                            <textarea
                                wire:model="remarks"
                                id="return_remarks"
                                rows="4"
                                class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20"
                                placeholder="Remarks for the applicant..."
                            ></textarea>
                            @error('remarks')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="returnApplication"
                                class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-500 disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="returnApplication">Return Application</span>
                                <span wire:loading wire:target="returnApplication">Returning...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="cancelReject"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                @elseif ($showRejectForm)
                    <form wire:submit="reject" class="mt-6 space-y-5">
                        <div>
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection Reason</label>
                            <select
                                wire:model="rejection_reason"
                                id="rejection_reason"
                                class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                            >
                                <option value="">Select a reason</option>
                                @foreach ($finalRejectionReasons as $reason)
                                    <option value="{{ $reason->value }}">{{ $reason->value }}</option>
                                @endforeach
                            </select>
                            @error('rejection_reason')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1.5 text-xs text-gray-500">
                                Rejection is final. The applicant is notified and the application moves to Archive.
                            </p>
                        </div>

                        <div>
                            <label for="remarks" class="block text-sm font-medium text-gray-700">Remarks</label>
                            <p class="mt-0.5 text-xs text-gray-500">Optional comments. Required when selecting Other.</p>
                            <textarea
                                wire:model="remarks"
                                id="remarks"
                                rows="4"
                                class="mt-1.5 block w-full rounded-lg border border-gray-300 bg-white px-3.5 py-2.5 text-sm text-gray-900 shadow-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-500/20"
                                placeholder="Additional comments for the applicant..."
                            ></textarea>
                            @error('remarks')
                                <p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex flex-wrap gap-3">
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                wire:target="reject"
                                class="inline-flex items-center rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-red-500 disabled:opacity-60"
                            >
                                <span wire:loading.remove wire:target="reject">Reject Application</span>
                                <span wire:loading wire:target="reject">Rejecting...</span>
                            </button>
                            <button
                                type="button"
                                wire:click="cancelReject"
                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                @else
                    <div class="mt-6 flex flex-wrap gap-3">
                        <button
                            type="button"
                            wire:click="verify"
                            wire:confirm="Approve this application and move it to Approved Applications?"
                            wire:loading.attr="disabled"
                            wire:target="verify"
                            class="inline-flex items-center rounded-lg bg-accent-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-accent-500 disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="verify">Approve Application</span>
                            <span wire:loading wire:target="verify">Approving...</span>
                        </button>
                        <button
                            type="button"
                            wire:click="showReturn"
                            class="inline-flex items-center rounded-lg border border-amber-300 bg-white px-4 py-2.5 text-sm font-semibold text-amber-700 transition hover:bg-amber-50"
                        >
                            Return Application
                        </button>
                        <button
                            type="button"
                            wire:click="showReject"
                            class="inline-flex items-center rounded-lg border border-red-300 bg-white px-4 py-2.5 text-sm font-semibold text-red-700 transition hover:bg-red-50"
                        >
                            Reject Application
                        </button>
                    </div>
                @endif
            </section>
        @endif
    </main>
</div>
