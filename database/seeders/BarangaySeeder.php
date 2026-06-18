<?php

namespace Database\Seeders;

use App\Models\Barangay;
use App\Support\ManoloFortich;
use Illuminate\Database\Seeder;

class BarangaySeeder extends Seeder
{
    public function run(): void
    {
        foreach (ManoloFortich::BARANGAYS as $name) {
            Barangay::query()->updateOrCreate(
                ['name' => $name],
                [
                    'municipality' => ManoloFortich::PROVINCE,
                    'is_active' => true,
                ],
            );
        }
    }
}
