<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Thana;
use App\Repositories\Contracts\ThanaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ThanaRepository extends BaseRepository implements ThanaRepositoryInterface
{
    public function __construct(Thana $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['district.division'])
            ->withCount(['territories', 'dealers'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['district_id'] ?? null, fn ($query, $districtId) => $query->where('district_id', $districtId))
            ->when($filters['division_id'] ?? null, fn ($query, $divisionId) => $query->whereHas('district', fn ($dq) => $dq->where('division_id', $divisionId)))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status === 'active'))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
