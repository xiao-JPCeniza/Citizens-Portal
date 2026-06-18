<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Applicant $applicant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Citizen ID Application Approved',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.application-approved',
            text: 'mail.application-approved-text',
        );
    }
}
