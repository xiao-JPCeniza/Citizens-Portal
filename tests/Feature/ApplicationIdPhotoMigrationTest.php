<?php

namespace Tests\Feature;

use App\Models\Applicant;
use App\Services\ApplicantPhotoNormalizationService;
use App\Support\ApplicantPhotoStorage;
use App\Support\ApplicationIdGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicationIdPhotoMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_normalization_assigns_application_ids_and_moves_photos_into_single_folder(): void
    {
        Storage::fake('public');

        $first = Applicant::factory()->create([
            'application_id' => 'TEMP01',
            'passport_photo' => 'applicants/aaaa-bbbb-cccc-dddd/CRUZ-JUAN.jpg',
            'gcash_screenshot' => 'applicants/aaaa-bbbb-cccc-dddd/hashed-gcash-name.jpg',
        ]);

        $second = Applicant::factory()->create([
            'application_id' => 'TEMP02',
            'passport_photo' => 'applicants/eeee-ffff-gggg-hhhh/SANTOS-MARIA.jpg',
            'gcash_screenshot' => 'applicants/eeee-ffff-gggg-hhhh/another-hash.png',
        ]);

        Storage::disk('public')->put('applicants/aaaa-bbbb-cccc-dddd/CRUZ-JUAN.jpg', 'passport-one');
        Storage::disk('public')->put('applicants/aaaa-bbbb-cccc-dddd/hashed-gcash-name.jpg', 'gcash-one');
        Storage::disk('public')->put('applicants/eeee-ffff-gggg-hhhh/SANTOS-MARIA.jpg', 'passport-two');
        Storage::disk('public')->put('applicants/eeee-ffff-gggg-hhhh/another-hash.png', 'gcash-two');

        app(ApplicantPhotoNormalizationService::class)->normalizeExistingApplicants();

        $first->refresh();
        $second->refresh();

        $this->assertSame('000001', $first->application_id);
        $this->assertSame('000002', $second->application_id);
        $this->assertSame(ApplicantPhotoStorage::passportPath('000001'), $first->passport_photo);
        $this->assertSame(ApplicantPhotoStorage::gcashPath('000001', 'jpg'), $first->gcash_screenshot);
        $this->assertSame(ApplicantPhotoStorage::passportPath('000002'), $second->passport_photo);
        $this->assertSame(ApplicantPhotoStorage::gcashPath('000002', 'png'), $second->gcash_screenshot);

        Storage::disk('public')->assertExists('applicants/000001.jpg');
        Storage::disk('public')->assertExists('applicants/000001-gcash.jpg');
        Storage::disk('public')->assertExists('applicants/000002.jpg');
        Storage::disk('public')->assertExists('applicants/000002-gcash.png');
        Storage::disk('public')->assertMissing('applicants/aaaa-bbbb-cccc-dddd/CRUZ-JUAN.jpg');
        Storage::disk('public')->assertMissing('applicants/eeee-ffff-gggg-hhhh/SANTOS-MARIA.jpg');

        $this->assertSame('passport-one', Storage::disk('public')->get('applicants/000001.jpg'));
        $this->assertSame('gcash-two', Storage::disk('public')->get('applicants/000002-gcash.png'));
    }

    public function test_new_applications_continue_sequence_after_existing_ids(): void
    {
        Applicant::factory()->create(['application_id' => '000300']);

        $this->assertSame('000301', ApplicationIdGenerator::next());
    }
}
