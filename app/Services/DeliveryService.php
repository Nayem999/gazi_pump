<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\AllocationStatus;
use App\Enums\ApprovalStatus;
use App\Enums\DeliveryStatus;
use App\Enums\TallyEntityType;
use App\Enums\TallyRecordSyncStatus;
use App\Enums\SyncDirection;
use App\Models\Delivery;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Dispatch (spec's Delivery/Challan phase): an Order can only be dispatched
 * once it's Approved and every line is fully depot-allocated — dispatching
 * what hasn't actually been picked from a depot isn't a real operation.
 * Delivery push to Tally mirrors Order/CollectionEntry's own guard-then-
 * enqueue shape exactly (see OrderService::enqueueTallySync()).
 */
class DeliveryService
{
    public function __construct(
        private readonly TallySyncQueueService $syncQueue,
        private readonly DepotAllocationService $depotAllocations,
    ) {}

    /**
     * @param  array{vehicle_id?: ?int, driver_id?: ?int, delivery_date: string, remarks?: ?string}  $data
     */
    public function dispatch(Order $order, array $data, int $dispatchedByUserId): Delivery
    {
        $order->loadMissing('items.product', 'dealer');

        if ($order->status !== ApprovalStatus::Approved) {
            throw ValidationException::withMessages(['order' => 'Only an approved order can be dispatched.']);
        }

        $unallocated = $order->items->reject(
            fn ($item) => $this->depotAllocations->lineStatus($item) === AllocationStatus::Allocated
        );

        if ($unallocated->isNotEmpty()) {
            throw ValidationException::withMessages([
                'order' => 'Every line must be fully depot-allocated before this order can be dispatched.',
            ]);
        }

        return DB::transaction(function () use ($order, $data, $dispatchedByUserId) {
            $delivery = Delivery::create([
                'order_id' => $order->id,
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'driver_id' => $data['driver_id'] ?? null,
                'delivery_date' => $data['delivery_date'],
                'status' => DeliveryStatus::Dispatched,
                'sync_status' => TallyRecordSyncStatus::NotSynced,
                'dispatched_by' => $dispatchedByUserId,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $delivery->update(['external_reference' => $this->generateExternalReference($delivery->id)]);

            $delivery = $delivery->fresh()->load(['order.items.product', 'order.dealer', 'vehicle', 'driver']);

            $this->enqueueTallySync($delivery);

            return $delivery->fresh();
        });
    }

    public function markDelivered(Delivery $delivery): Delivery
    {
        $delivery->update(['status' => DeliveryStatus::Delivered, 'delivered_at' => now()]);

        return $delivery->fresh();
    }

    private function generateExternalReference(int $deliveryId): string
    {
        return sprintf('SFA-DEL-%s-%06d', now()->format('Ymd'), $deliveryId);
    }

    /**
     * Same guard-then-enqueue shape as OrderService::enqueueTallySync():
     * a missing dealer/product mapping fails the sync in place (sync_status
     * = Failed, plain-English sync_error) without ever creating a queue
     * row the Sync Agent could never complete. The dispatch itself already
     * happened either way — a Tally mapping gap is a sync-side problem.
     */
    private function enqueueTallySync(Delivery $delivery): void
    {
        $order = $delivery->order;
        $dealerGuid = $order->dealer?->tally_guid;

        if (! $dealerGuid) {
            $delivery->update([
                'sync_status' => TallyRecordSyncStatus::Failed,
                'sync_error' => 'Dealer has no Tally mapping (tally_guid) yet.',
            ]);

            return;
        }

        $missingProduct = $order->items->first(fn ($item) => ! $item->product?->tally_guid);

        if ($missingProduct) {
            $delivery->update([
                'sync_status' => TallyRecordSyncStatus::Failed,
                'sync_error' => "Product \"{$missingProduct->product?->name}\" has no Tally mapping (tally_guid) yet.",
            ]);

            return;
        }

        $payload = [
            'delivery_date' => $delivery->delivery_date->toDateString(),
            'dealer_tally_guid' => $dealerGuid,
            'dealer_tally_name' => $order->dealer->tally_ledger_name ?: $order->dealer->name,
            'vehicle_number' => $delivery->vehicle?->registration_number,
            'driver_name' => $delivery->driver?->name,
            'remarks' => $delivery->remarks,
            'items' => $order->items->map(fn ($item) => [
                'product_tally_guid' => $item->product->tally_guid,
                'product_name' => $item->product->name,
                'quantity' => (float) $item->quantity,
            ])->all(),
        ];

        $this->syncQueue->enqueue(
            TallyEntityType::Delivery,
            $delivery->id,
            SyncDirection::PushToTally,
            $delivery->external_reference,
            $payload,
        );

        $delivery->update(['sync_status' => TallyRecordSyncStatus::Pending]);
    }
}
