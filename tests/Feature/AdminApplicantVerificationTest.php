<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Livewire\Admin\ApplicantView;
use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationForVerificationMail;
use App\Mail\ApplicationRejectedMail;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class AdminApplicantVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_accept_pending_application_for_verification(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'email' => 'applicant@example.com',
            'status' => ApplicantStatus::Pending,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->call('approve')
            ->assertRedirect(route('admin.applications.index'));

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Approved, $applicant->status);
        $this->assertTrue($applicant->isForVerification());
        $this->assertSame($admin->id, $applicant->verified_by);
        $this->assertNotNull($applicant->verified_at);
        $this->assertNull($applicant->rejection_reason);

        Mail::assertSent(ApplicationForVerificationMail::class, function (ApplicationForVerificationMail $mail) use ($applicant) {
            return $mail->hasTo('applicant@example.com')
                && $mail->applicant->is($applicant);
        });
        Mail::assertNotSent(ApplicationApprovedMail::class);

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Accepted',
        ]);
    }

    public function test_admin_can_verify_application_on_verification_dashboard(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create([
            'email' => 'verify@example.com',
            'full_name' => 'Verify Me',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->call('verify')
            ->assertRedirect(route('admin.finalized.index'));

        $applicant->refresh();

        $this->assertTrue($applicant->isVerified());
        $this->assertSame(Applicant::VERIFIED_REASON, $applicant->rejection_reason);
        $this->assertSame($admin->id, $applicant->verified_by);

        Mail::assertSent(ApplicationApprovedMail::class, fn (ApplicationApprovedMail $mail) => $mail->hasTo('verify@example.com'));

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Verified',
        ]);

        $this->flushSession();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.index'))
            ->assertDontSee('Verify Me');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.approved.index'))
            ->assertSee('Verify Me');
    }

    public function test_cannot_verify_pending_application(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->call('verify')
            ->assertHasErrors(['applicant']);

        Mail::assertNothingSent();
        $this->assertTrue($applicant->fresh()->isPending());
    }

    public function test_admin_can_return_application_from_verification_with_remarks(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create([
            'email' => 'return@example.com',
            'full_name' => 'Returned Person',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->call('showReturn')
            ->assertSet('showReturnForm', true)
            ->set('rejection_reason', RejectionReason::InvalidGcashScreenshot->value)
            ->set('remarks', 'GCash name does not match.')
            ->call('returnApplication')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.finalized.index'));

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Pending, $applicant->status);
        $this->assertSame('Invalid GCash Screenshot: GCash name does not match.', $applicant->rejection_reason);
        $this->assertNotNull($applicant->edit_token_hash);
        $this->assertTrue($applicant->awaitsDocumentCorrection());

        Mail::assertSent(ApplicationRejectedMail::class, function (ApplicationRejectedMail $mail) {
            return $mail->hasTo('return@example.com')
                && $mail->remarks === 'GCash name does not match.'
                && filled($mail->editUrl)
                && $mail->envelope()->subject === 'Citizen ID Application Returned for Correction';
        });

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Returned',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertSee('Returned Person');
    }

    public function test_return_requires_correctable_reason_and_remarks(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::DuplicateApplication->value)
            ->set('remarks', '')
            ->call('returnApplication')
            ->assertHasErrors(['rejection_reason', 'remarks']);

        Mail::assertNothingSent();
        $this->assertTrue($applicant->fresh()->isForVerification());
    }

    public function test_admin_can_reject_application_from_verification(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create(['email' => 'rejectv@example.com']);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::NonResident->value)
            ->call('reject')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.finalized.index'));

        $this->assertSame(ApplicantStatus::Rejected, $applicant->fresh()->status);

        Mail::assertSent(ApplicationRejectedMail::class, fn (ApplicationRejectedMail $mail) => $mail->hasTo('rejectv@example.com') && $mail->editUrl === null);
    }

    public function test_verification_reject_only_allows_final_reasons(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::InvalidPassportPhoto->value)
            ->call('reject')
            ->assertHasErrors(['rejection_reason']);

        Mail::assertNothingSent();
    }

    public function test_review_page_shows_final_verification_actions(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Final Verification')
            ->assertSee('Approve Application')
            ->assertSee('Return Application')
            ->assertSee('Reject Application')
            ->assertSee('Back to Verification Dashboard');
    }

    public function test_admin_can_request_documents_without_archiving(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'email' => 'applicant@example.com',
            'full_name' => 'Pending Docs Person',
            'status' => ApplicantStatus::Pending,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::InvalidPassportPhoto->value)
            ->set('remarks', 'Photo background is not white.')
            ->call('reject')
            ->assertRedirect(route('admin.applications.index'));

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Pending, $applicant->status);
        $this->assertSame('Invalid Passport Photo: Photo background is not white.', $applicant->rejection_reason);
        $this->assertSame($admin->id, $applicant->verified_by);
        $this->assertNotNull($applicant->verified_at);
        $this->assertNotNull($applicant->edit_token_hash);
        $this->assertNull($applicant->edit_token_expires_at);

        Mail::assertSent(ApplicationRejectedMail::class, function (ApplicationRejectedMail $mail) use ($applicant) {
            return $mail->hasTo('applicant@example.com')
                && $mail->applicant->is($applicant)
                && $mail->reason === 'Invalid Passport Photo'
                && $mail->remarks === 'Photo background is not white.'
                && filled($mail->editUrl)
                && str_contains($mail->editUrl, '/applications/'.$applicant->application_id.'/edit/');
        });

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Documents Requested',
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Pending Docs Person')
            ->assertSee('Awaiting Documents');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.archive.index'))
            ->assertOk()
            ->assertDontSee('Pending Docs Person');
    }

    public function test_reject_requires_reason(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', '')
            ->call('reject')
            ->assertHasErrors(['rejection_reason']);

        Mail::assertNothingSent();
        $this->assertSame(ApplicantStatus::Pending, $applicant->fresh()->status);
    }

    public function test_reject_other_requires_remarks(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::Other->value)
            ->set('remarks', '')
            ->call('reject')
            ->assertHasErrors(['remarks']);

        Mail::assertNothingSent();
    }

    public function test_reject_other_does_not_include_edit_link(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'email' => 'final@example.com',
            'status' => ApplicantStatus::Pending,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::Other->value)
            ->set('remarks', 'Fraudulent documents submitted.')
            ->call('reject')
            ->assertRedirect(route('admin.applications.index'));

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Rejected, $applicant->status);
        $this->assertNull($applicant->edit_token_hash);
        $this->assertNull($applicant->edit_token_expires_at);

        Mail::assertSent(ApplicationRejectedMail::class, function (ApplicationRejectedMail $mail) use ($applicant) {
            return $mail->hasTo('final@example.com')
                && $mail->applicant->is($applicant)
                && $mail->reason === 'Other'
                && $mail->remarks === 'Fraudulent documents submitted.'
                && $mail->editUrl === null;
        });

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Rejected',
        ]);
    }

    public function test_reject_duplicate_does_not_include_edit_link(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'email' => 'duplicate@example.com',
            'status' => ApplicantStatus::Pending,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::DuplicateApplication->value)
            ->call('reject');

        Mail::assertSent(ApplicationRejectedMail::class, function (ApplicationRejectedMail $mail) {
            return $mail->hasTo('duplicate@example.com')
                && $mail->editUrl === null;
        });

        $this->assertNull($applicant->fresh()->edit_token_hash);
    }

    public function test_cannot_approve_already_processed_application(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->call('approve')
            ->assertHasErrors(['applicant']);

        Mail::assertNothingSent();
    }

    public function test_rejected_application_is_removed_from_verification_queue(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'full_name' => 'Queue Test Person',
            'status' => ApplicantStatus::Pending,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::DuplicateApplication->value)
            ->call('reject');

        $this->assertSame(ApplicantStatus::Rejected, $applicant->fresh()->status);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('No pending applications');
    }

    public function test_review_page_shows_verification_actions_for_pending_applications(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Verify Application')
            ->assertSee('Accept Application')
            ->assertSee('Reject Application');
    }
}
