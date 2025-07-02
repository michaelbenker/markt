<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;
use App\Models\BuchungProtokoll;
use Illuminate\Support\Facades\Auth;

class AusstellerBestaetigung extends Mailable
{
    use Queueable, SerializesModels;

    public $aussteller;
    public $subject;
    public $htmlContent;

    public function __construct($aussteller)
    {
        $this->aussteller = $aussteller;

        // Template laden und rendern
        $template = EmailTemplate::getByKey('aussteller_bestaetigung');

        if ($template) {
            $variables = $this->prepareVariables();
            $rendered = $template->render($variables);

            $this->subject = $rendered['subject'];
            $this->htmlContent = $rendered['content'];
        } else {
            // Fallback auf ursprüngliches Template
            $this->subject = 'Bestätigung Ihrer Anmeldung';
            $this->htmlContent = null; // Wird dann die Blade-View verwenden
        }
    }

    /**
     * Bereitet die Variablen für das Template vor
     */
    private function prepareVariables(): array
    {
        $name = '';
        if ($this->aussteller->firma) {
            $name = $this->aussteller->firma;
        } else {
            $name = trim($this->aussteller->vorname . ' ' . $this->aussteller->name);
        }

        // Aktuelle Buchung des Ausstellers finden
        $buchung = $this->aussteller->buchungen()->latest()->first();
        $marktName = 'Unbekannt';
        $termine = '';
        $standplatz = '';

        if ($buchung) {
            $marktName = $buchung->termin->markt->name ?? 'Unbekannt';
            $standplatz = $buchung->standplatz ?? '';

            if ($buchung->termin && $buchung->termin->markt && $buchung->termin->markt->termine) {
                $termine = $buchung->termin->markt->termine
                    ->map(fn($t) => \Carbon\Carbon::parse($t->start)->format('d.m.Y'))
                    ->join(', ');
            }
        }

        return [
            'aussteller_name' => $name,
            'markt_name' => $marktName,
            'termine' => $termine,
            'standplatz' => $standplatz,
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
            markdown: 'emails.aussteller.bestaetigung',
            with: ['aussteller' => $this->aussteller]
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
                    'aussteller' => $this->aussteller
                ]);
        }

        // Ansonsten verwende die normale content() Methode
        return $this->subject($this->subject)
            ->markdown('emails.aussteller.bestaetigung', ['aussteller' => $this->aussteller]);
    }

    public function attachments(): array
    {
        $buchung = $this->aussteller->buchungen()->latest()->first();

        if ($buchung) {
            // Protokoll-Eintrag für E-Mail-Versand
            BuchungProtokoll::create([
                'buchung_id' => $buchung->id,
                'user_id' => Auth::id(),
                'aktion' => 'buchungsbestaetigung_email_versendet',
                'from_status' => $buchung->status,
                'to_status' => $buchung->status,
                'details' => 'Anmeldebestätigung wurde per E-Mail versendet.',
            ]);

            $pdf = Pdf::loadView('pdf.buchung', ['buchung' => $buchung]);

            return [
                Attachment::fromData(
                    fn() => $pdf->output(),
                    'buchung-' . $buchung->id . '.pdf'
                )->withMime('application/pdf'),
            ];
        }

        return [];
    }
}
