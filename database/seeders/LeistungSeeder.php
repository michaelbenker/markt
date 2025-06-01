<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Leistung;

class LeistungSeeder extends Seeder
{
    public function run(): void
    {
        $preise = [
            [
                'name' => 'Grundfläche',
                'kategorie' => 'miete',
                'bemerkung' => 'Standmiete Grundfläche (2 Meter Frontlänge x 3 Meter Standtiefe) ',
                'einheit' => 'pauschal',
                'preis' => 14400,
            ],
            [
                'name' => 'Frontlänge',
                'kategorie' => 'miete',
                'bemerkung' => 'Standmiete pro m Frontlänge',
                'einheit' => 'm',
                'preis' => 4500,
            ],
            [
                'name' => 'Wasseranschluss',
                'kategorie' => 'nebenkosten',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 5000,
            ],
            [
                'name' => 'Stromanschluss',
                'kategorie' => 'nebenkosten',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 5000,
            ],
            [
                'name' => 'Tisch',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 5000,
            ],
        ];

        foreach ($preise as $eintrag) {
            Leistung::create($eintrag);
        }
    }
}
