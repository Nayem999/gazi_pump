<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Spatie\Activitylog\Models\Activity;

class ActivityLogExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Activity>  $rows
     */
    public function __construct(private readonly Collection $rows) {}

    public function collection(): Collection
    {
        return $this->rows;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Date', 'Causer', 'Log Name', 'Event', 'Subject', 'Description'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($row): array
    {
        return [
            $row->created_at?->format('Y-m-d H:i:s'),
            $row->causer?->name ?? 'System',
            $row->log_name,
            $row->event,
            $row->subject_type ? class_basename($row->subject_type).' #'.$row->subject_id : null,
            $row->description,
        ];
    }
}
