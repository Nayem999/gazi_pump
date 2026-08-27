<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Retailer;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RetailersExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Retailer>  $retailers
     */
    public function __construct(private readonly Collection $retailers) {}

    public function collection(): Collection
    {
        return $this->retailers;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Dealer', 'Name', 'Phone', 'Email', 'Shipping Address', 'Status'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($retailer): array
    {
        return [
            $retailer->dealer?->name,
            $retailer->name,
            $retailer->phone,
            $retailer->email,
            $retailer->shipping_address,
            $retailer->status ? 'Active' : 'Inactive',
        ];
    }
}
