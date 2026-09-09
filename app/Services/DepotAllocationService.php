<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AllocationStatus;
use App\Models\DepotAllocation;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Alternative Depot (spec §12) and Split Depot Fulfillment (spec §13).
 * Never touches Tally-owned stock columns (opening/in/out/closing/
 * available) — only ever increments/decrements ProductStock's own
 * reserved_qty/allocated_qty overlay, per spec §14's explicit rule.
 */
class DepotAllocationService
{
    /**
     * Depots with sellable stock for this product, the preferred depot
     * first (if it has any), then every alternative ordered by how much
     * they can cover.
     *
     * @return Collection<int, ProductStock>
     */
    public function findAvailableDepots(Product $product, ?int $preferredDepotId = null): Collection
    {
        return ProductStock::where('product_id', $product->id)
            ->with('depot')
            ->get()
            ->filter(fn (ProductStock $stock) => $stock->sellableQty() > 0)
            ->sortByDesc(fn (ProductStock $stock) => $stock->depot_id === $preferredDepotId ? PHP_INT_MAX : $stock->sellableQty())
            ->values();
    }

    /**
     * Fully allocates one order line if possible, starting at the preferred
     * depot and falling through alternatives for whatever it can't cover —
     * splitting across as many depots as needed (spec §13). Any amount no
     * depot can cover is reported back as shortfall rather than forced into
     * a fake allocation row (depot_id is a real FK, never null).
     *
     * @return array{allocations: Collection<int, DepotAllocation>, shortfall: float}
     */
    public function autoAllocate(OrderItem $item, ?int $preferredDepotId = null): array
    {
        return DB::transaction(function () use ($item, $preferredDepotId) {
            $remaining = (float) $item->quantity;
            $candidates = $this->findAvailableDepots($item->product, $preferredDepotId);
            $allocations = collect();

            foreach ($candidates as $stock) {
                if ($remaining <= 0) {
                    break;
                }

                $take = min($remaining, $stock->sellableQty());

                if ($take <= 0) {
                    continue;
                }

                $allocations->push($this->reserve($item, $stock, $take, $stock->depot_id !== $preferredDepotId));
                $remaining -= $take;
            }

            return ['allocations' => $allocations, 'shortfall' => round(max(0.0, $remaining), 2)];
        });
    }

    /**
     * An admin explicitly picking a depot (including a deliberate
     * alternative-depot choice) rather than relying on autoAllocate()'s
     * best-first ordering.
     */
    public function allocateManually(OrderItem $item, int $depotId, float $qty, ?int $approverId = null, bool $isAlternativeDepot = false): DepotAllocation
    {
        return DB::transaction(function () use ($item, $depotId, $qty, $approverId, $isAlternativeDepot) {
            $stock = ProductStock::where('depot_id', $depotId)->where('product_id', $item->product_id)->first();

            if (! $stock || $stock->sellableQty() < $qty) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough sellable stock at this depot for the requested quantity.',
                ]);
            }

            $allocation = $this->reserve($item, $stock, $qty, $isAlternativeDepot);

            if ($approverId) {
                $allocation->update(['approved_by' => $approverId, 'approved_at' => now()]);
            }

            return $allocation->fresh();
        });
    }

    /**
     * Releases a rejected/cancelled allocation's reservation back to the
     * pool without touching Tally-owned figures.
     */
    public function release(DepotAllocation $allocation): DepotAllocation
    {
        return DB::transaction(function () use ($allocation) {
            $stock = ProductStock::where('depot_id', $allocation->depot_id)
                ->where('product_id', $allocation->orderItem->product_id)
                ->first();

            $stock?->decrement('reserved_qty', (float) $allocation->allocated_qty);

            $allocation->update(['allocation_status' => AllocationStatus::Rejected, 'allocated_qty' => 0]);

            return $allocation->fresh();
        });
    }

    /**
     * An order line's overall allocation state, derived from its rows
     * rather than stored redundantly: fully covered, partially covered, or
     * nothing allocated yet.
     */
    public function lineStatus(OrderItem $item): AllocationStatus
    {
        $allocated = (float) $item->depotAllocations()->where('allocation_status', '!=', AllocationStatus::Rejected)->sum('allocated_qty');

        return match (true) {
            $allocated <= 0 => AllocationStatus::Pending,
            $allocated < (float) $item->quantity => AllocationStatus::PartiallyAllocated,
            default => AllocationStatus::Allocated,
        };
    }

    private function reserve(OrderItem $item, ProductStock $stock, float $qty, bool $isAlternativeDepot): DepotAllocation
    {
        $stock->increment('reserved_qty', $qty);
        $stock->increment('allocated_qty', $qty);

        return DepotAllocation::create([
            'order_item_id' => $item->id,
            'depot_id' => $stock->depot_id,
            'requested_qty' => $qty,
            'allocated_qty' => $qty,
            'allocation_status' => AllocationStatus::Allocated,
            'is_alternative_depot' => $isAlternativeDepot,
        ]);
    }
}
