<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds exactly 5 attendance rows across the 2 sample Sales Executives
 * (see SalesExecutiveSampleSeeder), one on the most recent 5 weekdays, one
 * of each AttendanceStatus so the list page shows every status at a glance.
 */
class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $executives = User::role('Sales Executive')->orderBy('id')->get();

        if ($executives->isEmpty()) {
            return;
        }

        $day = Carbon::today();
        $days = [];
        while (count($days) < 5) {
            $day = $day->copy()->subDay();
            if (! $day->isWeekend()) {
                $days[] = $day->copy();
            }
        }

        $states = ['present', 'late', 'half_day', 'absent', 'present'];

        foreach ($days as $i => $date) {
            $executive = $executives->get($i % $executives->count());

            $factory = Attendance::factory()->state([
                'user_id' => $executive->id,
                'date' => $date->toDateString(),
                'check_in_at' => $date->copy()->setTime(9, 0),
                'check_out_at' => $date->copy()->setTime(17, 0),
            ]);

            match ($states[$i]) {
                'absent' => $factory->absent()->create(),
                'late' => $factory->late(30)->create(),
                'half_day' => $factory->halfDay()->create(),
                default => $factory->create(),
            };
        }
    }
}
