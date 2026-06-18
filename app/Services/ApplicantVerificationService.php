<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationRejectedMail;
use App\Models\Admin;
use App\Models\Applicant;
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
            $applicant->update([
                'status' => ApplicantStatus::Approved,
                'verified_by' => $admin->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ]);

            Mail::to($applicant->email)->send(new ApplicationApprovedMail($applicant));

            $this->activityLogService->log(
                $admin,
                'Application Approved',
                "Approved application for {$applicant->full_name}.",
            );

            return $applicant->fresh();
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
            $applicant->update([
                'status' => ApplicantStatus::Rejected,
                'rejection_reason' => $fullRemarks,
                'verified_by' => $admin->id,
                'verified_at' => now(),
            ]);

            Mail::to($applicant->email)->send(new ApplicationRejectedMail(
                $applicant,
                $reason->value,
                $trimmedRemarks !== '' ? $trimmedRemarks : null,
            ));

            $this->activityLogService->log(
                $admin,
                'Application Rejected',
                "Rejected application for {$applicant->full_name}. Reason: {$fullRemarks}",
            );

            return $applicant->fresh();
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
