<?php

namespace App\Mail;

use App\Models\Anfrage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AusstellerAbsage extends Mailable
{
    use Queueable, SerializesModels;

    public $anfrage;

    /**
     * Create a new message instance.
     */
    public function __construct(Anfrage $anfrage)
    {
        $this->anfrage = $anfrage;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Absage für Ihre Standanfrage - ' . ($this->anfrage->markt->name ?? 'Markt');

        // Im Testmodus Subject erweitern
        if (config('mail.dev_redirect_email')) {
            $subject = '[TEST für ' . $this->anfrage->email . '] ' . $subject;
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.aussteller.absage',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
