<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Livewire\Public\ApplicationEditForm;
use App\Mail\ApplicationReceivedMail;
use App\Models\Applicant;
use App\Support\ApplicantEditToken;
use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ApplicationEditFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BarangaySeeder::class);
    }

    public function test_secure_edit_link_loads_prefilled_form_for_application_awaiting_documents(): void
    {
        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'email' => 'maria@example.com',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
            'rejection_reason' => RejectionReason::InvalidPassportPhoto->value,
        ]);

        $token = ApplicantEditToken::issue($applicant);
        $url = ApplicantEditToken::url($applicant->fresh(), $token);

        $this->get($url)
            ->assertOk()
            ->assertSee('Edit Your Application')
            ->assertSee('Invalid Passport Photo')
            ->assertSee($applicant->application_id)
            ->assertSee('maria@example.com')
            ->assertSee('New Passport Photo')
            ->assertDontSee('New GCash Screenshot')
            ->assertSee('Resubmit Application');
    }

    public function test_gcash_rejection_only_shows_gcash_upload(): void
    {
        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'rejection_reason' => RejectionReason::InvalidGcashScreenshot->value.': Blurry image.',
        ]);

        $token = ApplicantEditToken::issue($applicant);

        $this->get(ApplicantEditToken::url($applicant->fresh(), $token))
            ->assertOk()
            ->assertSee('New GCash Screenshot')
            ->assertDontSee('New Passport Photo')
            ->assertSee('Application Summary');
    }

    public function test_incomplete_information_shows_editable_fields_without_document_uploads(): void
    {
        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'rejection_reason' => RejectionReason::IncompleteInformation->value,
        ]);

        $token = ApplicantEditToken::issue($applicant);

        $this->get(ApplicantEditToken::url($applicant->fresh(), $token))
            ->assertOk()
            ->assertSee('Personal Information')
            ->assertDontSee('Required Document Uploads')
            ->assertDontSee('New Passport Photo')
            ->assertDontSee('New GCash Screenshot');
    }

    public function test_edit_link_without_token_is_not_found(): void
    {
        $applicant = Applicant::factory()->awaitingDocuments()->create();

        $this->get('/applications/'.$applicant->application_id.'/edit/'.str_repeat('a', 64))
            ->assertNotFound();
    }

    public function test_pending_application_without_correction_request_cannot_open_edit_form(): void
    {
        $applicant = Applicant::factory()->create([
            'status' => ApplicantStatus::Pending,
        ]);

        $token = ApplicantEditToken::generatePlainText();

        $applicant->forceFill([
            'edit_token_hash' => ApplicantEditToken::hash($token),
            'edit_token_expires_at' => now()->addDay(),
            'status' => ApplicantStatus::Pending,
            'rejection_reason' => null,
        ])->save();

        $this->get(ApplicantEditToken::url($applicant, $token))
            ->assertNotFound();
    }

    public function test_wrong_token_cannot_open_edit_form(): void
    {
        $applicant = Applicant::factory()->awaitingDocuments()->create();
        ApplicantEditToken::issue($applicant);

        $this->get(ApplicantEditToken::url($applicant, str_repeat('b', 64)))
            ->assertNotFound();
    }

    public function test_expired_edit_token_is_rejected(): void
    {
        $applicant = Applicant::factory()->awaitingDocuments()->create();
        $token = ApplicantEditToken::generatePlainText();

        $applicant->forceFill([
            'edit_token_hash' => ApplicantEditToken::hash($token),
            'edit_token_expires_at' => now()->subMinute(),
        ])->save();

        $this->get(ApplicantEditToken::url($applicant, $token))
            ->assertNotFound();
    }

    public function test_passport_rejection_can_resubmit_with_new_passport_only(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'email' => 'maria@example.com',
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'full_name' => 'MARIA S. REYES',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
            'passport_photo' => 'applicants/000111.jpg',
            'gcash_screenshot' => 'applicants/000111-gcash.jpg',
            'rejection_reason' => RejectionReason::InvalidPassportPhoto->value,
        ]);

        Storage::disk('local')->put($applicant->passport_photo, 'old-passport');
        Storage::disk('local')->put($applicant->gcash_screenshot, 'old-gcash');

        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->set('address', '456 Should Not Change')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSee('Application Updated Successfully');

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Pending, $applicant->status);
        $this->assertNull($applicant->rejection_reason);
        $this->assertNull($applicant->verified_at);
        $this->assertNull($applicant->edit_token_hash);
        $this->assertSame('123 Main Street', $applicant->address);
        $this->assertSame('MARIA', $applicant->first_name);
        $this->assertSame('applicants/'.$applicant->application_id.'.jpg', $applicant->passport_photo);
        $this->assertSame('applicants/000111-gcash.jpg', $applicant->gcash_screenshot);
        Storage::disk('local')->assertExists($applicant->passport_photo);

        Mail::assertSent(ApplicationReceivedMail::class, function (ApplicationReceivedMail $mail) use ($applicant): bool {
            return $mail->hasTo('maria@example.com')
                && $mail->applicant->is($applicant);
        });
    }

    public function test_gcash_rejection_can_resubmit_with_new_gcash_only(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'email' => 'juan@example.com',
            'barangay' => 'Tankulan',
            'address' => '789 Keep Street',
            'passport_photo' => 'applicants/000222.jpg',
            'gcash_screenshot' => 'applicants/000222-gcash.jpg',
            'rejection_reason' => RejectionReason::InvalidGcashScreenshot->value,
        ]);

        Storage::disk('local')->put($applicant->passport_photo, 'old-passport');
        Storage::disk('local')->put($applicant->gcash_screenshot, 'old-gcash');

        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->set('gcash_screenshot', UploadedFile::fake()->image('gcash.jpg'))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Pending, $applicant->status);
        $this->assertNull($applicant->rejection_reason);
        $this->assertSame('applicants/000222.jpg', $applicant->passport_photo);
        $this->assertSame('applicants/'.$applicant->application_id.'-gcash.jpg', $applicant->gcash_screenshot);
        $this->assertSame('789 Keep Street', $applicant->address);
        Storage::disk('local')->assertExists($applicant->gcash_screenshot);
    }

    public function test_gcash_rejection_does_not_require_passport_photo(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'barangay' => 'Tankulan',
            'rejection_reason' => RejectionReason::InvalidGcashScreenshot->value,
        ]);
        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->call('submit')
            ->assertHasErrors(['gcash_screenshot'])
            ->assertHasNoErrors(['passport_photo']);
    }

    public function test_incomplete_information_can_resubmit_without_new_documents(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'email' => 'incomplete@example.com',
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
            'passport_photo' => 'applicants/000333.jpg',
            'gcash_screenshot' => 'applicants/000333-gcash.jpg',
            'rejection_reason' => RejectionReason::IncompleteInformation->value,
        ]);

        Storage::disk('local')->put($applicant->passport_photo, 'old-passport');
        Storage::disk('local')->put($applicant->gcash_screenshot, 'old-gcash');

        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->set('address', '456 Corrected Street')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $applicant->refresh();

        $this->assertSame('456 Corrected Street', $applicant->address);
        $this->assertSame('applicants/000333.jpg', $applicant->passport_photo);
        $this->assertSame('applicants/000333-gcash.jpg', $applicant->gcash_screenshot);
        $this->assertNull($applicant->rejection_reason);
    }

    public function test_used_edit_token_cannot_be_reused_after_resubmit(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'email' => 'reuse@example.com',
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
            'rejection_reason' => RejectionReason::InvalidPassportPhoto->value,
        ]);
        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('submitted', true);

        $this->assertNull($applicant->fresh()->edit_token_hash);

        $this->get(ApplicantEditToken::url($applicant, $token))
            ->assertNotFound();
    }

    public function test_passport_rejection_requires_new_passport_photo(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->awaitingDocuments()->create([
            'barangay' => 'Tankulan',
            'rejection_reason' => RejectionReason::InvalidPassportPhoto->value,
        ]);
        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->call('submit')
            ->assertHasErrors(['passport_photo']);

        Mail::assertNothingSent();
        $this->assertSame(ApplicantStatus::Pending, $applicant->fresh()->status);
        $this->assertNotNull($applicant->fresh()->edit_token_hash);
    }
}
