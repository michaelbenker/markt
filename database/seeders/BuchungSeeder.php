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
        }
    }
}
