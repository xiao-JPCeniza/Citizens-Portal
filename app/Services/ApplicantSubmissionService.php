<?php

namespace App\Services;

use App\Enums\ApplicantStatus;
use App\Mail\ApplicationReceivedMail;
use App\Models\Applicant;
use App\Support\ApplicantNameFormatter;
use App\Support\ManoloFortich;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        $storagePath = 'applicants/'.Str::uuid();
        $passportFileName = "{$lastName}-{$firstName}.jpg";

        $passportPath = $passportPhoto->storeAs($storagePath, $passportFileName, 'public');
        $gcashPath = $gcashScreenshot->store($storagePath, 'public');

        $fullName = ApplicantNameFormatter::buildFullName($firstName, $middleName ?? '', $lastName);

        $applicant = Applicant::create([
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

        Mail::to($applicant->email)->send(new ApplicationReceivedMail($applicant));

        return $applicant;
    }
}
