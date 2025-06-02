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
            'land' => $this->faker->randomElement(['Deutschland', 'Österreich', 'Schweiz']),
            'telefon' => $this->faker->phoneNumber,
            'mobil' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'homepage' => $this->faker->url,
            'bemerkung' => $this->faker->optional()->paragraph,
        ];
    }
}
