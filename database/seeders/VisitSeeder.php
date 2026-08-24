<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VisitPlanStatus;
use App\Helpers\DistanceCalculator;
use App\Models\Dealer;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds exactly 5 visit plans (one per sample dealer) and the 5 dealer
 * visits that fulfill them, spread across the 2 sample Sales Executives.
 * Seeds both tables together since a fulfilling Visit must reference an
 * already-created VisitPlan.
 */
class VisitSeeder extends Seeder
{
    private const NEARBY_JITTER_DEGREES = 0.001; // ~100m

    public function run(): void
    {
        $executives = User::role('Sales Executive')->orderBy('id')->get();
        $dealers = Dealer::orderBy('id')->get();

        if ($executives->isEmpty() || $dealers->isEmpty()) {
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

        foreach ($dealers as $i => $dealer) {
            $executive = $executives->get($i % $executives->count());
            $date = $days[$i];

            $plan = VisitPlan::create([
                'user_id' => $executive->id,
                'dealer_id' => $dealer->id,
                'territory_id' => $dealer->territory_id,
                'planned_date' => $date->toDateString(),
                'status' => VisitPlanStatus::Completed,
            ]);

            $this->createVisit($executive, $dealer, $date, $plan);
        }
    }

    private function createVisit(User $executive, Dealer $dealer, Carbon $date, VisitPlan $plan): void
    {
        $checkInAt = $date->copy()->setTime(random_int(9, 15), random_int(0, 59));

        if ($dealer->hasGps()) {
            $lat = (float) $dealer->gps_lat + fake()->randomFloat(6, -self::NEARBY_JITTER_DEGREES, self::NEARBY_JITTER_DEGREES);
            $lng = (float) $dealer->gps_lng + fake()->randomFloat(6, -self::NEARBY_JITTER_DEGREES, self::NEARBY_JITTER_DEGREES);
            $verified = true;
            $distanceMeters = round(DistanceCalculator::haversineKm(
                (float) $dealer->gps_lat,
                (float) $dealer->gps_lng,
                $lat,
                $lng
            ) * 1000, 2);
        } else {
            $verified = null;
            $lat = (float) fake()->latitude(23.7, 23.85);
            $lng = (float) fake()->longitude(90.35, 90.45);
            $distanceMeters = null;
        }

        Visit::factory()->create([
            'visit_plan_id' => $plan->id,
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'check_in_at' => $checkInAt,
            'check_in_lat' => $lat,
            'check_in_lng' => $lng,
            'check_out_at' => $checkInAt->copy()->addMinutes(random_int(15, 60)),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
            'is_gps_verified' => $verified,
            'distance_from_dealer_meters' => $distanceMeters,
        ]);
    }
}
