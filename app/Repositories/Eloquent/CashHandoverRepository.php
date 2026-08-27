<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\CashHandover;
use App\Repositories\Contracts\CashHandoverRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CashHandoverRepository extends BaseRepository implements CashHandoverRepositoryInterface
{
    public function __construct(CashHandover $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'confirmedBy'])
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('handover_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('handover_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('handover_date')
            ->paginate($perPage)
            ->withQueryString();
    }
}
