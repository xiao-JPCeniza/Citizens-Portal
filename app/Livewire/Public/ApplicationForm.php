<?php

namespace App\Livewire\Public;

use App\Models\Barangay;
use App\Services\ApplicantSubmissionService;
use App\Support\ApplicantAddressFormatter;
use App\Support\ApplicantFieldConstraints;
use App\Support\ApplicantNameFormatter;
use App\Support\ManoloFortich;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
class ApplicationForm extends Component
{
    use WithFileUploads;

    public string $email = '';

    public string $first_name = '';

    public string $middle_name = '';

    public string $last_name = '';

    public string $birthday = '';

    public string $gcash_number = '';

    public string $province = ManoloFortich::PROVINCE;

    public string $barangay = '';

    public string $address = '';

    public string $blood_type = '';

    public string $emergency_contact_person = '';

    public string $emergency_contact_number = '';

    public $passport_photo = null;

    public $gcash_screenshot = null;

    public bool $submitted = false;

    public function mount(): void
    {
        if (request()->boolean('terms_accepted')) {
            session(['terms_accepted' => true]);
        }

        if (! session('terms_accepted')) {
            session()->flash('error', 'Please accept the Terms and Conditions before applying.');
            $this->redirect(route('welcome'), navigate: false);

            return;
        }

        $verifiedEmail = session('application_verified_email');

        if (! is_string($verifiedEmail) || $verifiedEmail === '') {
            session()->flash('error', 'Please verify your email address before applying.');
            $this->redirect(route('verify-email'), navigate: false);

            return;
        }

        $this->email = $verifiedEmail;
    }

    public function updatedFirstName(): void
    {
        $this->first_name = strtoupper($this->first_name);
    }

    public function updatedMiddleName(): void
    {
        $this->middle_name = strtoupper($this->middle_name);
    }

    public function updatedLastName(): void
    {
        $this->last_name = strtoupper($this->last_name);
    }

    #[Computed]
    public function fullName(): string
    {
        return ApplicantNameFormatter::buildFullName(
            $this->first_name,
            $this->middle_name,
            $this->last_name,
        );
    }

    #[Computed]
    public function completeAddress(): string
    {
        return ApplicantAddressFormatter::build($this->address, $this->barangay);
    }

    public function rules(): array
    {
        $barangayNames = Barangay::query()
            ->active()
            ->forMunicipality(ManoloFortich::PROVINCE)
            ->pluck('name')
            ->all();

        return [
            'email' => ['required', 'email', 'max:255', Rule::in([session('application_verified_email')])],
            'first_name' => 'required|string|max:100',
            'middle_name' => 'nullable|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthday' => 'required|date|before_or_equal:today|after:1900-01-01',
            'gcash_number' => ['required', 'string', 'regex:'.ApplicantFieldConstraints::phoneNumberPattern()],
            'barangay' => ['required', 'string', Rule::in($barangayNames)],
            'address' => [
                'required',
                'string',
                'max:1000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $length = ApplicantAddressFormatter::length((string) $value, $this->barangay);

                    if ($length > ApplicantAddressFormatter::MAX_LENGTH) {
                        $fail('Complete address (including barangay and Manolo Fortich, Bukidnon) must not exceed '.ApplicantAddressFormatter::MAX_LENGTH.' characters.');
                    }
                },
            ],
            'blood_type' => ['required', 'string', Rule::in(ManoloFortich::BLOOD_TYPES)],
            'emergency_contact_person' => 'required|string|max:'.ApplicantFieldConstraints::EMERGENCY_CONTACT_PERSON_MAX_LENGTH,
            'emergency_contact_number' => ['required', 'string', 'regex:'.ApplicantFieldConstraints::phoneNumberPattern()],
            'passport_photo' => 'required|image|mimes:jpg,jpeg|max:5120',
            'gcash_screenshot' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'gcash_number.regex' => 'GCash number must be exactly '.ApplicantFieldConstraints::PHONE_NUMBER_LENGTH.' digits starting with 09.',
            'emergency_contact_number.regex' => 'Emergency contact number must be exactly '.ApplicantFieldConstraints::PHONE_NUMBER_LENGTH.' digits starting with 09.',
            'emergency_contact_person.max' => 'Emergency contact person must not exceed '.ApplicantFieldConstraints::EMERGENCY_CONTACT_PERSON_MAX_LENGTH.' characters.',
            'email.in' => 'The email address must match your verified email.',
            'passport_photo.mimes' => 'Passport photo must be a JPG file.',
            'gcash_screenshot.mimes' => 'GCash screenshot must be a JPG or PNG image.',
        ];
    }

    public function submit(ApplicantSubmissionService $submissionService): void
    {
        $this->first_name = strtoupper($this->first_name);
        $this->middle_name = strtoupper($this->middle_name);
        $this->last_name = strtoupper($this->last_name);

        $validated = $this->validate();

        $submissionService->submit(
            $validated,
            $this->passport_photo,
            $this->gcash_screenshot,
        );

        $this->submitted = true;
        session()->forget(['terms_accepted', 'application_verified_email']);
    }

    public function render()
    {
        return view('livewire.public.application-form', [
            'barangays' => Barangay::query()
                ->active()
                ->forMunicipality(ManoloFortich::PROVINCE)
                ->orderBy('name')
                ->pluck('name'),
            'bloodTypes' => ManoloFortich::BLOOD_TYPES,
            'phoneNumberLength' => ApplicantFieldConstraints::PHONE_NUMBER_LENGTH,
            'emergencyContactPersonMaxLength' => ApplicantFieldConstraints::EMERGENCY_CONTACT_PERSON_MAX_LENGTH,
            'addressMaxLength' => ApplicantAddressFormatter::MAX_LENGTH,
            'addressLocationSuffix' => ApplicantAddressFormatter::LOCATION_SUFFIX,
        ])->title('Apply for Citizen ID');
    }
}
