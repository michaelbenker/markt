<?php

namespace App\Mail;

use App\Models\Anfrage;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaeglicheAnfragenUebersicht extends Mailable
{
    use Queueable, SerializesModels;

    public $neueAnfragen;
    public $datum;
    public $gesamtAnzahl;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $this->datum = Carbon::yesterday();

        // Neue Anfragen von gestern
        $this->neueAnfragen = Anfrage::whereDate('created_at', $this->datum)
            ->with(['markt'])
            ->orderBy('created_at', 'desc')
            ->get();

        $this->gesamtAnzahl = $this->neueAnfragen->count();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tägliche Anfragen-Übersicht - ' . $this->datum->format('d.m.Y') . ' (' . $this->gesamtAnzahl . ' neue Anfragen)',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.taegliche-anfragen-uebersicht',
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
