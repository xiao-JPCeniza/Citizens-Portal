<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Applicant $applicant,
        public string $reason,
        public ?string $remarks = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Citizen ID Application Rejected',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.application-rejected',
            text: 'mail.application-rejected-text',
        );
    }
}
