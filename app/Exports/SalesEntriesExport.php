<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\SalesEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Flattens each sale to one row per line item — a "Sale #" column ties rows
 * from the same sale back together, since a sale can cover several products.
 */
class SalesEntriesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, SalesEntry>  $salesEntries
     */
    public function __construct(private readonly Collection $salesEntries) {}

    public function collection(): Collection
    {
        return $this->salesEntries->flatMap(
            fn (SalesEntry $salesEntry) => $salesEntry->items->map(fn ($item) => (object) [
                'salesEntry' => $salesEntry,
                'item' => $item,
            ])
        );
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Sale #', 'Executive', 'Customer', 'Sale Date', 'Product', 'Quantity', 'Unit Price', 'Discount', 'Line Total', 'Sale Total', 'Remarks'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($line): array
    {
        $salesEntry = $line->salesEntry;
        $item = $line->item;

        return [
            $salesEntry->id,
            $salesEntry->user?->name,
            $salesEntry->customer?->name,
            $salesEntry->sale_date->format('Y-m-d'),
            $item->product?->name,
            $item->quantity,
            (string) $item->unit_price,
            (string) $item->discount_amount,
            (string) $item->total_amount,
            (string) $salesEntry->total_amount,
            $salesEntry->remarks,
        ];
    }
}
