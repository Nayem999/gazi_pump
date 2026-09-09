<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\LeaveStatus;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        // Anchored to a Monday so the default range never lands on a
        // weekend, which would make `days` disagree with the dates in any
        // test that did not think about it.
        $from = Carbon::now()->next(Carbon::MONDAY);
        $to = $from->copy()->addDay();

        return [
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'days' => 2,
            'is_half_day' => false,
            'reason' => fake()->sentence(),
            'status' => LeaveStatus::Pending->value,
        ];
    }

    public function approved(?User $approver = null): static
    {
        return $this->state(fn () => [
            'status' => LeaveStatus::Approved->value,
            'approved_by' => $approver?->id ?? User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn () => [
            'status' => LeaveStatus::Rejected->value,
            'approved_at' => now(),
            'decision_remarks' => 'Not enough cover that week.',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => LeaveStatus::Cancelled->value]);
    }

    /** A single working day, for balance and attendance arithmetic. */
    public function onDate(string|Carbon $date): static
    {
        $date = $date instanceof Carbon ? $date : Carbon::parse($date);

        return $this->state(fn () => [
            'from_date' => $date->toDateString(),
            'to_date' => $date->toDateString(),
            'days' => 1,
        ]);
    }
}
