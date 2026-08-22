<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Dealer;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): OrderService
    {
        return app(OrderService::class);
    }

    private function header(): array
    {
        return [
            'user_id' => User::factory()->create()->id,
            'dealer_id' => Dealer::factory()->create()->id,
            'order_date' => now()->toDateString(),
        ];
    }

    public function test_a_single_item_total_is_quantity_times_price_minus_discount(): void
    {
        $product = Product::factory()->create();

        $entry = $this->service()->create([
            ...$this->header(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 50],
            ],
        ]);

        $this->assertSame('950.00', (string) $entry->items->first()->total_amount);
        $this->assertSame('950.00', (string) $entry->total_amount);
    }

    public function test_multiple_items_sum_into_the_header_total(): void
    {
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $entry = $this->service()->create([
            ...$this->header(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0],
                ['product_id' => $productB->id, 'quantity' => 2, 'unit_price' => 200, 'discount_amount' => 0],
            ],
        ]);

        $this->assertCount(2, $entry->items);
        $this->assertSame('900.00', (string) $entry->total_amount);
    }

    public function test_a_discount_within_the_configured_cap_is_allowed(): void
    {
        config(['sfa.orders.max_discount_percent' => 20]);
        $product = Product::factory()->create();

        // 20% of a 1000 subtotal is 200 — exactly at the cap.
        $entry = $this->service()->create([
            ...$this->header(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 200],
            ],
        ]);

        $this->assertSame('800.00', (string) $entry->total_amount);
    }

    public function test_a_discount_beyond_the_configured_cap_is_rejected(): void
    {
        config(['sfa.orders.max_discount_percent' => 20]);
        $product = Product::factory()->create();

        $this->expectException(ValidationException::class);

        // 20% of a 1000 subtotal is 200 — 201 exceeds it.
        $this->service()->create([
            ...$this->header(),
            'items' => [
                ['product_id' => $product->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 201],
            ],
        ]);
    }

    public function test_a_valid_line_before_an_invalid_one_is_not_persisted(): void
    {
        config(['sfa.orders.max_discount_percent' => 20]);
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        try {
            $this->service()->create([
                ...$this->header(),
                'items' => [
                    ['product_id' => $productA->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 0],
                    ['product_id' => $productB->id, 'quantity' => 10, 'unit_price' => 100, 'discount_amount' => 201],
                ],
            ]);
            $this->fail('Expected a ValidationException.');
        } catch (ValidationException) {
            // The whole order — including its already-valid first line — is
            // rolled back rather than left half-created.
            $this->assertDatabaseCount('orders', 0);
            $this->assertDatabaseCount('order_items', 0);
        }
    }

    public function test_updating_an_entry_replaces_its_line_items(): void
    {
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();

        $entry = $this->service()->create([
            ...$this->header(),
            'items' => [
                ['product_id' => $productA->id, 'quantity' => 1, 'unit_price' => 100, 'discount_amount' => 0],
            ],
        ]);

        $updated = $this->service()->update($entry, [
            'user_id' => $entry->user_id,
            'dealer_id' => $entry->dealer_id,
            'order_date' => $entry->order_date->toDateString(),
            'items' => [
                ['product_id' => $productB->id, 'quantity' => 3, 'unit_price' => 50, 'discount_amount' => 0],
            ],
        ]);

        $this->assertCount(1, $updated->items);
        $this->assertSame($productB->id, $updated->items->first()->product_id);
        $this->assertSame('150.00', (string) $updated->total_amount);
        $this->assertDatabaseCount('order_items', 1);
    }
}
