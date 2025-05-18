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
            ['name' => 'Speisen', 'kategorie_id' => 1],
            ['name' => 'Getränke', 'kategorie_id' => 1],
            ['name' => 'Schmuck', 'kategorie_id' => 2],
            ['name' => 'Holz', 'kategorie_id' => 2],
            ['name' => 'Mode', 'kategorie_id' => 4],
            ['name' => 'Sonstiges', 'kategorie_id' => 5],
        ]);
    }
}
