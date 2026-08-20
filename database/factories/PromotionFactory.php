<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Promotion;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Promotion>
 */
class PromotionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::instance(fake()->dateTimeBetween('-10 days', '+5 days'));

        return [
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'image' => null,
            'starts_at' => $startsAt->toDateString(),
            'ends_at' => $startsAt->copy()->addDays(30)->toDateString(),
            'is_active' => true,
        ];
    }
}
