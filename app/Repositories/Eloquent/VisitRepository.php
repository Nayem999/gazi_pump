<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Visit;
use App\Repositories\Contracts\VisitRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class VisitRepository extends BaseRepository implements VisitRepositoryInterface
{
    public function __construct(Visit $model)
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
            ->when($filters['dealer_id'] ?? null, fn ($query, $dealerId) => $query->where('dealer_id', $dealerId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('check_in_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('check_in_at', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('check_in_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findOpenVisitForUser(int $userId): ?Visit
    {
        return $this->query()->where('user_id', $userId)->whereNull('check_out_at')->latest('check_in_at')->first();
    }
}
