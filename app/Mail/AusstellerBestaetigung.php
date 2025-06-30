<?php

namespace App\Mail;

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

    public function __construct($aussteller)
    {
        $this->aussteller = $aussteller;
    }

    public function envelope(): Envelope
    {
        $toEmail = app()->environment('production')
            ? $this->aussteller->email
            : config('mail.dev_redirect_email');

        return new Envelope(
            subject: 'Deine Anmeldung zum Markt',
            to: $toEmail,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.aussteller.bestaetigung',
            with: ['aussteller' => $this->aussteller]
        );
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
