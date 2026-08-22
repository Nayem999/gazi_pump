<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\CollectionEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CollectionEntriesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, CollectionEntry>  $collectionEntries
     */
    public function __construct(private readonly Collection $collectionEntries) {}

    public function collection(): Collection
    {
        return $this->collectionEntries;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Executive', 'Dealer', 'Collection Date', 'Amount', 'Payment Method', 'Reference No', 'Remarks'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($collectionEntry): array
    {
        return [
            $collectionEntry->user?->name,
            $collectionEntry->dealer?->name,
            $collectionEntry->collection_date->format('Y-m-d'),
            (string) $collectionEntry->amount,
            $collectionEntry->payment_method->label(),
            $collectionEntry->reference_no,
            $collectionEntry->remarks,
        ];
    }
}
