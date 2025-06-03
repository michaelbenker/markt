<?php

namespace App\Mail;

use App\Models\Anfrage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AnfrageBestaetigung extends Mailable
{
    use Queueable, SerializesModels;

    public Anfrage $anfrage;

    public function __construct(Anfrage $anfrage)
    {
        $this->anfrage = $anfrage;
    }

    public function build()
    {
        return $this->subject('Ihre Buchungsanfrage bei Markt-App')
            ->markdown('emails.anfrage.bestaetigung')
            ->with(['anfrage' => $this->anfrage]);
    }
}
