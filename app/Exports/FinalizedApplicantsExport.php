<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Support\FinalizedApplicantExportFormatter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinalizedApplicantsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(
        private Builder $query,
    ) {}

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
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
        ];
    }

    /**
     * @param  Applicant  $applicant
     */
    public function map($applicant): array
    {
        return [
            FinalizedApplicantExportFormatter::fullName($applicant),
            FinalizedApplicantExportFormatter::firstName($applicant),
            FinalizedApplicantExportFormatter::middleName($applicant),
            FinalizedApplicantExportFormatter::lastName($applicant),
            $applicant->birthday?->format('Y-m-d'),
            $applicant->gcash_number,
            FinalizedApplicantExportFormatter::address($applicant),
            $applicant->blood_type,
            $applicant->emergency_contact_person,
            $applicant->emergency_contact_number,
            $applicant->passportPhotoUrl(),
        ];
    }
}
