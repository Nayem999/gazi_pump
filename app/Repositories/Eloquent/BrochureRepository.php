<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Brochure;
use App\Repositories\Contracts\BrochureRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class BrochureRepository extends BaseRepository implements BrochureRepositoryInterface
{
    public function __construct(Brochure $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->query()
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('is_published', $status === 'published'))
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
