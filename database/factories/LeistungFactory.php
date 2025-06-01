<?php

namespace Database\Factories;

use App\Models\Leistung;
use Illuminate\Database\Eloquent\Factories\Factory;

class LeistungFactory extends Factory
{
    protected $model = Leistung::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'bemerkung' => $this->faker->optional()->sentence(),
            'einheit' => $this->faker->randomElement(['m', 'stk', 'pauschal']),
            'preis' => $this->faker->numberBetween(1000, 20000),
        ];
    }
}
