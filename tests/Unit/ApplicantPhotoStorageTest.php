<?php

namespace Tests\Unit;

use App\Support\ApplicantPhotoStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ApplicantPhotoStorageTest extends TestCase
{
    public function test_store_passport_and_gcash_use_application_id_naming_convention(): void
    {
        Storage::fake('local');

        $passport = UploadedFile::fake()->image('My Passport Photo.JPG', 1200, 1200);
        $gcash = UploadedFile::fake()->image('screenshot.JPEG');

        $passportPath = ApplicantPhotoStorage::storePassport($passport, '000042');
        $gcashPath = ApplicantPhotoStorage::storeGcash($gcash, '000042');

        $this->assertSame('applicants/000042.jpg', $passportPath);
        $this->assertSame('applicants/000042-gcash.jpg', $gcashPath);

        Storage::disk('local')->assertExists('applicants/000042.jpg');
        Storage::disk('local')->assertExists('applicants/000042-gcash.jpg');
        $this->assertGreaterThan(0, Storage::disk('local')->size('applicants/000042.jpg'));
        $this->assertGreaterThan(0, Storage::disk('local')->size('applicants/000042-gcash.jpg'));
    }

    public function test_store_passport_overwrites_existing_canonical_file(): void
    {
        Storage::fake('local');

        Storage::disk('local')->put('applicants/000007.jpg', 'old-bytes');

        $passport = UploadedFile::fake()->image('fresh.jpg', 1200, 1200);
        $path = ApplicantPhotoStorage::storePassport($passport, '000007');

        $this->assertSame('applicants/000007.jpg', $path);
        $this->assertNotSame('old-bytes', Storage::disk('local')->get('applicants/000007.jpg'));
    }
}
