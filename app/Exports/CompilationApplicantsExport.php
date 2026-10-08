<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Support\ApplicantPhotoFileName;
use App\Support\FinalizedApplicantExportFormatter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CompilationApplicantsExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
{
    private int $rowNumber = 0;

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
            'No.',
            'FULL NAME',
            'firstname',
            'middlename',
            'lastname',
            'birthday',
            'gcashnumber',
            'Address',
            'Blood Type',
            'Unique ID# / Company ID # / Membership #',
            'Emergency Contact Person',
            'Emergency Contact Number',
            'Photo ID (if with Photo. JPG Format Only)',
        ];
    }

    /**
     * @param  Applicant  $applicant
     */
    public function map($applicant): array
    {
        return [
            ++$this->rowNumber,
            FinalizedApplicantExportFormatter::fullName($applicant),
            FinalizedApplicantExportFormatter::firstName($applicant),
            FinalizedApplicantExportFormatter::middleName($applicant),
            FinalizedApplicantExportFormatter::lastName($applicant),
            $applicant->birthday?->format('Y-m-d'),
            (string) $applicant->gcash_number,
            FinalizedApplicantExportFormatter::address($applicant),
            $applicant->blood_type,
            (string) $applicant->application_id,
            $applicant->emergency_contact_person,
            (string) $applicant->emergency_contact_number,
            ApplicantPhotoFileName::for($applicant),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_TEXT,
            'J' => NumberFormat::FORMAT_TEXT,
            'L' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
