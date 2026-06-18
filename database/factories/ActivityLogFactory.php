<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'admin_id' => Admin::factory(),
            'action' => fake()->randomElement([
                'Admin Login',
                'Admin Logout',
                'Application Viewed',
                'Application Approved',
                'Application Rejected',
                'Export Generated',
            ]),
            'description' => fake()->optional()->sentence(),
            'created_at' => now(),
        ];
    }
}
