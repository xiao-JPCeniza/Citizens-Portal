<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use App\Services\ApplicantExportService;
use App\Support\ApplicantAddressFormatter;
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

    public function test_finalized_compilation_export_matches_partner_template(): void
    {
        $admin = Admin::factory()->create();

        Applicant::factory()->approved()->create([
            'application_id' => '000042',
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'full_name' => 'JUAN D. CRUZ',
            'birthday' => '1990-05-15',
            'gcash_number' => '09171234567',
            'barangay' => 'Tankulan',
            'address' => 'Purok 1',
            'blood_type' => 'O+',
            'emergency_contact_person' => 'Maria Dela Cruz',
            'emergency_contact_number' => '09181234567',
            'passport_photo' => 'applicants/000042.jpg',
        ]);
        Applicant::factory()->verified()->create(['first_name' => 'Already Verified']);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.export', ['scope' => 'finalized', 'format' => 'compilation']));

        $response->assertOk();
        $this->assertStringContainsString('for-verification-compilation-', (string) $response->headers->get('content-disposition'));

        $sheet = IOFactory::load($response->getFile()->getPathname())->getActiveSheet();

        $this->assertSame([
            [
                'No.',
                'FULL NAME',
                'firstname',
                'middlename',
                'lastname',
                'birthday',
                'gcashnumber',
                'Address',
                'Blood Type',
                'Unique ID# / Company ID # / Membership #',
                'Emergency Contact Person',
                'Emergency Contact Number',
                'Photo ID (if with Photo. JPG Format Only)',
            ],
            [
                1,
                'JUAN D. CRUZ',
                'JUAN',
                'DELA',
                'CRUZ',
                '1990-05-15',
                '09171234567',
                ApplicantAddressFormatter::build('Purok 1', 'Tankulan'),
                'O+',
                '000042',
                'Maria Dela Cruz',
                '09181234567',
                'JUAN D. CRUZ.jpg',
            ],
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
