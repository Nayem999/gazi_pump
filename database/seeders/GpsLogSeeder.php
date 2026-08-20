<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GpsLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Backfills the last 7 weekdays of GPS pings for every Sales Executive, one
 * simulated field route per day (a starting point plus ~20 small random
 * moves every 20 minutes through the work day), so the GPS Tracking history
 * view and distance report have realistic trails right after a fresh seed.
 */
class GpsLogSeeder extends Seeder
{
    private const PINGS_PER_DAY = 20;

    private const PING_INTERVAL_MINUTES = 20;

    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();

        $day = Carbon::today();
        $days = [];
        while (count($days) < 7) {
            $day = $day->copy()->subDay();
            if (! $day->isWeekend()) {
                $days[] = $day->copy();
            }
        }

        foreach ($executives as $executive) {
            foreach ($days as $date) {
                $lat = (float) fake()->latitude(23.7, 23.85);
                $lng = (float) fake()->longitude(90.35, 90.45);
                $time = $date->copy()->setTime(9, 0);

                for ($i = 0; $i < self::PINGS_PER_DAY; $i++) {
                    $lat += fake()->randomFloat(6, -0.004, 0.004);
                    $lng += fake()->randomFloat(6, -0.004, 0.004);

                    GpsLog::factory()->create([
                        'user_id' => $executive->id,
                        'lat' => $lat,
                        'lng' => $lng,
                        'recorded_at' => $time->copy(),
                        'created_by' => $executive->id,
                    ]);

                    $time->addMinutes(self::PING_INTERVAL_MINUTES);
                }
            }
        }

        $this->seedRecentPingsForLiveDashboard($executives);
    }

    /**
     * The 7-day backfill above is all historical, so the Live GPS Dashboard
     * (Module 15) would show every executive as "last known" forever on a
     * fresh seed. Gives a handful of them a ping from the last few minutes
     * (online) and a few more from just past the staleness window (last
     * known but recent), so the dashboard has a realistic mix to show.
     */
    /**
     * @param  Collection<int, User>  $executives
     */
    private function seedRecentPingsForLiveDashboard(Collection $executives): void
    {
        $sample = $executives->random(min(10, $executives->count()));

        foreach ($sample->take(6) as $executive) {
            GpsLog::factory()->create([
                'user_id' => $executive->id,
                'lat' => fake()->latitude(23.7, 23.85),
                'lng' => fake()->longitude(90.35, 90.45),
                'recorded_at' => Carbon::now()->subMinutes(random_int(0, 15)),
                'created_by' => $executive->id,
            ]);
        }

        foreach ($sample->skip(6) as $executive) {
            GpsLog::factory()->create([
                'user_id' => $executive->id,
                'lat' => fake()->latitude(23.7, 23.85),
                'lng' => fake()->longitude(90.35, 90.45),
                'recorded_at' => Carbon::now()->subMinutes(random_int(45, 90)),
                'created_by' => $executive->id,
            ]);
        }
    }
}
