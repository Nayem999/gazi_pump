<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Activity;

/**
 * Read-only browsing over Spatie's activity_log table — logging itself has
 * been wired since Phase 0 via the LogsActivity trait on BaseModel/User;
 * this module only adds the admin UI to search and inspect it.
 */
class ActivityLogService
{
    /**
     * @param  array{search?: string, log_name?: string, event?: string, causer_id?: string, date_from?: string, date_to?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Activity::query()
            ->with(['causer', 'subject'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('description', 'like', "%{$search}%"))
            ->when($filters['log_name'] ?? null, fn ($query, $logName) => $query->where('log_name', $logName))
            ->when($filters['event'] ?? null, fn ($query, $event) => $query->where('event', $event))
            ->when($filters['causer_id'] ?? null, fn ($query, $causerId) => $query->where('causer_id', $causerId))
            ->when($filters['date_from'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, $date) => $query->whereDate('created_at', '<=', $date))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return Collection<int, string>
     */
    public function logNames(): Collection
    {
        return Activity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name');
    }

    /**
     * @return Collection<int, string>
     */
    public function events(): Collection
    {
        return Activity::query()->whereNotNull('event')->distinct()->orderBy('event')->pluck('event');
    }
}
