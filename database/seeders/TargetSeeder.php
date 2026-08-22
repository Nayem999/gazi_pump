<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CalculateAchievementAction;
use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Assigns a current-month and previous-month target to every Sales
 * Executive — OrderSeeder/CollectionEntrySeeder backfill the last 30
 * days, which typically spans both calendar months, so both targets get a
 * real, non-zero achievement computed immediately (no queue worker needed
 * for the demo data to look right after a fresh seed).
 */
class TargetSeeder extends Seeder
{
    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();
        $action = app(CalculateAchievementAction::class);

        $currentMonth = Carbon::today();
        $previousMonth = $currentMonth->copy()->subMonthNoOverflow();

        foreach ($executives as $executive) {
            foreach ([$previousMonth, $currentMonth] as $period) {
                $target = Target::create([
                    'user_id' => $executive->id,
                    'month' => $period->month,
                    'year' => $period->year,
                    // Scaled to this app's product price range (up to
                    // 50,000/unit) so achieved-vs-target lands in a
                    // believable spread of grades rather than the low
                    // thousands-percent overachievement a small target
                    // would produce against real seeded sales volume.
                    'order_value_target' => fake()->randomFloat(2, 10000000, 40000000),
                    'collection_target' => fake()->randomFloat(2, 10000000, 40000000),
                    'quantity_target' => fake()->numberBetween(500, 2000),
                ]);

                $action($target);
            }
        }
    }
}
