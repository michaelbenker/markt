<?php

namespace App\Mail;

use App\Models\Anfrage;
use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AusstellerAbsage extends Mailable
{
    use Queueable, SerializesModels;

    public $anfrage;
    public $subject;
    public $htmlContent;

    /**
     * Create a new message instance.
     */
    public function __construct(Anfrage $anfrage)
    {
        $this->anfrage = $anfrage;

        // Template aus Datenbank laden
        $template = EmailTemplate::getByKey('aussteller_absage');

        if (!$template || !$template->is_active) {
            throw new \Exception('E-Mail-Template "aussteller_absage" ist nicht verfügbar');
        }

        // Variablen vorbereiten und Template rendern
        $variables = $this->prepareVariables();
        $rendered = $template->render($variables);

        $this->subject = $rendered['subject'];
        $this->htmlContent = $rendered['content'];
    }

    /**
     * Bereitet die Variablen für das Template vor
     */
    private function prepareVariables(): array
    {
        $name = '';
        if ($this->anfrage->firma) {
            $name = $this->anfrage->firma;
        } else {
            $name = trim($this->anfrage->vorname . ' ' . $this->anfrage->name);
        }

        return [
            'aussteller_name' => $name,
            'markt_name' => $this->anfrage->markt->name ?? 'Unbekannt',
            'eingereicht_am' => $this->anfrage->created_at ? $this->anfrage->created_at->format('d.m.Y H:i') : '',
            'firma' => $this->anfrage->firma ?? '',
            'warenangebot' => is_array($this->anfrage->warenangebot)
                ? implode(', ', $this->anfrage->warenangebot)
                : $this->anfrage->warenangebot ?? '',
        ];
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->subject;

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
        // Verwende generischen Template-Wrapper für Datenbank-Templates
        return new Content(
            markdown: 'emails.template-wrapper',
            with: [
                'content' => $this->htmlContent,
                'anfrage' => $this->anfrage
            ]
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
