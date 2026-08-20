<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\ProductCategory;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductCategoriesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, ProductCategory>  $categories
     */
    public function __construct(private readonly Collection $categories) {}

    public function collection(): Collection
    {
        return $this->categories;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Code', 'Name', 'Products', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($category): array
    {
        return [
            $category->code,
            $category->name,
            $category->products_count ?? $category->products()->count(),
            $category->status ? 'Active' : 'Inactive',
        ];
    }
}
