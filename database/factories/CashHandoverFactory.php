<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CashHandoverStatus;
use App\Models\CashHandover;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CashHandover>
 */
class CashHandoverFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'amount' => fake()->randomFloat(2, 500, 20000),
            'handover_date' => now()->toDateString(),
            'status' => CashHandoverStatus::Pending->value,
            'remarks' => null,
        ];
    }
}
