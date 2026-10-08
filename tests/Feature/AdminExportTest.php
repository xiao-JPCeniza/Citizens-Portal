<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use App\Services\ApplicantExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\IOFactory;
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

    public function test_finalized_export_file_contains_verification_columns(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->approved()->create([
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'birthday' => '1990-05-15',
            'gcash_number' => '09171234567',
        ]);
        Applicant::factory()->verified()->create(['first_name' => 'Already Verified']);
        Applicant::factory()->create(['first_name' => 'Still Pending']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.export', ['scope' => 'finalized']));

        $response->assertOk();
        $this->assertStringContainsString('for-verification-applications-', (string) $response->headers->get('content-disposition'));

        $sheet = IOFactory::load($response->getFile()->getPathname())->getActiveSheet();

        $this->assertSame([
            ['No.', 'FIRST NAME', 'MIDDLE NAME', 'LAST NAME', 'BIRTHDAY', 'GCASH No.'],
            [1, 'JUAN', 'DELA', 'CRUZ', '1990-05-15', '09171234567'],
        ], $sheet->toArray(null, false, false));
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
