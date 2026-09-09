<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\TallyConnection;
use App\Repositories\Contracts\TallyConnectionRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TallyConnectionRepository extends BaseRepository implements TallyConnectionRepositoryInterface
{
    public function __construct(TallyConnection $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($inner) use ($search) {
                    $inner->where('connection_name', 'like', "%{$search}%")
                        ->orWhere('tally_company_name', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_active', $status === 'active'))
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
