<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Territory;
use App\Repositories\Contracts\TerritoryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TerritoryRepository extends BaseRepository implements TerritoryRepositoryInterface
{
    public function __construct(Territory $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['manager', 'division', 'district', 'thana'])
            ->withCount('users')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($filters['division_id'] ?? null, fn ($query, $id) => $query->where('division_id', $id))
            ->when($filters['district_id'] ?? null, fn ($query, $id) => $query->where('district_id', $id))
            ->when($filters['thana_id'] ?? null, fn ($query, $id) => $query->where('thana_id', $id))
            ->when($filters['geo'] ?? null, fn ($query, $geo) => match ($geo) {
                'missing' => $query->whereNull('thana_id'),
                default => null,
            })
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
