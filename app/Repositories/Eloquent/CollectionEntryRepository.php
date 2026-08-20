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
            ->with(['user', 'customer'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('customer', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('customer_code', 'like', "%{$search}%");
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId))
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
