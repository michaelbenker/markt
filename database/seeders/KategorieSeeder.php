<?php

namespace Database\Seeders;

use App\Models\Kategorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Kategorie::insert([
            ['name' => 'Gastro'],
            ['name' => 'Kunsthandwerk'],
            ['name' => 'Töpfer'],
            ['name' => 'Kleidung'],
            ['name' => 'Sonstiges'],
        ]);
    }
}
