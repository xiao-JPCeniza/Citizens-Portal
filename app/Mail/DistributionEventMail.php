<?php

namespace App\Mail;

use App\Models\Applicant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DistributionEventMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Applicant $applicant,
        public string $when,
        public string $where,
        public string $what,
        public string $posterPath,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Citizen ID Distribution Event',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.distribution-event',
            text: 'mail.distribution-event-text',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->posterPath)
                ->as('distribution-poster.jpg'),
        ];
    }
}
