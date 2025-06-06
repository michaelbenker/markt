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
        Markt::create([
            'slug' => 'adventsmarkt',
            'name' => 'Adventsmarkt',
            'bemerkung' => 'Beim “Advent in Fürstenfeld” präsentiert sich das Klosterareal an zwei Wochenenden von seiner schönsten Seite - Lichterglanz, Leckereien, Markt, Kunst & Musik stimmen hier auf die Weihnachtszeit ein.',
            'url' => 'https://adventinfuerstenfeld.de/',
        ]);

        Markt::create([
            'slug' => 'toepfermarkt',
            'name' => 'Töpfermarkt',
            'bemerkung' => 'Seit über 30 Jahren zählt der Töpfer- und Kunsthandwerkermarkt im Veranstaltungsforum Fürstenfeld zu den schönsten Märkten Bayerns. Getreu dem Motto „Qualität vor Quantität" präsentieren im ehemaligen Klosterareal rund 100 sorgfältig ausgewählte nationale und internationale Ausstellende handgefertigte Waren.',
            'url' => 'https://www.toepfermarkt-fuerstenfeld.de/',
        ]);

        Markt::create([
            'slug' => 'kirta',
            'name' => 'Kirta',
            'bemerkung' => 'Der Fürstenfelder Kirta ist das größte Kirchweihfest im Landkreis. Hier gibt es alles, was Bayern lebens- und liebenswert macht und eine Stimmung wie auf der Oidn Wiesn! Besonders eignet sich der Kirta im Veranstaltungsforum für einen schönen Familienausflug, zum gemütlichen Beisammensein bei zünftiger Musik und herzhafter Brotzeit.',
            'url' => 'https://www.kirta-fuerstenfeld.de/',
        ]);
    }
}
