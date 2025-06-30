<?php

namespace Database\Seeders;

use App\Models\Buchung;
use App\Models\Rechnung;
use App\Models\RechnungPosition;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BuchungSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $stand = [
            'art' => 'klein',
            'flaeche' => 6,
            'laenge' => 4
        ];

        $warenangebot = ['kleidung', 'schmuck', 'kunst'];

        $herkunft = [
            'eigenfertigung' => 80,
            'industrieware_nicht_entwicklungslaender' => 0,
            'industrieware_entwicklungslaender' => 20
        ];

        $werbematerial = [
            [
                'typ' => 'flyer',
                'anzahl' => 10,
                'physisch' => true,
                'digital' => false,
            ],
            [
                'typ' => 'brochure',
                'anzahl' => 10,
                'physisch' => true,
                'digital' => false,
            ],
        ];

        $leistungen = [
            [
                'leistung_id' => 1,
                'menge' => 1,
                'preis' => 14400,
            ],
            [
                'leistung_id' => 3,
                'menge' => 1,
                'preis' => 5000,
            ],
            [
                'leistung_id' => 4,
                'menge' => 1,
                'preis' => 5000,
            ],
            [
                'leistung_id' => 5,
                'menge' => 2,
                'preis' => 5000,
            ],
        ];

        $status = ['anfrage', 'bearbeitung', 'bestätigt', 'erledigt', 'abgelehnt'];
        $rechnungStatus = ['draft', 'sent', 'paid', 'overdue', 'canceled', 'partial'];

        for ($i = 1; $i <= 100; $i++) {
            $buchung = Buchung::create([
                'status' => $status[array_rand($status)],
                'termin_id' => rand(1, 2),
                'standort_id' => 1,
                'standplatz' => $i,
                'aussteller_id' => $i,
                'stand' => $stand,
                'warenangebot' => $warenangebot,
                'herkunft' => $herkunft,
                'werbematerial' => $werbematerial,
                'created_at' => now()->subDays(rand(0, 30))->setHour(rand(8, 20))->setMinute(rand(0, 59)),
            ]);

            // Füge die Leistungen hinzu
            foreach ($leistungen as $leistung) {
                $buchung->leistungen()->create($leistung);
            }

            // Erstelle Rechnungen nur für Buchungen mit Status "erledigt"
            if ($buchung->status === 'erledigt') {
                $this->createRechnungForBuchung($buchung, $rechnungStatus);
            }
        }

        // Erstelle auch einige manuelle Rechnungen (ohne Buchung)
        $this->createManuelleRechnungen($rechnungStatus);
    }

    private function createRechnungForBuchung(Buchung $buchung, array $rechnungStatus): void
    {
        $status = $rechnungStatus[array_rand($rechnungStatus)];
        $rechnungsDatum = Carbon::parse($buchung->created_at)->addDays(rand(1, 10));
        $faelligkeitsDatum = $rechnungsDatum->copy()->addDays(14);

        // Generiere Rechnungsnummer
        $year = $rechnungsDatum->year;
        $nummer = rand(1, 9999);
        $rechnungsnummer = $year . sprintf('%04d', $nummer);

        // Erstelle Rechnung
        $rechnung = Rechnung::create([
            'rechnungsnummer' => $rechnungsnummer,
            'status' => $status,
            'buchung_id' => $buchung->id,
            'aussteller_id' => $buchung->aussteller_id,
            'rechnungsdatum' => $rechnungsDatum->toDateString(),
            'lieferdatum' => $rechnungsDatum->toDateString(),
            'faelligkeitsdatum' => $faelligkeitsDatum->toDateString(),
            'betreff' => 'Rechnung für ' . ($buchung->termin->markt->name ?? 'Markt'),
            'anschreiben' => 'Vielen Dank für Ihre Teilnahme an unserem Markt.',
            'schlussschreiben' => 'Wir freuen uns auf eine weitere Zusammenarbeit.',
            'zahlungsziel' => '14 Tage netto',
            'gesamtrabatt_prozent' => rand(0, 1) ? 0 : rand(5, 15), // 50% ohne Rabatt
            'gesamtrabatt_betrag' => 0, // Wird später berechnet
            'nettobetrag' => 0, // Wird später berechnet
            'steuerbetrag' => 0, // Wird später berechnet
            'bruttobetrag' => 0, // Wird später berechnet
            // Empfänger-Daten kopieren
            'empf_firma' => $buchung->aussteller->firma,
            'empf_anrede' => $buchung->aussteller->anrede,
            'empf_vorname' => $buchung->aussteller->vorname,
            'empf_name' => $buchung->aussteller->name,
            'empf_strasse' => $buchung->aussteller->strasse,
            'empf_hausnummer' => $buchung->aussteller->hausnummer,
            'empf_plz' => $buchung->aussteller->plz,
            'empf_ort' => $buchung->aussteller->ort,
            'empf_land' => $buchung->aussteller->land ?? 'Deutschland',
            'empf_email' => $buchung->aussteller->email,
            // Status-spezifische Felder
            'versendet_am' => in_array($status, ['sent', 'paid', 'overdue', 'partial'])
                ? $rechnungsDatum->addHours(rand(1, 8))
                : null,
            'bezahlt_am' => in_array($status, ['paid', 'partial'])
                ? $rechnungsDatum->addDays(rand(1, 20))
                : null,
            'bezahlter_betrag' => $status === 'partial' ? rand(50, 80) : ($status === 'paid' ? 0 : 0), // Wird später gesetzt
            'created_at' => $rechnungsDatum,
            'updated_at' => $rechnungsDatum,
        ]);

        // Erstelle Rechnungspositionen aus BuchungLeistungen
        $position = 1;
        $gesamtNetto = 0;

        foreach ($buchung->leistungen as $buchungLeistung) {
            $einzelpreis = $buchungLeistung->preis; // Bereits in Cent
            $menge = $buchungLeistung->menge;
            $nettobetrag = $einzelpreis * $menge; // Cent
            $steuerbetrag = round($nettobetrag * 0.19); // Cent
            $bruttobetrag = $nettobetrag + $steuerbetrag; // Cent

            RechnungPosition::create([
                'rechnung_id' => $rechnung->id,
                'buchung_leistung_id' => $buchungLeistung->id,
                'position' => $position,
                'bezeichnung' => $buchungLeistung->leistung->name ?? 'Leistung',
                'beschreibung' => $buchungLeistung->leistung->bemerkung,
                'menge' => $menge,
                'einheit' => $buchungLeistung->leistung->einheit ?? 'Stück',
                'einzelpreis' => $einzelpreis, // Cent
                'rabatt_prozent' => 0,
                'nettobetrag' => $nettobetrag, // Cent
                'steuersatz' => 19.00,
                'steuerbetrag' => $steuerbetrag, // Cent
                'bruttobetrag' => $bruttobetrag, // Cent
            ]);

            $gesamtNetto += $nettobetrag; // Cent
            $position++;
        }

        // Berechne Gesamtbeträge (alle in Cent)
        $rabattBetrag = round($gesamtNetto * ($rechnung->gesamtrabatt_prozent / 100));
        $nettoNachRabatt = $gesamtNetto - $rabattBetrag;
        $steuerGesamt = round($nettoNachRabatt * 0.19);
        $bruttoGesamt = $nettoNachRabatt + $steuerGesamt;

        // Setze bezahlten Betrag für partial und paid (in Cent)
        $bezahlterBetrag = 0;
        if ($status === 'paid') {
            $bezahlterBetrag = $bruttoGesamt;
        } elseif ($status === 'partial') {
            $bezahlterBetrag = round($bruttoGesamt * (rand(30, 80) / 100)); // 30-80% bezahlt
        }

        // Update Rechnung mit berechneten Beträgen
        $rechnung->update([
            'gesamtrabatt_betrag' => $rabattBetrag,
            'nettobetrag' => $nettoNachRabatt,
            'steuerbetrag' => $steuerGesamt,
            'bruttobetrag' => $bruttoGesamt,
            'bezahlter_betrag' => $bezahlterBetrag,
        ]);
    }

    private function createManuelleRechnungen(array $rechnungStatus): void
    {
        // Erstelle 10 manuelle Rechnungen
        for ($i = 1; $i <= 10; $i++) {
            $status = $rechnungStatus[array_rand($rechnungStatus)];
            $ausstellerId = rand(1, 100);
            $rechnungsDatum = now()->subDays(rand(5, 60));
            $faelligkeitsDatum = $rechnungsDatum->copy()->addDays(14);

            // Hole Aussteller-Daten
            $aussteller = \App\Models\Aussteller::find($ausstellerId);
            if (!$aussteller) continue;

            // Generiere Rechnungsnummer
            $year = $rechnungsDatum->year;
            $nummer = rand(8000, 9999); // Höhere Nummern für manuelle Rechnungen
            $rechnungsnummer = $year . sprintf('%04d', $nummer);

            $rechnung = Rechnung::create([
                'rechnungsnummer' => $rechnungsnummer,
                'status' => $status,
                'buchung_id' => null, // Manuelle Rechnung - keine Buchung
                'aussteller_id' => $ausstellerId,
                'rechnungsdatum' => $rechnungsDatum->toDateString(),
                'lieferdatum' => $rechnungsDatum->toDateString(),
                'faelligkeitsdatum' => $faelligkeitsDatum->toDateString(),
                'betreff' => 'Sonderrechnung - ' . implode(' ', \Faker\Factory::create('de_DE')->words(3)),
                'anschreiben' => 'Vielen Dank für Ihre Anfrage. Hiermit stellen wir Ihnen folgende Leistungen in Rechnung.',
                'schlussschreiben' => 'Bei Fragen stehen wir Ihnen gerne zur Verfügung.',
                'zahlungsziel' => rand(0, 1) ? '14 Tage netto' : '30 Tage netto',
                'gesamtrabatt_prozent' => rand(0, 1) ? 0 : rand(5, 20),
                'gesamtrabatt_betrag' => 0,
                'nettobetrag' => 0,
                'steuerbetrag' => 0,
                'bruttobetrag' => 0,
                // Empfänger-Daten
                'empf_firma' => $aussteller->firma,
                'empf_anrede' => $aussteller->anrede,
                'empf_vorname' => $aussteller->vorname,
                'empf_name' => $aussteller->name,
                'empf_strasse' => $aussteller->strasse,
                'empf_hausnummer' => $aussteller->hausnummer,
                'empf_plz' => $aussteller->plz,
                'empf_ort' => $aussteller->ort,
                'empf_land' => $aussteller->land ?? 'Deutschland',
                'empf_email' => $aussteller->email,
                'versendet_am' => in_array($status, ['sent', 'paid', 'overdue', 'partial'])
                    ? $rechnungsDatum->addHours(rand(1, 8))
                    : null,
                'bezahlt_am' => in_array($status, ['paid', 'partial'])
                    ? $rechnungsDatum->addDays(rand(1, 25))
                    : null,
                'bezahlter_betrag' => 0,
                'created_at' => $rechnungsDatum,
                'updated_at' => $rechnungsDatum,
            ]);

            // Erstelle 1-3 manuelle Positionen
            $anzahlPositionen = rand(1, 3);
            $gesamtNetto = 0;

            for ($p = 1; $p <= $anzahlPositionen; $p++) {
                $bezeichnungen = [
                    'Beratungsleistung',
                    'Sondergebühr',
                    'Zusatzservice',
                    'Werbematerial',
                    'Standgebühr Nachzahlung',
                    'Stornierungsgebühr'
                ];

                $einzelpreis = rand(2500, 20000); // Cent (25-200 Euro)
                $menge = rand(1, 3);
                $nettobetrag = $einzelpreis * $menge; // Cent
                $steuerbetrag = round($nettobetrag * 0.19); // Cent
                $bruttobetrag = $nettobetrag + $steuerbetrag; // Cent

                RechnungPosition::create([
                    'rechnung_id' => $rechnung->id,
                    'buchung_leistung_id' => null, // Manuelle Position
                    'position' => $p,
                    'bezeichnung' => $bezeichnungen[array_rand($bezeichnungen)],
                    'beschreibung' => \Faker\Factory::create('de_DE')->sentence(),
                    'menge' => $menge,
                    'einheit' => 'Stück',
                    'einzelpreis' => $einzelpreis, // Cent
                    'rabatt_prozent' => 0,
                    'nettobetrag' => $nettobetrag, // Cent
                    'steuersatz' => 19.00,
                    'steuerbetrag' => $steuerbetrag, // Cent
                    'bruttobetrag' => $bruttobetrag, // Cent
                ]);

                $gesamtNetto += $nettobetrag;
            }

            // Berechne Gesamtbeträge (alle in Cent)
            $rabattBetrag = round($gesamtNetto * ($rechnung->gesamtrabatt_prozent / 100));
            $nettoNachRabatt = $gesamtNetto - $rabattBetrag;
            $steuerGesamt = round($nettoNachRabatt * 0.19);
            $bruttoGesamt = $nettoNachRabatt + $steuerGesamt;

            // Setze bezahlten Betrag (in Cent)
            $bezahlterBetrag = 0;
            if ($status === 'paid') {
                $bezahlterBetrag = $bruttoGesamt;
            } elseif ($status === 'partial') {
                $bezahlterBetrag = round($bruttoGesamt * (rand(25, 75) / 100));
            }

            $rechnung->update([
                'gesamtrabatt_betrag' => $rabattBetrag,
                'nettobetrag' => $nettoNachRabatt,
                'steuerbetrag' => $steuerGesamt,
                'bruttobetrag' => $bruttoGesamt,
                'bezahlter_betrag' => $bezahlterBetrag,
            ]);
        }
    }
}
