<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Markt;

class MarktSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */

    public function run(): void
    {
        // Adventsmarkt - Gemütlicher Weihnachtsmarkt
        $adventsmarkt = Markt::create([
            'slug' => 'adventsmarkt',
            'name' => 'Adventsmarkt',
            'bemerkung' => 'Beim "Advent in Fürstenfeld" präsentiert sich das Klosterareal an zwei Wochenenden von seiner schönsten Seite - Lichterglanz, Leckereien, Markt, Kunst & Musik stimmen hier auf die Weihnachtszeit ein.',
            'url' => 'https://adventinfuerstenfeld.de/',
            'subkategorien' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 22, 23, 24, 25], // Gastro + Kunsthandwerk + Textilien + Sonstiges
        ]);
        
        // Leistungen für Adventsmarkt (gemütlicher Markt, weniger Technik)
        $adventsmarkt->leistungen()->attach([
            1, 2, // Grundfläche, Frontlänge
            3, 4, 5, // Mobiliar: Tische, Stühle
            10, // Stromanschluss (16A)
            13, // Wasseranschluss
        ]);
        
        // Standorte für Adventsmarkt
        $adventsmarktStandorte = \App\Models\Standort::whereIn('name', ['Tenne OG', 'Tenne EG', 'Stadtsaalhof'])->pluck('id');
        $adventsmarkt->standorte()->attach($adventsmarktStandorte);

        // Töpfermarkt - Hochwertiger Kunsthandwerkermarkt
        $toepfermarkt = Markt::create([
            'slug' => 'toepfermarkt',
            'name' => 'Töpfermarkt',
            'bemerkung' => 'Seit über 30 Jahren zählt der Töpfer- und Kunsthandwerkermarkt im Veranstaltungsforum Fürstenfeld zu den schönsten Märkten Bayerns. Getreu dem Motto „Qualität vor Quantität" präsentieren im ehemaligen Klosterareal rund 100 sorgfältig ausgewählte nationale und internationale Ausstellende handgefertigte Waren.',
            'url' => 'https://www.toepfermarkt-fuerstenfeld.de/',
            'subkategorien' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25], // Alle Subkategorien
        ]);
        
        // Leistungen für Töpfermarkt (alle Leistungen verfügbar)
        $toepfermarkt->leistungen()->attach([
            1, 2, // Grundfläche, Frontlänge
            3, 4, 5, 6, 7, 8, 9, // Alle Möbel
            10, 11, 12, 13, // Alle Strom- und Wasseranschlüsse
        ]);
        
        // Standorte für Töpfermarkt
        $toepfermarktStandorte = \App\Models\Standort::whereIn('name', ['Arkadengang', 'Waaghäuslwiese', 'Kirchvorplatz', 'Fachhochschule'])->pluck('id');
        $toepfermarkt->standorte()->attach($toepfermarktStandorte);

        // Kirta - Volksfest mit viel Gastronomie
        $kirta = Markt::create([
            'slug' => 'kirta',
            'name' => 'Kirta',
            'bemerkung' => 'Der Fürstenfelder Kirta ist das größte Kirchweihfest im Landkreis. Hier gibt es alles, was Bayern lebens- und liebenswert macht und eine Stimmung wie auf der Oidn Wiesn! Besonders eignet sich der Kirta im Veranstaltungsforum für einen schönen Familienausflug, zum gemütlichen Beisammensein bei zünftiger Musik und herzhafter Brotzeit.',
            'url' => 'https://www.kirta-fuerstenfeld.de/',
            'subkategorien' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 25], // Gastro + Kunsthandwerk + Sonstiges
        ]);
        
        // Leistungen für Kirta (viel Gastronomie, Biertische, Starkstrom)
        $kirta->leistungen()->attach([
            1, 2, // Grundfläche, Frontlänge
            6, 7, 8, 9, // Biertische, Bierbänke, Stehtische, Barhocker
            10, 11, 12, 13, // Alle Strom- und Wasseranschlüsse (wichtig für Gastronomie)
        ]);
        
        // Standorte für Kirta (gleiche wie Adventsmarkt)  
        $kirtaStandorte = \App\Models\Standort::whereIn('name', ['Tenne OG', 'Tenne EG', 'Stadtsaalhof'])->pluck('id');
        $kirta->standorte()->attach($kirtaStandorte);
    }
}
