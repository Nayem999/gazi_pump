<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\SalesReturnStatus;
use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use App\Enums\TallyRecordSyncStatus;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\SalesReturn;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Full Sales Return workflow (spec's Phase 6): request -> approve/reject
 * -> dispatch -> depot receiving -> Tally accounting (a Credit Note
 * voucher, pushed automatically once received — same guard-then-enqueue
 * shape as Order/CollectionEntry/Delivery). Depot receiving intentionally
 * never touches product_stocks directly — Tally owns those columns (see
 * TallyStockSyncService), so a returned item's stock only shows up again
 * once the next stock-sync pull reflects the credit note Tally posted.
 */
class SalesReturnService
{
    public function __construct(private readonly TallySyncQueueService $syncQueue) {}

    /**
     * @param  array{items: array<int, array{order_item_id: int, quantity: float}>, reason?: ?string}  $data
     */
    public function requestReturn(Order $order, User $requester, array $data): SalesReturn
    {
        if ($order->status !== ApprovalStatus::Approved) {
            throw ValidationException::withMessages(['order' => 'Only an approved order can have a return requested against it.']);
        }

        return DB::transaction(function () use ($order, $requester, $data) {
            $return = SalesReturn::create([
                'order_id' => $order->id,
                'dealer_id' => $order->dealer_id,
                'user_id' => $requester->id,
                'status' => SalesReturnStatus::Requested,
                'reason' => $data['reason'] ?? null,
                'sync_status' => TallyRecordSyncStatus::NotSynced,
            ]);

            foreach ($data['items'] as $line) {
                /** @var OrderItem $orderItem */
                $orderItem = OrderItem::where('order_id', $order->id)->findOrFail($line['order_item_id']);

                $quantity = (float) $line['quantity'];
                $remaining = (float) $orderItem->quantity - $orderItem->returnedQuantity();

                if ($quantity <= 0 || $quantity > $remaining) {
                    throw ValidationException::withMessages([
                        'items' => "Requested return quantity for \"{$orderItem->product?->name}\" exceeds what's left to return ({$remaining}).",
                    ]);
                }

                $return->items()->create([
                    'order_item_id' => $orderItem->id,
                    'product_id' => $orderItem->product_id,
                    'requested_qty' => $quantity,
                    'unit_price' => $orderItem->unit_price,
                    'total_amount' => round($quantity * (float) $orderItem->unit_price, 2),
                ]);
            }

            $return->update(['external_reference' => $this->generateExternalReference($return->id)]);

            return $return->fresh()->load('items.product');
        });
    }

    public function approve(SalesReturn $return, int $approverId): SalesReturn
    {
        $this->assertStatus($return, SalesReturnStatus::Requested);

        $return->update([
            'status' => SalesReturnStatus::Approved,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $return->fresh();
    }

    public function reject(SalesReturn $return, int $approverId): SalesReturn
    {
        $this->assertStatus($return, SalesReturnStatus::Requested);

        $return->update([
            'status' => SalesReturnStatus::Rejected,
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $return->fresh();
    }

    /**
     * @param  array{vehicle_id?: ?int, driver_id?: ?int}  $data
     */
    public function dispatch(SalesReturn $return, array $data): SalesReturn
    {
        $this->assertStatus($return, SalesReturnStatus::Approved);

        $return->update([
            'status' => SalesReturnStatus::Dispatched,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'dispatched_at' => now(),
        ]);

        return $return->fresh();
    }

    /**
     * The depot confirms what actually arrived (per line, possibly less
     * than requested — e.g. some units damaged/missing in transit) — the
     * Credit Note pushed to Tally is always based on these confirmed
     * quantities, never the original request.
     *
     * @param  array<int, array{item_id: int, received_qty: float}>  $receivedItems
     */
    public function receive(SalesReturn $return, array $receivedItems, int $receivingDepotId, int $receivedByUserId): SalesReturn
    {
        $this->assertStatus($return, SalesReturnStatus::Dispatched);

        return DB::transaction(function () use ($return, $receivedItems, $receivingDepotId, $receivedByUserId) {
            foreach ($receivedItems as $line) {
                $item = $return->items()->findOrFail($line['item_id']);
                $item->update(['received_qty' => $line['received_qty']]);
            }

            $return->update([
                'status' => SalesReturnStatus::Received,
                'receiving_depot_id' => $receivingDepotId,
                'received_by' => $receivedByUserId,
                'received_at' => now(),
            ]);

            $return = $return->fresh()->load(['order.dealer', 'items.product']);

            $this->enqueueTallySync($return);

            return $return->fresh();
        });
    }

    /**
     * @param  array{status?: string, dealer_id?: string, search?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return SalesReturn::query()
            ->with(['order', 'dealer', 'user'])
            ->when($viewer, fn ($q) => $q->visibleTo($viewer))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['dealer_id'] ?? null, fn ($q, $id) => $q->where('dealer_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('external_reference', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    private function assertStatus(SalesReturn $return, SalesReturnStatus $expected): void
    {
        if ($return->status !== $expected) {
            throw ValidationException::withMessages([
                'status' => "This return is {$return->status->label()}, not {$expected->label()} — this action isn't available.",
            ]);
        }
    }

    private function generateExternalReference(int $returnId): string
    {
        return sprintf('SFA-SR-%s-%06d', now()->format('Ymd'), $returnId);
    }

    /**
     * Pushes a received return toward Tally as a Credit Note — same
     * missing-mapping guard as OrderService::enqueueTallySync(). Amounts
     * are based on received_qty (what the depot actually confirmed), not
     * the original requested_qty.
     */
    private function enqueueTallySync(SalesReturn $return): void
    {
        $dealerGuid = $return->order->dealer?->tally_guid;

        if (! $dealerGuid) {
            $return->update([
                'sync_status' => TallyRecordSyncStatus::Failed,
                'sync_error' => 'Dealer has no Tally mapping (tally_guid) yet.',
            ]);

            return;
        }

        $missingProduct = $return->items->first(fn ($item) => ! $item->product?->tally_guid);

        if ($missingProduct) {
            $return->update([
                'sync_status' => TallyRecordSyncStatus::Failed,
                'sync_error' => "Product \"{$missingProduct->product?->name}\" has no Tally mapping (tally_guid) yet.",
            ]);

            return;
        }

        $itemPayload = $return->items->map(fn ($item) => [
            'product_tally_guid' => $item->product->tally_guid,
            'product_name' => $item->product->name,
            'quantity' => (float) $item->received_qty,
            'unit_price' => (float) $item->unit_price,
            'total_amount' => round((float) $item->received_qty * (float) $item->unit_price, 2),
        ])->all();

        $totalAmount = round(array_sum(array_column($itemPayload, 'total_amount')), 2);

        $this->syncQueue->enqueue(
            TallyEntityType::SalesReturn,
            $return->id,
            SyncDirection::PushToTally,
            $return->external_reference,
            [
                'return_date' => now()->toDateString(),
                'dealer_tally_guid' => $dealerGuid,
                'dealer_tally_name' => $return->order->dealer->tally_ledger_name ?: $return->order->dealer->name,
                'total_amount' => $totalAmount,
                'remarks' => $return->reason,
                'items' => $itemPayload,
            ],
        );

        $return->update(['sync_status' => TallyRecordSyncStatus::Pending]);
    }
}
