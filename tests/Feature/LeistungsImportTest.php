<?php

namespace Tests\Feature;

use App\Models\Anfrage;
use App\Models\Markt;
use App\Models\Termin;
use App\Models\Subkategorie;
use App\Models\Kategorie;
use App\Models\Standort;
use App\Models\Leistung;
use App\Models\Aussteller;
use App\Models\Buchung;
use App\Models\BuchungLeistung;
use App\Filament\Resources\AnfrageResource\Pages\ViewAnfrage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeistungsImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
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
        for ($i = 1; $i <= 2; $i++) {
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

        // Standort erstellen
        $this->standort = Standort::create([
            'name' => 'Test Standort',
            'beschreibung' => 'Ein Test Standort',
            'flaeche' => '100 qm',
        ]);

        $this->markt->standorte()->attach([$this->standort->id]);

        // Leistungen erstellen
        $this->leistung1 = Leistung::create([
            'name' => 'Stromanschluss',
            'kategorie' => 'Technik',
            'bemerkung' => 'Standard Stromanschluss 16A',
            'preis' => 2500, // 25,00 € in Cent
            'einheit' => 'Stück',
        ]);

        $this->leistung2 = Leistung::create([
            'name' => 'Tisch',
            'kategorie' => 'Möbel',
            'bemerkung' => 'Standard Biertisch',
            'preis' => 1500, // 15,00 € in Cent
            'einheit' => 'Stück',
        ]);

        // Leistungen dem Markt zuordnen
        $this->markt->leistungen()->attach([$this->leistung1->id, $this->leistung2->id]);

        // Aussteller erstellen
        $this->aussteller = Aussteller::create([
            'firma' => 'Test Firma',
            'anrede' => 'Herr',
            'vorname' => 'Max',
            'name' => 'Mustermann',
            'strasse' => 'Teststraße',
            'hausnummer' => '123',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'telefon' => '0123456789',
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function leistungen_werden_beim_import_korrekt_übertragen()
    {
        // Anfrage mit gewünschten Leistungen erstellen
        $anfrage = Anfrage::create([
            'termin_id' => $this->termin->id,
            'firma' => 'Test Firma',
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
            'stand' => ['laenge' => 3, 'tiefe' => 2, 'flaeche' => 6],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'wuensche_zusatzleistungen' => [$this->leistung1->id, $this->leistung2->id],
            'importiert' => false,
        ]);

        // ViewAnfrage Page erstellen und Import durchführen
        $viewAnfrage = new ViewAnfrage();
        $viewAnfrage->anfrageId = $anfrage->id;
        
        // Reflection nutzen um private Methode zu testen
        $reflection = new \ReflectionClass($viewAnfrage);
        $method = $reflection->getMethod('importLeistungenFromAnfrage');
        $method->setAccessible(true);

        // Buchung erstellen
        $buchung = Buchung::create([
            'status' => 'bearbeitung',
            'termin_id' => $this->termin->id,
            'standort_id' => $this->standort->id,
            'standplatz' => 1,
            'aussteller_id' => $this->aussteller->id,
            'stand' => $anfrage->stand,
            'warenangebot' => $anfrage->warenangebot,
            'herkunft' => $anfrage->herkunft,
        ]);

        // Import-Methode aufrufen
        $method->invoke($viewAnfrage, $anfrage, $buchung);

        // Prüfen ob Leistungen korrekt importiert wurden
        $buchungsLeistungen = BuchungLeistung::where('buchung_id', $buchung->id)->get();
        
        $this->assertCount(2, $buchungsLeistungen);
        
        // Erste Leistung prüfen
        $ersteleistung = $buchungsLeistungen->where('leistung_id', $this->leistung1->id)->first();
        $this->assertNotNull($ersteleistung);
        $this->assertEquals($this->leistung1->id, $ersteleistung->leistung_id);
        $this->assertEquals($this->leistung1->preis, $ersteleistung->preis);
        $this->assertEquals(1, $ersteleistung->menge);
        $this->assertEquals(1, $ersteleistung->sort);

        // Zweite Leistung prüfen
        $zweiteleistung = $buchungsLeistungen->where('leistung_id', $this->leistung2->id)->first();
        $this->assertNotNull($zweiteleistung);
        $this->assertEquals($this->leistung2->id, $zweiteleistung->leistung_id);
        $this->assertEquals($this->leistung2->preis, $zweiteleistung->preis);
        $this->assertEquals(1, $zweiteleistung->menge);
        $this->assertEquals(2, $zweiteleistung->sort);
    }

    /** @test */
    public function import_funktioniert_ohne_leistungen()
    {
        // Anfrage ohne gewünschte Leistungen erstellen
        $anfrage = Anfrage::create([
            'termin_id' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'email' => 'test@example.com',
            'stand' => ['laenge' => 3, 'tiefe' => 2, 'flaeche' => 6],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'wuensche_zusatzleistungen' => [], // Keine Leistungen
            'importiert' => false,
        ]);

        $viewAnfrage = new ViewAnfrage();
        $viewAnfrage->anfrageId = $anfrage->id;
        
        $reflection = new \ReflectionClass($viewAnfrage);
        $method = $reflection->getMethod('importLeistungenFromAnfrage');
        $method->setAccessible(true);

        $buchung = Buchung::create([
            'status' => 'bearbeitung',
            'termin_id' => $this->termin->id,
            'standort_id' => $this->standort->id,
            'standplatz' => 1,
            'aussteller_id' => $this->aussteller->id,
            'stand' => $anfrage->stand,
            'warenangebot' => $anfrage->warenangebot,
            'herkunft' => $anfrage->herkunft,
        ]);

        // Import-Methode aufrufen
        $method->invoke($viewAnfrage, $anfrage, $buchung);

        // Prüfen dass keine Leistungen importiert wurden
        $buchungsLeistungen = BuchungLeistung::where('buchung_id', $buchung->id)->get();
        $this->assertCount(0, $buchungsLeistungen);
    }

    /** @test */
    public function import_ignoriert_nicht_existierende_leistungen()
    {
        // Anfrage mit nicht-existierenden Leistungs-IDs erstellen
        $anfrage = Anfrage::create([
            'termin_id' => $this->termin->id,
            'vorname' => 'Max',
            'nachname' => 'Mustermann',
            'strasse' => 'Teststraße',
            'plz' => '12345',
            'ort' => 'Teststadt',
            'land' => 'Deutschland',
            'email' => 'test@example.com',
            'stand' => ['laenge' => 3, 'tiefe' => 2, 'flaeche' => 6],
            'warenangebot' => $this->subkategorieIds,
            'herkunft' => [
                'eigenfertigung' => 100,
                'industrieware_nicht_entwicklungslaender' => 0,
                'industrieware_entwicklungslaender' => 0,
            ],
            'wuensche_zusatzleistungen' => [$this->leistung1->id, 999], // ID 999 existiert nicht
            'importiert' => false,
        ]);

        $viewAnfrage = new ViewAnfrage();
        $viewAnfrage->anfrageId = $anfrage->id;
        
        $reflection = new \ReflectionClass($viewAnfrage);
        $method = $reflection->getMethod('importLeistungenFromAnfrage');
        $method->setAccessible(true);

        $buchung = Buchung::create([
            'status' => 'bearbeitung',
            'termin_id' => $this->termin->id,
            'standort_id' => $this->standort->id,
            'standplatz' => 1,
            'aussteller_id' => $this->aussteller->id,
            'stand' => $anfrage->stand,
            'warenangebot' => $anfrage->warenangebot,
            'herkunft' => $anfrage->herkunft,
        ]);

        // Import-Methode aufrufen
        $method->invoke($viewAnfrage, $anfrage, $buchung);

        // Prüfen dass nur die existierende Leistung importiert wurde
        $buchungsLeistungen = BuchungLeistung::where('buchung_id', $buchung->id)->get();
        $this->assertCount(1, $buchungsLeistungen);
        $this->assertEquals($this->leistung1->id, $buchungsLeistungen->first()->leistung_id);
    }
}