<?php

namespace Tests\Feature;

use App\Mail\ApplicationEmailOtpMail;
use Illuminate\Mail\MailManager;
use Tests\TestCase;

class OtpMailLogoTest extends TestCase
{
    public function test_otp_email_uses_embedded_logo_not_external_url(): void
    {
        $html = (new ApplicationEmailOtpMail('123456'))->render();

        $this->assertStringNotContainsString(
            asset('images/branding/lupad-logo-white.png'),
            $html,
        );
        $this->assertStringContainsString('data:image/png;base64,', $html);
    }

    public function test_otp_email_sent_with_inline_logo_attachment(): void
    {
        /** @var MailManager $manager */
        $manager = $this->app->make(MailManager::class);
        $mailer = $manager->mailer('array');

        $mailer->to('applicant@example.com')->send(new ApplicationEmailOtpMail('123456'));

        $sent = $mailer->getSymfonyTransport()->messages()->first();
        $html = $sent->getOriginalMessage()->getHtmlBody();

        $this->assertIsString($html);
        $this->assertStringContainsString('cid:', $html);
        $this->assertStringNotContainsString(
            asset('images/branding/lupad-logo-white.png'),
            $html,
        );

        $hasInlineLogo = collect($sent->getOriginalMessage()->getAttachments())
            ->contains(fn ($attachment) => str_contains((string) $attachment->getContentType(), 'image/png'));

        $this->assertTrue($hasInlineLogo);
    }
}
