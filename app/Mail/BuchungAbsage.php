<?php

namespace App\Mail;

use App\Models\Buchung;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BuchungAbsage extends Mailable
{
    use Queueable, SerializesModels;

    public $buchung;

    /**
     * Create a new message instance.
     */
    public function __construct(Buchung $buchung)
    {
        $this->buchung = $buchung;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = 'Absage für Ihre Buchung - ' . ($this->buchung->markt->name ?? 'Markt');

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
            view: 'emails.buchung.absage',
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
