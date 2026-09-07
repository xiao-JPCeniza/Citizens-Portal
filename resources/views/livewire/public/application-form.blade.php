<div>
    {{-- Header --}}
    <header class="mf-header">
        <div class="mx-auto flex max-w-5xl flex-col gap-4 px-4 py-6 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div class="flex items-center gap-4">
                <x-brand-logo variant="color" class="h-11 w-auto sm:h-12" />
                <div>
                    <p class="mf-header-subtitle text-xs font-medium uppercase tracking-wider">Municipality of Manolo Fortich</p>

                    <p class="mf-header-subtitle mt-0.5 text-sm">Province of Bukidnon, Philippines</p>
                </div>
            </div>
            <a href="{{ route('welcome') }}" class="rounded-lg bg-white/10 px-4 py-2 text-sm font-medium text-gray-100 backdrop-blur-sm transition hover:bg-white/20">
                &larr; Back to Welcome
            </a>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-12">
        @if ($submitted)
            <section class="rounded-2xl border border-accent-200 bg-accent-50 p-8 text-center shadow-sm">
                <x-brand-logo variant="color" class="mx-auto mb-4 h-14 w-auto sm:h-16" />
                <h2 class="text-2xl font-bold text-gray-900">Application Submitted Successfully</h2>
                <p class="mx-auto mt-3 max-w-lg text-gray-600">
                    Thank you for submitting your Citizen ID application. Your application is currently under verification.
                    A confirmation email has been sent to your email address.
                </p>
                <p class="mx-auto mt-6 max-w-lg text-sm text-gray-500">
                    Please wait for another email regarding the result of your application.
                </p>
            </section>
        @else
            <section class="mb-8 text-center">
                <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary-700">Step 3 of 3</p>
                <h2 class="text-3xl font-bold tracking-tight text-gray-900">Citizen Application Form</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-gray-600">
                    @if ($formStep === 1)
                        Complete your personal details below. You can continue to document uploads next, and return anytime before submitting.
                    @else
                        Upload your required documents to finish your application. You can go back to edit your details if needed.
                    @endif
                </p>
                <p class="mx-auto mt-3 text-sm font-medium text-gray-500">
                    Form page {{ $formStep }} of 2
                </p>
            </section>

            <form wire:submit="submit" class="space-y-8">
                @if ($formStep === 1)
                    {{-- Personal Information --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900">Personal Information</h3>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Email Address <span class="text-red-500">*</span></label>
                                <input wire:model="email" type="email" id="email" autocomplete="email" readonly
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 shadow-sm @error('email') border-red-400 @enderror">
                                <p class="mt-1 text-xs text-gray-500">Verified via email OTP. This address cannot be changed on this form.</p>
                                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div
                                class="sm:col-span-2 grid gap-6 sm:grid-cols-2"
                                x-data="{
                                    preview: @js($this->fullName),
                                    updatePreview() {
                                        const first = (this.$refs.firstName?.value || '').trim().toUpperCase();
                                        const middle = (this.$refs.middleName?.value || '').trim().toUpperCase();
                                        const last = (this.$refs.lastName?.value || '').trim().toUpperCase();
                                        let middleInitial = '';
                                        if (middle) {
                                            const letter = middle.replace(/^\./, '').charAt(0);
                                            if (letter) {
                                                middleInitial = letter.toUpperCase() + '.';
                                            }
                                        }
                                        this.preview = [first, middleInitial, last].filter(Boolean).join(' ');
                                    },
                                    uppercaseInput(event) {
                                        event.target.value = event.target.value.toUpperCase();
                                        this.updatePreview();
                                    },
                                }"
                                x-init="updatePreview()"
                            >
                                <div class="sm:col-span-2">
                                    <label for="full_name" class="mb-1.5 block text-sm font-medium text-gray-700">Full Name</label>
                                    <input type="text" id="full_name" readonly
                                        :value="preview"
                                        class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm uppercase tracking-wide text-gray-700 shadow-sm">
                                </div>

                                <div>
                                    <label for="first_name" class="mb-1.5 block text-sm font-medium text-gray-700">First Name <span class="text-red-500">*</span></label>
                                    <input wire:model="first_name" type="text" id="first_name" x-ref="firstName" autocomplete="given-name"
                                        @input="uppercaseInput($event)"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm uppercase shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('first_name') border-red-400 @enderror"
                                        placeholder="JUAN">
                                    @error('first_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="middle_name" class="mb-1.5 block text-sm font-medium text-gray-700">Middle Name <span class="text-gray-400">(optional)</span></label>
                                    <input wire:model="middle_name" type="text" id="middle_name" x-ref="middleName" autocomplete="additional-name"
                                        @input="uppercaseInput($event)"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm uppercase shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('middle_name') border-red-400 @enderror"
                                        placeholder="PONCERO">
                                    @error('middle_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label for="last_name" class="mb-1.5 block text-sm font-medium text-gray-700">Last Name <span class="text-red-500">*</span></label>
                                    <input wire:model="last_name" type="text" id="last_name" x-ref="lastName" autocomplete="family-name"
                                        @input="uppercaseInput($event)"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm uppercase shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('last_name') border-red-400 @enderror"
                                        placeholder="DELA CRUZ">
                                    @error('last_name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label for="birthday" class="mb-1.5 block text-sm font-medium text-gray-700">Birthday <span class="text-red-500">*</span></label>
                                    <input wire:model="birthday" type="date" id="birthday" autocomplete="bday"
                                        max="{{ now()->toDateString() }}"
                                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('birthday') border-red-400 @enderror">
                                    @error('birthday') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label for="gcash_number" class="mb-1.5 block text-sm font-medium text-gray-700">GCash Number <span class="text-red-500">*</span></label>
                                <input wire:model="gcash_number" type="text" id="gcash_number" inputmode="numeric"
                                    maxlength="{{ $phoneNumberLength }}" pattern="09[0-9]{9}" autocomplete="tel"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('gcash_number') border-red-400 @enderror"
                                    placeholder="09123456789">
                                @error('gcash_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="blood_type" class="mb-1.5 block text-sm font-medium text-gray-700">Blood Type <span class="text-red-500">*</span></label>
                                <select wire:model="blood_type" id="blood_type"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('blood_type') border-red-400 @enderror">
                                    <option value="">Select blood type</option>
                                    @foreach ($bloodTypes as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                                @error('blood_type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    {{-- Address --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900">Address</h3>
                        <div
                            class="grid gap-6 sm:grid-cols-2"
                            x-data="{
                                preview: @js($this->completeAddress),
                                locationSuffix: @js($addressLocationSuffix),
                                maxLength: {{ $addressMaxLength }},
                                updateAddressPreview() {
                                    const parts = [
                                        (this.$refs.addressInput?.value || '').trim(),
                                        ($wire.barangay || '').trim(),
                                    ].filter(Boolean);

                                    let built = parts.join(', ');

                                    if (!built.toLowerCase().includes('manolo fortich')) {
                                        built = built ? `${built}, ${this.locationSuffix}` : this.locationSuffix;
                                    }

                                    this.preview = built;
                                },
                            }"
                            x-init="updateAddressPreview(); $watch('$wire.barangay', () => updateAddressPreview())"
                        >
                            <div>
                                <label for="province" class="mb-1.5 block text-sm font-medium text-gray-700">Municipality</label>
                                <input type="text" id="province" value="{{ $province }}" readonly
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-600">
                                <p class="mt-1 text-xs text-gray-500">Only residents of Manolo Fortich may apply.</p>
                            </div>

                            <div>
                                <label for="barangay" class="mb-1.5 block text-sm font-medium text-gray-700">Barangay <span class="text-red-500">*</span></label>
                                <select wire:model="barangay" id="barangay" @change="updateAddressPreview()"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('barangay') border-red-400 @enderror">
                                    <option value="">Select barangay</option>
                                    @foreach ($barangays as $brgy)
                                        <option value="{{ $brgy }}">{{ $brgy }}</option>
                                    @endforeach
                                </select>
                                @error('barangay') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="address" class="mb-1.5 block text-sm font-medium text-gray-700">Complete Address <span class="text-red-500">*</span></label>
                                <textarea wire:model="address" id="address" rows="3" x-ref="addressInput"
                                    @input="updateAddressPreview()"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('address') border-red-400 @enderror"
                                    placeholder="House No., Street, Purok/Sitio"></textarea>
                                @error('address') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="complete_address_preview" class="mb-1.5 block text-sm font-medium text-gray-700">Formatted Address Preview</label>
                                <input type="text" id="complete_address_preview" readonly
                                    :value="preview"
                                    class="w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-700 shadow-sm @error('address') border-red-400 @enderror">
                                <p class="mt-1 text-xs"
                                    :class="preview.length > maxLength ? 'text-red-600' : 'text-gray-500'">
                                    <span x-text="preview.length"></span>/{{ $addressMaxLength }} characters maximum (including barangay and {{ $addressLocationSuffix }})
                                </p>
                            </div>
                        </div>
                    </section>

                    {{-- Emergency Contact --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900">Emergency Contact</h3>
                        <div class="grid gap-6 sm:grid-cols-2">
                            <div>
                                <label for="emergency_contact_person" class="mb-1.5 block text-sm font-medium text-gray-700">Emergency Contact Person <span class="text-red-500">*</span></label>
                                <input wire:model="emergency_contact_person" type="text" id="emergency_contact_person"
                                    maxlength="{{ $emergencyContactPersonMaxLength }}"
                                    pattern="[A-Za-zÑñ][A-Za-zÑñ\s.'\-]*"
                                    inputmode="text"
                                    autocomplete="name"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('emergency_contact_person') border-red-400 @enderror"
                                    placeholder="Contact person name">

                                @error('emergency_contact_person') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="emergency_contact_number" class="mb-1.5 block text-sm font-medium text-gray-700">Emergency Contact Number <span class="text-red-500">*</span></label>
                                <input wire:model="emergency_contact_number" type="text" id="emergency_contact_number" inputmode="numeric"
                                    maxlength="{{ $phoneNumberLength }}" pattern="09[0-9]{9}"
                                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500/20 @error('emergency_contact_number') border-red-400 @enderror"
                                    placeholder="09123456789">
                                @error('emergency_contact_number') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <div class="flex flex-col items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500">
                            Your details are kept until you submit or your session ends. You can return later to finish document uploads.
                        </p>
                        <button type="button"
                            wire:click="next"
                            wire:loading.attr="disabled"
                            wire:target="next"
                            class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-700 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                            <span wire:loading.remove wire:target="next">Next</span>
                            <span wire:loading wire:target="next">Saving...</span>
                            <svg wire:loading.remove wire:target="next" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>
                    </div>
                @else
                    {{-- Document Uploads --}}
                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                        <h3 class="mb-6 text-lg font-semibold text-gray-900">Document Uploads</h3>
                        <div class="space-y-8">
                            {{-- Passport Photo --}}
                            <div>
                                <div class="mb-5 flex items-start gap-4 sm:gap-5">
                                    <div class="w-32 max-w-[8rem] shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm sm:w-36 sm:max-w-[9rem]">
                                        <img src="{{ asset('storage/' . rawurlencode('example(last name)-name.jpg')) }}"
                                            alt="Sample passport photo named Lastname-Firstname.jpg"
                                            width="144"
                                            height="144"
                                            class="h-auto w-full object-cover">
                                    </div>
                                    <div class="min-w-0">
                                        <h4 class="text-base font-semibold text-gray-900">Passport Photo <span class="text-red-500">*</span></h4>
                                        <ul class="mt-2 space-y-1.5 text-sm leading-relaxed text-gray-600">
                                            <li>White background, face clearly visible</li>
                                            <li>No hat or sunglasses</li>
                                            <li>Must be exactly 1200 x 1200 pixels</li>
                                            <li>JPG or JPEG format, max 5MB</li>
                                        </ul>
                                    </div>
                                </div>
                                <input wire:model="passport_photo" type="file" id="passport_photo" accept="image/jpeg,.jpg,.jpeg"
                                    class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                                <x-document-upload-preview
                                    :file="$passport_photo"
                                    target="passport_photo"
                                    label="Passport photo preview"
                                />
                                @error('passport_photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            {{-- GCash gate --}}
                            <div class="rounded-xl border border-gray-200 bg-gray-50 p-5">
                                <fieldset>
                                    <legend class="text-base font-medium text-gray-900">
                                        Do you have a GCash? <span class="font-normal text-gray-600">(May Gcash ka?)</span>
                                        <span class="text-red-500">*</span>
                                    </legend>
                                    <div class="mt-4 flex flex-wrap gap-3">
                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition {{ $has_gcash === 'yes' ? 'border-primary-600 bg-primary-50 text-primary-800' : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400' }}">
                                            <input type="radio" wire:model.live="has_gcash" value="yes" class="text-primary-700 focus:ring-primary-600">
                                            Yes
                                        </label>
                                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition {{ $has_gcash === 'no' ? 'border-primary-600 bg-primary-50 text-primary-800' : 'border-gray-300 bg-white text-gray-700 hover:border-gray-400' }}">
                                            <input type="radio" wire:model.live="has_gcash" value="no" class="text-primary-700 focus:ring-primary-600">
                                            No
                                        </label>
                                    </div>
                                </fieldset>
                                @error('has_gcash') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                            </div>

                            @if ($has_gcash === 'yes')
                                {{-- GCash Screenshot --}}
                                <div>
                                    <div class="mb-5 flex items-start gap-4 sm:gap-5">
                                        <div class="w-28 max-w-[7rem] shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm sm:w-32 sm:max-w-[8rem]">
                                            <img src="{{ asset('storage/Gemini_Generated_Image_qird33qird33qird.png') }}"
                                                alt="Sample GCash profile screenshot"
                                                width="128"
                                                height="280"
                                                class="mx-auto h-auto max-h-56 w-full object-contain object-top sm:max-h-64">
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="text-base font-semibold text-gray-900">GCash Screenshot <span class="text-red-500">*</span></h4>
                                            <ul class="mt-2 space-y-1.5 text-sm leading-relaxed text-gray-600">
                                                <li>Clear screenshot of your GCash account</li>
                                                <li>Number must match application</li>
                                                <li>JPG or JPEG format, max 5MB</li>
                                                <li><span class="text-red-500">*</span> Make sure to click the eye button to view information.</li>
                                            </ul>
                                        </div>
                                    </div>
                                    <input wire:model="gcash_screenshot" type="file" id="gcash_screenshot" accept="image/jpeg,.jpg,.jpeg"
                                        class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                                    <x-document-upload-preview
                                        :file="$gcash_screenshot"
                                        target="gcash_screenshot"
                                        label="GCash screenshot preview"
                                    />
                                    @error('gcash_screenshot') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>
                            @elseif ($has_gcash === 'no')
                                <div class="overflow-hidden rounded-xl border border-amber-200 bg-amber-50">
                                    <div class="border-b border-amber-200/80 px-4 py-4 sm:px-5">
                                        <h4 class="text-base font-semibold text-gray-900 sm:text-lg">Get GCash to continue</h4>
                                        <p class="mt-1.5 text-sm leading-relaxed text-gray-700">
                                            You need a verified GCash account before you can submit. Download the app, finish setup, then return here and select <span class="font-medium text-gray-900">Yes</span> to upload your screenshot.
                                        </p>
                                    </div>

                                    <div class="grid gap-5 p-4 sm:gap-6 sm:p-5 md:grid-cols-[auto_1fr] md:items-center">
                                        {{-- QR: phone-sized, centered on small screens --}}
                                        <div class="flex justify-center md:justify-start">
                                            <a href="https://gcash.onelink.me/YA3x/jr0jhjhc"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="block w-36 shrink-0 rounded-lg border border-gray-200 bg-white p-2 shadow-sm transition hover:border-primary-300 hover:shadow focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 sm:w-40">
                                                <img src="{{ asset('storage/' . rawurlencode('Gcash Link Qr.png')) }}"
                                                    alt="Scan to download or open GCash"
                                                    width="144"
                                                    height="144"
                                                    class="aspect-square h-auto w-full object-contain">
                                            </a>
                                        </div>

                                        {{-- Actions --}}
                                        <div class="min-w-0 space-y-3 text-center md:text-left">
                                            <p class="text-xs font-medium uppercase tracking-wide text-amber-900/80 md:text-sm md:normal-case md:tracking-normal md:text-gray-600">
                                                <span class="md:hidden">Scan the QR or tap below</span>
                                                <span class="hidden md:inline">Scan the QR code on your phone, or open the download link:</span>
                                            </p>

                                            <a href="https://gcash.onelink.me/YA3x/jr0jhjhc"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex w-full max-w-sm items-center justify-center gap-2 rounded-lg bg-primary-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 md:w-auto md:max-w-none md:py-2.5">
                                                Open GCash download link
                                                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                            </a>

                                            <a href="https://gcash.onelink.me/YA3x/jr0jhjhc"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="mx-auto block max-w-sm break-all text-xs text-primary-700 underline underline-offset-2 hover:text-primary-800 md:mx-0">
                                                https://gcash.onelink.me/YA3x/jr0jhjhc
                                            </a>

                                            <p class="text-xs leading-relaxed text-gray-600">
                                                Your details on the previous page are kept until you submit or your session ends.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="border-t border-amber-200/80 bg-amber-100/60 px-4 py-3 sm:px-5">
                                        <p class="text-sm font-medium text-amber-950">
                                            You cannot submit yet. When your GCash is ready, select <span class="font-semibold">Yes</span> above and upload your screenshot.
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </section>

                    <div class="flex flex-col items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <button type="button"
                            wire:click="back"
                            wire:loading.attr="disabled"
                            wire:target="back"
                            class="inline-flex items-center justify-center gap-2 rounded-xl border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Back
                        </button>
                        <div class="flex flex-col items-stretch gap-3 sm:items-end">
                            <p class="text-sm text-gray-500 sm:text-right">
                                By submitting, you confirm that all information provided is accurate and complete.
                            </p>
                            <button type="submit"
                                wire:loading.attr="disabled"
                                wire:target="submit,passport_photo,gcash_screenshot"
                                @disabled($has_gcash !== 'yes')
                                class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-700 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                                <span wire:loading.remove wire:target="submit">Submit Application</span>
                                <span wire:loading wire:target="submit">Submitting...</span>
                                <svg wire:loading.remove wire:target="submit" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif
            </form>
        @endif
    </main>
</div>
