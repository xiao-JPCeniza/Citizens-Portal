<?php

namespace Database\Factories;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;
use App\Models\Barangay;
use App\Support\ManoloFortich;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Applicant>
 */
class ApplicantFactory extends Factory
{
    protected $model = Applicant::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $middleName = fake()->lastName();
        $lastName = fake()->lastName();

        return [
            'email' => fake()->unique()->safeEmail(),
            'first_name' => $firstName,
            'middle_name' => $middleName,
            'last_name' => $lastName,
            'full_name' => "{$firstName} {$middleName} {$lastName}",
            'birthday' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
            'gcash_number' => '09'.fake()->numerify('#########'),
            'province' => ManoloFortich::PROVINCE,
            'barangay' => Barangay::query()->active()->inRandomOrder()->value('name')
                ?? fake()->randomElement(ManoloFortich::BARANGAYS),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'blood_type' => fake()->randomElement(ManoloFortich::BLOOD_TYPES),
            'emergency_contact_person' => fake()->name(),
            'emergency_contact_number' => '09'.fake()->numerify('#########'),
            'passport_photo' => 'applicants/sample/passport.jpg',
            'gcash_screenshot' => 'applicants/sample/gcash.jpg',
            'status' => ApplicantStatus::Pending,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => [
            'status' => ApplicantStatus::Approved,
            'verified_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => ApplicantStatus::Rejected,
            'rejection_reason' => 'Invalid Passport Photo',
        ]);
    }
}
