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
    public $templateResult;

    public function __construct(Rechnung $rechnung)
    {
        $this->rechnung = $rechnung;

        // Template Service mit Fallback verwenden
        $service = new \App\Services\EmailTemplateService();
        $variables = $this->prepareVariables();

        try {
            $this->templateResult = $service->renderTemplate('rechnung_versand', $variables);
            $this->subject = $this->templateResult['subject'];
            $this->htmlContent = $this->templateResult['content'];

            // Debug: Logge welche Template-Quelle verwendet wird
            \Illuminate\Support\Facades\Log::info('RechnungMail Template Source: ' . $this->templateResult['source']);
        } catch (\Exception $e) {
            throw new \Exception('E-Mail-Template "rechnung_versand" konnte nicht geladen werden: ' . $e->getMessage());
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
        // Debug-Output direkt in die Konsole
        error_log('RechnungMail content() wurde aufgerufen!');
        \Illuminate\Support\Facades\Log::debug('RechnungMail content() aufgerufen - DEBUG');
        \Illuminate\Support\Facades\Log::debug('Template Source: ' . $this->templateResult['source']);

        return new Content(
            markdown: 'emails.template-wrapper',
            with: [
                'content' => $this->htmlContent, // HTML-Content aus Datenbank
                'rechnung' => $this->rechnung
            ]
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
