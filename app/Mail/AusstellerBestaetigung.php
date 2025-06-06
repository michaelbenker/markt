<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\App;
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
        return new Envelope(
            subject: 'Ihre Buchungsbestätigung',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.aussteller-bestaetigung',
        );
    }

    public function build()
    {
        $toEmail = App::environment('production')
            ? $this->aussteller->email
            : config('mail.dev_redirect_email');

        $buchung = $this->aussteller->buchungen()->latest()->first();
        if ($buchung) {
            BuchungProtokoll::create([
                'buchung_id' => $buchung->id,
                'user_id' => Auth::id(),
                'aktion' => 'buchungsbestaetigung_email_versendet',
                'from_status' => $buchung->status,
                'to_status' => $buchung->status,
                'details' => 'Buchungsbestätigung wurde per E-Mail an ' . $toEmail . ' versendet.',
            ]);
        }

        return $this->to($toEmail)
            ->markdown('emails.aussteller.bestaetigung')
            ->subject('Deine Anmeldung zum Markt')
            ->with(['aussteller' => $this->aussteller]);
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $buchung = $this->aussteller->buchungen()->latest()->first();

        if ($buchung) {
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
