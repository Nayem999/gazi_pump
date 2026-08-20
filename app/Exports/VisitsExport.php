<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Visit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisitsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, Visit>  $visits
     */
    public function __construct(private readonly Collection $visits) {}

    public function collection(): Collection
    {
        return $this->visits;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Employee', 'Customer', 'Check In', 'Check Out', 'GPS Verified', 'Distance (m)', 'Feedback'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($visit): array
    {
        return [
            $visit->user?->name,
            $visit->customer?->name,
            $visit->check_in_at?->format('Y-m-d H:i'),
            $visit->check_out_at?->format('Y-m-d H:i'),
            match ($visit->is_gps_verified) {
                true => 'Yes',
                false => 'No',
                default => 'Unknown',
            },
            $visit->distance_from_customer_meters !== null ? (string) $visit->distance_from_customer_meters : null,
            $visit->feedback,
        ];
    }
}
