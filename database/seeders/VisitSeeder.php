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
 * Backfills the last 7 weekdays of visit plans + the dealer visits that
 * fulfill most of them, for every Sales Executive. Seeds both tables
 * together (rather than two separate seeders) since a fulfilling Visit must
 * reference an already-created VisitPlan.
 */
class VisitSeeder extends Seeder
{
    private const NEARBY_JITTER_DEGREES = 0.001; // ~100m

    private const FAR_JITTER_DEGREES = 0.02; // ~2km, well outside the verification radius

    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();
        $dealersByTerritory = Dealer::all()->groupBy('territory_id');

        $day = Carbon::today();
        $days = [];
        while (count($days) < 7) {
            $day = $day->copy()->subDay();
            if (! $day->isWeekend()) {
                $days[] = $day->copy();
            }
        }

        foreach ($executives as $executive) {
            $pool = $dealersByTerritory->get($executive->territory_id) ?? collect();
            if ($pool->isEmpty()) {
                $pool = Dealer::inRandomOrder()->limit(5)->get();
            }

            foreach ($days as $date) {
                foreach ($pool->random(min(2, $pool->count())) as $dealer) {
                    $plan = VisitPlan::factory()->create([
                        'user_id' => $executive->id,
                        'dealer_id' => $dealer->id,
                        'planned_date' => $date->toDateString(),
                        'status' => VisitPlanStatus::Planned,
                    ]);

                    $roll = random_int(1, 100);

                    if ($roll <= 90) {
                        $this->createVisit($executive, $dealer, $date, $plan);
                        $plan->update(['status' => VisitPlanStatus::Completed]);
                    } elseif ($roll <= 95) {
                        $plan->update(['status' => VisitPlanStatus::Cancelled]);
                    }
                    // else: left "planned" on a past date -> displays as Missed.
                }

                // A couple of unplanned/ad-hoc visits too, not every module
                // is plan-first in the field.
                if ($pool->isNotEmpty() && random_int(1, 100) <= 30) {
                    $this->createVisit($executive, $pool->random(), $date, null);
                }
            }
        }
    }

    private function createVisit(User $executive, Dealer $dealer, Carbon $date, ?VisitPlan $plan): void
    {
        $checkInAt = $date->copy()->setTime(random_int(9, 15), random_int(0, 59));

        if ($dealer->hasGps()) {
            $verified = random_int(1, 100) <= 85;
            $jitter = $verified ? self::NEARBY_JITTER_DEGREES : self::FAR_JITTER_DEGREES;
            $lat = (float) $dealer->gps_lat + fake()->randomFloat(6, -$jitter, $jitter);
            $lng = (float) $dealer->gps_lng + fake()->randomFloat(6, -$jitter, $jitter);
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
            'visit_plan_id' => $plan?->id,
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
