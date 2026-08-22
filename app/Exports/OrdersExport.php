<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Flattens each order to one row per line item — an "Order #" column ties rows
 * from the same order back together, since an order can cover several products.
 */
class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Order>  $orders
     */
    public function __construct(private readonly Collection $orders) {}

    public function collection(): Collection
    {
        return $this->orders->flatMap(
            fn (Order $order) => $order->items->map(fn ($item) => (object) [
                'order' => $order,
                'item' => $item,
            ])
        );
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Order #', 'Executive', 'Dealer', 'Order Date', 'Product', 'Quantity', 'Unit Price', 'Discount', 'Line Total', 'Order Total', 'Remarks'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($line): array
    {
        $order = $line->order;
        $item = $line->item;

        return [
            $order->id,
            $order->user?->name,
            $order->dealer?->name,
            $order->order_date->format('Y-m-d'),
            $item->product?->name,
            $item->quantity,
            (string) $item->unit_price,
            (string) $item->discount_amount,
            (string) $item->total_amount,
            (string) $order->total_amount,
            $order->remarks,
        ];
    }
}
