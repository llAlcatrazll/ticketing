<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Organization>
 */
class OrganizationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $level = fake()->randomElement([
            'Organization',
            'Department',
            'Division',
            'Office',
            'Ministry',
            'Bureau',
            'Agency',
            'Program',
        ]);

        $name = mb_ucfirst(fake()->unique()->word());

        return [
            'name' => "{$level} of {$name}",
            'code' => (substr($level, 0, 3)).'o'.(substr($name, 0, 3)),
        ];
    }
}
