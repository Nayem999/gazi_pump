<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CollectionEntry;
use App\Models\Customer;
use App\Models\SalesEntry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Backfills the last 30 days of collections against the outstanding balance
 * each customer already owes from SalesEntrySeeder — run after it for that
 * reason. Balances are tracked in memory and decremented as collections are
 * generated so no customer is ever seeded into overpayment.
 */
class CollectionEntrySeeder extends Seeder
{
    private const DAYS = 30;

    public function run(): void
    {
        $executives = User::role('Sales Executive')->get();
        $customersByTerritory = Customer::all()->groupBy('territory_id');

        $outstanding = SalesEntry::query()
            ->selectRaw('customer_id, SUM(total_amount) as total')
            ->groupBy('customer_id')
            ->pluck('total', 'customer_id')
            ->map(fn ($total) => (float) $total)
            ->all();

        foreach ($executives as $executive) {
            $pool = $customersByTerritory->get($executive->territory_id) ?? collect();
            if ($pool->isEmpty()) {
                $pool = Customer::inRandomOrder()->limit(5)->get();
            }

            if ($pool->isEmpty()) {
                continue;
            }

            for ($daysAgo = self::DAYS - 1; $daysAgo >= 0; $daysAgo--) {
                $date = Carbon::today()->subDays($daysAgo);
                $collectionsToday = random_int(0, 2);

                for ($i = 0; $i < $collectionsToday; $i++) {
                    $customer = $pool->random();
                    $balance = $outstanding[$customer->id] ?? 0.0;

                    // A balance below this is too small to make a realistic
                    // collection out of — round($balance * fraction, 2) could
                    // otherwise land on 0.00 and seed a meaningless row.
                    if ($balance < 1.0) {
                        continue;
                    }

                    $amount = round($balance * (random_int(50, 100) / 100), 2);
                    $outstanding[$customer->id] = $balance - $amount;

                    CollectionEntry::factory()->create([
                        'user_id' => $executive->id,
                        'customer_id' => $customer->id,
                        'collection_date' => $date->toDateString(),
                        'amount' => $amount,
                    ]);
                }
            }
        }
    }
}
