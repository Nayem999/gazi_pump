<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\GpsLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds today-only GPS pings for the sample Sales Executives: a simulated
 * field route every 20 minutes covering the last 8 hours up through right
 * now (clamped to the start of today), ending with a ping timestamped at
 * the exact current moment. That guarantees every row is today and never
 * in the future — regardless of what time of day this seeder actually
 * runs — so the GPS Tracking history view (defaults to today) has a real
 * trail and the Live GPS Dashboard always shows both as "Online".
 */
class GpsLogSeeder extends Seeder
{
    private const PING_INTERVAL_MINUTES = 20;

    private const ROUTE_HOURS = 8;

    public function run(): void
    {
        $executives = User::role('Sales Executive')->orderBy('id')->get();

        if ($executives->isEmpty()) {
            return;
        }

        foreach ($executives as $executive) {
            $this->seedTodaysRoute($executive);
        }
    }

    private function seedTodaysRoute(User $executive): void
    {
        $now = Carbon::now();
        $start = $now->copy()->subHours(self::ROUTE_HOURS);
        if ($start->lessThan(Carbon::today())) {
            $start = Carbon::today();
        }

        $lat = (float) fake()->latitude(23.7, 23.85);
        $lng = (float) fake()->longitude(90.35, 90.45);

        $time = $start->copy();
        while ($time->lessThan($now)) {
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

        GpsLog::factory()->create([
            'user_id' => $executive->id,
            'lat' => $lat + fake()->randomFloat(6, -0.004, 0.004),
            'lng' => $lng + fake()->randomFloat(6, -0.004, 0.004),
            'recorded_at' => $now->copy(),
            'created_by' => $executive->id,
        ]);
    }
}
