<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use RuntimeException;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $defaultPassword = trim((string) config('app.default_admin_password', ''));

        if ($defaultPassword === '') {
            if (app()->isProduction()) {
                throw new RuntimeException('DEFAULT_ADMIN_PASSWORD must be set before running AdminSeeder in production.');
            }

            $defaultPassword = '@M1s02026!';
        }

        $admins = [
            [
                'name' => 'Super Admin',
                'email' => 'Sadminn@mf.gov.ph',
                'role' => AdminRole::SuperAdmin->value,
            ],
            [
                'name' => 'Super Admin',
                'email' => 'mary.rambonanza@manolofortich.gov.ph',
                'role' => AdminRole::SuperAdmin->value,
            ],
            [
                'name' => 'Municipal Administrator',
                'email' => 'Adminnmn@mf.gov.ph',
                'role' => AdminRole::Administrator->value,
            ],
            [
                'name' => 'Application Verifier',
                'email' => 'Verifiermn@mf.gov.ph',
                'role' => AdminRole::Verifier->value,
            ],
        ];

        foreach ($admins as $admin) {
            Admin::query()->updateOrCreate(
                ['email' => $admin['email']],
                [
                    'name' => $admin['name'],
                    'password' => $defaultPassword,
                    'role' => $admin['role'],
                ],
            );
        }
    }
}
