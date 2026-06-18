<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Admin;

class AdminActivityLogService
{
    public function log(Admin $admin, string $action, ?string $description = null): ActivityLog
    {
        return ActivityLog::query()->create([
            'admin_id' => $admin->id,
            'action' => $action,
            'description' => $description,
            'created_at' => now(),
        ]);
    }
}
