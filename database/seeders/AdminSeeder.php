<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
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
                    'password' => 'MISO12354',
                    'role' => $admin['role'],
                ],
            );
        }
    }
}
