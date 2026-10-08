<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Admin\ApprovedTable;
use App\Livewire\Admin\FinalizationTable;
use App\Mail\ApplicationApprovedMail;
use App\Models\ActivityLog;
use App\Models\Admin;
use App\Models\Applicant;
use App\Support\AdminTable;
use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class AdminFinalizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BarangaySeeder::class);
    }

    public function test_verification_dashboard_lists_accepted_applications_only(): void
    {
        $admin = Admin::factory()->create();
        $verifier = Admin::factory()->create(['name' => 'Accepting Admin']);

        Applicant::factory()->approved()->create([
            'full_name' => 'Accepted Applicant',
            'barangay' => 'Tankulan',
            'blood_type' => 'O+',
            'verified_by' => $verifier->id,
            'verified_at' => now()->subDay(),
        ]);

        Applicant::factory()->create(['full_name' => 'Pending Applicant']);
        Applicant::factory()->rejected()->create(['full_name' => 'Rejected Applicant']);
        Applicant::factory()->verified()->create(['full_name' => 'Verified Applicant']);
        Applicant::factory()->approved()->create([
            'full_name' => 'Delivered Applicant',
            'rejection_reason' => Applicant::CARD_DELIVERED_REASON,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.index'))
            ->assertOk()
            ->assertSee('Verification Dashboard')
            ->assertSee('Accepted Applicant')
            ->assertSee('Tankulan')
            ->assertSee('O+')
            ->assertSee('Accepting Admin')
            ->assertSee('Review')
            ->assertSee('Approve All on Page')
            ->assertSee('Export for Compiling')
            ->assertSee('Export to Excel')
            ->assertSee('format=compilation', false)
            ->assertDontSee('Download Zip ID')
            ->assertDontSee('Card Delivered')
            ->assertDontSee('Pending Applicant')
            ->assertDontSee('Rejected Applicant')
            ->assertDontSee('Verified Applicant')
            ->assertDontSee('Delivered Applicant');
    }

    public function test_approved_page_lists_verified_applications_only(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->verified()->create(['full_name' => 'Verified Applicant']);
        Applicant::factory()->approved()->create(['full_name' => 'Accepted Applicant']);
        Applicant::factory()->approved()->create([
            'full_name' => 'Delivered Applicant',
            'rejection_reason' => Applicant::CARD_DELIVERED_REASON,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.approved.index'))
            ->assertOk()
            ->assertSee('Approved Applications')
            ->assertSee('Verified Applicant')
            ->assertSee('View Details')
            ->assertSee('Download Zip ID')
            ->assertSee('Export to Excel')
            ->assertSee('Card Delivered')
            ->assertDontSee('Accepted Applicant')
            ->assertDontSee('Delivered Applicant');
    }

    public function test_finalized_page_filters_by_search_barangay_and_date(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->approved()->create([
            'full_name' => 'Maria Santos',
            'barangay' => 'Tankulan',
            'verified_at' => '2026-06-10 10:00:00',
        ]);

        Applicant::factory()->approved()->create([
            'full_name' => 'Juan Dela Cruz',
            'barangay' => 'Dalirig',
            'verified_at' => '2026-06-15 10:00:00',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->set('search', 'Maria')
            ->assertSee('Maria Santos')
            ->assertDontSee('Juan Dela Cruz');

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->set('barangay', 'Dalirig')
            ->assertSee('Juan Dela Cruz')
            ->assertDontSee('Maria Santos');

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->set('date_from', '2026-06-14')
            ->set('date_to', '2026-06-16')
            ->assertSee('Juan Dela Cruz')
            ->assertDontSee('Maria Santos');
    }

    public function test_finalized_page_lists_first_approved_first(): void
    {
        $admin = Admin::factory()->create();

        $later = Applicant::factory()->approved()->create([
            'full_name' => 'Later Approved',
            'verified_at' => '2026-06-20 10:00:00',
        ]);

        $earlier = Applicant::factory()->approved()->create([
            'full_name' => 'Earlier Approved',
            'verified_at' => '2026-06-10 10:00:00',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->assertSeeInOrder(['Earlier Approved', 'Later Approved']);

        $this->assertTrue($earlier->verified_at->lt($later->verified_at));
    }

    public function test_admin_can_mark_selected_applicants_as_card_delivered(): void
    {
        $admin = Admin::factory()->create();

        $applicant = Applicant::factory()->verified()->create([
            'full_name' => 'Ready For Delivery',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApprovedTable::class)
            ->set('selectedApplicants', [(string) $applicant->id])
            ->call('markCardDelivered')
            ->assertHasNoErrors()
            ->assertSee('Marked 1 applicant(s) as card delivered')
            ->assertDontSee('Ready For Delivery');

        $applicant->refresh();

        $this->assertTrue($applicant->isCardDelivered());
        $this->assertSame(ApplicantStatus::Approved, $applicant->status);
        $this->assertSame(Applicant::CARD_DELIVERED_REASON, $applicant->rejection_reason);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.archive.index'))
            ->assertSee('Ready For Delivery')
            ->assertSee('Card Delivered');
    }

    public function test_card_delivered_ignores_applicants_still_for_verification(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ApprovedTable::class)
            ->set('selectedApplicants', [(string) $applicant->id])
            ->call('markCardDelivered')
            ->assertHasErrors(['selectedApplicants']);

        $this->assertTrue($applicant->fresh()->isForVerification());
    }

    public function test_admin_can_approve_all_applications_on_the_current_page(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $onPage = Applicant::factory()->approved()->count(3)->create();
        $notOnPage = Applicant::factory()->approved()->create(['full_name' => 'Not Shown Applicant']);
        $pending = Applicant::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->call('approvePage', [...$onPage->modelKeys(), $pending->id])
            ->assertSee('Approved 3 application(s)')
            ->assertSee('Not Shown Applicant');

        foreach ($onPage as $applicant) {
            $this->assertTrue($applicant->fresh()->isVerified());
            $this->assertSame($admin->id, $applicant->fresh()->verified_by);
        }

        $this->assertTrue($notOnPage->fresh()->isForVerification());
        $this->assertTrue($pending->fresh()->isPending());

        Mail::assertSent(ApplicationApprovedMail::class, 3);
        $this->assertSame(3, ActivityLog::query()->where('action', 'Application Verified')->count());
    }

    public function test_approve_all_on_page_is_limited_to_one_page_of_applicants(): void
    {
        Mail::fake();

        $admin = Admin::factory()->create();
        $applicants = Applicant::factory()->approved()->count(AdminTable::PER_PAGE + 5)->create();

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->call('approvePage', $applicants->modelKeys());

        $this->assertSame(AdminTable::PER_PAGE, Applicant::query()->verified()->count());
        $this->assertSame(5, Applicant::query()->forVerification()->count());
    }

    public function test_accepted_application_detail_links_back_to_verification_dashboard(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Acceptance Details')
            ->assertSee('Back to Verification Dashboard');
    }

    public function test_verified_application_detail_links_back_to_approved_page(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->verified()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Approval Details')
            ->assertSee('Back to Approved Applications')
            ->assertDontSee('Final Verification');
    }

    public function test_guest_cannot_access_verification_and_approved_pages(): void
    {
        $this->get(route('admin.finalized.index'))
            ->assertRedirect(route('admin.login'));

        $this->get(route('admin.approved.index'))
            ->assertRedirect(route('admin.login'));
    }
}
