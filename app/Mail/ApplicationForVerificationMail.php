<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationForVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Applicant $applicant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Citizen ID Application For Verification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.application-for-verification',
            text: 'mail.application-for-verification-text',
        );
    }
}
