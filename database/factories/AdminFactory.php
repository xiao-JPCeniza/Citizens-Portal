<?php

namespace Database\Factories;

use App\Enums\AdminRole;
use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => fake()->randomElement(AdminRole::cases())->value,
        ];
    }

    public function superAdmin(): static
    {
        return $this->state(fn () => ['role' => AdminRole::SuperAdmin->value]);
    }

    public function administrator(): static
    {
        return $this->state(fn () => ['role' => AdminRole::Administrator->value]);
    }

    public function verifier(): static
    {
        return $this->state(fn () => ['role' => AdminRole::Verifier->value]);
    }
}
