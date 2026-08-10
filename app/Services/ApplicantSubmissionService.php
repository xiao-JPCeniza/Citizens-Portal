<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Mail\ApplicationReceivedMail;
use App\Models\Applicant;
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

            $passportPath = $passportPhoto->storeAs(
                ApplicantPhotoStorage::DIRECTORY,
                basename(ApplicantPhotoStorage::passportPath($applicationId)),
                ApplicantPhotoStorage::DISK,
            );

            $gcashExtension = $gcashScreenshot->getClientOriginalExtension()
                ?: $gcashScreenshot->extension()
                ?: 'jpg';

            $gcashPath = $gcashScreenshot->storeAs(
                ApplicantPhotoStorage::DIRECTORY,
                basename(ApplicantPhotoStorage::gcashPath($applicationId, $gcashExtension)),
                ApplicantPhotoStorage::DISK,
            );

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
        UploadedFile $passportPhoto,
        ?UploadedFile $gcashScreenshot = null,
        string $editToken = '',
    ): Applicant {
        $firstName = strtoupper($data['first_name']);
        $middleName = filled($data['middle_name'] ?? null) ? strtoupper($data['middle_name']) : null;
        $lastName = strtoupper($data['last_name']);
        $fullName = ApplicantNameFormatter::buildFullName($firstName, $middleName ?? '', $lastName);

        $applicant = DB::transaction(function () use (
            $applicant,
            $data,
            $passportPhoto,
            $gcashScreenshot,
            $editToken,
            $firstName,
            $middleName,
            $lastName,
            $fullName,
        ) {
            $locked = Applicant::query()
                ->whereKey($applicant->id)
                ->lockForUpdate()
                ->first();

            if ($locked === null || ! ApplicantEditToken::isValid($locked, $editToken)) {
                throw ValidationException::withMessages([
                    'applicant' => 'Only rejected applications with a valid edit link can be resubmitted.',
                ]);
            }

            $applicationId = $locked->application_id;
            $disk = ApplicantPhotoStorage::disk();

            $passportPath = $passportPhoto->storeAs(
                ApplicantPhotoStorage::DIRECTORY,
                basename(ApplicantPhotoStorage::passportPath($applicationId)),
                ApplicantPhotoStorage::DISK,
            );

            $gcashPath = $locked->gcash_screenshot;

            if ($gcashScreenshot !== null) {
                $previousGcash = $locked->gcash_screenshot;

                $gcashExtension = $gcashScreenshot->getClientOriginalExtension()
                    ?: $gcashScreenshot->extension()
                    ?: 'jpg';

                $gcashPath = $gcashScreenshot->storeAs(
                    ApplicantPhotoStorage::DIRECTORY,
                    basename(ApplicantPhotoStorage::gcashPath($applicationId, $gcashExtension)),
                    ApplicantPhotoStorage::DISK,
                );

                if (
                    filled($previousGcash)
                    && $previousGcash !== $gcashPath
                    && $disk->exists($previousGcash)
                ) {
                    $disk->delete($previousGcash);
                }
            }

            $locked->forceFill([
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
                'rejection_reason' => null,
                'verified_by' => null,
                'verified_at' => null,
                'edit_token_hash' => null,
                'edit_token_expires_at' => null,
            ])->save();

            $fresh = $locked->fresh();

            DB::afterCommit(function () use ($fresh): void {
                Mail::to($fresh->email)->send(new ApplicationReceivedMail($fresh));
            });

            return $fresh;
        });

        return $applicant;
    }
}
