<?php

namespace Database\Seeders;

use App\Models\Markt;
use App\Models\Standort;
use Illuminate\Database\Seeder;

class StandortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Standorte erstellen (ohne direkte Markt-Zuordnung)
        $standorte = [
            ['name' => 'Tenne OG', 'beschreibung' => 'Oberes Geschoss der Tenne', 'flaeche' => '200 qm'],
            ['name' => 'Tenne EG', 'beschreibung' => 'Erdgeschoss der Tenne', 'flaeche' => '300 qm'],
            ['name' => 'Stadtsaalhof', 'beschreibung' => 'Innenhof des Stadtsaals', 'flaeche' => '150 qm'],
            ['name' => 'Arkadengang', 'beschreibung' => 'Überdachter Arkadengang', 'flaeche' => '100 qm'],
            ['name' => 'Waaghäuslwiese', 'beschreibung' => 'Große Wiese beim Waaghäusl', 'flaeche' => '500 qm'],
            ['name' => 'Kirchvorplatz', 'beschreibung' => 'Platz vor der Kirche', 'flaeche' => '250 qm'],
            ['name' => 'Fachhochschule', 'beschreibung' => 'Gelände der Fachhochschule', 'flaeche' => '400 qm'],
        ];

        foreach ($standorte as $standortData) {
            Standort::create($standortData);
        }

        // Märkte laden und Standorte zuweisen
        $adventsmarkt = Markt::where('name', 'Adventsmarkt')->first();
        $toepfermarkt = Markt::where('name', 'Töpfermarkt')->first();

        if ($adventsmarkt) {
            $standorteAdventsmarkt = Standort::whereIn('name', ['Tenne OG', 'Tenne EG', 'Stadtsaalhof'])->get();
            $adventsmarkt->standorte()->attach($standorteAdventsmarkt->pluck('id'));
        }

        if ($toepfermarkt) {
            $standorteToepfermarkt = Standort::whereIn('name', ['Arkadengang', 'Waaghäuslwiese', 'Kirchvorplatz', 'Fachhochschule'])->get();
            $toepfermarkt->standorte()->attach($standorteToepfermarkt->pluck('id'));
        }

        // Beispiel: Ein Standort kann mehreren Märkten zugewiesen werden
        $gemeinsamerStandort = Standort::where('name', 'Stadtsaalhof')->first();
        if ($gemeinsamerStandort && $toepfermarkt) {
            $toepfermarkt->standorte()->attach($gemeinsamerStandort->id);
        }
    }
}
