<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Backfills the last 14 weekdays of attendance for every Sales Executive, so
 * the Attendance list/report/dashboard have realistic data to show right
 * after a fresh seed. Mix of on-time, late, and absent days.
 */
class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();

        $day = Carbon::today();
        $days = [];
        while (count($days) < 14) {
            $day = $day->copy()->subDay();
            if (! $day->isWeekend()) {
                $days[] = $day->copy();
            }
        }

        foreach ($executives as $executive) {
            foreach ($days as $date) {
                $roll = random_int(1, 100);

                $state = match (true) {
                    $roll <= 5 => 'absent',
                    $roll <= 20 => 'late',
                    default => 'present',
                };

                $factory = Attendance::factory()->state([
                    'user_id' => $executive->id,
                    'date' => $date->toDateString(),
                    'check_in_at' => $date->copy()->setTime(9, 0),
                    'check_out_at' => $date->copy()->setTime(17, 0),
                ]);

                match ($state) {
                    'absent' => $factory->absent()->create(),
                    'late' => $factory->late(random_int(16, 60))->create(),
                    default => $factory->create(),
                };
            }
        }
    }
}
