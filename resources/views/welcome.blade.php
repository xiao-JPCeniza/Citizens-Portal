@extends('layouts.public')

@section('title', 'Welcome')

@section('content')
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
            <div class="rounded-lg bg-white/10 px-4 py-2 text-sm text-gray-100 backdrop-blur-sm">
                <span class="font-medium">For residents only</span>
            </div>
        </div>
    </header>

    <main class="mx-auto max-w-5xl px-4 py-10 sm:px-6 sm:py-12">
        @if (session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                {{ session('error') }}
            </div>
        @endif

        {{-- Hero --}}
        <section class="mb-10 text-center">
            <p class="mb-2 text-sm font-semibold uppercase tracking-wide text-primary-700">Welcome</p>
            <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Apply for Your Citizen ID Online</h2>
            <p class="mx-auto mt-4 max-w-2xl text-base leading-relaxed text-gray-600">
                The Citizen ID Program provides official identification for residents of Manolo Fortich.
                Please review the information below before starting your application.
            </p>
        </section>

        {{-- Overview --}}
        <section class="mb-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary-100 text-primary-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                </span>
                <h3 class="text-xl font-semibold text-gray-900">Citizen ID Overview</h3>
            </div>
            <div class="flex flex-col items-center gap-8 lg:flex-row lg:items-start">
                <div class="flex-1">
                    <p class="leading-relaxed text-gray-600">
                        The Citizen ID is the official municipal identification for qualified residents of
                        Manolo Fortich, Bukidnon. More than proof of residency, it is a co-branded GCash-linked
                        Visa debit card for secure everyday payments.
                    </p>
                    <p class="mt-3 leading-relaxed text-gray-600">
                        Use this portal to apply online, upload the required documents, and receive email updates
                        as your request is processed.
                    </p>
                </div>
                <div class="w-full max-w-sm shrink-0 overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-sm lg:max-w-xs">
                    <img
                        src="{{ asset('storage/atm/' . rawurlencode('atm gcash.png')) }}"
                        alt="Citizen ID card featuring the Municipality of Manolo Fortich seal, GCash, and Visa branding"
                        class="h-auto w-full"
                        width="400"
                        height="252"
                    >
                </div>
            </div>
        </section>

        <div class="mb-8 grid gap-8 lg:grid-cols-2">
            {{-- Benefits --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-accent-100 text-accent-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043A3.745 3.745 0 0 1 12 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 0 1-3.296-1.043 3.745 3.745 0 0 1-1.043-3.296A3.745 3.745 0 0 1 3 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 0 1 1.043-3.296 3.746 3.746 0 0 1 3.296-1.043A3.746 3.746 0 0 1 12 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.296A3.745 3.745 0 0 1 21 12Z" />
                        </svg>
                    </span>
                    <h3 class="text-xl font-semibold text-gray-900">Benefits and Purpose</h3>
                </div>
                <ul class="space-y-3 text-gray-600">
                    <li class="flex gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500"></span>
                        <span>Serves as an official proof of residency within Manolo Fortich, Bukidnon.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500"></span>
                        <span>This will be your official ID as a resident of Manolo Fortich, Bukidnon.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500"></span>
                        <span>Can be used for payments and withdrawals with your linked GCash account.</span>
                    </li>
                    <li class="flex gap-3">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-accent-500"></span>
                        <span>Accepted for municipal programs, local transactions, and other GCash-integrated services.</span>
                    </li>
                </ul>
            </section>

            {{-- Requirements --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-8">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                    </span>
                    <h3 class="text-xl font-semibold text-gray-900">Application Requirements</h3>
                </div>
                <p class="mb-4 text-sm text-gray-500">You will need the following information and documents ready before applying:</p>
                <ul class="grid gap-2 sm:grid-cols-2">
                    @foreach ([
                        'Valid email address',
                        'Full name (as it appears on Gcash)',
                        'GCash mobile number',
                        'Barangay (Manolo Fortich only)',
                        'Complete home address',
                        'Blood type',
                        'Emergency contact person',
                        'Emergency contact number',
                        'Passport-size photo upload',
                        'GCash account screenshot',
                    ] as $requirement)
                        <li class="flex items-start gap-2 text-sm text-gray-600">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-accent-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            {{ $requirement }}
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>

        {{-- Sample uploads --}}
        <section class="mb-8">
            <h3 class="mb-6 text-center text-xl font-semibold text-gray-900">Document Samples &amp; Upload Guidelines</h3>
            <div class="grid gap-8 md:grid-cols-2">
                {{-- Passport photo --}}
                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                        <h4 class="font-semibold text-gray-900">Sample Passport Photo</h4>
                        <p class="mt-1 text-sm text-gray-500">Reference for your photo upload</p>
                    </div>
                    <div class="flex flex-col items-center gap-6 p-6 sm:flex-row sm:items-start">
                        <div class="w-36 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <img
                                src="{{ asset('storage/' . rawurlencode('example(last name)-name.jpg')) }}"
                                alt="Sample passport photo named Lastname-Firstname.jpg with white background and clearly visible face"
                                class="h-auto w-full"
                                width="144"
                                height="180"
                            >
                        </div>
                        <div class="flex-1">
                            <p class="mb-3 text-sm font-medium text-gray-700">Photo requirements:</p>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex gap-2"><span class="text-accent-600">•</span> White background</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Face clearly visible</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> No hat or head covering</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> No sunglasses</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Recent photo</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> JPG format</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Maximum file size: 5MB</li>
                            </ul>
                        </div>
                    </div>
                </article>

                {{-- GCash screenshot --}}
                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50 px-6 py-4">
                        <h4 class="font-semibold text-gray-900">Sample GCash Screenshot</h4>
                        <p class="mt-1 text-sm text-gray-500">Reference for your GCash upload</p>
                    </div>
                    <div class="flex flex-col items-center gap-6 p-6 sm:flex-row sm:items-start">
                        <div class="w-32 shrink-0 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                            <img
                                src="{{ asset('storage/Gemini_Generated_Image_qird33qird33qird.png') }}"
                                alt="Sample GCash profile screenshot showing account name and mobile number"
                                class="h-auto w-full"
                                width="128"
                                height="224"
                            >
                        </div>
                        <div class="flex-1">
                            <p class="mb-3 text-sm font-medium text-gray-700">Screenshot requirements:</p>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Clear screenshot of your GCash account</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Registered GCash number must match application</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> JPG or PNG format</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Maximum file size: 5MB</li>
                                <li class="flex gap-2"><span class="text-accent-600">•</span> Image must be readable and not cropped</li>
                            </ul>
                            <p class="mt-3 text-sm italic text-gray-500">* Make sure to click the eye button to view information.</p>
                        </div>
                    </div>
                </article>
            </div>
        </section>

        {{-- Terms and Conditions --}}
        <section class="mb-8 rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-6 py-4 sm:px-8">
                <h3 class="text-xl font-semibold text-gray-900">Terms and Conditions</h3>
                <p class="mt-1 text-sm text-gray-500">Please read carefully before proceeding with your application.</p>
            </div>
            <div class="max-h-64 overflow-y-auto px-6 py-5 text-sm leading-relaxed text-gray-600 sm:px-8">
                <ol class="list-decimal space-y-4 pl-5">
                    <li>
                        <strong class="text-gray-800">Eligibility.</strong>
                        This portal is exclusively for residents of Manolo Fortich, Bukidnon. Applicants must
                        provide accurate barangay and address information. Non-residents or applicants with
                        falsified information may be rejected.
                    </li>
                    <li>
                        <strong class="text-gray-800">Accuracy of Information.</strong>
                        All details submitted — including name, contact information, GCash number, and emergency
                        contacts — must be truthful and complete. The municipality reserves the right to reject
                        applications with incomplete or incorrect information.
                    </li>
                    <li>
                        <strong class="text-gray-800">Document Submission.</strong>
                        Uploaded passport photos and GCash screenshots must meet the specified requirements.
                        Blurred, edited, or invalid documents may result in rejection. You may reapply if your
                        application is rejected.
                    </li>
                    <li>
                        <strong class="text-gray-800">Verification Process.</strong>
                        Submitted applications are subject to review by authorized municipal administrators.
                        Applications are processed on a first-in, first-out basis. Status updates will be sent
                        to the email address you provide.
                    </li>
                    <li>
                        <strong class="text-gray-800">Data Privacy.</strong>
                        Personal information collected through this portal will be used solely for Citizen ID
                        application processing, verification, and related municipal services, in accordance
                        with applicable data privacy laws.
                    </li>
                    <li>
                        <strong class="text-gray-800">Approval and Rejection.</strong>
                        Approval of an application does not guarantee immediate issuance of a physical ID card.
                        Further instructions will be provided via email upon approval. Rejected applications will
                        include the reason for rejection when applicable.
                    </li>
                    <li>
                        <strong class="text-gray-800">Agreement.</strong>
                        By proceeding with your application, you confirm that you have read, understood, and
                        agree to comply with these terms and conditions.
                    </li>
                </ol>
            </div>
        </section>

        {{-- CTA --}}
        <section class="rounded-2xl border border-primary-200 bg-primary-50 p-6 sm:p-8">
            <form id="welcome-form" action="{{ route('verify-email') }}" method="get">
                <label class="flex cursor-pointer items-start gap-3">
                    <input
                        type="checkbox"
                        id="terms-checkbox"
                        name="terms_accepted"
                        value="1"
                        class="mt-1 h-5 w-5 shrink-0 rounded border-gray-300 text-primary-700 focus:ring-primary-600"
                    >
                    <span class="text-sm leading-relaxed text-gray-700">
                        I have read and agree to the
                        <button
                            type="button"
                            id="terms-link"
                            class="font-semibold text-primary-700 underline decoration-primary-700/40 underline-offset-2 hover:text-primary-800 hover:decoration-primary-800"
                        >
                            Terms and Conditions
                        </button>.
                    </span>
                </label>

                <div class="mt-6 flex flex-col items-stretch gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-500">
                        Only proceed if you meet all eligibility and document requirements.
                    </p>
                    <button
                        type="submit"
                        id="proceed-button"
                        disabled
                        class="inline-flex items-center justify-center gap-2 rounded-xl px-8 py-3 text-sm font-semibold shadow-sm transition focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-500 enabled:bg-primary-700 enabled:text-white enabled:hover:bg-primary-800"
                    >
                        Proceed Application
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </button>
                </div>
            </form>
        </section>
    </main>

    {{-- Terms and Conditions modal --}}
    <div
        id="terms-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        aria-labelledby="terms-modal-title"
        aria-hidden="true"
    >
        <div id="terms-modal-backdrop" class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
        <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl">
            <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4 sm:px-8">
                <h3 id="terms-modal-title" class="text-lg font-semibold text-gray-900">Terms and Conditions</h3>
                <button
                    type="button"
                    id="terms-modal-close"
                    class="rounded-lg p-1 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-primary-600"
                    aria-label="Close"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="max-h-[70vh] overflow-y-auto px-6 py-5 sm:px-8">
                <p class="mb-3 text-sm font-semibold text-primary-800">Privacy Notice</p>
                <p class="text-sm leading-relaxed text-gray-600">
                    By applying for the GCash Netizens ID, you hereby authorize the Local Government Unit (LGU) of
                    Manolo Fortich and its authorized partners (including GCash/G-Xchange, Inc.) to collect, process,
                    and store your personal information in accordance with Republic Act No. 10173, also known as the
                    Data Privacy Act of 2012. Your data—including but not limited to your full name, address, and
                    biometric information—will be used solely for identity verification against municipal records,
                    card personalization, and the delivery of government and financial services. We are committed to
                    protecting your privacy and will not share your information with unauthorized third parties without
                    your explicit consent, except as required by law or for the fulfillment of the services integrated
                    into this ID system.
                </p>
            </div>
            <div class="border-t border-gray-100 px-6 py-4 sm:px-8">
                <button
                    type="button"
                    id="terms-modal-acknowledge"
                    class="w-full rounded-xl bg-primary-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-800 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 sm:w-auto"
                >
                    I Understand
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkbox = document.getElementById('terms-checkbox');
            const button = document.getElementById('proceed-button');
            const termsLink = document.getElementById('terms-link');
            const modal = document.getElementById('terms-modal');
            const backdrop = document.getElementById('terms-modal-backdrop');
            const closeBtn = document.getElementById('terms-modal-close');
            const acknowledgeBtn = document.getElementById('terms-modal-acknowledge');

            checkbox.addEventListener('change', () => {
                button.disabled = !checkbox.checked;
            });

            const openModal = () => {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
            };

            const closeModal = () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('overflow-hidden');
            };

            termsLink.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                openModal();
            });

            termsLink.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            closeBtn.addEventListener('click', closeModal);
            acknowledgeBtn.addEventListener('click', closeModal);
            backdrop.addEventListener('click', closeModal);

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
        });
    </script>
@endsection
