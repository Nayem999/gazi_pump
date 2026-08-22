<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Target;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TargetsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Target>  $targets
     */
    public function __construct(private readonly Collection $targets) {}

    public function collection(): Collection
    {
        return $this->targets;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'Executive', 'Period',
            'Order Target', 'Order Achieved', 'Order %',
            'Collection Target', 'Collection Achieved', 'Collection %',
            'Qty Target', 'Qty Achieved', 'Qty %',
            'Overall %', 'Grade',
        ];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($target): array
    {
        $achievement = $target->achievement;

        return [
            $target->user?->name,
            $target->periodLabel(),
            (string) $target->order_value_target,
            $achievement ? (string) $achievement->order_achieved : '—',
            $achievement ? (string) $achievement->order_pct : '—',
            (string) $target->collection_target,
            $achievement ? (string) $achievement->collection_achieved : '—',
            $achievement ? (string) $achievement->collection_pct : '—',
            (string) $target->quantity_target,
            $achievement ? (string) $achievement->quantity_achieved : '—',
            $achievement ? (string) $achievement->quantity_pct : '—',
            $achievement ? (string) $achievement->overall_pct : '—',
            $achievement?->grade->label() ?? '—',
        ];
    }
}
