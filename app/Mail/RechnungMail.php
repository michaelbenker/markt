<?php

namespace App\Mail;

use App\Models\Rechnung;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf;

class RechnungMail extends Mailable
{
    use Queueable, SerializesModels;

    public Rechnung $rechnung;

    public function __construct(Rechnung $rechnung)
    {
        $this->rechnung = $rechnung;
    }

    public function envelope(): Envelope
    {
        $toEmail = app()->environment('production')
            ? $this->rechnung->empf_email
            : config('mail.dev_redirect_email');

        return new Envelope(
            subject: 'Rechnung ' . $this->rechnung->rechnungsnummer,
            to: $toEmail,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.rechnung.versand',
            with: ['rechnung' => $this->rechnung]
        );
    }

    public function attachments(): array
    {
        // PDF als Anhang generieren
        $pdf = Pdf::loadView('pdf.rechnung', ['rechnung' => $this->rechnung]);

        return [
            Attachment::fromData(
                fn() => $pdf->output(),
                'rechnung-' . $this->rechnung->rechnungsnummer . '.pdf'
            )->withMime('application/pdf'),
        ];
    }
}
