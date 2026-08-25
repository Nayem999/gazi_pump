<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['category', 'salesTeam'])
            ->visibleTo($viewer)
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, fn ($query, $categoryId) => $query->where('category_id', $categoryId))
            ->when($filters['sales_team_id'] ?? null, fn ($query, $teamId) => $query->where('sales_team_id', $teamId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status === 'active'))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
