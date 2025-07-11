<?php

namespace Tests\Feature;

use App\Services\MailService;
use App\Models\Aussteller;
use App\Models\Buchung;
use App\Models\Termin;
use App\Models\Markt;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendAusstellerBestaetigungMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_aussteller_bestaetigung_mail_can_be_sent()
    {
        // Mail::fake() aktivieren, um echte E-Mails zu verhindern
        Mail::fake();

        // Test-Daten erstellen
        $markt = Markt::factory()->create(['name' => 'Test Markt']);
        $termin = Termin::factory()->create(['markt_id' => $markt->id]);
        $aussteller = Aussteller::factory()->create([
            'email' => 'test@example.com',
            'firma' => 'Test Firma',
            'vorname' => 'Max',
            'name' => 'Mustermann'
        ]);
        $buchung = Buchung::factory()->create([
            'aussteller_id' => $aussteller->id,
            'termin_id' => $termin->id
        ]);

        // E-Mail über MailService versenden
        $mailService = new MailService();
        $success = $mailService->sendAusstellerBestaetigung($buchung);

        // Überprüfen, ob die E-Mail erfolgreich versendet wurde
        $this->assertTrue($success);

        // Überprüfen, ob eine E-Mail versendet wurde
        Mail::assertSent(\App\Services\UniversalMail::class);
    }

    public function test_aussteller_bestaetigung_mail_with_invalid_data()
    {
        Mail::fake();

        $aussteller = Aussteller::factory()->create(['email' => null]);
        $buchung = Buchung::factory()->create(['aussteller_id' => $aussteller->id]);

        $mailService = new MailService();
        $success = $mailService->sendAusstellerBestaetigung($buchung);

        // Sollte fehlschlagen bei fehlender E-Mail-Adresse
        $this->assertFalse($success);

        // Keine E-Mail sollte versendet worden sein
        Mail::assertNotSent(\App\Services\UniversalMail::class);
    }
}
