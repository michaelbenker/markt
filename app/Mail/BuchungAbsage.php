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

        // Im Testmodus Subject erweitern
        if (config('mail.dev_redirect_email')) {
            $subject = '[TEST für ' . $this->buchung->aussteller->email . '] ' . $subject;
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

    /**
     * Get the message's to address.
     */
    public function to($address, $name = null)
    {
        // Im Testmodus alle E-Mails an MAIL_DEV_REDIRECT_EMAIL umleiten
        if (config('mail.dev_redirect_email')) {
            return parent::to(config('mail.dev_redirect_email'), 'Test Recipient');
        }

        return parent::to($address, $name);
    }
}
