<?php

namespace Database\Factories;

use App\Models\Barangay;
use App\Support\ManoloFortich;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Barangay>
 */
class BarangayFactory extends Factory
{
    protected $model = Barangay::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'municipality' => ManoloFortich::PROVINCE,
            'is_active' => true,
        ];
    }
}
