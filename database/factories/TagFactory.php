<?php

namespace Database\Factories;

use Filament\Support\Colors\Color;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $colors = array_keys(Color::all());

        return [
            'name' => fake()->unique()->word(),
            'color' => fake()->randomElement($colors),
        ];
    }
}
