<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Dealer;
use App\Models\User;
use App\Repositories\Contracts\DealerRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class DealerRepository extends BaseRepository implements DealerRepositoryInterface
{
    public function __construct(Dealer $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['territory', 'thana', 'district', 'division'])
            ->when($viewer, fn ($query, $viewer) => $query->visibleTo($viewer))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('dealer_code', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($filters['division_id'] ?? null, fn ($query, $divisionId) => $query->where('division_id', $divisionId))
            ->when($filters['district_id'] ?? null, fn ($query, $districtId) => $query->where('district_id', $districtId))
            ->when($filters['thana_id'] ?? null, fn ($query, $thanaId) => $query->where('thana_id', $thanaId))
            ->when($filters['territory_id'] ?? null, fn ($query, $territoryId) => $query->whereIn('territory_id', (array) $territoryId))
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
