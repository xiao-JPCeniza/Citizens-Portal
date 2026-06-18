<?php

namespace App\Services;

use App\Mail\DistributionEventMail;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;

class DistributionEmailService
{
    public function __construct(
        private AdminActivityLogService $activityLogService,
    ) {}

    /**
     * @param  Collection<int, Applicant>  $applicants
     */
    public function send(
        Collection $applicants,
        Admin $admin,
        string $when,
        string $where,
        string $what,
        string $posterPath,
    ): int {
        $sent = 0;

        foreach ($applicants as $applicant) {
            Mail::to($applicant->email)->send(new DistributionEventMail(
                $applicant,
                $when,
                $where,
                $what,
                $posterPath,
            ));

            $sent++;
        }

        $this->activityLogService->log(
            $admin,
            'Distribution Email Sent',
            "Sent distribution event email to {$sent} applicant(s). When: {$when}. Where: {$where}.",
        );

        return $sent;
    }
}
