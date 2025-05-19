<?php

namespace Database\Factories;

use App\Models\Aussteller;
use Illuminate\Database\Eloquent\Factories\Factory;

class AusstellerFactory extends Factory
{
    protected $model = Aussteller::class;

    public function definition(): array
    {
        return [
            'firma' => $this->faker->company,
            'anrede' => $this->faker->randomElement(['Herr', 'Frau', 'Divers']),
            'vorname' => $this->faker->firstName,
            'name' => $this->faker->lastName,
            'strasse' => $this->faker->streetName,
            'hausnummer' => $this->faker->buildingNumber,
            'plz' => $this->faker->postcode,
            'ort' => $this->faker->city,
            'land' => $this->faker->randomElement(['Deutschland', 'Österreich', 'Schweiz', 'Italien', 'Frankreich', 'Niederlande']),
            'telefon' => $this->faker->phoneNumber,
            'mobil' => $this->faker->phoneNumber,
            'homepage' => $this->faker->url,
            'email' => $this->faker->unique()->safeEmail,
            'briefanrede' => $this->faker->title,
            'bemerkung' => $this->faker->paragraph,
        ];
    }
}
