<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Dealer;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Visit>
 */
class VisitFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $checkInAt = Carbon::now()->subHours(fake()->numberBetween(1, 6));

        return [
            'visit_plan_id' => null,
            'user_id' => User::factory(),
            'dealer_id' => Dealer::factory(),
            'check_in_at' => $checkInAt,
            'check_in_lat' => fake()->latitude(23.7, 23.85),
            'check_in_lng' => fake()->longitude(90.35, 90.45),
            'check_in_photo' => null,
            'check_out_at' => $checkInAt->copy()->addMinutes(fake()->numberBetween(15, 60)),
            'check_out_lat' => fake()->latitude(23.7, 23.85),
            'check_out_lng' => fake()->longitude(90.35, 90.45),
            'check_out_photo' => null,
            'is_gps_verified' => true,
            'distance_from_dealer_meters' => fake()->randomFloat(2, 5, 150),
            'feedback' => fake()->sentence(),
        ];
    }

    public function notCheckedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_out_at' => null,
            'check_out_lat' => null,
            'check_out_lng' => null,
            'feedback' => null,
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_gps_verified' => false,
            'distance_from_dealer_meters' => fake()->randomFloat(2, 500, 3000),
        ]);
    }
}
