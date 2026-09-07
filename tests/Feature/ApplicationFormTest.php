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

    /**
     * @return array<string, string>
     */
    protected function validStepOneFields(): array
    {
        return [
            'first_name' => 'juan',
            'middle_name' => 'dela',
            'last_name' => 'cruz',
            'birthday' => '1990-05-15',
            'gcash_number' => '09123456789',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
            'blood_type' => 'O+',
            'emergency_contact_person' => 'Maria Cruz',
            'emergency_contact_number' => '09987654321',
        ];
    }

    public function test_application_form_page_displays_step_one_sections(): void
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
            ->assertSee('Form page 1 of 2')
            ->assertSee('Next')
            ->assertDontSee('Document Uploads')
            ->assertDontSee('Submit Application')
            ->assertSee('Back to Welcome');
    }

    public function test_next_advances_to_document_uploads_after_validating_step_one(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->assertHasNoErrors()
            ->assertSet('formStep', 2)
            ->assertSee('Document Uploads')
            ->assertSee('Do you have a GCash?')
            ->assertSee('Submit Application')
            ->assertSee('Back');
    }

    public function test_next_validates_step_one_required_fields(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->call('next')
            ->assertSet('formStep', 1)
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
            ]);
    }

    public function test_back_returns_to_step_one_with_fields_preserved(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->assertSet('formStep', 2)
            ->call('back')
            ->assertSet('formStep', 1)
            ->assertSet('first_name', 'JUAN')
            ->assertSet('last_name', 'CRUZ')
            ->assertSet('gcash_number', '09123456789')
            ->assertSee('Personal Information')
            ->assertDontSee('Document Uploads');
    }

    public function test_gcash_no_shows_download_help_and_blocks_submit(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'no')
            ->assertSee('Get GCash to continue')
            ->assertSee('https://gcash.onelink.me/YA3x/jr0jhjhc')
            ->assertSeeHtml('href="https://gcash.onelink.me/YA3x/jr0jhjhc"')
            ->assertSee('Gcash%20Link%20Qr.png', false)
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->call('submit')
            ->assertHasErrors(['has_gcash'])
            ->assertSet('submitted', false);

        Mail::assertNothingSent();
        $this->assertDatabaseCount('applicants', 0);
    }

    public function test_gcash_yes_shows_screenshot_upload(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'yes')
            ->assertSee('GCash Screenshot')
            ->assertSee('Make sure to click the eye button')
            ->assertDontSee('Get GCash to continue');
    }

    public function test_session_draft_restores_progress_on_remount(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'no')
            ->assertSet('formStep', 2);

        Livewire::test(ApplicationForm::class)
            ->assertSet('formStep', 2)
            ->assertSet('first_name', 'JUAN')
            ->assertSet('has_gcash', 'no')
            ->assertSet('barangay', 'Tankulan');
    }

    public function test_document_upload_shows_preview_after_file_reaches_server(): void
    {
        Storage::fake('local');

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport-preview.jpg', 1200, 1200))
            ->assertHasNoErrors('passport_photo')
            ->assertSee('Uploaded to server')
            ->assertSee('passport-preview.jpg')
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash-preview.jpg'))
            ->assertHasNoErrors('gcash_screenshot')
            ->assertSee('gcash-preview.jpg');
    }

    public function test_user_can_submit_complete_application(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSee('Application Submitted Successfully');

        $this->assertDatabaseHas('applicants', [
            'email' => 'applicant@example.com',
            'application_id' => '000001',
            'first_name' => 'JUAN',
            'middle_name' => 'DELA',
            'last_name' => 'CRUZ',
            'full_name' => 'JUAN D. CRUZ',
            'barangay' => 'Tankulan',
            'status' => ApplicantStatus::Pending->value,
        ]);

        $applicant = Applicant::query()->where('email', 'applicant@example.com')->firstOrFail();
        $this->assertSame('applicants/000001.jpg', $applicant->passport_photo);
        $this->assertStringStartsWith('applicants/000001-gcash.', $applicant->gcash_screenshot);
        Storage::disk('local')->assertExists($applicant->passport_photo);
        Storage::disk('local')->assertExists($applicant->gcash_screenshot);

        Mail::assertSent(ApplicationReceivedMail::class, function (ApplicationReceivedMail $mail): bool {
            return $mail->hasTo('applicant@example.com');
        });

        $this->assertNull(session('terms_accepted'));
        $this->assertNull(session('application_verified_email'));
        $this->assertNull(session(ApplicationForm::DRAFT_SESSION_KEY));
    }

    public function test_application_form_validates_required_fields(): void
    {
        Mail::fake();
        Storage::fake('local');

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set('formStep', 2)
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
                'has_gcash',
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
            ->set([
                ...$this->validStepOneFields(),
                'gcash_number' => '12345',
            ])
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
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
            ->set([
                ...$this->validStepOneFields(),
                'gcash_number' => '08123456789',
            ])
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertHasErrors(['gcash_number']);
    }

    public function test_application_form_rejects_pdf_gcash_screenshot(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->set('gcash_screenshot', UploadedFile::fake()->create('gcash.pdf', 100, 'application/pdf'))
            ->call('submit')
            ->assertHasErrors(['gcash_screenshot']);
    }

    public function test_application_form_rejects_png_gcash_screenshot(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.png'))
            ->call('submit')
            ->assertHasErrors(['gcash_screenshot']);
    }

    public function test_application_form_rejects_png_passport_photo(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set($this->validStepOneFields())
            ->call('next')
            ->set('has_gcash', 'yes')
            ->set('passport_photo', UploadedFile::fake()->image('passport.png'))
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertHasErrors(['passport_photo']);
    }

    public function test_application_form_rejects_matching_gcash_and_emergency_numbers(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set([
                ...$this->validStepOneFields(),
                'emergency_contact_number' => '09123456789',
            ])
            ->call('next')
            ->assertHasErrors(['emergency_contact_number'])
            ->assertSet('formStep', 1);
    }

    public function test_application_form_rejects_numeric_emergency_contact_person(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set([
                ...$this->validStepOneFields(),
                'emergency_contact_person' => '09123456789',
            ])
            ->call('next')
            ->assertHasErrors(['emergency_contact_person'])
            ->assertSet('formStep', 1);
    }

    public function test_application_form_rejects_invalid_barangay(): void
    {
        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ]);

        Livewire::test(ApplicationForm::class)
            ->set([
                ...$this->validStepOneFields(),
                'barangay' => 'Invalid Barangay',
            ])
            ->call('next')
            ->assertHasErrors(['barangay'])
            ->assertSet('formStep', 1);
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
