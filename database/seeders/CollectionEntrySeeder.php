<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Backfills the last 30 days of collections against the outstanding balance
 * each dealer already owes from OrderSeeder — run after it for that
 * reason. Balances are tracked in memory and decremented as collections are
 * generated so no dealer is ever seeded into overpayment.
 */
class CollectionEntrySeeder extends Seeder
{
    private const DAYS = 30;

    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();
        $dealersByTerritory = Dealer::all()->groupBy('territory_id');

        $outstanding = Order::query()
            ->selectRaw('dealer_id, SUM(total_amount) as total')
            ->groupBy('dealer_id')
            ->pluck('total', 'dealer_id')
            ->map(fn ($total) => (float) $total)
            ->all();

        foreach ($executives as $executive) {
            $pool = $dealersByTerritory->get($executive->territory_id) ?? collect();
            if ($pool->isEmpty()) {
                $pool = Dealer::inRandomOrder()->limit(5)->get();
            }

            if ($pool->isEmpty()) {
                continue;
            }

            for ($daysAgo = self::DAYS - 1; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);
                $collectionsToday = random_int(0, 2);

                for ($i = 0; $i < $collectionsToday; $i++) {
                    $dealer = $pool->random();
                    $balance = $outstanding[$dealer->id] ?? 0.0;

                    // A balance below this is too small to make a realistic
                    // collection out of — round($balance * fraction, 2) could
                    // otherwise land on 0.00 and seed a meaningless row.
                    if ($balance < 1.0) {
                        continue;
                    }

                    $amount = round($balance * (random_int(50, 100) / 100), 2);
                    $outstanding[$dealer->id] = $balance - $amount;

                    CollectionEntry::factory()->create([
                        'user_id' => $executive->id,
                        'dealer_id' => $dealer->id,
                        'collection_date' => $date->toDateString(),
                        'amount' => $amount,
                    ]);
                }
            }
        }
    }
}
