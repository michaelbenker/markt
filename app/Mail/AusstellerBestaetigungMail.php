<?php

namespace App\Mail;

use App\Models\Aussteller;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AusstellerBestaetigungMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Aussteller $aussteller
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Test-E-Mail Markt-App',
        );
    }

    public function content(): Content
    {
        return new Content(
            text: 'Dies ist eine Test-E-Mail von der Markt-App.',
        );
    }
}
