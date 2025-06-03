<?php

namespace Database\Factories;

use App\Models\Anfrage;
use App\Models\Markt;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnfrageFactory extends Factory
{
    protected $model = Anfrage::class;

    public function definition(): array
    {
        return [
            'markt_id' => Markt::factory(),
            'firma' => $this->faker->company,
            'anrede' => $this->faker->randomElement(['Herr', 'Frau', 'Divers']),
            'vorname' => $this->faker->firstName,
            'nachname' => $this->faker->lastName,
            'strasse' => $this->faker->streetName,
            'hausnummer' => $this->faker->buildingNumber,
            'plz' => $this->faker->postcode,
            'ort' => $this->faker->city,
            'land' => $this->faker->randomElement(['Deutschland', 'Österreich', 'Schweiz']),
            'telefon' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail,
            'stand' => [
                'art' => $this->faker->randomElement(['klein', 'mittel', 'groß']),
                'laenge' => $this->faker->numberBetween(1, 10),
                'flaeche' => $this->faker->numberBetween(2, 30),
            ],
            'warenangebot' => $this->faker->randomElements([
                'kleidung',
                'schmuck',
                'kunst',
                'accessoires',
                'dekoration',
                'lebensmittel',
                'getraenke',
                'handwerk',
                'antiquitäten',
                'sonstiges'
            ], $this->faker->numberBetween(1, 3)),
            'herkunft' => [
                'eigenfertigung' => $this->faker->numberBetween(0, 100),
                'industrieware_nicht_entwicklungslaender' => $this->faker->numberBetween(0, 100),
                'industrieware_entwicklungslaender' => $this->faker->numberBetween(0, 100),
            ],
            'bereits_ausgestellt' => $this->faker->boolean,
            'importiert' => false,
            'bemerkung' => $this->faker->optional()->sentence,
        ];
    }
}
