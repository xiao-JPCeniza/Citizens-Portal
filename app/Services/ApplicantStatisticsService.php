<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;

class ApplicantStatisticsService
{
    /**
     * @return array{
     *     pending: int,
     *     for_verification: int,
     *     approved: int,
     *     rejected: int,
     *     archived: int,
     *     total: int
     * }
     */
    public function getSummary(): array
    {
        $counts = Applicant::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $pending = (int) ($counts[ApplicantStatus::Pending->value] ?? 0);
        $approvedTotal = (int) ($counts[ApplicantStatus::Approved->value] ?? 0);
        $rejected = (int) ($counts[ApplicantStatus::Rejected->value] ?? 0);
        $delivered = (int) Applicant::query()->cardDelivered()->count();
        $approved = (int) Applicant::query()->verified()->count();
        $forVerification = max(0, $approvedTotal - $delivered - $approved);

        return [
            'pending' => $pending,
            'for_verification' => $forVerification,
            'approved' => $approved,
            'rejected' => $rejected,
            'archived' => $rejected + $delivered,
            'total' => $pending + $approvedTotal + $rejected,
        ];
    }
}
