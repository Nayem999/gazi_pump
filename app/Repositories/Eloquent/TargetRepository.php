<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\Target;
use App\Models\User;
use App\Repositories\Contracts\TargetRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TargetRepository extends BaseRepository implements TargetRepositoryInterface
{
    public function __construct(Target $model)
    {
        parent::__construct($model);
    }

    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'achievement', 'items'])
            ->when($viewer, fn ($query, $viewer) => $query->visibleTo($viewer))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('user', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['month'] ?? null, fn ($query, $month) => $query->where('month', $month))
            ->when($filters['year'] ?? null, fn ($query, $year) => $query->where('year', $year))
            ->when($filters['grade'] ?? null, function ($query, $grade) {
                $query->whereHas('achievement', fn ($inner) => $inner->where('grade', $grade));
            })
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate($perPage)
            ->withQueryString();
    }
}
