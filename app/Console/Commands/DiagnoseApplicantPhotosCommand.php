<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Support\ApplicantPhotoStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class DiagnoseApplicantPhotosCommand extends Command
{
    protected $signature = 'applicants:diagnose-photos {applicant? : Applicant ID or application_id}';

    protected $description = 'Diagnose missing applicant passport/GCash files on disk';

    public function handle(): int
    {
        $this->line('Storage roots:');
        $this->line('  local(private): '.storage_path('app/private'));
        $this->line('  public:         '.storage_path('app/public'));
        $this->line('  legacy app:     '.storage_path('app'));
        $this->newLine();

        foreach ([
            storage_path('app/private/applicants'),
            storage_path('app/public/applicants'),
            storage_path('app/applicants'),
        ] as $dir) {
            $count = is_dir($dir) ? count(File::allFiles($dir)) : 0;
            $this->line(($count > 0 ? '[ok] ' : '[--] ').$dir.' ('.$count.' file(s))');
        }

        $this->newLine();

        $query = Applicant::query()->orderBy('id');

        if ($this->argument('applicant')) {
            $key = (string) $this->argument('applicant');
            $query->where(function ($builder) use ($key) {
                $builder->where('id', $key)->orWhere('application_id', $key);
            });
        }

        $rows = [];

        foreach ($query->get(['id', 'application_id', 'full_name', 'passport_photo', 'gcash_screenshot']) as $applicant) {
            $passportResolved = ApplicantPhotoStorage::absolutePath($applicant->passport_photo)
                ?? (filled($applicant->application_id)
                    ? ApplicantPhotoStorage::absolutePathForApplication($applicant->application_id, 'passport')
                    : null);

            $gcashResolved = ApplicantPhotoStorage::absolutePath($applicant->gcash_screenshot)
                ?? (filled($applicant->application_id)
                    ? ApplicantPhotoStorage::absolutePathForApplication($applicant->application_id, 'gcash')
                    : null);

            $rows[] = [
                $applicant->id,
                $applicant->application_id,
                $applicant->passport_photo,
                $passportResolved ? 'FOUND' : 'MISSING',
                $applicant->gcash_screenshot,
                $gcashResolved ? 'FOUND' : 'MISSING',
            ];
        }

        if ($rows === []) {
            $this->warn('No applicants found.');

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'App ID', 'Passport DB path', 'Passport', 'GCash DB path', 'GCash'],
            $rows,
        );

        $missing = collect($rows)->filter(fn (array $row) => $row[3] === 'MISSING' || $row[5] === 'MISSING')->count();

        if ($missing > 0) {
            $this->warn("{$missing} applicant(s) have missing file(s).");
            $this->line('Next steps:');
            $this->line('  1. php artisan applicants:secure-photos');
            $this->line('  2. Confirm uploaded files exist under storage/app/private/applicants');
            $this->line('  3. Ensure the web user can read that directory');
        } else {
            $this->info('All checked document files were found.');
        }

        // Helpful for single-applicant debugging.
        if ($this->argument('applicant') && isset($rows[0])) {
            $this->newLine();
            $this->line('local disk exists(passport): '.(Storage::disk('local')->exists((string) $rows[0][2]) ? 'yes' : 'no'));
            $this->line('public disk exists(passport): '.(Storage::disk('public')->exists((string) $rows[0][2]) ? 'yes' : 'no'));
        }

        return self::SUCCESS;
    }
}
