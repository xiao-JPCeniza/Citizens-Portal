<?php

namespace App\Exports;

use App\Models\Applicant;
use App\Support\FinalizedApplicantExportFormatter;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VerificationApplicantsExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles
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
            'FIRST NAME',
            'MIDDLE NAME',
            'LAST NAME',
            'BIRTHDAY',
            'GCASH No.',
        ];
    }

    /**
     * @param  Applicant  $applicant
     */
    public function map($applicant): array
    {
        return [
            ++$this->rowNumber,
            FinalizedApplicantExportFormatter::firstName($applicant),
            FinalizedApplicantExportFormatter::middleName($applicant),
            FinalizedApplicantExportFormatter::lastName($applicant),
            $applicant->birthday?->format('Y-m-d'),
            (string) $applicant->gcash_number,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
