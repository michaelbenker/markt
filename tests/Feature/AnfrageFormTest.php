<?php

namespace Tests\Feature;

use App\Models\Anfrage;
use App\Models\Markt;
use App\Models\Termin;
use App\Models\Subkategorie;
use App\Models\Kategorie;
use App\Models\Standort;
use App\Models\Leistung;
use App\Models\User;
use App\Models\Medien;
use App\Notifications\NeueAnfrageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnfrageFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Testdaten erstellen
        $this->createTestData();
    }

    private function createTestData()
    {
        // Kategorien und Subkategorien erstellen
        $kategorie = Kategorie::create([
            'name' => 'Test Kategorie',
            'beschreibung' => 'Eine Test Kategorie',
        ]);

        $this->subkategorieIds = [];
        for ($i = 1; $i <= 3; $i++) {
            $subkategorie = Subkategorie::create([
                'kategorie_id' => $kategorie->id,
                'name' => "Test Subkategorie $i",
                'bemerkung' => "Eine Test Subkategorie $i",
            ]);
            $this->subkategorieIds[] = $subkategorie->id;
        }

        // Markt erstellen
        $this->markt = Markt::create([
            'slug' => 'test-markt',
            'name' => 'Test Markt',
            'bemerkung' => 'Ein Test Markt',
            'url' => 'https://test-markt.de',
            'subkategorien' => $this->subkategorieIds,
        ]);

        // Termin erstellen
        $this->termin = Termin::create([
            'markt_id' => $this->markt->id,
            'start' => now()->addDays(30),
            'ende' => now()->addDays(32),
            'anmeldeschluss' => now()->addDays(20),
        ]);

        // Standorte erstellen
        $standort1 = Standort::create([
            'name' => 'Test Standort 1',
            'beschreibung' => 'Ein Test Standort',
            'flaeche' => '100 qm',
        ]);

        $standort2 = Standort::create([
            'name' => 'Test Standort 2', 
            'beschreibung' => 'Noch ein Test Standort',
            'flaeche' => '150 qm',
        ]);

        // Standorte dem Markt zuordnen
        $this->markt->standorte()->attach([$standort1->id, $standort2->id]);

        // Leistungen erstellen
        $leistung1 = Leistung::create([
            'name' => 'Stromanschluss',
            'kategorie' => 'Technik',
            'bemerkung' => 'Standard Stromanschluss 16A',
            'preis' => 2500, // 25,00 € in Cent
            'einheit' => 'Stück',
        ]);

        $leistung2 = Leistung::create([
            'name' => 'Tisch',
            'kategorie' => 'Möbel',
            'bemerkung' => 'Standard Biertisch',
            'preis' => 1500, // 15,00 € in Cent
            'einheit' => 'Stück',
        ]);

        // Leistungen dem Markt zuordnen
        $this->markt->leistungen()->attach([$leistung1->id, $leistung2->id]);

        // Test User für Notifications
        $this->user = User::factory()->create();
    }

    /** @test */
    public function anfrageformular_wird_korrekt_geladen()
    {
        $response = $this->get('/anfrage');

        $response->assertStatus(200);
        $response->assertSee('Buchung anfragen');
        $response->assertSee($this->termin->markt->name);
        $response->assertSee('Test Markt');
    }

    /** @test */
    public function anfrageformular_mit_gültigen_daten_wird_erfolgreich_gespeichert()
    {
        Mail::fake();
        Notification::fake();

        $anfrageData = [
            'termin' => $this->termin->id,
            'firma' => 'Test Firma GmbH',
            'anrede' => 'Herr',
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'hausnummer' => '123',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'telefon' => '0123456789',
            'email' => 'test@example.com',
            'stand' => [
                'laenge' => 3,
                'tiefe' => 2,
                'flaeche' => 6,
            ],
            'wunsch_standort_id' => $this->markt->standorte->first()->id,
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 80,
                'industrieware_nicht_entwicklungslaender' => 15,
                'industrieware_entwicklungslaender' => 5,
            ],
            'bereits_ausgestellt' => 'Ja, bei anderen Märkten',
            'vorfuehrung_am_stand' => '1',
            'bemerkung' => 'Das ist eine Test-Bemerkung',
            'soziale_medien' => ['facebook', 'instagram'],
            'wuensche_zusatzleistungen' => [$this->markt->leistungen->first()->id],
            'werbematerial' => [
                'plakate_a3' => 5,
                'plakate_a1' => 2,
                'flyer' => 100,
                'social_media_post' => '1',
            ],
        ];

        $response = $this->post('/anfrage', $anfrageData);

        $response->assertRedirect('/anfrage/success');
        $response->assertSessionHas('success');

        // Prüfen ob Anfrage in Datenbank gespeichert wurde
        $this->assertDatabaseHas('anfrage', [
            'termin_id' => $this->termin->id,
            'email' => 'test@example.com',
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'firma' => 'Test Firma GmbH',
        ]);

        // Prüfen ob Notification versendet wurde
        Notification::assertSentTo($this->user, NeueAnfrageNotification::class);
    }

    /** @test */
    public function anfrageformular_validierung_für_pflichtfelder()
    {
        $response = $this->post('/anfrage', []);

        $response->assertSessionHasErrors([
            'termin',
            'vorname', 
            'nachname',
            'strasse',
            'plz',
            'ort',
            'land',
            'email',
            'warenangebot',
            'herkunft.eigenfertigung',
            'herkunft.industrieware_nicht_entwicklungslaender', 
            'herkunft.industrieware_entwicklungslaender',
        ]);
    }

    /** @test */
    public function anfrageformular_validierung_für_email_format()
    {
        $response = $this->post('/anfrage', [
            'email' => 'ungültige-email',
            'termin' => $this->termin->id,
            'vorname' => 'Test',
            'nachname' => 'Test',
            'strasse' => 'Test',
            'plz' => '12345',
            'ort' => 'Test',
            'land' => 'Test',
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function anfrageformular_validierung_für_herkunft_prozentsätze()
    {
        $response = $this->post('/anfrage', [
            'termin' => $this->termin->id,
            'vorname' => 'Test',
            'nachname' => 'Test',
            'strasse' => 'Test',
            'plz' => '12345',
            'ort' => 'Test',
            'land' => 'Test',
            'email' => 'test@example.com',
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 150, // Ungültig: > 100
                'industrieware_nicht_entwicklungslaender' => -5, // Ungültig: < 0
                'industrieware_entwicklungslaender' => 50,
            ],
        ]);

        $response->assertSessionHasErrors([
            'herkunft.eigenfertigung',
            'herkunft.industrieware_nicht_entwicklungslaender',
        ]);
    }

    /** @test */
    public function file_upload_funktioniert_korrekt()
    {
        Storage::fake('public');
        Mail::fake();
        Notification::fake();

        $file = UploadedFile::fake()->image('test-foto.jpg', 800, 600)->size(1000); // 1MB

        $anfrageData = [
            'termin' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'email' => 'test@example.com',
            'stand' => [
                'laenge' => 3,
                'tiefe' => 2,
                'flaeche' => 6,
            ],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'detailfotos_warenangebot' => [$file],
        ];

        $response = $this->post('/anfrage', $anfrageData);

        $response->assertRedirect('/anfrage/success');

        // Prüfen ob File in Storage gespeichert wurde
        $anfrage = Anfrage::latest()->first();
        $this->assertCount(1, $anfrage->medien);
        
        $medien = $anfrage->medien->first();
        $this->assertEquals('angebot', $medien->category);
        Storage::disk('public')->assertExists($medien->path);
    }

    /** @test */
    public function file_upload_größenlimit_wird_geprüft()
    {
        $file = UploadedFile::fake()->image('zu-groß.jpg', 4000, 3000)->size(6000); // 6MB (> 5MB Limit)

        $response = $this->post('/anfrage', [
            'termin' => $this->termin->id,
            'vorname' => 'Test',
            'nachname' => 'Test',
            'strasse' => 'Test',
            'plz' => '12345',
            'ort' => 'Test',
            'land' => 'Test',
            'email' => 'test@example.com',
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'detailfotos_warenangebot' => [$file],
        ]);

        $response->assertSessionHasErrors(['detailfotos_warenangebot.0']);
    }

    /** @test */
    public function maximal_vier_detailfotos_erlaubt()
    {
        $files = [
            UploadedFile::fake()->image('foto1.jpg'),
            UploadedFile::fake()->image('foto2.jpg'),
            UploadedFile::fake()->image('foto3.jpg'),
            UploadedFile::fake()->image('foto4.jpg'),
            UploadedFile::fake()->image('foto5.jpg'), // Ein Foto zu viel
        ];

        $response = $this->post('/anfrage', [
            'termin' => $this->termin->id,
            'vorname' => 'Test',
            'nachname' => 'Test',
            'strasse' => 'Test',
            'plz' => '12345',
            'ort' => 'Test',
            'land' => 'Test',
            'email' => 'test@example.com',
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'detailfotos_warenangebot' => $files,
        ]);

        $response->assertSessionHasErrors(['detailfotos_warenangebot']);
    }

    /** @test */
    public function success_seite_wird_nach_erfolgreicher_anfrage_angezeigt()
    {
        $response = $this->get('/anfrage/success');

        $response->assertStatus(200);
        $response->assertSee('Vielen Dank');
    }

    /** @test */
    public function formular_lädt_korrekte_subkategorien_für_markt()
    {
        $response = $this->get('/anfrage');

        $response->assertStatus(200);
        
        // Prüfen ob JavaScript-Daten korrekt übergeben werden
        $response->assertSee('subkategorienByMarkt');
        $response->assertSee('Test Subkategorie 1');
        $response->assertSee('Test Subkategorie 2');
    }

    /** @test */
    public function formular_lädt_korrekte_standorte_für_markt()
    {
        $response = $this->get('/anfrage');

        $response->assertStatus(200);
        
        // Prüfen ob Standorte-Daten korrekt übergeben werden
        $response->assertSee('standorteByMarkt');
        $response->assertSee('Test Standort 1');
        $response->assertSee('Test Standort 2');
    }

    /** @test */
    public function formular_lädt_korrekte_leistungen_für_markt()
    {
        $response = $this->get('/anfrage');

        $response->assertStatus(200);
        
        // Prüfen ob Leistungen-Daten korrekt übergeben werden
        $response->assertSee('maerkteBySlug');
        $response->assertSee('Stromanschluss');
        // Der Preis wird in der JavaScript-Ausgabe als Zahl dargestellt  
        $response->assertSee('2500');
    }

    /** @test */
    public function old_values_bleiben_bei_validierungsfehlern_erhalten()
    {
        $invalidData = [
            'termin' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'email' => 'ungültige-email', // Validation Error
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 80,
                'industrieware_nicht_entwicklungslaender' => 15,
                'industrieware_entwicklungslaender' => 5,
            ],
            'wuensche_zusatzleistungen' => [$this->markt->leistungen->first()->id],
        ];

        $response = $this->post('/anfrage', $invalidData);

        $response->assertSessionHasErrors(['email']);
        
        // Prüfen ob old() Werte korrekt gesetzt werden
        $this->assertEquals('Max', old('vorname'));
        $this->assertEquals('Mustermann', old('nachname'));
        $this->assertEquals('ungültige-email', old('email'));
        $this->assertEquals($this->subkategorieIds, old('warenangebot'));
        $this->assertEquals([$this->markt->leistungen->first()->id], old('wuensche_zusatzleistungen'));
    }

    /** @test */
    public function gewuenschte_leistungen_werden_korrekt_abgerufen()
    {
        Mail::fake();
        Notification::fake();

        // Anfrage mit Leistungen erstellen
        $anfrageData = [
            'termin' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'email' => 'test@example.com',
            'stand' => [
                'laenge' => 3,
                'tiefe' => 2,
                'flaeche' => 6,
            ],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'wuensche_zusatzleistungen' => [$this->markt->leistungen->first()->id, $this->markt->leistungen->last()->id],
        ];

        $response = $this->post('/anfrage', $anfrageData);
        $response->assertRedirect('/anfrage/success');

        $anfrage = Anfrage::latest()->first();
        $gewuenschteLeistungen = $anfrage->gewuenschteLeistungen();

        // Prüfen ob die richtigen Leistungen zurückgegeben werden
        $this->assertCount(2, $gewuenschteLeistungen);
        $this->assertEquals('Stromanschluss', $gewuenschteLeistungen->first()->name);
        $this->assertEquals('Tisch', $gewuenschteLeistungen->last()->name);
        $this->assertEquals(2500, $gewuenschteLeistungen->first()->preis);
        $this->assertEquals(1500, $gewuenschteLeistungen->last()->preis);
    }

    /** @test */
    public function werbematerial_wird_korrekt_transformiert()
    {
        Mail::fake();
        Notification::fake();

        $anfrageData = [
            'termin' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'email' => 'test@example.com',
            'stand' => [
                'laenge' => 3,
                'tiefe' => 2,
                'flaeche' => 6,
            ],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'werbematerial' => [
                'plakate_a3' => 5,
                'plakate_a1' => 2,
                'flyer' => 100,
                'social_media_post' => '1',
            ],
        ];

        $response = $this->post('/anfrage', $anfrageData);
        $response->assertRedirect('/anfrage/success');

        $anfrage = Anfrage::latest()->first();
        $werbematerial = $anfrage->werbematerial;

        // Prüfen ob das Werbematerial korrekt transformiert wurde
        $this->assertIsArray($werbematerial);
        $this->assertCount(4, $werbematerial);

        // Plakat A3 prüfen
        $plakatA3 = collect($werbematerial)->firstWhere('typ', 'plakat_a3');
        $this->assertNotNull($plakatA3);
        $this->assertEquals('plakat_a3', $plakatA3['typ']);
        $this->assertEquals(5, $plakatA3['anzahl']);
        $this->assertFalse($plakatA3['digital']);
        $this->assertTrue($plakatA3['physisch']);

        // Plakat A1 prüfen
        $plakatA1 = collect($werbematerial)->firstWhere('typ', 'plakat_a1');
        $this->assertNotNull($plakatA1);
        $this->assertEquals('plakat_a1', $plakatA1['typ']);
        $this->assertEquals(2, $plakatA1['anzahl']);
        $this->assertFalse($plakatA1['digital']);
        $this->assertTrue($plakatA1['physisch']);

        // Flyer prüfen
        $flyer = collect($werbematerial)->firstWhere('typ', 'flyer');
        $this->assertNotNull($flyer);
        $this->assertEquals('flyer', $flyer['typ']);
        $this->assertEquals(100, $flyer['anzahl']);
        $this->assertFalse($flyer['digital']);
        $this->assertTrue($flyer['physisch']);

        // Social Media Post prüfen
        $socialMedia = collect($werbematerial)->firstWhere('typ', 'social_media_post');
        $this->assertNotNull($socialMedia);
        $this->assertEquals('social_media_post', $socialMedia['typ']);
        $this->assertEquals(1, $socialMedia['anzahl']);
        $this->assertTrue($socialMedia['digital']);
        $this->assertFalse($socialMedia['physisch']);
    }

    /** @test */
    public function werbematerial_transformation_ignoriert_leere_werte()
    {
        Mail::fake();
        Notification::fake();

        $anfrageData = [
            'termin' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'email' => 'test@example.com',
            'stand' => [
                'laenge' => 3,
                'tiefe' => 2,
                'flaeche' => 6,
            ],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'werbematerial' => [
                'plakate_a3' => 0, // Sollte ignoriert werden
                'plakate_a1' => '', // Sollte ignoriert werden
                'flyer' => 50, // Sollte behalten werden
                'social_media_post' => false, // Sollte ignoriert werden
            ],
        ];

        $response = $this->post('/anfrage', $anfrageData);
        $response->assertRedirect('/anfrage/success');

        $anfrage = Anfrage::latest()->first();
        $werbematerial = $anfrage->werbematerial;

        // Nur Flyer sollte übrig bleiben
        $this->assertIsArray($werbematerial);
        $this->assertCount(1, $werbematerial);
        $this->assertEquals('flyer', $werbematerial[0]['typ']);
        $this->assertEquals(50, $werbematerial[0]['anzahl']);
    }
}