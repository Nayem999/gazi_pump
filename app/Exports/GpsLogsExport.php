<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\GpsLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class GpsLogsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, GpsLog>  $logs
     */
    public function __construct(private readonly Collection $logs) {}

    public function collection(): Collection
    {
        return $this->logs;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Time', 'Latitude', 'Longitude', 'Accuracy (m)', 'Speed (km/h)', 'Battery (%)'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($log): array
    {
        return [
            $log->recorded_at->format('Y-m-d H:i:s'),
            (string) $log->lat,
            (string) $log->lng,
            $log->accuracy !== null ? (string) $log->accuracy : null,
            $log->speed !== null ? (string) $log->speed : null,
            $log->battery_level,
        ];
    }
}
