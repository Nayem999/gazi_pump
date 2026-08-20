<?php

declare(strict_types=1);

namespace App\Repositories\Eloquent;

use App\Models\GpsLog;
use App\Repositories\Contracts\GpsLogRepositoryInterface;
use Illuminate\Support\Collection;

class GpsLogRepository extends BaseRepository implements GpsLogRepositoryInterface
{
    public function __construct(GpsLog $model)
    {
        parent::__construct($model);
    }

    public function historyForUserOnDate(int $userId, string $date, ?string $trashed = null): Collection
    {
        return $this->query()
            ->where('user_id', $userId)
            ->whereDate('recorded_at', $date)
            ->when($trashed, function ($query, $trashed) {
                match ($trashed) {
                    'only' => $query->onlyTrashed(),
                    'with' => $query->withTrashed(),
                    default => null,
                };
            })
            ->orderBy('recorded_at')
            ->get();
    }

    public function latestForUser(int $userId): ?GpsLog
    {
        return $this->query()->where('user_id', $userId)->latest('recorded_at')->first();
    }
}
