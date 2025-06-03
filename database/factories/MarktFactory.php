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
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'bemerkung' => $this->faker->paragraph
        ];
    }
}
