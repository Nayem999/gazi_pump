<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\VisitPlan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisitPlansExport implements FromCollection, WithHeadings, WithMapping
{
    /**
     * @param  Collection<int, VisitPlan>  $visitPlans
     */
    public function __construct(private readonly Collection $visitPlans) {}

    public function collection(): Collection
    {
        return $this->visitPlans;
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return ['Employee', 'Dealer', 'Planned Date', 'Status', 'Notes'];
    }

    /**
     * @return array<int, string|null>
     */
    public function map($visitPlan): array
    {
        return [
            $visitPlan->user?->name,
            $visitPlan->dealer?->name,
            $visitPlan->planned_date->toDateString(),
            $visitPlan->status->label(),
            $visitPlan->notes,
        ];
    }
}
