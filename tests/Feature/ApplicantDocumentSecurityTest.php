<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantDocumentSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_applicant_documents(): void
    {
        Storage::fake('local');

        $applicant = Applicant::factory()->create([
            'passport_photo' => 'applicants/000123.jpg',
            'gcash_screenshot' => 'applicants/000123-gcash.jpg',
        ]);

        Storage::disk('local')->put('applicants/000123.jpg', 'secret-passport');
        Storage::disk('local')->put('applicants/000123-gcash.jpg', 'secret-gcash');

        $this->get(route('admin.applications.document', [
            'applicant' => $applicant,
            'type' => 'passport',
        ]))->assertRedirect(route('admin.login'));

        $this->get(route('admin.applications.document', [
            'applicant' => $applicant,
            'type' => 'gcash',
        ]))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_private_applicant_documents(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'passport_photo' => 'applicants/000123.jpg',
            'gcash_screenshot' => 'applicants/000123-gcash.jpg',
        ]);

        Storage::disk('local')->put('applicants/000123.jpg', 'secret-passport');
        Storage::disk('local')->put('applicants/000123-gcash.jpg', 'secret-gcash');

        $passportResponse = $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.document', [
                'applicant' => $applicant,
                'type' => 'passport',
            ]));

        $passportResponse
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->assertSame('secret-passport', $passportResponse->streamedContent());

        $gcashResponse = $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.document', [
                'applicant' => $applicant,
                'type' => 'gcash',
            ]));

        $gcashResponse->assertOk();
        $this->assertSame('secret-gcash', $gcashResponse->streamedContent());
    }

    public function test_admin_can_view_applicant_documents_from_public_disk_fallback(): void
    {
        Storage::fake('local');
        Storage::fake('public');

        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->create([
            'passport_photo' => 'applicants/000999.jpg',
            'gcash_screenshot' => 'applicants/000999-gcash.jpg',
        ]);

        Storage::disk('public')->put('applicants/000999.jpg', 'public-passport');
        Storage::disk('public')->put('applicants/000999-gcash.jpg', 'public-gcash');

        $passportResponse = $this->actingAs($admin, 'admin')
            ->get(route('admin.applications.document', [
                'applicant' => $applicant,
                'type' => 'passport',
            ]));

        $passportResponse->assertOk();
        $this->assertSame('public-passport', $passportResponse->streamedContent());
    }

    public function test_passport_photo_urls_point_to_admin_protected_routes(): void
    {
        $applicant = Applicant::factory()->create([
            'passport_photo' => 'applicants/000123.jpg',
            'gcash_screenshot' => 'applicants/000123-gcash.jpg',
        ]);

        $this->assertStringContainsString(
            '/admin/applications/'.$applicant->id.'/documents/passport',
            (string) $applicant->passportPhotoUrl(),
        );
        $this->assertStringNotContainsString('/storage/', (string) $applicant->passportPhotoUrl());
        $this->assertStringNotContainsString('/storage/', (string) $applicant->gcashScreenshotUrl());
    }

    public function test_secure_photos_command_moves_public_files_to_private_disk(): void
    {
        Storage::fake('public');
        Storage::fake('local');

        Storage::disk('public')->put('applicants/000001.jpg', 'passport-data');
        Storage::disk('public')->put('applicants/000001-gcash.jpg', 'gcash-data');

        Artisan::call('applicants:secure-photos');

        Storage::disk('local')->assertExists('applicants/000001.jpg');
        Storage::disk('local')->assertExists('applicants/000001-gcash.jpg');
        Storage::disk('public')->assertMissing('applicants/000001.jpg');
        Storage::disk('public')->assertMissing('applicants/000001-gcash.jpg');
        $this->assertSame('passport-data', Storage::disk('local')->get('applicants/000001.jpg'));
    }
}
