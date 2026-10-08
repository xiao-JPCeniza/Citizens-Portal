<?php

namespace App\Services;

use App\Exports\ApplicantsExport;
use App\Exports\FinalizedApplicantsExport;
use App\Exports\VerificationApplicantsExport;
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

        $scope = $filters['scope'] ?? null;
        $prefix = match ($scope) {
            'approved' => 'approved-applications-',
            'finalized' => 'for-verification-applications-',
            default => 'citizen-id-applications-',
        };
        $filename = $prefix.now()->format('Y-m-d-His').'.xlsx';

        $export = match ($scope) {
            'approved' => new FinalizedApplicantsExport($query),
            'finalized' => new VerificationApplicantsExport($query),
            default => new ApplicantsExport($query),
        };

        return Excel::download($export, $filename);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function buildQuery(array $filters = []): Builder
    {
        $query = Applicant::query()->orderBy('created_at');

        match ($filters['scope'] ?? null) {
            'approved' => $query->verified(),
            'finalized' => $query->forVerification(),
            default => null,
        };

        $query
            ->search($filters['search'] ?? null)
            ->inBarangay($filters['barangay'] ?? null)
            ->approvedBetween($filters['date_from'] ?? null, $filters['date_to'] ?? null);

        return $query;
    }
}
