<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;

class ApplicantStatisticsService
{
    /**
     * @return array{
     *     pending: int,
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
        $approved = (int) ($counts[ApplicantStatus::Approved->value] ?? 0);
        $rejected = (int) ($counts[ApplicantStatus::Rejected->value] ?? 0);

        return [
            'pending' => $pending,
            'approved' => $approved,
            'rejected' => $rejected,
            'archived' => $rejected,
            'total' => $pending + $approved + $rejected,
        ];
    }
}
