<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Livewire\Admin\ApplicantsTable;
use App\Livewire\Admin\ArchiveTable;
use App\Livewire\Admin\FinalizationTable;
use App\Models\Admin;
use App\Models\Applicant;
use App\Support\AdminTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTablePaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_tables_show_at_most_twenty_records_per_page(): void
    {
        $this->assertSame(20, AdminTable::PER_PAGE);

        $admin = Admin::factory()->create();

        Applicant::factory()->count(21)->create(['status' => ApplicantStatus::Pending]);
        Applicant::factory()->count(21)->rejected()->create();
        Applicant::factory()->count(21)->approved()->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->assertViewHas('applicants', fn ($paginator) => $paginator->count() === 20 && $paginator->total() === 21);

        Livewire::actingAs($admin, 'admin')
            ->test(ArchiveTable::class)
            ->assertViewHas('applicants', fn ($paginator) => $paginator->count() === 20 && $paginator->total() === 21);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->assertViewHas('applicants', fn ($paginator) => $paginator->count() === 20 && $paginator->total() === 21);
    }

    public function test_admin_tables_show_pagination_controls_when_more_than_one_page(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->count(21)->create(['status' => ApplicantStatus::Pending]);
        Applicant::factory()->count(21)->rejected()->create();
        Applicant::factory()->count(21)->approved()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Pagination Navigation', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.archive.index'))
            ->assertOk()
            ->assertSee('Pagination Navigation', false);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.index'))
            ->assertOk()
            ->assertSee('Pagination Navigation', false);
    }

    public function test_admin_tables_can_navigate_to_the_next_page(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->count(21)->create(['status' => ApplicantStatus::Pending]);

        Livewire::actingAs($admin, 'admin')
            ->test(ApplicantsTable::class)
            ->assertViewHas('applicants', fn ($paginator) => $paginator->currentPage() === 1)
            ->call('nextPage')
            ->assertViewHas('applicants', fn ($paginator) => $paginator->currentPage() === 2 && $paginator->count() === 1);
    }

    public function test_admin_tables_show_result_summary_on_single_page(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->count(3)->create(['status' => ApplicantStatus::Pending]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.index'))
            ->assertOk()
            ->assertSee('Showing')
            ->assertSee('3');
    }

    public function test_archive_table_resets_to_first_page_when_search_changes(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->count(21)->rejected()->sequence(
            ['full_name' => 'Alpha One'],
            ['full_name' => 'Alpha Two'],
        )->create();

        Livewire::actingAs($admin, 'admin')
            ->test(ArchiveTable::class)
            ->call('nextPage')
            ->assertViewHas('applicants', fn ($paginator) => $paginator->currentPage() === 2)
            ->set('search', 'Alpha One')
            ->assertViewHas('applicants', fn ($paginator) => $paginator->currentPage() === 1);
    }
}
