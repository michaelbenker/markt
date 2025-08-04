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
            // Mobiliar
            [
                'name' => 'Tisch 140 x 70',
                'kategorie' => 'mobiliar',
                'bemerkung' => 'Tisch 140cm x 70cm',
                'einheit' => 'stk',
                'preis' => 5000,
            ],
            [
                'name' => 'Tisch 70 x 70',
                'kategorie' => 'mobiliar',
                'bemerkung' => 'Tisch 70cm x 70cm',
                'einheit' => 'stk',
                'preis' => 4000,
            ],
            [
                'name' => 'Stuhl',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 1500,
            ],
            [
                'name' => 'Biertisch',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 3500,
            ],
            [
                'name' => 'Bierbank',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 2500,
            ],
            [
                'name' => 'Stehtisch',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 4000,
            ],
            [
                'name' => 'Barhocker',
                'kategorie' => 'mobiliar',
                'bemerkung' => '',
                'einheit' => 'stk',
                'preis' => 2000,
            ],
            // Elektro/Wasser
            [
                'name' => 'Stromanschluss (16 A Schuko)',
                'kategorie' => 'nebenkosten',
                'bemerkung' => '16 A Schuko-Steckdose',
                'einheit' => 'stk',
                'preis' => 5000,
            ],
            [
                'name' => 'Drehstromanschluss CEE 16 A Kraft',
                'kategorie' => 'nebenkosten',
                'bemerkung' => 'CEE 16 A Kraftstrom',
                'einheit' => 'stk',
                'preis' => 7500,
            ],
            [
                'name' => 'Drehstromanschluss CEE 32 A Kraft',
                'kategorie' => 'nebenkosten',
                'bemerkung' => 'CEE 32 A Kraftstrom',
                'einheit' => 'stk',
                'preis' => 10000,
            ],
            [
                'name' => 'Wasseranschluss',
                'kategorie' => 'nebenkosten',
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
