<?php

namespace Database\Factories;

use App\Models\Preis;
use Illuminate\Database\Eloquent\Factories\Factory;

class PreisFactory extends Factory
{
    protected $model = Preis::class;

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
