<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Retailer;
use App\Repositories\Contracts\RetailerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class RetailerRepository extends BaseRepository implements RetailerRepositoryInterface
{
    public function __construct(Retailer $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with('dealer')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['dealer_id'] ?? null, fn ($query, $dealerId) => $query->where('dealer_id', $dealerId))
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
