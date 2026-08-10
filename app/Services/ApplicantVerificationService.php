<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Mail\ApplicationApprovedMail;
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
                Mail::to($fresh->email)->send(new ApplicationApprovedMail($fresh));
            });

            $this->activityLogService->log(
                $admin,
                'Application Approved',
                "Approved application for {$fresh->full_name}.",
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
        $this->ensurePending($applicant);

        $fullRemarks = $this->formatRejectionRemarks($reason, $remarks);
        $trimmedRemarks = trim((string) $remarks);

        return DB::transaction(function () use ($applicant, $admin, $reason, $trimmedRemarks, $fullRemarks): Applicant {
            $applicant->forceFill([
                'status' => ApplicantStatus::Rejected,
                'rejection_reason' => $fullRemarks,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'edit_token_hash' => null,
                'edit_token_expires_at' => null,
            ])->save();

            $fresh = $applicant->fresh();
            $editUrl = null;

            if ($reason->allowsEditLink()) {
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

            $this->activityLogService->log(
                $admin,
                'Application Rejected',
                "Rejected application for {$fresh->full_name}. Reason: {$fullRemarks}",
            );

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
}
