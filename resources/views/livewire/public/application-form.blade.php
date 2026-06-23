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
                    Complete all required fields below. Only residents of Manolo Fortich, Bukidnon may apply.
                </p>
            </section>

            <form wire:submit="submit" class="space-y-8">
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

                {{-- Document Uploads --}}
                <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                    <h3 class="mb-6 text-lg font-semibold text-gray-900">Document Uploads</h3>
                    <div class="grid gap-8 lg:grid-cols-2">
                        {{-- Passport Photo --}}
                        <div>
                            <div class="mb-4 flex items-start gap-4">
                                <div class="w-24 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <img src="{{ asset('storage/' . rawurlencode('example(last name)-name.jpg')) }}" alt="Sample passport photo named Lastname-Firstname.jpg" class="h-auto w-full">
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Passport Photo <span class="text-red-500">*</span></h4>
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        <li>White background, face clearly visible</li>
                                        <li>No hat or sunglasses</li>
                                        <li>JPG format, max 5MB</li>
                                    </ul>
                                </div>
                            </div>
                            <input wire:model="passport_photo" type="file" id="passport_photo" accept="image/jpeg,.jpg,.jpeg"
                                class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                            <div wire:loading wire:target="passport_photo" class="mt-2 text-sm text-gray-500">Uploading...</div>
                            @if ($passport_photo)
                                <p class="mt-2 text-sm text-primary-700">Selected: {{ $passport_photo->getClientOriginalName() }}</p>
                            @endif
                            @error('passport_photo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        {{-- GCash Screenshot --}}
                        <div>
                            <div class="mb-4 flex items-start gap-4">
                                <div class="w-20 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                                    <img src="{{ asset('storage/Gemini_Generated_Image_qird33qird33qird.png') }}" alt="Sample GCash profile screenshot" class="h-auto w-full">
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">GCash Screenshot <span class="text-red-500">*</span></h4>
                                    <ul class="mt-2 space-y-1 text-xs text-gray-600">
                                        <li>Clear screenshot of your GCash account</li>
                                        <li>Number must match application</li>
                                        <li>JPG or PNG, max 5MB</li>
                                        <li><span class="text-red-500"></span>*</span> Make sure to click the eye button to view information.</li>
                                    </ul>
                                </div>
                            </div>
                            <input wire:model="gcash_screenshot" type="file" id="gcash_screenshot" accept="image/jpeg,image/png"
                                class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-primary-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-primary-700 hover:file:bg-primary-100">
                            <div wire:loading wire:target="gcash_screenshot" class="mt-2 text-sm text-gray-500">Uploading...</div>
                            @if ($gcash_screenshot)
                                <p class="mt-2 text-sm text-primary-700">Selected: {{ $gcash_screenshot->getClientOriginalName() }}</p>
                            @endif
                            @error('gcash_screenshot') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </section>

                <div class="flex flex-col items-stretch gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500">
                        By submitting, you confirm that all information provided is accurate and complete.
                    </p>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:target="submit,passport_photo,gcash_screenshot"
                        class="inline-flex items-center justify-center gap-2 rounded-xl bg-primary-700 px-8 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60">
                        <span wire:loading.remove wire:target="submit">Submit Application</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                        <svg wire:loading.remove wire:target="submit" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
                        </svg>
                    </button>
                </div>
            </form>
        @endif
    </main>
</div>
