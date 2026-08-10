<?php

namespace App\Console\Commands;

use App\Support\ApplicantPhotoStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class SecureApplicantPhotosCommand extends Command
{
    protected $signature = 'applicants:secure-photos';

    protected $description = 'Move applicant passport/GCash files from the public disk into private storage';

    public function handle(): int
    {
        $public = Storage::disk('public');
        $private = ApplicantPhotoStorage::disk();

        if (! $public->exists(ApplicantPhotoStorage::DIRECTORY)) {
            $this->info('No public applicant photos directory found. Nothing to move.');

            return self::SUCCESS;
        }

        $moved = 0;
        $skipped = 0;

        foreach ($public->allFiles(ApplicantPhotoStorage::DIRECTORY) as $path) {
            if ($private->exists($path)) {
                $public->delete($path);
                $skipped++;

                continue;
            }

            $private->put($path, $public->get($path));
            $public->delete($path);
            $moved++;
        }

        if ($public->exists(ApplicantPhotoStorage::DIRECTORY)) {
            $remaining = $public->allFiles(ApplicantPhotoStorage::DIRECTORY);

            if ($remaining === []) {
                $public->deleteDirectory(ApplicantPhotoStorage::DIRECTORY);
            }
        }

        $this->info("Moved {$moved} file(s) to private storage. Removed {$skipped} duplicate public file(s).");

        return self::SUCCESS;
    }
}
