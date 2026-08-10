<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Enums\RejectionReason;
use App\Livewire\Admin\ApplicantView;
use App\Mail\ApplicationApprovedMail;
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

    public function test_admin_can_approve_pending_application(): void
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
        $this->assertSame($admin->id, $applicant->verified_by);
        $this->assertNotNull($applicant->verified_at);
        $this->assertNull($applicant->rejection_reason);

        Mail::assertSent(ApplicationApprovedMail::class, function (ApplicationApprovedMail $mail) use ($applicant) {
            return $mail->hasTo('applicant@example.com')
                && $mail->applicant->is($applicant);
        });

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Approved',
        ]);
    }

    public function test_admin_can_reject_pending_application(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'email' => 'applicant@example.com',
            'status' => ApplicantStatus::Pending,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->set('rejection_reason', RejectionReason::InvalidPassportPhoto->value)
            ->set('remarks', 'Photo background is not white.')
            ->call('reject')
            ->assertRedirect(route('admin.applications.index'));

        $applicant->refresh();

        $this->assertSame(ApplicantStatus::Rejected, $applicant->status);
        $this->assertSame('Invalid Passport Photo: Photo background is not white.', $applicant->rejection_reason);
        $this->assertSame($admin->id, $applicant->verified_by);
        $this->assertNotNull($applicant->verified_at);

        Mail::assertSent(ApplicationRejectedMail::class, function (ApplicationRejectedMail $mail) use ($applicant) {
            return $mail->hasTo('applicant@example.com')
                && $mail->applicant->is($applicant)
                && $mail->reason === 'Invalid Passport Photo'
                && $mail->remarks === 'Photo background is not white.'
                && filled($mail->editUrl)
                && str_contains($mail->editUrl, '/applications/'.$applicant->application_id.'/edit/');
        });

        $applicant->refresh();
        $this->assertNotNull($applicant->edit_token_hash);
        $this->assertNotNull($applicant->edit_token_expires_at);

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Application Rejected',
        ]);
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
            ->assertSee('Approve Application')
            ->assertSee('Reject Application');
    }
}
