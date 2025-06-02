<?php

namespace Database\Factories;

use App\Models\Markt;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MarktFactory extends Factory
{
    protected $model = Markt::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $start = $this->faker->dateTimeBetween('now', '+1 year');
        $ende = (clone $start)->modify('+' . rand(1, 3) . ' days');
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'beschreibung' => $this->faker->paragraph,
            'start' => $start,
            'ende' => $ende,
            'ort' => $this->faker->city,
            'strasse' => $this->faker->streetName,
            'hausnummer' => $this->faker->buildingNumber,
            'plz' => $this->faker->postcode,
            'land' => 'Deutschland',
            'status' => $this->faker->randomElement(['aktiv', 'inaktiv', 'archiviert']),
        ];
    }
}
