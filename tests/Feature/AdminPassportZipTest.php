<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class AdminPassportZipTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_passport_photos_zip_for_selected_finalized_applicants(): void
    {
        Storage::fake('public');

        $admin = Admin::factory()->create();

        $first = Applicant::factory()->approved()->create([
            'application_id' => '000001',
            'first_name' => 'JUAN',
            'last_name' => 'CRUZ',
            'passport_photo' => 'applicants/000001.jpg',
        ]);

        $second = Applicant::factory()->approved()->create([
            'application_id' => '000002',
            'first_name' => 'MARIA',
            'last_name' => 'SANTOS',
            'passport_photo' => 'applicants/000002.jpg',
        ]);

        Storage::disk('public')->put('applicants/000001.jpg', 'first-passport');
        Storage::disk('public')->put('applicants/000002.jpg', 'second-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip', ['ids' => [$first->id, $second->id]]));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $this->assertSame(2, $zip->numFiles);
        $this->assertSame('000001.jpg', $zip->getNameIndex(0));
        $this->assertSame('000002.jpg', $zip->getNameIndex(1));
        $zip->close();

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Passport Zip Downloaded',
        ]);
    }

    public function test_passport_zip_excludes_non_finalized_applicants(): void
    {
        Storage::fake('public');

        $admin = Admin::factory()->create();

        $approved = Applicant::factory()->approved()->create([
            'application_id' => '000010',
            'passport_photo' => 'applicants/000010.jpg',
        ]);

        $pending = Applicant::factory()->create([
            'application_id' => '000011',
            'passport_photo' => 'applicants/000011.jpg',
        ]);

        Storage::disk('public')->put('applicants/000010.jpg', 'approved-passport');
        Storage::disk('public')->put('applicants/000011.jpg', 'pending-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip', ['ids' => [$approved->id, $pending->id]]));

        $response->assertOk();
        $response->assertDownload();

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('000010.jpg', $zip->getNameIndex(0));
        $zip->close();
    }

    public function test_passport_zip_requires_at_least_one_selected_applicant(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip'))
            ->assertStatus(400);
    }

    public function test_guest_cannot_download_passport_zip(): void
    {
        $this->get(route('admin.finalized.passport-zip', ['ids' => [1]]))
            ->assertRedirect(route('admin.login'));
    }
}
