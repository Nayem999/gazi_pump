<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Dealer;
use App\Repositories\Contracts\DealerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DealerRepository extends BaseRepository implements DealerRepositoryInterface
{
    public function __construct(Dealer $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->with('territory')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('dealer_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['territory_id'] ?? null, fn ($query, $territoryId) => $query->where('territory_id', $territoryId))
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
