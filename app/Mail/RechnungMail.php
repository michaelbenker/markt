<?php

namespace App\Mail;

use App\Models\Rechnung;
use App\Models\EmailTemplate;
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
    public $subject;
    public $htmlContent;

    public function __construct(Rechnung $rechnung)
    {
        $this->rechnung = $rechnung;

        // Template laden und rendern
        $template = EmailTemplate::getByKey('rechnung_versand');

        if ($template) {
            $variables = $this->prepareVariables();
            $rendered = $template->render($variables);

            $this->subject = $rendered['subject'];
            $this->htmlContent = $rendered['content'];
        } else {
            // Fallback auf ursprüngliches Template
            $this->subject = 'Rechnung ' . $this->rechnung->rechnungsnummer;
            $this->htmlContent = null; // Wird dann die Blade-View verwenden
        }
    }

    /**
     * Bereitet die Variablen für das Template vor
     */
    private function prepareVariables(): array
    {
        $ausstellerName = '';
        if ($this->rechnung->empf_firma) {
            $ausstellerName = $this->rechnung->empf_firma;
        } else {
            $ausstellerName = trim($this->rechnung->empf_vorname . ' ' . $this->rechnung->empf_name);
        }

        return [
            'aussteller_name' => $ausstellerName,
            'rechnung_nummer' => $this->rechnung->rechnungsnummer,
            'markt_name' => $this->rechnung->buchung->termin->markt->name ?? 'Unbekannt',
            'betrag' => number_format($this->rechnung->gesamtbetrag, 2, ',', '.') . ' €',
        ];
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }
    public function content(): Content
    {
        // Ansonsten Fallback auf Blade-View
        return new Content(
            markdown: 'emails.rechnung.versand',
            with: ['rechnung' => $this->rechnung]
        );
    }
    /**
     * Build the message.
     */
    public function build()
    {
        // Wenn HTML-Content vom Template vorhanden ist, verwende diesen im Layout
        if ($this->htmlContent) {
            return $this->subject($this->subject)
                ->view('emails.template-wrapper', [
                    'content' => $this->htmlContent,
                    'rechnung' => $this->rechnung
                ]);
        }

        // Ansonsten verwende die normale content() Methode
        return $this->subject($this->subject)
            ->markdown('emails.rechnung.versand', ['rechnung' => $this->rechnung]);
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
