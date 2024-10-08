<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $roles = ['admin', 'user', 'support', 'officer'];
        $positions = ['PGO-Executivew', 'SP-Legislation', 'SP-Secretariat', 'PGO-Administrative', 'Executive', 'Administrator', 'Chairman'];

        return [
            'name' => $this->faker->unique()->name,
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt(value: 'password'),
            'role' => $this->faker->randomElement($roles),
            'number' => $this->faker->phoneNumber(),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'position' => $this->faker->randomElement($positions),
        ];
    }
}
