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
            'first_name' => 'JUAN',
            'last_name' => 'CRUZ',
            'passport_photo' => 'applicants/first/CRUZ-JUAN.jpg',
        ]);

        $second = Applicant::factory()->approved()->create([
            'first_name' => 'MARIA',
            'last_name' => 'SANTOS',
            'passport_photo' => 'applicants/second/SANTOS-MARIA.jpg',
        ]);

        Storage::disk('public')->put('applicants/first/CRUZ-JUAN.jpg', 'first-passport');
        Storage::disk('public')->put('applicants/second/SANTOS-MARIA.jpg', 'second-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip', ['ids' => [$first->id, $second->id]]));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $this->assertSame(2, $zip->numFiles);
        $this->assertSame('CRUZ-JUAN.jpg', $zip->getNameIndex(0));
        $this->assertSame('SANTOS-MARIA.jpg', $zip->getNameIndex(1));
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
            'passport_photo' => 'applicants/approved/CRUZ-JUAN.jpg',
        ]);

        $pending = Applicant::factory()->create([
            'passport_photo' => 'applicants/pending/PENDING-PERSON.jpg',
        ]);

        Storage::disk('public')->put('applicants/approved/CRUZ-JUAN.jpg', 'approved-passport');
        Storage::disk('public')->put('applicants/pending/PENDING-PERSON.jpg', 'pending-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip', ['ids' => [$approved->id, $pending->id]]));

        $response->assertOk();
        $response->assertDownload();

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('CRUZ-JUAN.jpg', $zip->getNameIndex(0));
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
