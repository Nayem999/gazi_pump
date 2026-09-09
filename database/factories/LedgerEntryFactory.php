<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Dealer;
use App\Models\LedgerEntry;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<LedgerEntry>
 */
class LedgerEntryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dealer_id' => Dealer::factory(),
            'tally_guid' => 'VOUCHER-GUID-'.fake()->unique()->numerify('######'),
            'voucher_date' => Carbon::today()->toDateString(),
            'voucher_type' => 'Sales',
            'voucher_number' => fake()->numerify('SV-###'),
            'debit_amount' => 0,
            'credit_amount' => 0,
            'synced_at' => now(),
        ];
    }
}
