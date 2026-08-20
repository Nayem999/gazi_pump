<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\VisitRequest;
use App\Repositories\Contracts\VisitRequestRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class VisitRequestRepository extends BaseRepository implements VisitRequestRepositoryInterface
{
    public function __construct(VisitRequest $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with('customerAccount')
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('address', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
