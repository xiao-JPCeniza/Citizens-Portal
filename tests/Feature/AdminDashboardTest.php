<?php

namespace Tests\Feature;

use App\Enums\ApplicantStatus;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_application_statistics(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->count(3)->create(['status' => ApplicantStatus::Pending]);
        Applicant::factory()->count(2)->approved()->create();
        Applicant::factory()->count(1)->rejected()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pending Applications')
            ->assertSee('Approved Applications')
            ->assertSee('Rejected Applications')
            ->assertSee('Archived Applications')
            ->assertSee('Total Applications')
            ->assertSee('3')
            ->assertSee('2')
            ->assertSee('6');
    }

    public function test_dashboard_shows_review_queue_link_when_pending_exists(): void
    {
        $admin = Admin::factory()->create();
        Applicant::factory()->create(['status' => ApplicantStatus::Pending]);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Review Queue');
    }
}
