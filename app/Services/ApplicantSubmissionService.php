<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Mail\ApplicationReceivedMail;
use App\Models\Applicant;
use App\Support\ApplicantCorrectionScope;
use App\Support\ApplicantEditToken;
use App\Support\ApplicantNameFormatter;
use App\Support\ApplicantPhotoStorage;
use App\Support\ApplicationIdGenerator;
use App\Support\ManoloFortich;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ApplicantSubmissionService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function submit(array $data, UploadedFile $passportPhoto, UploadedFile $gcashScreenshot): Applicant
    {
        $firstName = strtoupper($data['first_name']);
        $middleName = filled($data['middle_name'] ?? null) ? strtoupper($data['middle_name']) : null;
        $lastName = strtoupper($data['last_name']);
        $fullName = ApplicantNameFormatter::buildFullName($firstName, $middleName ?? '', $lastName);

        $applicant = DB::transaction(function () use (
            $data,
            $passportPhoto,
            $gcashScreenshot,
            $firstName,
            $middleName,
            $lastName,
            $fullName,
        ) {
            $applicationId = ApplicationIdGenerator::next();

            try {
                $passportPath = ApplicantPhotoStorage::storePassport($passportPhoto, $applicationId);
                $gcashPath = ApplicantPhotoStorage::storeGcash($gcashScreenshot, $applicationId);
            } catch (\Throwable $exception) {
                report($exception);

                throw ValidationException::withMessages([
                    'passport_photo' => self::storageFailureMessage($exception, 'documents'),
                ]);
            }

            $created = Applicant::create([
                'application_id' => $applicationId,
                'email' => $data['email'],
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'full_name' => $fullName,
                'birthday' => $data['birthday'],
                'gcash_number' => $data['gcash_number'],
                'province' => ManoloFortich::PROVINCE,
                'barangay' => $data['barangay'],
                'address' => $data['address'],
                'blood_type' => $data['blood_type'],
                'emergency_contact_person' => $data['emergency_contact_person'],
                'emergency_contact_number' => $data['emergency_contact_number'],
                'passport_photo' => $passportPath,
                'gcash_screenshot' => $gcashPath,
                'status' => ApplicantStatus::Pending,
            ]);

            DB::afterCommit(function () use ($created): void {
                Mail::to($created->email)->send(new ApplicationReceivedMail($created));
            });

            return $created;
        });

        return $applicant;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function resubmit(
        Applicant $applicant,
        array $data,
        ?UploadedFile $passportPhoto = null,
        ?UploadedFile $gcashScreenshot = null,
        string $editToken = '',
    ): Applicant {
        $applicant = DB::transaction(function () use (
            $applicant,
            $data,
            $passportPhoto,
            $gcashScreenshot,
            $editToken,
        ) {
            $locked = Applicant::query()
                ->whereKey($applicant->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! ApplicantEditToken::isValid($locked, $editToken)) {
                throw ValidationException::withMessages([
                    'applicant' => 'Only applications with a valid edit link can be resubmitted.',
                ]);
            }

            $scope = ApplicantCorrectionScope::fromRejectionReason($locked->rejection_reason);
            $applicationId = $locked->application_id;
            $disk = ApplicantPhotoStorage::disk();

            $errors = [];

            if ($scope->requiresPassportPhoto() && $passportPhoto === null) {
                $errors['passport_photo'] = 'Please upload a new passport photo.';
            }

            if ($scope->requiresGcashScreenshot() && $gcashScreenshot === null) {
                $errors['gcash_screenshot'] = 'Please upload a new GCash screenshot.';
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }

            $passportPath = $locked->passport_photo;

            if ($scope->requiresPassportPhoto() && $passportPhoto !== null) {
                try {
                    $passportPath = ApplicantPhotoStorage::storePassport($passportPhoto, $applicationId);
                } catch (\Throwable $exception) {
                    report($exception);

                    throw ValidationException::withMessages([
                        'passport_photo' => self::storageFailureMessage($exception, 'passport photo'),
                    ]);
                }
            }

            $gcashPath = $locked->gcash_screenshot;

            if ($scope->requiresGcashScreenshot() && $gcashScreenshot !== null) {
                $previousGcash = $locked->gcash_screenshot;

                try {
                    $gcashPath = ApplicantPhotoStorage::storeGcash($gcashScreenshot, $applicationId);
                } catch (\Throwable $exception) {
                    report($exception);

                    throw ValidationException::withMessages([
                        'gcash_screenshot' => self::storageFailureMessage($exception, 'GCash screenshot'),
                    ]);
                }

                if (
                    filled($previousGcash)
                    && $previousGcash !== $gcashPath
                    && $disk->exists($previousGcash)
                ) {
                    $disk->delete($previousGcash);
                }
            }

            $updates = [
                'passport_photo' => $passportPath,
                'gcash_screenshot' => $gcashPath,
                'status' => ApplicantStatus::Pending,
                'rejection_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
                'edit_token_hash' => null,
                'edit_token_expires_at' => null,
            ];

            if ($scope->allowsInformationCorrection()) {
                $firstName = strtoupper($data['first_name']);
                $middleName = filled($data['middle_name'] ?? null) ? strtoupper($data['middle_name']) : null;
                $lastName = strtoupper($data['last_name']);

                $updates = array_merge($updates, [
                    'first_name' => $firstName,
                    'middle_name' => $middleName,
                    'last_name' => $lastName,
                    'full_name' => ApplicantNameFormatter::buildFullName($firstName, $middleName ?? '', $lastName),
                    'birthday' => $data['birthday'],
                    'gcash_number' => $data['gcash_number'],
                    'province' => ManoloFortich::PROVINCE,
                    'barangay' => $data['barangay'],
                    'address' => $data['address'],
                    'blood_type' => $data['blood_type'],
                    'emergency_contact_person' => $data['emergency_contact_person'],
                    'emergency_contact_number' => $data['emergency_contact_number'],
                ]);
            }

            $locked->forceFill($updates)->save();

            $fresh = $locked->fresh();

            DB::afterCommit(function () use ($fresh): void {
                Mail::to($fresh->email)->send(new ApplicationReceivedMail($fresh));
            });

            return $fresh;
        });

        return $applicant;
    }

    protected static function storageFailureMessage(\Throwable $exception, string $label): string
    {
        $details = strtolower($exception->getMessage());

        if (
            str_contains($details, 'not writable')
            || str_contains($details, 'permission')
            || str_contains($details, 'write probe failed')
            || str_contains($details, 'could not create')
        ) {
            return "Failed to save the {$label} because server storage is not writable. Please ask the administrator to fix permissions on storage/app/private.";
        }

        return "Failed to save the {$label} to the server. Please try again.";
    }
}
