<?php

namespace Tests\Feature;

use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationForVerificationMail;
use App\Mail\ApplicationRejectedMail;
use App\Models\Applicant;
use Tests\TestCase;

class ApplicationStatusMailDesignTest extends TestCase
{
    public function test_approved_email_renders_branded_html_layout(): void
    {
        $applicant = new Applicant([
            'application_id' => '000101',
            'full_name' => 'JUAN DELA CRUZ',
            'email' => 'juan@example.com',
        ]);

        $html = (new ApplicationApprovedMail($applicant))->render();

        $this->assertStringContainsString('Citizen ID Application Portal', $html);
        $this->assertStringContainsString('Congratulations, JUAN DELA CRUZ!', $html);
        $this->assertStringContainsString('000101', $html);
        $this->assertStringContainsString('distribution event is scheduled', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
    }

    public function test_for_verification_email_renders_status(): void
    {
        $applicant = new Applicant([
            'application_id' => '000111',
            'full_name' => 'ANA REYES',
            'email' => 'ana@example.com',
        ]);

        $mail = new ApplicationForVerificationMail($applicant);
        $html = $mail->render();

        $this->assertSame('Citizen ID Application For Verification', $mail->envelope()->subject);
        $this->assertStringContainsString('For Verification', $html);
        $this->assertStringContainsString('000111', $html);
        $this->assertStringContainsString('undergoing final verification', $html);
    }

    public function test_rejected_email_renders_reason_remarks_and_edit_link(): void
    {
        $applicant = new Applicant([
            'application_id' => '000202',
            'full_name' => 'MARIA SANTOS',
            'email' => 'maria@example.com',
        ]);
        $applicant->id = 202;

        $editUrl = 'http://citizens-id.test/applications/000202/edit/'.str_repeat('a', 64);

        $html = (new ApplicationRejectedMail(
            $applicant,
            'Invalid Passport Photo',
            'Photo background is not white.',
            $editUrl,
        ))->render();

        $this->assertStringContainsString('Application Returned for Correction', $html);
        $this->assertStringNotContainsString('Application Not Approved', $html);
        $this->assertStringContainsString('000202', $html);
        $this->assertStringContainsString('Invalid Passport Photo', $html);
        $this->assertStringContainsString('Photo background is not white.', $html);
        $this->assertStringContainsString('Edit Application &amp; Submit Corrections', $html);
        $this->assertStringContainsString('/applications/000202/edit/', $html);
        $this->assertStringContainsString('does not expire', $html);
    }

    public function test_rejected_email_without_edit_link_shows_final_message(): void
    {
        $applicant = new Applicant([
            'application_id' => '000303',
            'full_name' => 'PEDRO CRUZ',
            'email' => 'pedro@example.com',
        ]);

        $html = (new ApplicationRejectedMail(
            $applicant,
            'Other',
            'Fraudulent documents submitted.',
            null,
        ))->render();

        $this->assertStringContainsString('Application Not Approved', $html);
        $this->assertStringContainsString('Other', $html);
        $this->assertStringContainsString('Fraudulent documents submitted.', $html);
        $this->assertStringContainsString('This decision is final for this application', $html);
        $this->assertStringNotContainsString('Edit Application', $html);
        $this->assertStringNotContainsString('/edit/', $html);
    }
}
