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

    public const DRAFT_SESSION_KEY = 'application_form_draft';

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

    public int $formStep = 1;

    public string $has_gcash = '';

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
        $this->restoreDraft($verifiedEmail);
    }

    public function updatedFirstName(): void
    {
        $this->first_name = strtoupper($this->first_name);
        $this->persistDraft();
    }

    public function updatedMiddleName(): void
    {
        $this->middle_name = strtoupper($this->middle_name);
        $this->persistDraft();
    }

    public function updatedLastName(): void
    {
        $this->last_name = strtoupper($this->last_name);
        $this->persistDraft();
    }

    public function updatedBirthday(): void
    {
        $this->persistDraft();
    }

    public function updatedGcashNumber(): void
    {
        $this->persistDraft();
    }

    public function updatedBarangay(): void
    {
        $this->persistDraft();
    }

    public function updatedAddress(): void
    {
        $this->persistDraft();
    }

    public function updatedBloodType(): void
    {
        $this->persistDraft();
    }

    public function updatedEmergencyContactPerson(): void
    {
        $this->persistDraft();
    }

    public function updatedEmergencyContactNumber(): void
    {
        $this->persistDraft();
    }

    public function updatedHasGcash(): void
    {
        if ($this->has_gcash !== 'yes') {
            $this->gcash_screenshot = null;
            $this->resetValidation('gcash_screenshot');
        }

        $this->persistDraft();
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

    public function next(): void
    {
        $this->first_name = strtoupper($this->first_name);
        $this->middle_name = strtoupper($this->middle_name);
        $this->last_name = strtoupper($this->last_name);

        $this->validate($this->stepOneRules());

        $this->formStep = 2;
        $this->persistDraft();
    }

    public function back(): void
    {
        $this->formStep = 1;
        $this->persistDraft();
    }

    /**
     * @return array<string, mixed>
     */
    public function stepOneRules(): array
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
            'emergency_contact_person' => [
                'required',
                'string',
                'max:'.ApplicantFieldConstraints::EMERGENCY_CONTACT_PERSON_MAX_LENGTH,
                'regex:'.ApplicantFieldConstraints::personNamePattern(),
            ],
            'emergency_contact_number' => [
                'required',
                'string',
                'regex:'.ApplicantFieldConstraints::phoneNumberPattern(),
                'different:gcash_number',
            ],
        ];
    }

    public function rules(): array
    {
        return array_merge($this->stepOneRules(), [
            'has_gcash' => ['required', 'in:yes'],
            'passport_photo' => 'required|image|mimes:jpg,jpeg|dimensions:width=1200,height=1200|max:5120',
            'gcash_screenshot' => 'required|image|mimes:jpg,jpeg|max:5120',
        ]);
    }

    public function messages(): array
    {
        return [
            'gcash_number.regex' => 'GCash number must be exactly '.ApplicantFieldConstraints::PHONE_NUMBER_LENGTH.' digits starting with 09.',
            'emergency_contact_number.regex' => 'Emergency contact number must be exactly '.ApplicantFieldConstraints::PHONE_NUMBER_LENGTH.' digits starting with 09.',
            'emergency_contact_number.different' => 'Emergency contact number must be different from your GCash number.',
            'emergency_contact_person.max' => 'Emergency contact person must not exceed '.ApplicantFieldConstraints::EMERGENCY_CONTACT_PERSON_MAX_LENGTH.' characters.',
            'emergency_contact_person.regex' => 'Emergency contact person must be a name (letters only, no numbers).',
            'email.in' => 'The email address must match your verified email.',
            'has_gcash.required' => 'Please confirm whether you have a GCash account before submitting.',
            'has_gcash.in' => 'A verified GCash account and screenshot are required to submit your application. Once your GCash is ready, select Yes and upload your screenshot.',
            'passport_photo.mimes' => 'Passport photo must be a JPG or JPEG file.',
            'passport_photo.dimensions' => 'Passport photo must be exactly 1200 x 1200 pixels.',
            'gcash_screenshot.mimes' => 'GCash screenshot must be a JPG or JPEG file.',
            'gcash_screenshot.image' => 'GCash screenshot must be an image file.',
            'gcash_screenshot.required' => 'GCash screenshot is required. Select Yes above and upload a clear screenshot of your GCash account.',
        ];
    }

    public function submit(ApplicantSubmissionService $submissionService): void
    {
        if ($this->formStep !== 2) {
            $this->formStep = 2;
        }

        $this->first_name = strtoupper($this->first_name);
        $this->middle_name = strtoupper($this->middle_name);
        $this->last_name = strtoupper($this->last_name);

        $validated = $this->validate();

        unset($validated['has_gcash']);

        $submissionService->submit(
            $validated,
            $this->passport_photo,
            $this->gcash_screenshot,
        );

        $this->submitted = true;
        $this->clearDraft();
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

    protected function persistDraft(): void
    {
        $verifiedEmail = session('application_verified_email');

        if (! is_string($verifiedEmail) || $verifiedEmail === '') {
            return;
        }

        session([
            self::DRAFT_SESSION_KEY => [
                'email' => $verifiedEmail,
                'formStep' => $this->formStep,
                'has_gcash' => $this->has_gcash,
                'first_name' => $this->first_name,
                'middle_name' => $this->middle_name,
                'last_name' => $this->last_name,
                'birthday' => $this->birthday,
                'gcash_number' => $this->gcash_number,
                'barangay' => $this->barangay,
                'address' => $this->address,
                'blood_type' => $this->blood_type,
                'emergency_contact_person' => $this->emergency_contact_person,
                'emergency_contact_number' => $this->emergency_contact_number,
            ],
        ]);
    }

    protected function restoreDraft(string $verifiedEmail): void
    {
        $draft = session(self::DRAFT_SESSION_KEY);

        if (! is_array($draft)) {
            return;
        }

        if (($draft['email'] ?? null) !== $verifiedEmail) {
            $this->clearDraft();

            return;
        }

        $this->formStep = in_array((int) ($draft['formStep'] ?? 1), [1, 2], true)
            ? (int) $draft['formStep']
            : 1;
        $this->has_gcash = in_array($draft['has_gcash'] ?? '', ['', 'yes', 'no'], true)
            ? (string) $draft['has_gcash']
            : '';
        $this->first_name = (string) ($draft['first_name'] ?? '');
        $this->middle_name = (string) ($draft['middle_name'] ?? '');
        $this->last_name = (string) ($draft['last_name'] ?? '');
        $this->birthday = (string) ($draft['birthday'] ?? '');
        $this->gcash_number = (string) ($draft['gcash_number'] ?? '');
        $this->barangay = (string) ($draft['barangay'] ?? '');
        $this->address = (string) ($draft['address'] ?? '');
        $this->blood_type = (string) ($draft['blood_type'] ?? '');
        $this->emergency_contact_person = (string) ($draft['emergency_contact_person'] ?? '');
        $this->emergency_contact_number = (string) ($draft['emergency_contact_number'] ?? '');
    }

    protected function clearDraft(): void
    {
        session()->forget(self::DRAFT_SESSION_KEY);
    }
}
