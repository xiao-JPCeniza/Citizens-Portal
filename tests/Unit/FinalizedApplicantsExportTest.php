<?php

namespace Tests\Unit;

use App\Exports\FinalizedApplicantsExport;
use App\Models\Applicant;
use App\Support\ApplicantAddressFormatter;
use App\Support\FinalizedApplicantExportFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinalizedApplicantsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_finalized_export_maps_requested_columns(): void
    {
        $applicant = Applicant::factory()->approved()->create([
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'full_name' => 'Juan Dela Cruz',
            'birthday' => '1990-05-15',
            'gcash_number' => '09171234567',
            'barangay' => 'Tankulan',
            'address' => 'Purok 1',
            'blood_type' => 'O+',
            'emergency_contact_person' => 'Maria Dela Cruz',
            'emergency_contact_number' => '09181234567',
            'passport_photo' => 'applicants/sample/passport.jpg',
        ]);

        $export = new FinalizedApplicantsExport(Applicant::query());

        $this->assertSame([
            'Full Name',
            'First Name',
            'Middle Name',
            'Last Name',
            'Birthday',
            'Gcash Number',
            'Address',
            'Blood Type',
            'Emergency Contact Person',
            'Emergency Contact Number',
            'Passport Photo',
        ], $export->headings());

        $this->assertSame([
            'JUAN DELA CRUZ',
            'JUAN',
            'DELA',
            'CRUZ',
            '1990-05-15',
            '09171234567',
            FinalizedApplicantExportFormatter::address($applicant->fresh()),
            'O+',
            'Maria Dela Cruz',
            '09181234567',
            $applicant->passportPhotoUrl(),
        ], $export->map($applicant->fresh()));
    }

    public function test_full_name_is_exported_in_uppercase_without_truncation(): void
    {
        $applicant = Applicant::factory()->make([
            'full_name' => 'Christopher Alexander Montgomery',
        ]);

        $this->assertSame('CHRISTOPHER ALEXANDER MONTGOMERY', FinalizedApplicantExportFormatter::fullName($applicant));
    }

    public function test_address_matches_formatted_preview(): void
    {
        $applicant = Applicant::factory()->make([
            'address' => 'Purok 1',
            'barangay' => 'Tankulan',
        ]);

        $address = FinalizedApplicantExportFormatter::address($applicant);

        $this->assertSame(
            ApplicantAddressFormatter::build('Purok 1', 'Tankulan'),
            $address,
        );
        $this->assertStringContainsString('Manolo Fortich, Bukidnon', $address);
    }

    public function test_address_is_not_padded_or_truncated_for_export(): void
    {
        $applicant = Applicant::factory()->make([
            'address' => 'Lot 32 and 33 Block 61 Mountain Breeze Residences San Isidro',
            'barangay' => 'Alae',
        ]);

        $address = FinalizedApplicantExportFormatter::address($applicant);

        $this->assertSame(
            ApplicantAddressFormatter::build(
                'Lot 32 and 33 Block 61 Mountain Breeze Residences San Isidro',
                'Alae',
            ),
            $address,
        );
    }
}
