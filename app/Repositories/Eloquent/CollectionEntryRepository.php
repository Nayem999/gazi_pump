<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\CollectionEntry;
use App\Repositories\Contracts\CollectionEntryRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class CollectionEntryRepository extends BaseRepository implements CollectionEntryRepositoryInterface
{
    public function __construct(CollectionEntry $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters)
            ->with(['user', 'dealer.territory'])
            ->latest('collection_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function sumWithFilters(array $filters): float
    {
        return (float) $this->filtered($filters)->sum('amount');
    }

    private function filtered(array $filters): Builder
    {
        return $this->query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('dealer', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('dealer_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['dealer_id'] ?? null, fn ($query, $dealerId) => $query->where('dealer_id', $dealerId))
            ->when($filters['territory_id'] ?? null, function ($query, $territoryId) {
                $query->whereHas('dealer', fn ($q) => $q->where('territory_id', $territoryId));
            })
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('collection_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('collection_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            });
    }
}
