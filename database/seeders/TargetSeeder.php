<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CalculateAchievementAction;
use App\Models\CollectionEntry;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Target;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Seeds exactly 5 targets across the 2 sample Sales Executives (3 months
 * for the first, 2 for the second) — run after OrderSeeder/
 * CollectionEntrySeeder so the current month's target gets a real,
 * non-zero achievement computed immediately.
 */
class TargetSeeder extends Seeder
{
    public function run(): void
    {
        $executives = User::role('Sales Executive')->orderBy('id')->get();

        if ($executives->isEmpty()) {
            return;
        }

        $action = app(CalculateAchievementAction::class);

        $periods = [
            Carbon::today(),
            Carbon::today()->subMonthNoOverflow(),
            Carbon::today()->subMonthsNoOverflow(2),
        ];

        $counts = [3, 2];

        $executives->each(function (User $executive, int $i) use ($periods, $counts, $action) {
            $count = $counts[$i] ?? 0;
            $currentPeriod = $periods[0];

            // The current month already has real Order/CollectionEntry rows
            // from OrderSeeder/CollectionEntrySeeder — base its target on
            // what was actually generated (±a random factor) so the
            // achievement lands in a believable range instead of a fixed
            // guess that could be wildly over- or under-shot by however
            // large those randomly generated orders happened to be. Past
            // months have no seeded activity, so a modest fixed target
            // there just means a legitimate 0% achievement.
            $orderThisMonth = (float) Order::where('user_id', $executive->id)
                ->whereYear('order_date', $currentPeriod->year)
                ->whereMonth('order_date', $currentPeriod->month)
                ->sum('total_amount');

            $collectionThisMonth = (float) CollectionEntry::where('user_id', $executive->id)
                ->whereYear('collection_date', $currentPeriod->year)
                ->whereMonth('collection_date', $currentPeriod->month)
                ->sum('amount');

            $quantityThisMonth = (int) OrderItem::whereHas('order', fn ($query) => $query
                ->where('user_id', $executive->id)
                ->whereYear('order_date', $currentPeriod->year)
                ->whereMonth('order_date', $currentPeriod->month)
            )->sum('quantity');

            foreach (array_slice($periods, 0, $count) as $index => $period) {
                $isCurrentMonth = $index === 0;
                $factor = fake()->randomFloat(2, 0.7, 1.1);

                $target = Target::create([
                    'user_id' => $executive->id,
                    'month' => $period->month,
                    'year' => $period->year,
                    'order_value_target' => $isCurrentMonth && $orderThisMonth > 0
                        ? round($orderThisMonth * $factor, 2)
                        : fake()->randomFloat(2, 50000, 200000),
                    'collection_target' => $isCurrentMonth && $collectionThisMonth > 0
                        ? round($collectionThisMonth * $factor, 2)
                        : fake()->randomFloat(2, 50000, 200000),
                    'quantity_target' => $isCurrentMonth && $quantityThisMonth > 0
                        ? (int) round($quantityThisMonth * $factor)
                        : fake()->numberBetween(20, 100),
                ]);

                $action($target);
            }
        });
    }
}
