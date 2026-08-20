<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\User;
use App\Repositories\Contracts\SalesEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SalesEntryService extends BaseCrudService
{
    public function __construct(private readonly SalesEntryRepositoryInterface $salesEntries)
    {
        parent::__construct($salesEntries);
    }

    /**
     * @param  array{search?: string, user_id?: string, customer_id?: string, product_id?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->salesEntries->paginateWithFilters($filters, $perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Model
    {
        $items = $data['items'];
        unset($data['items']);

        return DB::transaction(function () use ($data, $items) {
            $lines = $this->buildLines($items);
            $data['total_amount'] = array_sum(array_column($lines, 'total_amount'));

            /** @var SalesEntry $salesEntry */
            $salesEntry = parent::create($data);
            $salesEntry->items()->createMany($lines);

            return $salesEntry->load('items.product');
        });
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

            /** @var SalesEntry $salesEntry */
            $salesEntry = parent::update($model, $data);
            $salesEntry->items()->delete();
            $salesEntry->items()->createMany($lines);

            return $salesEntry->load('items.product');
        });
    }

    /**
     * Self-service field entry: each line's unit price is always the
     * product's current price (never trusted from the mobile client), and
     * the sale date defaults to today when the rep doesn't backdate it.
     *
     * @param  array<int, array{product_id: int, quantity: int, discount_amount?: float}>  $items
     */
    public function recordSale(User $user, int $customerId, array $items, ?string $saleDate, ?string $remarks): SalesEntry
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

        /** @var SalesEntry $entry */
        $entry = $this->create([
            'user_id' => $user->id,
            'customer_id' => $customerId,
            'sale_date' => $saleDate ?? Carbon::today()->toDateString(),
            'remarks' => $remarks,
            'items' => $itemsWithPrice,
        ]);

        return $entry;
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
     * the correct figure instead of an under-discounted sale going unnoticed.
     */
    private function calculateLineTotal(int $quantity, float $unitPrice, float $discountAmount, int $lineIndex): float
    {
        $subtotal = $quantity * $unitPrice;
        $maxDiscountPercent = (float) config('sfa.sales.max_discount_percent');
        $maxDiscountAmount = round($subtotal * $maxDiscountPercent / 100, 2);

        if ($discountAmount > $maxDiscountAmount) {
            throw ValidationException::withMessages([
                "items.{$lineIndex}.discount_amount" => "Discount cannot exceed {$maxDiscountPercent}% of the line subtotal (max ".number_format($maxDiscountAmount, 2).').',
            ]);
        }

        return round($subtotal - $discountAmount, 2);
    }
}
