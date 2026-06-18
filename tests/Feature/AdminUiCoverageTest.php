<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Admin\ApplicantView;
use App\Livewire\Admin\FinalizationTable;
use App\Models\Admin;
use App\Models\Applicant;
use Database\Seeders\BarangaySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminUiCoverageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(BarangaySeeder::class);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function adminPageProvider(): array
    {
        return [
            'dashboard' => ['admin.dashboard', 'Dashboard'],
            'applications queue' => ['admin.applications.index', 'New Applicants Queue'],
            'archive' => ['admin.archive.index', 'Archive'],
            'finalized' => ['admin.finalized.index', 'Finalized Applications'],
        ];
    }

    #[DataProvider('adminPageProvider')]
    public function test_authenticated_admin_can_access_all_admin_pages(string $routeName, string $expectedContent): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route($routeName))
            ->assertOk()
            ->assertSee($expectedContent);
    }

    #[DataProvider('adminPageProvider')]
    public function test_admin_navigation_links_are_present_on_all_pages(string $routeName): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route($routeName))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('New Applicants')
            ->assertSee('Archive')
            ->assertSee('Finalized Applications')
            ->assertSee('Sign Out');
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function guestProtectedRouteProvider(): array
    {
        return [
            'dashboard' => ['admin.dashboard'],
            'applications queue' => ['admin.applications.index'],
            'archive' => ['admin.archive.index'],
            'finalized' => ['admin.finalized.index'],
            'export' => ['admin.export'],
        ];
    }

    #[DataProvider('guestProtectedRouteProvider')]
    public function test_guest_is_redirected_from_protected_admin_routes(string $routeName): void
    {
        $this->get(route($routeName))
            ->assertRedirect(route('admin.login'));
    }

    public function test_dashboard_export_link_is_accessible(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Export to Excel');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.export'))
            ->assertOk()
            ->assertDownload();
    }

    public function test_dashboard_review_queue_link_navigates_to_applications(): void
    {
        $admin = Admin::factory()->create();
        Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertSee('Review Queue');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('New Applicants Queue');
    }

    public function test_applicant_view_link_from_queue_is_accessible(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'full_name' => 'Queue Link Test',
            'status' => ApplicantStatus::Pending,
        ]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertSee('View Application');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Queue Link Test')
            ->assertSee('Back to New Applicants');
    }

    public function test_reject_form_can_be_opened_and_cancelled(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantView::class, ['applicant' => $applicant])
            ->assertSet('showRejectForm', false)
            ->call('showReject')
            ->assertSet('showRejectForm', true)
            ->set('rejection_reason', 'Invalid Passport Photo')
            ->set('remarks', 'Some remarks')
            ->call('cancelReject')
            ->assertSet('showRejectForm', false)
            ->assertSet('rejection_reason', '')
            ->assertSet('remarks', '');
    }

    public function test_finalization_table_select_all_and_clear_filters(): void
    {
        $admin = Admin::factory()->create();

        $first = Applicant::factory()->approved()->create(['full_name' => 'Select All One']);
        $second = Applicant::factory()->approved()->create(['full_name' => 'Select All Two']);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->call('toggleSelectAllOnPage')
            ->assertSet('selectedApplicants', [(string) $first->id, (string) $second->id])
            ->call('toggleSelectAllOnPage')
            ->assertSet('selectedApplicants', [])
            ->set('search', 'Select All One')
            ->set('barangay', 'Tankulan')
            ->set('date_from', '2026-01-01')
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('barangay', '')
            ->assertSet('date_from', '');
    }

    public function test_finalization_email_modal_can_be_opened_and_closed(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->set('selectedApplicants', [(string) $applicant->id])
            ->call('openEmailModal')
            ->assertSet('showEmailModal', true)
            ->set('when', 'June 25, 2026')
            ->set('where', 'Municipal Gymnasium')
            ->set('what', 'Distribution event')
            ->call('closeEmailModal')
            ->assertSet('showEmailModal', false)
            ->assertSet('when', '')
            ->assertSet('where', '')
            ->assertSet('what', '');
    }

    public function test_finalization_open_email_modal_does_nothing_without_selection(): void
    {
        $admin = Admin::factory()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->call('openEmailModal')
            ->assertSet('showEmailModal', false);
    }

    public function test_finalized_export_link_includes_filter_params(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.index'))
            ->assertSee('Export to Excel');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.export', [
                'scope' => 'finalized',
                'q' => 'test',
                'barangay' => 'Tankulan',
                'from' => '2026-01-01',
                'to' => '2026-12-31',
            ]))
            ->assertOk()
            ->assertDownload();
    }

    public function test_archive_review_history_link_navigates_to_detail(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->rejected()->create(['full_name' => 'Archive Nav Test']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.archive.index'))
            ->assertSee('Review History');

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.show', $applicant))
            ->assertOk()
            ->assertSee('Archive Nav Test')
            ->assertSee('Back to Archive');
    }

    public function test_admin_login_page_shows_remember_me_checkbox(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Remember me')
            ->assertSee('Sign In');
    }
}
