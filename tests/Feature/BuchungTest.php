<?php

namespace Tests\Feature;

use App\Models\Markt;
use App\Models\Aussteller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuchungTest extends TestCase
{
    use RefreshDatabase;

    public function test_buchung_wird_mit_status_anfrage_erstellt()
    {
        // Arrange
        $markt = Markt::factory()->create();
        $aussteller = Aussteller::factory()->create();

        $buchungData = [
            'markt' => $markt->id,
            'firma' => 'Test Firma',
            'anrede' => 'Herr',
            'vorname' => 'Max',
            'name' => 'Mustermann',
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
            ]
        ];

        // Act
        $response = $this->post('/buchung', $buchungData);

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('buchung', [
            'status' => 'anfrage',
            'markt_id' => $markt->id,
            'aussteller_id' => $aussteller->id
        ]);
    }
}
