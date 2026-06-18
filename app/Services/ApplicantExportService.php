<?php

namespace App\Services;

use App\Exports\ApplicantsExport;
use App\Exports\FinalizedApplicantsExport;
use App\Models\Applicant;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ApplicantExportService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function download(array $filters = []): BinaryFileResponse
    {
        $query = $this->buildQuery($filters);

        $isFinalized = ($filters['scope'] ?? null) === 'finalized';
        $filename = ($isFinalized ? 'finalized-applications-' : 'citizen-id-applications-')
            .now()->format('Y-m-d-His').'.xlsx';

        $export = $isFinalized
            ? new FinalizedApplicantsExport($query)
            : new ApplicantsExport($query);

        return Excel::download($export, $filename);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function buildQuery(array $filters = []): Builder
    {
        $query = Applicant::query()->orderBy('created_at');

        if (($filters['scope'] ?? null) === 'finalized') {
            $query->finalized();
        }

        $query
            ->search($filters['search'] ?? null)
            ->inBarangay($filters['barangay'] ?? null)
            ->approvedBetween($filters['date_from'] ?? null, $filters['date_to'] ?? null);

        return $query;
    }
}
