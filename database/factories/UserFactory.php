<?php

namespace Database\Factories;

use App\Models\Territory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
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
            'employee_id' => 'EMP-'.fake()->unique()->numerify('#####'),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->numerify('01#########'),
            'photo' => null,
            'designation' => 'Sales Executive',
            'date_of_birth' => fake()->dateTimeBetween('-55 years', '-22 years')->format('Y-m-d'),
            'manager_id' => null,
            'status' => true,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function designation(string $designation): static
    {
        return $this->state(fn (array $attributes) => [
            'designation' => $designation,
        ]);
    }

    public function manager(?int $managerId): static
    {
        return $this->state(fn (array $attributes) => [
            'manager_id' => $managerId,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function inTerritory(Territory|int $territory): static
    {
        return $this->afterCreating(function (User $user) use ($territory) {
            $user->territories()->attach($territory instanceof Territory ? $territory->id : $territory);
        });
    }
}
