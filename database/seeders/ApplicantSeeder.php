<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\RejectionReason;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Database\Seeder;

class ApplicantSeeder extends Seeder
{
    public const COUNT_PER_STATUS = 45;

    public function run(): void
    {
        $verifier = Admin::query()
            ->where('role', AdminRole::Verifier->value)
            ->first()
            ?? Admin::query()->first();

        Applicant::factory()
            ->count(self::COUNT_PER_STATUS)
            ->sequence(fn ($sequence) => [
                'created_at' => now()->subDays(self::COUNT_PER_STATUS - $sequence->index),
            ])
            ->create();

        Applicant::factory()
            ->count(self::COUNT_PER_STATUS)
            ->approved()
            ->sequence(fn ($sequence) => [
                'verified_by' => $verifier?->id,
                'verified_at' => now()->subDays(self::COUNT_PER_STATUS - $sequence->index),
            ])
            ->create();

        $rejectionReasons = array_map(
            fn (RejectionReason $reason) => $reason->value,
            RejectionReason::cases(),
        );

        Applicant::factory()
            ->count(self::COUNT_PER_STATUS)
            ->rejected()
            ->sequence(fn ($sequence) => [
                'verified_by' => $verifier?->id,
                'verified_at' => now()->subDays(self::COUNT_PER_STATUS - $sequence->index),
                'rejection_reason' => $rejectionReasons[$sequence->index % count($rejectionReasons)],
            ])
            ->create();
    }
}
