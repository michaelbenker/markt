<?php

namespace Tests\Feature;

use App\Mail\AusstellerBestaetigungMail;
use App\Models\Aussteller;
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

        // Test-Aussteller erstellen
        $aussteller = Aussteller::factory()->create([
            'email' => 'test@example.com',
            'firma' => 'Test Firma',
            'vorname' => 'Max',
            'name' => 'Mustermann'
        ]);

        // E-Mail versenden
        Mail::to($aussteller->email)
            ->send(new AusstellerBestaetigungMail($aussteller));

        // Überprüfen, ob die E-Mail versendet wurde
        Mail::assertSent(AusstellerBestaetigungMail::class, function ($mail) use ($aussteller) {
            return $mail->hasTo($aussteller->email) &&
                $mail->envelope()->subject === 'Test-E-Mail Markt-App';
        });
    }

    public function test_aussteller_bestaetigung_mail_contains_correct_data()
    {
        Mail::fake();

        $aussteller = Aussteller::factory()->create([
            'email' => 'test@example.com',
            'firma' => 'Test Firma',
            'vorname' => 'Max',
            'name' => 'Mustermann'
        ]);

        Mail::to($aussteller->email)
            ->send(new AusstellerBestaetigungMail($aussteller));

        Mail::assertSent(AusstellerBestaetigungMail::class, function ($mail) use ($aussteller) {
            return $mail->hasTo($aussteller->email) &&
                $mail->envelope()->subject === 'Test-E-Mail Markt-App' &&
                $mail->content()->text === 'Dies ist eine Test-E-Mail von der Markt-App.';
        });
    }
}
