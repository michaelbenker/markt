<?php

namespace Database\Seeders;

use App\Models\Termin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TerminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $termine = [
            [
                'markt_id' => 1,
                'start' => '2025-12-06',
                'ende' => '2025-12-07',
            ],
            [
                'markt_id' => 1,
                'start' => '2025-12-13',
                'ende' => '2025-12-14',
            ],
        ];

        foreach ($termine as $termin) {
            Termin::create($termin);
        }
    }
}
