<?php

namespace Database\Seeders;

use App\Models\Buchung;
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

        // Erstelle Buchungen für Aussteller 1-4
        for ($i = 1; $i <= 4; $i++) {
            $buchung = Buchung::create([
                'status' => 'anfrage',
                'termin_id' => 1,
                'standort_id' => 1,
                'standplatz' => $i,
                'aussteller_id' => $i,
                'stand' => $stand,
                'warenangebot' => $warenangebot,
                'herkunft' => $herkunft,
            ]);

            // Füge die Leistungen hinzu
            foreach ($leistungen as $leistung) {
                $buchung->preise()->attach($leistung['leistung_id'], [
                    'menge' => $leistung['menge'],
                    'preis' => $leistung['preis']
                ]);
            }
        }
    }
}
