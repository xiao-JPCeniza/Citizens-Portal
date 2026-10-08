<?php

namespace Tests\Unit;

use App\Exports\VerificationApplicantsExport;
use App\Models\Applicant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerificationApplicantsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_verification_export_maps_requested_columns(): void
    {
        $first = Applicant::factory()->approved()->create([
            'first_name' => 'Juan',
            'middle_name' => 'Dela',
            'last_name' => 'Cruz',
            'birthday' => '1990-05-15',
            'gcash_number' => '09171234567',
        ]);
        $second = Applicant::factory()->approved()->create([
            'first_name' => 'Maria',
            'middle_name' => null,
            'last_name' => 'Santos',
            'birthday' => '1985-12-01',
            'gcash_number' => '09181234567',
        ]);

        $export = new VerificationApplicantsExport(Applicant::query());

        $this->assertSame([
            'No.',
            'FIRST NAME',
            'MIDDLE NAME',
            'LAST NAME',
            'BIRTHDAY',
            'GCASH No.',
        ], $export->headings());

        $this->assertSame(
            [1, 'JUAN', 'DELA', 'CRUZ', '1990-05-15', '09171234567'],
            $export->map($first->fresh()),
        );
        $this->assertSame(
            [2, 'MARIA', '', 'SANTOS', '1985-12-01', '09181234567'],
            $export->map($second->fresh()),
        );
    }
}
