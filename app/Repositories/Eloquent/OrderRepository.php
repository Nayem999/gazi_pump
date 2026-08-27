<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{
    public function __construct(Order $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->filtered($filters)
            ->with(['user', 'dealer.territory', 'items.product'])
            ->latest('order_date')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function sumWithFilters(array $filters): float
    {
        return (float) $this->filtered($filters)->sum('total_amount');
    }

    private function filtered(array $filters): Builder
    {
        return $this->query()
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
            ->when($filters['territory_id'] ?? null, function ($query, $territoryId) {
                $query->whereHas('dealer', fn ($q) => $q->where('territory_id', $territoryId));
            })
            ->when($filters['product_id'] ?? null, function ($query, $productId) {
                $query->whereHas('items', fn ($q) => $q->where('product_id', $productId));
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('order_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('order_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            });
    }
}
