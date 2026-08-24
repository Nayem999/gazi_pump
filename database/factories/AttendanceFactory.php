<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $date = Carbon::parse(fake()->dateTimeBetween('-30 days', 'now'))->startOfDay();
        $checkInAt = $date->copy()->setTime(9, fake()->numberBetween(0, 5));

        return [
            'user_id' => User::factory(),
            'date' => $date->toDateString(),
            'check_in_at' => $checkInAt,
            'check_in_lat' => fake()->latitude(23.6, 23.9),
            'check_in_lng' => fake()->longitude(90.3, 90.5),
            'check_in_photo' => null,
            'check_out_at' => $checkInAt->copy()->addHours(8),
            'check_out_lat' => fake()->latitude(23.6, 23.9),
            'check_out_lng' => fake()->longitude(90.3, 90.5),
            'check_out_photo' => null,
            'status' => AttendanceStatus::Present,
            'late_minutes' => 0,
            'remarks' => null,
        ];
    }

    public function late(int $lateMinutes = 30): static
    {
        return $this->state(function (array $attributes) use ($lateMinutes) {
            $checkInAt = Carbon::parse($attributes['check_in_at'])->addMinutes($lateMinutes);

            return [
                'check_in_at' => $checkInAt,
                'check_out_at' => $checkInAt->copy()->addHours(8),
                'status' => AttendanceStatus::Late,
                'late_minutes' => $lateMinutes,
            ];
        });
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_at' => null,
            'check_in_lat' => null,
            'check_in_lng' => null,
            'check_out_at' => null,
            'check_out_lat' => null,
            'check_out_lng' => null,
            'status' => AttendanceStatus::Absent,
            'late_minutes' => 0,
        ]);
    }

    public function halfDay(): static
    {
        return $this->state(function (array $attributes) {
            $checkInAt = Carbon::parse($attributes['check_in_at']);

            return [
                'check_out_at' => $checkInAt->copy()->addHours(4),
                'status' => AttendanceStatus::HalfDay,
            ];
        });
    }

    public function notCheckedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_out_at' => null,
            'check_out_lat' => null,
            'check_out_lng' => null,
        ]);
    }
}
