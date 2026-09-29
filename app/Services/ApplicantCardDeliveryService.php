<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ApplicantCardDeliveryService
{
    public function __construct(
        private AdminActivityLogService $activityLogService,
    ) {}

    /**
     * Mark approved (verified) applicants as card delivered and move them to Archive.
     * Uses the existing rejection_reason column — no schema change.
     *
     * @param  Collection<int, int|string>  $applicantIds
     */
    public function markDelivered(Collection $applicantIds, Admin $admin): int
    {
        $ids = $applicantIds
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return 0;
        }

        return (int) DB::transaction(function () use ($ids, $admin) {
            $applicants = Applicant::query()
                ->verified()
                ->whereIn('id', $ids->all())
                ->lockForUpdate()
                ->get();

            if ($applicants->isEmpty()) {
                return 0;
            }

            Applicant::query()
                ->whereIn('id', $applicants->modelKeys())
                ->update([
                    'rejection_reason' => Applicant::CARD_DELIVERED_REASON,
                    'updated_at' => now(),
                ]);

            $this->activityLogService->log(
                $admin,
                'Card Delivered',
                'Marked '.$applicants->count().' applicant(s) as card delivered and moved them to Archive. Application IDs: '.$applicants->pluck('application_id')->implode(', ').'.',
            );

            return $applicants->count();
        });
    }
}
