<?php

namespace Database\Seeders;

use App\Models\Subkategorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubkategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Subkategorie::insert([
            // Gastro (ID: 1)
            ['name' => 'Speisen', 'kategorie_id' => 1],
            ['name' => 'Getränke', 'kategorie_id' => 1],
            ['name' => 'Lebensmittel', 'kategorie_id' => 1],
            ['name' => 'Marktgastronomie', 'kategorie_id' => 1],
            
            // Kunsthandwerk (ID: 2)
            ['name' => 'Schmuck', 'kategorie_id' => 2],
            ['name' => 'Holz', 'kategorie_id' => 2],
            ['name' => 'Metall', 'kategorie_id' => 2],
            ['name' => 'Glas', 'kategorie_id' => 2],
            ['name' => 'Gemälde', 'kategorie_id' => 2],
            ['name' => 'Dekoration', 'kategorie_id' => 2],
            ['name' => 'Kalligraphie', 'kategorie_id' => 2],
            ['name' => 'Blaudruck', 'kategorie_id' => 2],
            ['name' => 'Korbwaren', 'kategorie_id' => 2],
            ['name' => 'Bürstenwaren', 'kategorie_id' => 2],
            ['name' => 'Seifen', 'kategorie_id' => 2],
            ['name' => 'Skulpturen', 'kategorie_id' => 2],
            ['name' => 'Taschen', 'kategorie_id' => 2],
            ['name' => 'Floristik', 'kategorie_id' => 2],
            
            // Töpfer (ID: 3)
            ['name' => 'Keramik', 'kategorie_id' => 3],
            ['name' => 'Porzellan', 'kategorie_id' => 3],
            
            // Kleidung (ID: 4)
            ['name' => 'Mode', 'kategorie_id' => 4],
            ['name' => 'Bekleidung', 'kategorie_id' => 4],
            ['name' => 'Textilien', 'kategorie_id' => 4],
            
            // Sonstiges (ID: 5)
            ['name' => 'Sonstiges', 'kategorie_id' => 5],
        ]);
    }
}
