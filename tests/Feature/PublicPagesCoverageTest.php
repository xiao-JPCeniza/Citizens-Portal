<?php

namespace Tests\Feature;

use App\Livewire\Public\EmailVerification;
use App\Mail\ApplicationEmailOtpMail;
use App\Support\ManoloFortich;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class PublicPagesCoverageTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_displays_all_key_sections(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('Apply for Your Citizen ID Online')
            ->assertSee('Citizen ID Overview')
            ->assertSee('Benefits and Purpose')
            ->assertSee('Application Requirements')
            ->assertSee('Terms and Conditions')
            ->assertSee('Proceed Application')
            ->assertSee(ManoloFortich::SUPPORT_PHONE)
            ->assertSee(ManoloFortich::SUPPORT_EMAIL);
    }

    public function test_welcome_page_contains_terms_form_and_proceed_button(): void
    {
        $this->get(route('welcome'))
            ->assertOk()
            ->assertSee('id="terms-checkbox"', false)
            ->assertSee('id="proceed-button"', false)
            ->assertSee('id="terms-link"', false)
            ->assertSee('id="terms-modal"', false)
            ->assertSee(route('verify-email', absolute: false));
    }

    public function test_accepting_terms_via_query_param_grants_access_to_verify_email(): void
    {
        $this->get(route('verify-email', ['terms_accepted' => 1]))
            ->assertOk()
            ->assertSee('Verify Your Email')
            ->assertSee('Send Verification Code')
            ->assertSee('Back to Welcome');
    }

    public function test_verify_email_page_shows_otp_form_after_sending_code(): void
    {
        Mail::fake();

        $this->withSession(['terms_accepted' => true]);

        Livewire::test(EmailVerification::class)
            ->set('email', 'applicant@example.com')
            ->call('sendOtp')
            ->assertSet('otpSent', true)
            ->assertSee('Use a different email')
            ->assertSee('Verify & Continue')
            ->assertSee('Resend Code');
    }

    public function test_user_can_change_email_after_otp_sent(): void
    {
        Mail::fake();

        $this->withSession(['terms_accepted' => true]);

        Livewire::test(EmailVerification::class)
            ->set('email', 'first@example.com')
            ->call('sendOtp')
            ->assertSet('otpSent', true)
            ->call('changeEmail')
            ->assertSet('otpSent', false)
            ->assertSet('otp', '');
    }

    public function test_user_can_resend_otp_code(): void
    {
        Mail::fake();

        $this->withSession(['terms_accepted' => true]);

        Livewire::test(EmailVerification::class)
            ->set('email', 'applicant@example.com')
            ->call('sendOtp')
            ->call('sendOtp');

        Mail::assertSent(ApplicationEmailOtpMail::class, 2);
    }

    public function test_verify_email_requires_valid_email_before_sending_otp(): void
    {
        Mail::fake();

        $this->withSession(['terms_accepted' => true]);

        Livewire::test(EmailVerification::class)
            ->set('email', 'not-an-email')
            ->call('sendOtp')
            ->assertHasErrors(['email']);

        Mail::assertNothingSent();
    }

    public function test_verify_email_back_to_welcome_link_works(): void
    {
        $this->withSession(['terms_accepted' => true])
            ->get(route('verify-email'))
            ->assertSee(route('welcome', absolute: false));

        $this->get(route('welcome'))
            ->assertOk();
    }

    public function test_admin_login_back_to_public_portal_link_works(): void
    {
        $this->get(route('admin.login'))
            ->assertSee('Back to Public Portal')
            ->assertSee(route('welcome', absolute: false));

        $this->get(route('welcome'))
            ->assertOk();
    }

    public function test_health_check_endpoint_is_accessible(): void
    {
        $this->get('/up')
            ->assertOk();
    }
}
