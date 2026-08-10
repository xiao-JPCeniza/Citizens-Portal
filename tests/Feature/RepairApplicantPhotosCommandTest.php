<?php

namespace Tests\Feature;

use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RepairApplicantPhotosCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_repair_photos_remaps_legacy_named_files_to_application_id_paths(): void
    {
        $legacyDir = storage_path('app/private/applicants/legacy-uuid-test');
        $targetPassport = storage_path('app/private/applicants/000001.jpg');
        $targetGcash = storage_path('app/private/applicants/000001-gcash.jpg');

        File::deleteDirectory(storage_path('app/private/applicants'));
        File::ensureDirectoryExists($legacyDir);
        File::put($legacyDir.'/DELA CRUZ-JUAN.jpg', 'passport-bytes');
        File::put($legacyDir.'/random-gcash-hash.jpg', 'gcash-bytes');

        $applicant = Applicant::factory()->create([
            'application_id' => '000001',
            'first_name' => 'JUAN',
            'last_name' => 'DELA CRUZ',
            'passport_photo' => 'applicants/000001.jpg',
            'gcash_screenshot' => 'applicants/000001-gcash.jpg',
        ]);

        try {
            Artisan::call('applicants:repair-photos', [
                'applicant' => $applicant->id,
                '--force' => true,
            ]);

            $applicant->refresh();

            $this->assertSame('applicants/000001.jpg', $applicant->passport_photo);
            $this->assertSame('applicants/000001-gcash.jpg', $applicant->gcash_screenshot);
            $this->assertFileExists($targetPassport);
            $this->assertFileExists($targetGcash);
            $this->assertSame('passport-bytes', File::get($targetPassport));
            $this->assertSame('gcash-bytes', File::get($targetGcash));
        } finally {
            File::deleteDirectory(storage_path('app/private/applicants'));
        }
    }
}
