<?php

namespace Database\Factories;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
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

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'designation' => fake()->jobTitle(),
            'role' => fake()->randomElement(array_filter(UserRole::cases(), fn ($role) => $role !== UserRole::ROOT)),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'verified_at' => $verified = fake()->boolean(60) ? now() : null,
            'approved_at' => $approved = $verified ? (fake()->boolean(60) ? $verified : null) : null,
            'deactivated_at' => $approved ? (fake()->boolean(15) ? $verified : null) : null,
        ];
    }

    /**
     * Default root user account.
     */
    public function root(): static
    {
        return $this->state(fn () => [
            'name' => 'Root',
            'email' => 'root@local.dev',
            'role' => UserRole::ROOT,
            'verified_at' => 1,
            'approved_at' => 1,
            'password' => '$2y$12$.jM7SD37qQAvDhmCHz414uToHIWwl9129xyMTgbDXlT8/KvKfXxU.',
            'remember_token' => null,
            'deactivated_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::ADMIN,
            'verified_at' => 1,
            'approved_at' => 1,
            'deactivated_at' => null,
        ]);
    }

    public function moderator(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::MODERATOR,
            'verified_at' => 1,
            'approved_at' => 1,
            'deactivated_at' => null,
        ]);
    }

    public function user(): static
    {
        return $this->state(fn () => [
            'role' => UserRole::USER,
            'verified_at' => 1,
            'approved_at' => 1,
            'deactivated_at' => null,
        ]);
    }
}
