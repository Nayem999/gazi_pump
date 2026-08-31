<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\AchievementEntry;
use App\Models\User;
use App\Repositories\Contracts\AchievementEntryRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AchievementEntryRepository extends BaseRepository implements AchievementEntryRepositoryInterface
{
    public function __construct(AchievementEntry $model)
    {
        parent::__construct($model);
    }

    /**
     * @param  array{search?: string, user_id?: string, status?: string, date_from?: string, date_to?: string, trashed?: string}  $filters
     */
    public function paginateWithFilters(array $filters, int $perPage = 15, ?User $viewer = null): LengthAwarePaginator
    {
        return $this->query()
            ->with(['user', 'approvedBy', 'items'])
            ->when($viewer, fn ($query, $viewer) => $query->visibleTo($viewer))
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->whereHas('user', function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")->orWhere('employee_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('user_id', $userId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('entry_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('entry_date', '<=', $date))
            ->when($filters['trashed'] ?? null, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->orderByDesc('entry_date')
            ->paginate($perPage)
            ->withQueryString();
    }
}
