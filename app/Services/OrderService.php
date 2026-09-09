<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ApprovalStatus;
use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use App\Enums\TallyRecordSyncStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService extends BaseCrudService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly TallySyncQueueService $syncQueue,
    ) {
        parent::__construct($orders);
    }

    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, territory_id?: string, product_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->orders->paginateWithFilters($filters, $perPage, $viewer);
    }

    /**
     * @param  array{search?: string, user_id?: string, dealer_id?: string, territory_id?: string, product_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function total(array $filters, ?User $viewer = null): float
    {
        return $this->orders->sumWithFilters($filters, $viewer);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $items = $data['items'];
        unset($data['items']);
        $data['status'] ??= ApprovalStatus::Pending->value;

        return DB::transaction(function () use ($data, $items) {
            $lines = $this->buildLines($items);
            $data['total_amount'] = array_sum(array_column($lines, 'total_amount'));

            /** @var Order $order */
            $order = parent::create($data);
            $order->items()->createMany($lines);

            // The idempotency key every retry of this exact order must share
            // (spec §30) — generated once the row has a real id, since the
            // format embeds it.
            $order->update(['external_reference' => $this->generateExternalReference($order->id)]);

            return $order->fresh()->load('items.product');
        });
    }

    /**
     * Forward-only, mirroring CashHandoverService::confirm()/reject(): only
     * a Pending order can be approved or rejected, and both are terminal —
     * a rejected order is corrected and resubmitted, not reopened here.
     * Only an Approved order is ever pushed toward Tally — its own
     * sync_status is a separate dimension from this business status (spec
     * §46), tracked independently from here on.
     */
    public function approve(Order $order, int $approverId): Order
    {
        $this->assertPending($order);

        $order->update(['status' => ApprovalStatus::Approved->value, 'approved_by' => $approverId, 'approved_at' => now()]);

        $this->enqueueTallySync($order->fresh(['dealer', 'retailer', 'items.product']));

        return $order->fresh();
    }

    public function reject(Order $order, int $approverId): Order
    {
        $this->assertPending($order);

        $order->update(['status' => ApprovalStatus::Rejected->value, 'approved_by' => $approverId, 'approved_at' => now()]);

        return $order->fresh();
    }

    private function assertPending(Order $order): void
    {
        if ($order->status !== ApprovalStatus::Pending) {
            throw ValidationException::withMessages([
                'status' => 'This order has already been '.$order->status->label().' and cannot be changed.',
            ]);
        }
    }

    private function generateExternalReference(int $orderId): string
    {
        return sprintf('SFA-SO-%s-%06d', now()->format('Ymd'), $orderId);
    }

    /**
     * Pushes an approved order toward Tally as a Sales Voucher — but only
     * once every product/dealer it references actually has a Tally mapping
     * (spec §32: CUSTOMER_MAPPING_MISSING / PRODUCT_MAPPING_MISSING). A
     * missing mapping fails the order's sync_status immediately with a
     * clear reason rather than silently enqueueing something the Sync Agent
     * could never complete; an admin fixes the mapping (Tally Integration →
     * Mapping) and retries from the Order's own action, which re-runs this
     * same check.
     */
    private function enqueueTallySync(Order $order): void
    {
        if (! $order->dealer?->tally_guid) {
            $order->update(['sync_status' => TallyRecordSyncStatus::Failed, 'sync_error' => "Dealer \"{$order->dealer?->name}\" has no Tally mapping yet."]);

            return;
        }

        $itemPayload = [];

        foreach ($order->items as $item) {
            if (! $item->product?->tally_guid) {
                $order->update(['sync_status' => TallyRecordSyncStatus::Failed, 'sync_error' => "Product \"{$item->product?->name}\" has no Tally mapping yet."]);

                return;
            }

            $itemPayload[] = [
                'product_tally_guid' => $item->product->tally_guid,
                'product_name' => $item->product->name,
                'quantity' => $item->quantity,
                'unit_price' => (float) $item->unit_price,
                'discount_amount' => (float) $item->discount_amount,
                'total_amount' => (float) $item->total_amount,
            ];
        }

        $this->syncQueue->enqueue(
            TallyEntityType::SalesOrder,
            $order->id,
            SyncDirection::PushToTally,
            $order->external_reference,
            [
                'order_date' => $order->order_date->toDateString(),
                'dealer_tally_guid' => $order->dealer->tally_guid,
                'dealer_tally_name' => $order->dealer->tally_ledger_name ?: $order->dealer->name,
                'retailer_tally_guid' => $order->retailer?->tally_guid,
                'total_amount' => (float) $order->total_amount,
                'remarks' => $order->remarks,
                'items' => $itemPayload,
            ],
        );

        $order->update(['sync_status' => TallyRecordSyncStatus::Pending]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Model $model, array $data): Model
    {
        $items = $data['items'];
        unset($data['items']);

        return DB::transaction(function () use ($model, $data, $items) {
            $lines = $this->buildLines($items);
            $data['total_amount'] = array_sum(array_column($lines, 'total_amount'));

            /** @var Order $order */
            $order = parent::update($model, $data);
            $order->items()->delete();
            $order->items()->createMany($lines);

            return $order->load('items.product');
        });
    }

    /**
     * Self-service field entry: each line's unit price is always the
     * product's current price (never trusted from the mobile client), and
     * the order date defaults to today when the rep doesn't backdate it.
     *
     * @param  array<int, array{product_id: int, quantity: int, discount_amount?: float}>  $items
     */
    public function recordOrder(User $user, int $dealerId, array $items, ?string $orderDate, ?string $remarks, ?int $retailerId = null): Order
    {
        $itemsWithPrice = array_map(function (array $item) {
            $product = Product::findOrFail($item['product_id']);

            return [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => (float) $product->price,
                'discount_amount' => $item['discount_amount'] ?? 0,
            ];
        }, $items);

        /** @var Order $entry */
        $entry = $this->create([
            'user_id' => $user->id,
            'dealer_id' => $dealerId,
            'retailer_id' => $retailerId,
            'order_date' => $orderDate ?? Carbon::today()->toDateString(),
            'remarks' => $remarks,
            'items' => $itemsWithPrice,
        ]);

        return $entry;
    }

    /**
     * The mandatory Preview step (spec §11): computes exactly what
     * store()/recordOrder() would persist — same per-line validation
     * (discount cap), same server-authoritative pricing — without writing
     * anything, so the caller can render a confirm-before-submit screen
     * from real numbers rather than duplicating this math client-side.
     *
     * @param  array<int, array{product_id: int, quantity: int, discount_amount?: float}>  $items
     * @return array{items: array<int, array<string, mixed>>, subtotal: float, grand_total: float}
     */
    public function previewOrder(array $items): array
    {
        $itemsWithPrice = array_map(function (array $item) {
            $product = Product::findOrFail($item['product_id']);

            return [
                'product_id' => $item['product_id'],
                'product_name' => $product->name,
                'quantity' => $item['quantity'],
                'unit_price' => (float) $product->price,
                'discount_amount' => $item['discount_amount'] ?? 0,
            ];
        }, $items);

        $lines = $this->buildLines($itemsWithPrice);
        $grandTotal = array_sum(array_column($lines, 'total_amount'));

        foreach ($lines as $index => $line) {
            $lines[$index]['product_name'] = $itemsWithPrice[$index]['product_name'];
        }

        // No tax/discount-beyond-line concept exists yet (Phase 4 adds
        // tax), so subtotal and grand_total are the same figure for now —
        // both are still returned so the mobile Preview screen's layout
        // (spec §11: Subtotal, Grand Total as separate rows) doesn't need
        // to change shape once Phase 4 adds a real tax line.
        return ['items' => $lines, 'subtotal' => $grandTotal, 'grand_total' => $grandTotal];
    }

    /**
     * @param  array<int, array{product_id: int|string, quantity: int|string, unit_price: float|string, discount_amount?: float|string}>  $items
     * @return array<int, array<string, mixed>>
     */
    private function buildLines(array $items): array
    {
        $lines = [];
        $index = 0;

        foreach ($items as $item) {
            $quantity = (int) $item['quantity'];
            $unitPrice = (float) $item['unit_price'];
            $discountAmount = (float) ($item['discount_amount'] ?? 0);

            $lines[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'discount_amount' => $discountAmount,
                'total_amount' => $this->calculateLineTotal($quantity, $unitPrice, $discountAmount, $index),
            ];

            $index++;
        }

        return $lines;
    }

    /**
     * A discount larger than the configured percentage of a line's subtotal
     * is rejected outright rather than silently capped, so the rep re-enters
     * the correct figure instead of an under-discounted order going unnoticed.
     */
    private function calculateLineTotal(int $quantity, float $unitPrice, float $discountAmount, int $lineIndex): float
    {
        $subtotal = $quantity * $unitPrice;
        $maxDiscountPercent = (float) config('sfa.orders.max_discount_percent');
        $maxDiscountAmount = round($subtotal * $maxDiscountPercent / 100, 2);

        if ($discountAmount > $maxDiscountAmount) {
            throw ValidationException::withMessages([
                "items.{$lineIndex}.discount_amount" => "Discount cannot exceed {$maxDiscountPercent}% of the line subtotal (max ".number_format($maxDiscountAmount, 2).').',
            ]);
        }

        return round($subtotal - $discountAmount, 2);
    }
}
