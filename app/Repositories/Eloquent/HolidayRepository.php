<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Holiday;
use App\Repositories\Contracts\HolidayRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class HolidayRepository extends BaseRepository implements HolidayRepositoryInterface
{
    public function __construct(Holiday $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->whereYear('date', $year))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status === 'active'))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->orderBy('date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function existsOnDate(string $date): bool
    {
        return $this->query()->where('status', true)->whereDate('date', $date)->exists();
    }
}
