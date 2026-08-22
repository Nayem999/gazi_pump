<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\CollectionEntry;
use App\Repositories\Contracts\CollectionEntryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class CollectionEntryRepository extends BaseRepository implements CollectionEntryRepositoryInterface
{
    public function __construct(CollectionEntry $model)
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
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('collection_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('collection_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('collection_date')
            ->paginate($perPage)
            ->withQueryString();
    }
}
