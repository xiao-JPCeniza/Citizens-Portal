<?php

namespace App\Livewire\Public;

use App\Models\Applicant;
use App\Models\Barangay;
use App\Services\ApplicantSubmissionService;
use App\Support\ApplicantAddressFormatter;
use App\Support\ApplicantEditToken;
use App\Support\ApplicantFieldConstraints;
use App\Support\ApplicantNameFormatter;
use App\Support\ManoloFortich;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.public')]
class ApplicationEditForm extends Component
{
    use WithFileUploads;

    public Applicant $applicant;

    #[Locked]
    public string $token = '';

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

    public function mount(Applicant $applicant, string $token): void
    {
        $this->token = strtolower($token);
        $this->applicant = $applicant->fresh() ?? $applicant;

        $this->assertAuthorizedAccess();

        $this->email = $this->applicant->email;
        $this->first_name = $this->applicant->first_name;
        $this->middle_name = (string) $this->applicant->middle_name;
        $this->last_name = $this->applicant->last_name;
        $this->birthday = optional($this->applicant->birthday)->format('Y-m-d') ?? '';
        $this->gcash_number = $this->applicant->gcash_number;
        $this->barangay = $this->applicant->barangay;
        $this->address = $this->applicant->address;
        $this->blood_type = $this->applicant->blood_type;
        $this->emergency_contact_person = $this->applicant->emergency_contact_person;
        $this->emergency_contact_number = $this->applicant->emergency_contact_number;
    }

    public function hydrate(): void
    {
        if ($this->submitted) {
            return;
        }

        $this->assertAuthorizedAccess();
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

    public function updatedPassportPhoto(): void
    {
        $this->validateOnly('passport_photo');
    }

    public function updatedGcashScreenshot(): void
    {
        $this->validateOnly('gcash_screenshot');
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
            'passport_photo' => 'required|image|mimes:jpg,jpeg|dimensions:width=1200,height=1200|max:5120',
            'gcash_screenshot' => 'nullable|image|mimes:jpg,jpeg|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'gcash_number.regex' => 'GCash number must be exactly '.ApplicantFieldConstraints::PHONE_NUMBER_LENGTH.' digits starting with 09.',
            'emergency_contact_number.regex' => 'Emergency contact number must be exactly '.ApplicantFieldConstraints::PHONE_NUMBER_LENGTH.' digits starting with 09.',
            'emergency_contact_person.max' => 'Emergency contact person must not exceed '.ApplicantFieldConstraints::EMERGENCY_CONTACT_PERSON_MAX_LENGTH.' characters.',
            'passport_photo.required' => 'Please upload a new passport photo.',
            'passport_photo.mimes' => 'Passport photo must be a JPG or JPEG file.',
            'passport_photo.dimensions' => 'Passport photo must be exactly 1200 x 1200 pixels.',
            'gcash_screenshot.mimes' => 'GCash screenshot must be a JPG or JPEG file.',
            'gcash_screenshot.image' => 'GCash screenshot must be an image file.',
        ];
    }

    public function submit(ApplicantSubmissionService $submissionService): void
    {
        $this->assertAuthorizedAccess();
        $this->ensureWithinSubmitRateLimit();

        $this->first_name = strtoupper($this->first_name);
        $this->middle_name = strtoupper($this->middle_name);
        $this->last_name = strtoupper($this->last_name);

        $validated = $this->validate();

        try {
        $submissionService->resubmit(
            $this->applicant,
            $validated,
            $this->passport_photo,
            $this->gcash_screenshot,
            $this->token,
        );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }

        $this->submitted = true;
        RateLimiter::clear($this->submitRateLimitKey());
    }

    protected function assertAuthorizedAccess(): void
    {
        $this->applicant = $this->applicant->fresh() ?? $this->applicant;

        if (! ApplicantEditToken::isValid($this->applicant, $this->token)) {
            abort(404);
        }
    }

    protected function ensureWithinSubmitRateLimit(): void
    {
        $key = $this->submitRateLimitKey();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            abort(429, 'Too many resubmission attempts. Please try again later.');
        }

        RateLimiter::hit($key, 60 * 15);
    }

    protected function submitRateLimitKey(): string
    {
        return 'application-edit-submit:'.$this->applicant->id.':'.request()->ip();
    }

    public function render()
    {
        return view('livewire.public.application-edit-form', [
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
        ])->title('Update Citizen ID Application');
    }
}
