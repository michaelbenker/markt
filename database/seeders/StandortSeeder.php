<?php

namespace Database\Seeders;

use App\Models\Markt;
use App\Models\Standort;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StandortSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adventsmarkt = Markt::where('name', 'Adventsmarkt')->first();
        $toepfermarkt = Markt::where('name', 'Töpfermarkt')->first();

        if ($adventsmarkt) {
            Standort::create(['markt_id' => $adventsmarkt->id, 'name' => 'Tenne OG']);
            Standort::create(['markt_id' => $adventsmarkt->id, 'name' => 'Tenne EG']);
            Standort::create(['markt_id' => $adventsmarkt->id, 'name' => 'Stadtsaalhof']);
        }

        if ($toepfermarkt) {
            Standort::create(['markt_id' => $toepfermarkt->id, 'name' => 'Arkadengang']);
            Standort::create(['markt_id' => $toepfermarkt->id, 'name' => 'Waaghäuslwiese']);
            Standort::create(['markt_id' => $toepfermarkt->id, 'name' => 'Kirchvorplatz']);
            Standort::create(['markt_id' => $toepfermarkt->id, 'name' => 'Fachhochschule']);
        }
    }
}
