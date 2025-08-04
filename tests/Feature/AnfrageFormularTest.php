<?php

namespace Tests\Feature;

use App\Models\Markt;
use App\Models\Anfrage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnfrageFormularTest extends TestCase
{
    use RefreshDatabase;

    public function test_anfrage_formular_speichert_erfolgreich()
    {
        $markt = Markt::factory()->create();

        $payload = [
            'markt' => $markt->id,
            'firma' => 'Test GmbH',
            'anrede' => 'Herr',
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'hausnummer' => '1',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'telefon' => '0123456789',
            'email' => 'test@example.com',
            'stand' => [
                'art' => 'klein',
                'laenge' => 3,
                'flaeche' => 9
            ],
            'warenangebot' => ['kleidung', 'schmuck'],
            'herkunft' => [
                'eigenfertigung' => 50,
                'industrieware_nicht_entwicklungslaender' => 30,
                'industrieware_entwicklungslaender' => 20
            ],
            'bereits_ausgestellt' => true,
            'vorfuehrung_am_stand' => true,
            'bemerkung' => 'Test-Bemerkung',
        ];

        $response = $this->post(route('anfrage.store'), $payload);

        $response->assertRedirect(route('anfrage.success'));
        $this->assertDatabaseHas('anfrage', [
            'email' => 'test@example.com',
            'markt_id' => $markt->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'bemerkung' => 'Test-Bemerkung',
        ]);
    }
}
