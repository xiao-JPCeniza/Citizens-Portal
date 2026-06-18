<?php

namespace App\Exports;

use App\Models\Applicant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ApplicantsExport implements FromQuery, WithHeadings, WithMapping
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
            'Birthday',
            'Email Address',
            'GCash Number',
            'Barangay',
            'Address',
            'Blood Type',
            'Emergency Contact Person',
            'Emergency Contact Number',
            'Passport Photo Preview',
            'Status',
            'Date Submitted',
            'Date Approved',
        ];
    }

    /**
     * @param  Applicant  $applicant
     */
    public function map($applicant): array
    {
        return [
            $applicant->full_name,
            $applicant->birthday?->format('Y-m-d'),
            $applicant->email,
            $applicant->gcash_number,
            $applicant->barangay,
            $applicant->address,
            $applicant->blood_type,
            $applicant->emergency_contact_person,
            $applicant->emergency_contact_number,
            $applicant->passportPhotoUrl(),
            $applicant->status->value,
            $applicant->created_at?->format('Y-m-d H:i:s'),
            $applicant->verified_at?->format('Y-m-d H:i:s'),
        ];
    }
}
