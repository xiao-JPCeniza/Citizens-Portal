<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Public\ApplicationForm;
use App\Models\Applicant;
use App\Mail\ApplicationReceivedMail;
use App\Support\ManoloFortich;
use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BarangaySeeder::class);
    }

    public function test_application_form_page_displays_all_sections(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ])
            ->get(route('apply'))
            ->assertOk()
            ->assertSee('Citizen Application Form')
            ->assertSee('applicant@example.com')
            ->assertSee('Verified via email OTP')
            ->assertSee('Personal Information')
            ->assertSee('Address')
            ->assertSee('Emergency Contact')
            ->assertSee('Document Uploads')
            ->assertSee('Submit Application')
            ->assertSee('Back to Welcome');
    }

    public function test_user_can_submit_complete_application(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set('first_name', 'juan')
            ->set('middle_name', 'dela')
            ->set('last_name', 'cruz')
            ->set('birthday', '1990-05-15')
            ->set('gcash_number', '09123456789')
            ->set('barangay', 'Tankulan')
            ->set('address', '123 Main Street')
            ->set('blood_type', 'O+')
            ->set('emergency_contact_person', 'Maria Cruz')
            ->set('emergency_contact_number', '09987654321')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg'))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSee('Application Submitted Successfully');

        $this->assertDatabaseHas('applicants', [
            'email' => 'applicant@example.com',
            'first_name' => 'JUAN',
            'middle_name' => 'DELA',
            'last_name' => 'CRUZ',
            'full_name' => 'JUAN D. CRUZ',
            'barangay' => 'Tankulan',
            'status' => ApplicantStatus::Pending->value,
        ]);

        $applicant = Applicant::query()->where('email', 'applicant@example.com')->firstOrFail();
        $this->assertStringEndsWith('CRUZ-JUAN.jpg', $applicant->passport_photo);
        Storage::disk('public')->assertExists($applicant->passport_photo);

        Mail::assertSent(ApplicationReceivedMail::class, function (ApplicationReceivedMail $mail): bool {
            return $mail->hasTo('applicant@example.com');
        });

        $this->assertNull(session('terms_accepted'));
        $this->assertNull(session('application_verified_email'));
    }

    public function test_application_form_validates_required_fields(): void
    {
        Mail::fake();
        Storage::fake('public');

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->call('submit')
            ->assertHasErrors([
                'first_name',
                'last_name',
                'birthday',
                'gcash_number',
                'barangay',
                'address',
                'blood_type',
                'emergency_contact_person',
                'emergency_contact_number',
                'passport_photo',
                'gcash_screenshot',
            ]);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('applicants', 0);
    }

    public function test_application_form_rejects_invalid_phone_numbers(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set('first_name', 'JUAN')
            ->set('last_name', 'CRUZ')
            ->set('birthday', '1990-05-15')
            ->set('gcash_number', '12345')
            ->set('barangay', 'Tankulan')
            ->set('address', '123 Main Street')
            ->set('blood_type', 'O+')
            ->set('emergency_contact_person', 'Maria Cruz')
            ->set('emergency_contact_number', '09987654321')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg'))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertHasErrors(['gcash_number']);
    }

    public function test_application_form_rejects_phone_numbers_not_starting_with_09(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set('first_name', 'JUAN')
            ->set('last_name', 'CRUZ')
            ->set('birthday', '1990-05-15')
            ->set('gcash_number', '08123456789')
            ->set('barangay', 'Tankulan')
            ->set('address', '123 Main Street')
            ->set('blood_type', 'O+')
            ->set('emergency_contact_person', 'Maria Cruz')
            ->set('emergency_contact_number', '09987654321')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg'))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertHasErrors(['gcash_number']);
    }

    public function test_application_form_rejects_invalid_barangay(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set('first_name', 'JUAN')
            ->set('last_name', 'CRUZ')
            ->set('birthday', '1990-05-15')
            ->set('gcash_number', '09123456789')
            ->set('barangay', 'Invalid Barangay')
            ->set('address', '123 Main Street')
            ->set('blood_type', 'O+')
            ->set('emergency_contact_person', 'Maria Cruz')
            ->set('emergency_contact_number', '09987654321')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg'))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertHasErrors(['barangay']);
    }

    public function test_application_form_uppercases_names_on_input(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set('first_name', 'juan')
            ->assertSet('first_name', 'JUAN')
            ->set('middle_name', 'dela')
            ->assertSet('middle_name', 'DELA')
            ->set('last_name', 'cruz')
            ->assertSet('last_name', 'CRUZ');
    }

    public function test_application_form_shows_blood_type_options(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        $response = $this->get(route('apply'));

        foreach (ManoloFortich::BLOOD_TYPES as $bloodType) {
            $response->assertSee($bloodType, false);
        }
    }

    public function test_back_to_welcome_link_is_accessible(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ])
            ->get(route('apply'))
            ->assertSee(route('welcome', absolute: false));

        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Apply for Your Citizen ID Online');
    }
}
