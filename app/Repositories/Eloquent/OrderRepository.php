<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'dealer', 'items.product'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->whereHas('dealer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('dealer_code', 'like', "%{$search}%");
                    })->orWhereHas('items.product', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                    });
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['dealer_id'] ?? null, fn ($query, $dealerId) => $query->where('dealer_id', $dealerId))
            ->when($filters['product_id'] ?? null, function ($query, $productId) {
                $query->whereHas('items', fn ($q) => $q->where('product_id', $productId));
            })
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('order_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('order_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest('order_date')
            ->paginate($perPage)
            ->withQueryString();
    }
}
