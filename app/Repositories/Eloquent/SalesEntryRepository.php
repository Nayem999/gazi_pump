<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\SalesEntry;
use App\Repositories\Contracts\SalesEntryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class SalesEntryRepository extends BaseRepository implements SalesEntryRepositoryInterface
{
    public function __construct(SalesEntry $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'customer', 'items.product'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('customer_code', 'like', "%{$search}%");
                    })->orWhereHas('items.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['customer_id'] ?? null, fn ($query, $customerId) => $query->where('customer_id', $customerId))
            ->when($filters['product_id'] ?? null, function ($query, $productId) {
                $query->whereHas('items', fn ($q) => $q->where('product_id', $productId));
            })
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('sale_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('sale_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('sale_date')
            ->paginate($perPage)
            ->withQueryString();
    }
}
