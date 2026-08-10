<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
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

    public function test_secure_edit_link_loads_prefilled_form_for_rejected_application(): void
    {
        $applicant = Applicant::factory()->rejected()->create([
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'email' => 'maria@example.com',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
        ]);

        $token = ApplicantEditToken::issue($applicant);
        $url = ApplicantEditToken::url($applicant->fresh(), $token);

        $this->get($url)
            ->assertOk()
            ->assertSee('Edit Your Application')
            ->assertSee('Invalid Passport Photo')
            ->assertSee($applicant->application_id)
            ->assertSee('maria@example.com')
            ->assertSee('MARIA')
            ->assertSee('New Passport Photo')
            ->assertSee('Resubmit Application');
    }

    public function test_edit_link_without_token_is_not_found(): void
    {
        $applicant = Applicant::factory()->rejected()->create();

        $this->get('/applications/'.$applicant->application_id.'/edit/'.str_repeat('a', 64))
            ->assertNotFound();
    }

    public function test_pending_application_cannot_open_edit_form(): void
    {
        $applicant = Applicant::factory()->create([
            'status' => ApplicantStatus::Pending,
        ]);

        $token = ApplicantEditToken::generatePlainText();

        $applicant->forceFill([
            'edit_token_hash' => ApplicantEditToken::hash($token),
            'edit_token_expires_at' => now()->addDay(),
            'status' => ApplicantStatus::Pending,
        ])->save();

        $this->get(ApplicantEditToken::url($applicant, $token))
            ->assertNotFound();
    }

    public function test_wrong_token_cannot_open_edit_form(): void
    {
        $applicant = Applicant::factory()->rejected()->create();
        ApplicantEditToken::issue($applicant);

        $this->get(ApplicantEditToken::url($applicant, str_repeat('b', 64)))
            ->assertNotFound();
    }

    public function test_expired_edit_token_is_rejected(): void
    {
        $applicant = Applicant::factory()->rejected()->create();
        $token = ApplicantEditToken::generatePlainText();

        $applicant->forceFill([
            'edit_token_hash' => ApplicantEditToken::hash($token),
            'edit_token_expires_at' => now()->subMinute(),
        ])->save();

        $this->get(ApplicantEditToken::url($applicant, $token))
            ->assertNotFound();
    }

    public function test_rejected_applicant_can_resubmit_with_updated_info_and_new_photo(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->rejected()->create([
            'email' => 'maria@example.com',
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
            'verified_by' => null,
            'verified_at' => now(),
            'rejection_reason' => 'Invalid Passport Photo',
        ]);

        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->set('first_name', 'maria')
            ->set('middle_name', 'santos')
            ->set('last_name', 'reyes')
            ->set('address', '456 Corrected Street')
            ->set('passport_photo', UploadedFile::fake()->image('passport.jpg', 1200, 1200))
            ->call('submit')
            ->assertSet('submitted', true)
            ->assertSee('Application Updated Successfully');

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Pending, $applicant->status);
        $this->assertNull($applicant->rejection_reason);
        $this->assertNull($applicant->verified_at);
        $this->assertNull($applicant->edit_token_hash);
        $this->assertNull($applicant->edit_token_expires_at);
        $this->assertSame('MARIA', $applicant->first_name);
        $this->assertSame('456 Corrected Street', $applicant->address);
        $this->assertSame('applicants/'.$applicant->application_id.'.jpg', $applicant->passport_photo);
        Storage::disk('local')->assertExists($applicant->passport_photo);

        Mail::assertSent(ApplicationReceivedMail::class, function (ApplicationReceivedMail $mail) use ($applicant): bool {
            return $mail->hasTo('maria@example.com')
                && $mail->applicant->is($applicant);
        });
    }

    public function test_used_edit_token_cannot_be_reused_after_resubmit(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->rejected()->create([
            'email' => 'reuse@example.com',
            'first_name' => 'MARIA',
            'middle_name' => 'SANTOS',
            'last_name' => 'REYES',
            'barangay' => 'Tankulan',
            'address' => '123 Main Street',
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

    public function test_resubmit_requires_new_passport_photo(): void
    {
        Mail::fake();
        Storage::fake('local');

        $applicant = Applicant::factory()->rejected()->create([
            'barangay' => 'Tankulan',
        ]);
        $token = ApplicantEditToken::issue($applicant);

        Livewire::test(ApplicationEditForm::class, [
            'applicant' => $applicant->fresh(),
            'token' => $token,
        ])
            ->call('submit')
            ->assertHasErrors(['passport_photo']);

        Mail::assertNothingSent();
        $this->assertSame(ApplicantStatus::Rejected, $applicant->fresh()->status);
        $this->assertNotNull($applicant->fresh()->edit_token_hash);
    }
}
