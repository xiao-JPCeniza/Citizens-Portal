<?php

namespace App\Console\Commands;

use App\Models\Applicant;
use App\Support\ApplicantPhotoStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RepairApplicantPhotosCommand extends Command
{
    protected $signature = 'applicants:repair-photos
                            {applicant? : Applicant ID or application_id}
                            {--dry-run : Show what would be repaired without changing files}
                            {--force : Apply repairs}';

    protected $description = 'Remap orphaned applicant photos to application_id filenames';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->option('dry-run')) {
            $this->warn('Running in dry-run mode. Pass --force to apply repairs.');
        }

        $dryRun = ! $this->option('force');
        $index = $this->buildFileIndex();

        $this->line('Indexed '.$index['count'].' file(s) under applicant photo directories.');
        $this->newLine();

        if ($index['samples'] !== []) {
            $this->line('Sample files on disk:');
            foreach ($index['samples'] as $sample) {
                $this->line('  - '.$sample);
            }
            $this->newLine();
        }

        $query = Applicant::query()->orderBy('id');

        if ($this->argument('applicant')) {
            $key = (string) $this->argument('applicant');
            $query->where(function ($builder) use ($key) {
                $builder->where('id', $key)->orWhere('application_id', $key);
            });
        }

        $repaired = 0;
        $stillMissing = 0;
        $rows = [];

        foreach ($query->cursor() as $applicant) {
            $passportResult = $this->repairDocument($applicant, 'passport', $index, $dryRun);
            $gcashResult = $this->repairDocument($applicant, 'gcash', $index, $dryRun);

            if ($passportResult['status'] === 'repaired' || $gcashResult['status'] === 'repaired') {
                $repaired++;
            }

            if ($passportResult['status'] === 'missing' || $gcashResult['status'] === 'missing') {
                $stillMissing++;
            }

            $rows[] = [
                $applicant->id,
                $applicant->application_id,
                $passportResult['status'],
                $passportResult['source'] ?? '—',
                $gcashResult['status'],
                $gcashResult['source'] ?? '—',
            ];
        }

        $this->table(
            ['ID', 'App ID', 'Passport', 'Passport source', 'GCash', 'GCash source'],
            $rows,
        );

        if ($dryRun) {
            $this->info("Dry-run complete. {$repaired} applicant(s) can be repaired.");
            $this->line('Re-run with --force to apply: php artisan applicants:repair-photos --force');
        } else {
            $this->info("Repair complete. Updated {$repaired} applicant(s).");
        }

        if ($stillMissing > 0) {
            $this->warn("{$stillMissing} applicant(s) still have missing document(s).");
        }

        return self::SUCCESS;
    }

    /**
     * @param  array{count: int, files: array<string, string>, byDirectory: array<string, list<string>>}  $index
     * @return array{status: string, source?: string}
     */
    protected function repairDocument(Applicant $applicant, string $type, array $index, bool $dryRun): array
    {
        $stored = $type === 'passport' ? $applicant->passport_photo : $applicant->gcash_screenshot;
        $existing = ApplicantPhotoStorage::absolutePath($stored)
            ?? (filled($applicant->application_id)
                ? ApplicantPhotoStorage::absolutePathForApplication($applicant->application_id, $type)
                : null);

        $source = $existing ?? $this->findOrphanedFile($applicant, $type, $index);

        if ($source === null) {
            return ['status' => 'missing'];
        }

        $expectedRelative = $this->expectedRelativePath($applicant, $type, $source);
        $expectedAbsolute = ApplicantPhotoStorage::disk()->path($expectedRelative);
        $alreadyCorrect = is_file($expectedAbsolute)
            && @md5_file($source) === @md5_file($expectedAbsolute)
            && ApplicantPhotoStorage::normalizeRelativePath($stored) === $expectedRelative;

        if ($alreadyCorrect) {
            return ['status' => 'ok', 'source' => $expectedRelative];
        }

        if (! $dryRun) {
            $this->installFile($source, $expectedRelative);
            $applicant->forceFill([
                $type === 'passport' ? 'passport_photo' : 'gcash_screenshot' => $expectedRelative,
            ])->save();
        }

        return [
            'status' => 'repaired',
            'source' => $this->displaySource($source),
        ];
    }

    protected function expectedRelativePath(Applicant $applicant, string $type, string $sourceAbsolute): string
    {
        $extension = strtolower(pathinfo($sourceAbsolute, PATHINFO_EXTENSION) ?: 'jpg');

        if ($type === 'gcash') {
            return ApplicantPhotoStorage::gcashPath((string) $applicant->application_id, $extension);
        }

        // Passport files are normalized to .jpg in current app storage rules.
        return ApplicantPhotoStorage::passportPath((string) $applicant->application_id);
    }

    protected function installFile(string $sourceAbsolute, string $relativeTarget): void
    {
        $disk = ApplicantPhotoStorage::disk();
        $contents = File::get($sourceAbsolute);

        if ($disk->exists($relativeTarget)) {
            $disk->delete($relativeTarget);
        }

        $disk->put($relativeTarget, $contents);
    }

    /**
     * @param  array{files: array<string, string>, byDirectory: array<string, list<string>>}  $index
     */
    protected function findOrphanedFile(Applicant $applicant, string $type, array $index): ?string
    {
        $files = $index['files'];

        foreach ($this->basenameCandidates($applicant, $type) as $candidate) {
            $key = strtolower($candidate);

            if (isset($files[$key])) {
                return $files[$key];
            }
        }

        if ($type === 'gcash') {
            $passportOrphan = null;

            foreach ($this->basenameCandidates($applicant, 'passport') as $candidate) {
                $key = strtolower($candidate);

                if (isset($files[$key])) {
                    $passportOrphan = $files[$key];
                    break;
                }
            }

            if ($passportOrphan !== null) {
                $dir = dirname($passportOrphan);
                $companions = $index['byDirectory'][strtolower($dir)] ?? [];

                foreach ($companions as $companion) {
                    if ($companion === $passportOrphan) {
                        continue;
                    }

                    $name = strtolower(basename($companion));

                    if (str_contains($name, 'gcash')) {
                        return $companion;
                    }
                }

                // Legacy UUID folders typically contain exactly two files: passport + gcash.
                $other = array_values(array_filter(
                    $companions,
                    fn (string $path) => $path !== $passportOrphan,
                ));

                if (count($other) === 1) {
                    return $other[0];
                }
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    protected function basenameCandidates(Applicant $applicant, string $type): array
    {
        if ($type === 'gcash') {
            return [
                $applicant->application_id.'-gcash.jpg',
                $applicant->application_id.'-gcash.jpeg',
                $applicant->application_id.'-gcash.png',
                $applicant->application_id.'-gcash.pdf',
            ];
        }

        $last = Str::upper(trim((string) $applicant->last_name));
        $first = Str::upper(trim((string) $applicant->first_name));
        $middle = Str::upper(trim((string) $applicant->middle_name));
        $candidates = [
            $applicant->application_id.'.jpg',
            $applicant->application_id.'.jpeg',
        ];

        if ($last !== '' && $first !== '') {
            $lastCompact = str_replace(' ', '', $last);
            $lastUnderscore = str_replace(' ', '_', $last);
            $firstCompact = str_replace(' ', '', $first);

            foreach ([$last, $lastCompact, $lastUnderscore] as $lastVariant) {
                foreach ([$first, $firstCompact] as $firstVariant) {
                    $candidates[] = $lastVariant.'-'.$firstVariant.'.jpg';
                    $candidates[] = $lastVariant.'-'.$firstVariant.'.jpeg';
                    $candidates[] = $lastVariant.'_'.$firstVariant.'.jpg';
                    $candidates[] = $firstVariant.'-'.$lastVariant.'.jpg';
                }
            }

            if ($middle !== '') {
                $candidates[] = $last.'-'.$first.'-'.$middle.'.jpg';
                $candidates[] = $last.'-'.$middle.'-'.$first.'.jpg';
            }
        }

        return $candidates;
    }

    protected function displaySource(string $absolute): string
    {
        $storageRoot = storage_path('app');

        if (str_starts_with($absolute, $storageRoot)) {
            return ltrim(str_replace('\\', '/', substr($absolute, strlen($storageRoot))), '/');
        }

        return $absolute;
    }

    /**
     * @return array{count: int, files: array<string, string>, byDirectory: array<string, list<string>>, samples: list<string>}
     */
    protected function buildFileIndex(): array
    {
        $roots = [
            storage_path('app/private/applicants'),
            storage_path('app/public/applicants'),
            storage_path('app/applicants'),
        ];

        $files = [];
        $byDirectory = [];
        $samples = [];
        $count = 0;

        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            foreach (File::allFiles($root) as $file) {
                $count++;
                $absolute = $file->getPathname();
                $basename = strtolower($file->getFilename());
                $dirKey = strtolower($file->getPath());

                if (! isset($files[$basename])) {
                    $files[$basename] = $absolute;
                }

                $byDirectory[$dirKey][] = $absolute;

                if (count($samples) < 15) {
                    $samples[] = $this->displaySource($absolute);
                }
            }
        }

        return [
            'count' => $count,
            'files' => $files,
            'byDirectory' => $byDirectory,
            'samples' => $samples,
        ];
    }
}
