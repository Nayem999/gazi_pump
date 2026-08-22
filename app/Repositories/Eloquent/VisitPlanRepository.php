<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\VisitPlan;
use App\Repositories\Contracts\VisitPlanRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class VisitPlanRepository extends BaseRepository implements VisitPlanRepositoryInterface
{
    public function __construct(VisitPlan $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'dealer'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('dealer', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('dealer_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('planned_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('planned_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('planned_date')
            ->paginate($perPage)
            ->withQueryString();
    }
}
