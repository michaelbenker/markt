<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'mb@sistecs.de'],
            [
                'name' => 'Michael Benker',
                'password' => Hash::make('1Pdimnmk!'),
            ]
        );
        User::updateOrCreate(
            ['email' => 'michaela.landmann@fuerstenfeld.de'],
            [
                'name' => 'Michaela Landmann',
                'password' => Hash::make('test1234'),
            ]
        );

        $this->call([
            MarktSeeder::class,
            AusstellerImportSeeder::class,
            StandortSeeder::class,
        ]);
    }
}
