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
 * Seeds exactly 5 collections (one per sample dealer, run after OrderSeeder)
 * against the outstanding balance each dealer owes, spread across the 2
 * sample Sales Executives. Never collects more than what's outstanding.
 */
class CollectionEntrySeeder extends Seeder
{
    public function run(): void
    {
        $executives = User::role('Sales Executive')->orderBy('id')->get();
        $dealers = Dealer::orderBy('id')->get();

        if ($executives->isEmpty() || $dealers->isEmpty()) {
            return;
        }

        $outstanding = Order::query()
            ->selectRaw('dealer_id, SUM(total_amount) as total')
            ->groupBy('dealer_id')
            ->pluck('total', 'dealer_id')
            ->map(fn ($total) => (float) $total)
            ->all();

        foreach ($dealers as $i => $dealer) {
            $executive = $executives->get($i % $executives->count());
            $balance = $outstanding[$dealer->id] ?? 0.0;

            if ($balance < 1.0) {
                continue;
            }

            $amount = round($balance * (random_int(50, 100) / 100), 2);

            CollectionEntry::factory()->create([
                'user_id' => $executive->id,
                'dealer_id' => $dealer->id,
                'collection_date' => Carbon::today()->subDays($i)->toDateString(),
                'amount' => $amount,
            ]);
        }
    }
}
