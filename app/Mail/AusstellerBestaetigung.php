<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AusstellerBestaetigung extends Mailable
{
    use Queueable, SerializesModels;

    public $aussteller;

    public function __construct($aussteller)
    {
        $this->aussteller = $aussteller;
    }

    public function build()
    {
        return $this->markdown('emails.aussteller.bestaetigung')
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
        return [];
    }
}
