<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AllocationStatus;
use App\Models\Depot;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use App\Services\DepotAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DepotAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private DepotAllocationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(DepotAllocationService::class);
    }

    public function test_find_available_depots_excludes_depots_with_no_sellable_stock(): void
    {
        $product = Product::factory()->create();
        $depotWithStock = Depot::factory()->create();
        $depotWithoutStock = Depot::factory()->create();

        ProductStock::factory()->create([
            'depot_id' => $depotWithStock->id,
            'product_id' => $product->id,
            'available_qty' => 50,
            'reserved_qty' => 0,
        ]);
        ProductStock::factory()->create([
            'depot_id' => $depotWithoutStock->id,
            'product_id' => $product->id,
            'available_qty' => 10,
            'reserved_qty' => 10,
        ]);

        $available = $this->service->findAvailableDepots($product);

        $this->assertCount(1, $available);
        $this->assertSame($depotWithStock->id, $available->first()->depot_id);
    }

    public function test_find_available_depots_prefers_the_preferred_depot_even_if_it_has_less_stock(): void
    {
        $product = Product::factory()->create();
        $preferred = Depot::factory()->create();
        $bigger = Depot::factory()->create();

        ProductStock::factory()->create(['depot_id' => $preferred->id, 'product_id' => $product->id, 'available_qty' => 10, 'reserved_qty' => 0]);
        ProductStock::factory()->create(['depot_id' => $bigger->id, 'product_id' => $product->id, 'available_qty' => 100, 'reserved_qty' => 0]);

        $available = $this->service->findAvailableDepots($product, $preferred->id);

        $this->assertSame($preferred->id, $available->first()->depot_id);
    }

    public function test_auto_allocate_fully_covers_a_line_from_a_single_depot(): void
    {
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 100, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        $result = $this->service->autoAllocate($item);

        $this->assertSame(0.0, $result['shortfall']);
        $this->assertCount(1, $result['allocations']);
        $this->assertSame(10.0, (float) $result['allocations']->first()->allocated_qty);

        $stock = ProductStock::where('depot_id', $depot->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(10.0, (float) $stock->reserved_qty);
        $this->assertSame(10.0, (float) $stock->allocated_qty);
    }

    public function test_auto_allocate_splits_across_depots_when_no_single_depot_can_cover_the_line(): void
    {
        $product = Product::factory()->create();
        $depotA = Depot::factory()->create();
        $depotB = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depotA->id, 'product_id' => $product->id, 'available_qty' => 6, 'reserved_qty' => 0]);
        ProductStock::factory()->create(['depot_id' => $depotB->id, 'product_id' => $product->id, 'available_qty' => 10, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 15]);

        $result = $this->service->autoAllocate($item);

        $this->assertSame(0.0, $result['shortfall']);
        $this->assertCount(2, $result['allocations']);
        $this->assertSame(15.0, (float) $result['allocations']->sum('allocated_qty'));
    }

    public function test_auto_allocate_reports_a_shortfall_when_no_depot_combination_covers_the_line(): void
    {
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 4, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        $result = $this->service->autoAllocate($item);

        $this->assertSame(6.0, $result['shortfall']);
        $this->assertCount(1, $result['allocations']);
        $this->assertSame(4.0, (float) $result['allocations']->first()->allocated_qty);
    }

    public function test_allocate_manually_succeeds_when_enough_sellable_stock_exists(): void
    {
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 50, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 5]);

        $allocation = $this->service->allocateManually($item, $depot->id, 5, isAlternativeDepot: true);

        $this->assertSame(AllocationStatus::Allocated, $allocation->allocation_status);
        $this->assertTrue($allocation->is_alternative_depot);
        $this->assertSame(5.0, (float) $allocation->allocated_qty);
    }

    public function test_allocate_manually_rejects_a_quantity_exceeding_sellable_stock(): void
    {
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 5, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        $this->expectException(ValidationException::class);

        $this->service->allocateManually($item, $depot->id, 10);
    }

    public function test_release_returns_reserved_quantity_to_the_pool(): void
    {
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 50, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 5]);

        $allocation = $this->service->allocateManually($item, $depot->id, 5);
        $this->service->release($allocation);

        $stock = ProductStock::where('depot_id', $depot->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(0.0, (float) $stock->reserved_qty);
        $this->assertSame(AllocationStatus::Rejected, $allocation->fresh()->allocation_status);
    }

    public function test_line_status_reflects_partial_then_full_allocation(): void
    {
        $product = Product::factory()->create();
        $depot = Depot::factory()->create();
        ProductStock::factory()->create(['depot_id' => $depot->id, 'product_id' => $product->id, 'available_qty' => 50, 'reserved_qty' => 0]);
        $item = OrderItem::factory()->create(['product_id' => $product->id, 'quantity' => 10]);

        $this->assertSame(AllocationStatus::Pending, $this->service->lineStatus($item));

        $this->service->allocateManually($item, $depot->id, 4);
        $this->assertSame(AllocationStatus::PartiallyAllocated, $this->service->lineStatus($item->fresh()));

        $this->service->allocateManually($item, $depot->id, 6);
        $this->assertSame(AllocationStatus::Allocated, $this->service->lineStatus($item->fresh()));
    }
}
