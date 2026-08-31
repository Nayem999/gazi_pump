<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\AchievementEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AchievementEntriesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, AchievementEntry>  $entries
     */
    public function __construct(private readonly Collection $entries) {}

    public function collection(): Collection
    {
        return $this->entries;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Executive', 'Date', 'Order Achieved', 'Collection Achieved', 'Quantity Achieved', 'Status',
        ];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($entry): array
    {
        return [
            $entry->user?->name,
            $entry->entryDateLabel(),
            (string) $entry->order_value_achieved,
            (string) $entry->collection_achieved,
            (string) $entry->quantity_achieved,
            $entry->status->label(),
        ];
    }
}
