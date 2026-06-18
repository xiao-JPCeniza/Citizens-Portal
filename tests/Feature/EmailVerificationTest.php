<?php

namespace Tests\Feature;

use App\Livewire\Public\EmailVerification;
use App\Mail\ApplicationEmailOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verify_email_page_requires_accepted_terms(): void
    {
        $this->get(route('verify-email'))
            ->assertRedirect(route('welcome'));

        $this->get(route('welcome'))
            ->assertSee('Please accept the Terms and Conditions before applying.');
    }

    public function test_verify_email_page_is_accessible_after_accepting_terms(): void
    {
        $this->withSession(['terms_accepted' => true])
            ->get(route('verify-email'))
            ->assertOk()
            ->assertSee('Verify Your Email')
            ->assertSee('Send Verification Code');
    }

    public function test_welcome_form_redirects_to_email_verification(): void
    {
        $this->get(route('welcome'))
            ->assertSee(route('verify-email', absolute: false));
    }

    public function test_user_can_verify_email_and_access_application_form(): void
    {
        Mail::fake();

        $this->withSession(['terms_accepted' => true]);

        Livewire::test(EmailVerification::class)
            ->set('email', 'applicant@example.com')
            ->call('sendOtp')
            ->assertSet('otpSent', true);

        Mail::assertSent(ApplicationEmailOtpMail::class, function (ApplicationEmailOtpMail $mail): bool {
            return $mail->hasTo('applicant@example.com');
        });

        $code = Mail::sent(ApplicationEmailOtpMail::class)->first()->code;

        Livewire::test(EmailVerification::class)
            ->set('email', 'applicant@example.com')
            ->set('otpSent', true)
            ->set('otp', $code)
            ->call('verifyOtp')
            ->assertRedirect(route('apply'));

        $this->withSession([
            'terms_accepted' => true,
            'application_verified_email' => 'applicant@example.com',
        ])
            ->get(route('apply'))
            ->assertOk()
            ->assertSee('applicant@example.com')
            ->assertSee('Verified via email OTP');
    }

    public function test_application_form_redirects_without_verified_email(): void
    {
        $this->withSession(['terms_accepted' => true])
            ->get(route('apply'))
            ->assertRedirect(route('verify-email'))
            ->assertSessionHas('error', 'Please verify your email address before applying.');
    }

    public function test_invalid_otp_is_rejected(): void
    {
        Mail::fake();

        $this->withSession(['terms_accepted' => true]);

        Livewire::test(EmailVerification::class)
            ->set('email', 'applicant@example.com')
            ->call('sendOtp')
            ->set('otpSent', true)
            ->set('otp', '000000')
            ->call('verifyOtp')
            ->assertHasErrors(['otp']);
    }
}
