<?php

namespace Tests\Feature;

use App\Livewire\Admin\FinalizationTable;
use App\Models\Admin;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;
use ZipArchive;

class AdminPassportZipTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_passport_photos_zip_for_selected_approved_applicants(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $first = Applicant::factory()->verified()->create([
            'application_id' => '000001',
            'first_name' => 'JUAN',
            'middle_name' => 'DELA',
            'last_name' => 'CRUZ',
            'full_name' => 'JUAN D. CRUZ',
            'passport_photo' => 'applicants/000001.jpg',
        ]);

        $second = Applicant::factory()->verified()->create([
            'application_id' => '000002',
            'first_name' => 'MARIA',
            'middle_name' => 'REYES',
            'last_name' => 'SANTOS',
            'full_name' => 'MARIA R. SANTOS',
            'passport_photo' => 'applicants/000002.jpg',
        ]);

        Storage::disk('local')->put('applicants/000001.jpg', 'first-passport');
        Storage::disk('local')->put('applicants/000002.jpg', 'second-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.approved.passport-zip', ['ids' => [$first->id, $second->id]]));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('.zip', (string) $response->headers->get('content-disposition'));

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $this->assertSame(2, $zip->numFiles);
        $this->assertSame('JUAN D. CRUZ.jpg', $zip->getNameIndex(0));
        $this->assertSame('MARIA R. SANTOS.jpg', $zip->getNameIndex(1));
        $zip->close();

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Passport Zip Downloaded',
        ]);
    }

    public function test_passport_zip_excludes_non_approved_applicants(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $approved = Applicant::factory()->verified()->create([
            'application_id' => '000010',
            'full_name' => 'PEDRO P. REYES',
            'passport_photo' => 'applicants/000010.jpg',
        ]);

        $pending = Applicant::factory()->create([
            'application_id' => '000011',
            'passport_photo' => 'applicants/000011.jpg',
        ]);

        Storage::disk('local')->put('applicants/000010.jpg', 'approved-passport');
        Storage::disk('local')->put('applicants/000011.jpg', 'pending-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.approved.passport-zip', ['ids' => [$approved->id, $pending->id]]));

        $response->assertOk();
        $response->assertDownload();

        $zipPath = $response->baseResponse->getFile()->getPathname();
        $zip = new ZipArchive;
        $zip->open($zipPath);
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('PEDRO P. REYES.jpg', $zip->getNameIndex(0));
        $zip->close();
    }

    public function test_passport_zip_disambiguates_applicants_with_the_same_name(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $first = Applicant::factory()->verified()->create([
            'application_id' => '000020',
            'first_name' => 'JOSE',
            'last_name' => 'RIZAL',
            'full_name' => 'JOSE P. RIZAL',
            'passport_photo' => 'applicants/000020.jpg',
        ]);

        $second = Applicant::factory()->verified()->create([
            'application_id' => '000021',
            'first_name' => 'JOSE',
            'last_name' => 'RIZAL',
            'full_name' => 'JOSE P. RIZAL',
            'passport_photo' => 'applicants/000021.png',
        ]);

        Storage::disk('local')->put('applicants/000020.jpg', 'first-passport');
        Storage::disk('local')->put('applicants/000021.png', 'second-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.approved.passport-zip', ['ids' => [$first->id, $second->id]]));

        $response->assertOk();

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $names = [$zip->getNameIndex(0), $zip->getNameIndex(1)];
        $zip->close();

        sort($names);

        $this->assertSame(['JOSE P. RIZAL (000021).png', 'JOSE P. RIZAL.jpg'], $names);
    }

    public function test_passport_zip_requires_at_least_one_selected_applicant(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.approved.passport-zip'))
            ->assertStatus(400);
    }

    public function test_guest_cannot_download_passport_zip(): void
    {
        $this->get(route('admin.approved.passport-zip', ['ids' => [1]]))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_download_passport_photos_zip_for_selected_verification_applicants(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $first = Applicant::factory()->approved()->create([
            'application_id' => '000101',
            'first_name' => 'ANA',
            'middle_name' => 'LIZA',
            'last_name' => 'GARCIA',
            'full_name' => 'ANA L. GARCIA',
            'passport_photo' => 'applicants/000101.jpg',
        ]);

        $second = Applicant::factory()->approved()->create([
            'application_id' => '000102',
            'first_name' => 'LUIS',
            'middle_name' => 'TORRES',
            'last_name' => 'MENDOZA',
            'full_name' => 'LUIS T. MENDOZA',
            'passport_photo' => 'applicants/000102.jpg',
        ]);

        Storage::disk('local')->put('applicants/000101.jpg', 'first-passport');
        Storage::disk('local')->put('applicants/000102.jpg', 'second-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip', ['ids' => [$first->id, $second->id]]));

        $response->assertOk();
        $response->assertDownload();
        $this->assertStringContainsString('verification-id-photos-', (string) $response->headers->get('content-disposition'));

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $this->assertSame(2, $zip->numFiles);
        $names = [$zip->getNameIndex(0), $zip->getNameIndex(1)];
        $zip->close();

        sort($names);

        $this->assertSame(['ANA L. GARCIA.jpg', 'LUIS T. MENDOZA.jpg'], $names);

        $this->assertDatabaseHas('activity_logs', [
            'admin_id' => $admin->id,
            'action' => 'Passport Zip Downloaded',
        ]);
    }

    public function test_verification_passport_zip_excludes_applicants_that_are_not_awaiting_verification(): void
    {
        Storage::fake('local');

        $admin = Admin::factory()->create();

        $awaiting = Applicant::factory()->approved()->create([
            'application_id' => '000110',
            'full_name' => 'ROSA M. DELA CRUZ',
            'passport_photo' => 'applicants/000110.jpg',
        ]);

        $verified = Applicant::factory()->verified()->create([
            'application_id' => '000111',
            'full_name' => 'CARLO V. SANTOS',
            'passport_photo' => 'applicants/000111.jpg',
        ]);

        Storage::disk('local')->put('applicants/000110.jpg', 'awaiting-passport');
        Storage::disk('local')->put('applicants/000111.jpg', 'verified-passport');

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip', ['ids' => [$awaiting->id, $verified->id]]));

        $response->assertOk();
        $response->assertDownload();

        $zip = new ZipArchive;
        $zip->open($response->baseResponse->getFile()->getPathname());
        $this->assertSame(1, $zip->numFiles);
        $this->assertSame('ROSA M. DELA CRUZ.jpg', $zip->getNameIndex(0));
        $zip->close();
    }

    public function test_verification_dashboard_links_selected_applicants_to_the_zip_download(): void
    {
        $admin = Admin::factory()->create();
        $applicant = Applicant::factory()->approved()->create([
            'full_name' => 'Selected For Zip',
        ]);

        Livewire::actingAs($admin, 'admin')
            ->test(FinalizationTable::class)
            ->assertSee('Download Zip ID')
            ->set('selectedApplicants', [(string) $applicant->id])
            ->assertSee(route('admin.finalized.passport-zip', ['ids' => [(string) $applicant->id]]), false)
            ->call('toggleSelectAllOnPage')
            ->assertSet('selectedApplicants', []);
    }

    public function test_verification_passport_zip_requires_at_least_one_selected_applicant(): void
    {
        $admin = Admin::factory()->create();

        $this->actingAs($admin, 'admin')
            ->get(route('admin.finalized.passport-zip'))
            ->assertStatus(400);
    }

    public function test_guest_cannot_download_verification_passport_zip(): void
    {
        $this->get(route('admin.finalized.passport-zip', ['ids' => [1]]))
            ->assertRedirect(route('admin.login'));
    }
}
