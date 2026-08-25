<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Product>  $products
     */
    public function __construct(private readonly Collection $products) {}

    public function collection(): Collection
    {
        return $this->products;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['SKU', 'Name', 'Category', 'Sales Team', 'Price', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($product): array
    {
        return [
            $product->sku,
            $product->name,
            $product->category?->name,
            $product->salesTeam?->name,
            $product->price,
            $product->status ? 'Active' : 'Inactive',
        ];
    }
}
