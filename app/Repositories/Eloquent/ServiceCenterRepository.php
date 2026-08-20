<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\ServiceCenter;
use App\Repositories\Contracts\ServiceCenterRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ServiceCenterRepository extends BaseRepository implements ServiceCenterRepositoryInterface
{
    public function __construct(ServiceCenter $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('name', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
