<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use App\Services\ApplicantExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_applications_to_excel(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->approved()->create([
            'full_name' => 'Export Test Applicant',
            'email' => 'export@example.com',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.export'));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Export Generated',
        ]);
    }

    public function test_finalized_export_respects_scope_filter(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->approved()->create(['full_name' => 'Approved Only']);
        Applicant::factory()->create(['full_name' => 'Pending Only']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.export', ['scope' => 'finalized']));

        $response->assertOk();
        $response->assertDownload();
    }

    public function test_approved_export_scope_only_includes_verified_applicants(): void
    {
        $verified = Applicant::factory()->verified()->create(['full_name' => 'Verified Only']);
        $accepted = Applicant::factory()->approved()->create(['full_name' => 'Accepted Only']);
        Applicant::factory()->create(['full_name' => 'Pending Only']);

        $service = app(ApplicantExportService::class);

        $this->assertSame([$verified->id], $service->buildQuery(['scope' => 'approved'])->pluck('id')->all());
        $this->assertSame([$accepted->id], $service->buildQuery(['scope' => 'finalized'])->pluck('id')->all());

        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.export', ['scope' => 'approved']))
            ->assertOk()
            ->assertDownload();
    }

    public function test_guest_cannot_export_applications(): void
    {
        $this->get(route('admin.export'))
            ->assertRedirect(route('admin.login'));
    }
}
