<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Admin\FinalizationTable;
use App\Models\Admin;
use App\Models\Applicant;
use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

    public function test_finalized_page_lists_approved_applications_only(): void
    {
        $admin = Admin::factory()->create();
        $verifier = Admin::factory()->create(['name' => 'Approver Admin']);

        $approved = Applicant::factory()->approved()->create([
            'full_name' => 'Approved Applicant',
            'barangay' => 'Tankulan',
            'blood_type' => 'O+',
            'verified_by' => $verifier->id,
            'verified_at' => now()->subDay(),
        ]);

        Applicant::factory()->create(['full_name' => 'Pending Applicant']);
        Applicant::factory()->rejected()->create(['full_name' => 'Rejected Applicant']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.index'))
            ->assertOk()
            ->assertSee('Finalized Applications')
            ->assertSee('Approved Applicant')
            ->assertSee('Tankulan')
            ->assertSee('O+')
            ->assertSee('Approver Admin')
            ->assertSee('View Details')
            ->assertSee('Download Zip ID')
            ->assertSee('Export to Excel')
            ->assertDontSee('Pending Applicant')
            ->assertDontSee('Rejected Applicant');
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

        $applicant = Applicant::factory()->approved()->create([
            'full_name' => 'Ready For Delivery',
            'rejection_reason' => null,
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
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

    public function test_approved_application_detail_links_back_to_finalized_page(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Approval Details')
            ->assertSee('Back to Finalized Applications');
    }

    public function test_guest_cannot_access_finalized_page(): void
    {
        $this->get(route('admin.finalized.index'))
            ->assertRedirect(route('admin.login'));
    }
}
