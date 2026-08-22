<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Dealer;
use App\Models\Order;
use App\Models\User;
use App\Services\CollectionEntryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CollectionEntryServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): CollectionEntryService
    {
        return app(CollectionEntryService::class);
    }

    public function test_outstanding_balance_is_total_sold_minus_total_collected(): void
    {
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->assertSame(1000.0, $this->service()->outstandingBalance($dealer->id));
    }

    public function test_a_dealer_with_no_sales_has_zero_outstanding_balance(): void
    {
        $dealer = Dealer::factory()->create();

        $this->assertSame(0.0, $this->service()->outstandingBalance($dealer->id));
    }

    public function test_a_collection_within_the_outstanding_balance_is_allowed(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $entry = $this->service()->create([
            'user_id' => User::factory()->create()->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 1000,
            'payment_method' => 'cash',
        ]);

        $this->assertSame('1000.00', (string) $entry->amount);
    }

    public function test_a_collection_within_the_overpayment_tolerance_is_allowed(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $entry = $this->service()->create([
            'user_id' => User::factory()->create()->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 1100,
            'payment_method' => 'cash',
        ]);

        $this->assertSame('1100.00', (string) $entry->amount);
    }

    public function test_a_collection_beyond_the_overpayment_tolerance_is_rejected(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $this->expectException(ValidationException::class);

        $this->service()->create([
            'user_id' => User::factory()->create()->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 1101,
            'payment_method' => 'cash',
        ]);
    }

    public function test_a_collection_against_a_dealer_with_no_outstanding_balance_is_rejected(): void
    {
        $dealer = Dealer::factory()->create();

        $this->expectException(ValidationException::class);

        $this->service()->create([
            'user_id' => User::factory()->create()->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 100,
            'payment_method' => 'cash',
        ]);
    }

    public function test_updating_an_entry_excludes_its_own_amount_from_the_outstanding_balance_calculation(): void
    {
        config(['sfa.collections.overpayment_tolerance_percent' => 10]);
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'total_amount' => 1000]);

        $entry = $this->service()->create([
            'user_id' => User::factory()->create()->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 600,
            'payment_method' => 'cash',
        ]);

        // Balance is now 400 (1000 - 600). Updating this same entry to 900
        // should be allowed because it excludes its own prior 600 first —
        // 1000 - 0 (itself excluded) = 1000 outstanding, well within range.
        $updated = $this->service()->update($entry, [
            'user_id' => $entry->user_id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->toDateString(),
            'amount' => 900,
            'payment_method' => 'cash',
        ]);

        $this->assertSame('900.00', (string) $updated->amount);
    }
}
