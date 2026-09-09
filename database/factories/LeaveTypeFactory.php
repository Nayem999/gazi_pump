<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement(['Casual', 'Sick', 'Annual', 'Unpaid', 'Maternity', 'Bereavement']);

        return [
            'name' => $name.' Leave',
            'code' => strtoupper(substr($name, 0, 3)).'-'.fake()->unique()->numberBetween(100, 999),
            'description' => fake()->sentence(),
            'annual_quota' => fake()->numberBetween(5, 20),
            'is_paid' => true,
            'status' => true,
        ];
    }

    public function unpaid(): static
    {
        return $this->state(fn () => ['is_paid' => false, 'annual_quota' => 0]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => false]);
    }
}
