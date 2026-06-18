<?php

namespace Tests\Feature;

use App\Mail\ApplicationApprovedMail;
use App\Mail\ApplicationRejectedMail;
use App\Models\Applicant;
use Tests\TestCase;

class ApplicationStatusMailDesignTest extends TestCase
{
    public function test_approved_email_renders_branded_html_layout(): void
    {
        $applicant = new Applicant([
            'full_name' => 'JUAN DELA CRUZ',
            'email' => 'juan@example.com',
        ]);

        $html = (new ApplicationApprovedMail($applicant))->render();

        $this->assertStringContainsString('Citizen ID Application Portal', $html);
        $this->assertStringContainsString('Congratulations, JUAN DELA CRUZ!', $html);
        $this->assertStringContainsString('distribution event is scheduled', $html);
        $this->assertStringContainsString('data:image/png;base64,', $html);
    }

    public function test_rejected_email_renders_reason_and_remarks(): void
    {
        $applicant = new Applicant([
            'full_name' => 'MARIA SANTOS',
            'email' => 'maria@example.com',
        ]);

        $html = (new ApplicationRejectedMail(
            $applicant,
            'Invalid Passport Photo',
            'Photo background is not white.',
        ))->render();

        $this->assertStringContainsString('Application Not Approved', $html);
        $this->assertStringContainsString('Invalid Passport Photo', $html);
        $this->assertStringContainsString('Photo background is not white.', $html);
        $this->assertStringContainsString('submit a new application', $html);
    }
}
