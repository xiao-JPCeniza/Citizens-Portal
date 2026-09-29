<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationForVerificationMail;
use App\Mail\ApplicationRejectedMail;
use App\Models\Admin;
use App\Models\Applicant;
use App\Support\ApplicantEditToken;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ApplicantVerificationService
{
    public function __construct(
        private AdminActivityLogService $activityLogService,
    ) {}

    /**
     * Accept a pending application and move it to the Verification Dashboard.
     */
    public function approve(Applicant $applicant, Admin $admin): Applicant
    {
        $this->ensurePending($applicant);

        return DB::transaction(function () use ($applicant, $admin): Applicant {
            $applicant->forceFill([
                'status' => ApplicantStatus::Approved,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rejection_reason' => null,
                'edit_token_hash' => null,
                'edit_token_expires_at' => null,
            ])->save();

            $fresh = $applicant->fresh();

            DB::afterCommit(function () use ($fresh): void {
                Mail::to($fresh->email)->send(new ApplicationForVerificationMail($fresh));
            });

            $this->activityLogService->log(
                $admin,
                'Application Accepted',
                "Accepted application for {$fresh->full_name} and moved it to the Verification Dashboard.",
            );

            return $fresh;
        });
    }

    /**
     * Final approval of an application on the Verification Dashboard.
     */
    public function verify(Applicant $applicant, Admin $admin): Applicant
    {
        $this->ensureForVerification($applicant);

        return DB::transaction(function () use ($applicant, $admin): Applicant {
            $applicant->forceFill([
                'rejection_reason' => Applicant::VERIFIED_REASON,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'edit_token_hash' => null,
                'edit_token_expires_at' => null,
            ])->save();

            $fresh = $applicant->fresh();

            DB::afterCommit(function () use ($fresh): void {
                Mail::to($fresh->email)->send(new ApplicationApprovedMail($fresh));
            });

            $this->activityLogService->log(
                $admin,
                'Application Verified',
                "Verified and approved application for {$fresh->full_name}.",
            );

            return $fresh;
        });
    }

    public function reject(
        Applicant $applicant,
        Admin $admin,
        RejectionReason $reason,
        ?string $remarks = null,
    ): Applicant {
        $this->ensurePendingOrForVerification($applicant);

        $fromVerification = $applicant->isForVerification();
        $fullRemarks = $this->formatRejectionRemarks($reason, $remarks);
        $trimmedRemarks = trim((string) $remarks);

        return DB::transaction(function () use ($applicant, $admin, $reason, $trimmedRemarks, $fullRemarks, $fromVerification): Applicant {
            $awaitsDocuments = $reason->allowsEditLink();

            $applicant->forceFill([
                // Correctable reasons keep the application pending until documents are resubmitted.
                // Final reasons archive the application as Rejected.
                'status' => $awaitsDocuments ? ApplicantStatus::Pending : ApplicantStatus::Rejected,
                'rejection_reason' => $fullRemarks,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'edit_token_hash' => null,
                'edit_token_expires_at' => null,
            ])->save();

            $fresh = $applicant->fresh();
            $editUrl = null;

            if ($awaitsDocuments) {
                $plainToken = ApplicantEditToken::issue($fresh);
                $editUrl = ApplicantEditToken::url($fresh->fresh(), $plainToken);
                $fresh = $fresh->fresh();
            }

            DB::afterCommit(function () use ($fresh, $reason, $trimmedRemarks, $editUrl): void {
                Mail::to($fresh->email)->send(new ApplicationRejectedMail(
                    $fresh,
                    $reason->value,
                    $trimmedRemarks !== '' ? $trimmedRemarks : null,
                    $editUrl,
                ));
            });

            if ($awaitsDocuments) {
                $action = $fromVerification ? 'Application Returned' : 'Documents Requested';
                $description = $fromVerification
                    ? "Returned application for {$fresh->full_name} from verification. Reason: {$fullRemarks}"
                    : "Requested corrected documents for {$fresh->full_name}. Reason: {$fullRemarks}";
            } else {
                $action = 'Application Rejected';
                $description = "Rejected application for {$fresh->full_name}. Reason: {$fullRemarks}";
            }

            $this->activityLogService->log($admin, $action, $description);

            return $fresh;
        });
    }

    public function formatRejectionRemarks(RejectionReason $reason, ?string $remarks): string
    {
        $remarks = trim((string) $remarks);

        if ($remarks === '') {
            return $reason->value;
        }

        return "{$reason->value}: {$remarks}";
    }

    protected function ensurePending(Applicant $applicant): void
    {
        if ($applicant->status !== ApplicantStatus::Pending) {
            throw ValidationException::withMessages([
                'applicant' => 'This application has already been processed.',
            ]);
        }
    }

    protected function ensureForVerification(Applicant $applicant): void
    {
        if (! $applicant->isForVerification()) {
            throw ValidationException::withMessages([
                'applicant' => 'This application is not awaiting verification.',
            ]);
        }
    }

    protected function ensurePendingOrForVerification(Applicant $applicant): void
    {
        if (! $applicant->isPending() && ! $applicant->isForVerification()) {
            throw ValidationException::withMessages([
                'applicant' => 'This application has already been processed.',
            ]);
        }
    }
}
