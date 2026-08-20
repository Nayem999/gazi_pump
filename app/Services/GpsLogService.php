<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\DistanceCalculator;
use App\Models\GpsLog;
use App\Models\User;
use App\Repositories\Contracts\GpsLogRepositoryInterface;
use Illuminate\Support\Collection;

class GpsLogService extends BaseCrudService
{
    public function __construct(private readonly GpsLogRepositoryInterface $gpsLogs)
    {
        parent::__construct($gpsLogs);
    }

    /**
     * @param  array<int, array{lat: float, lng: float, recorded_at: string, accuracy?: float|null, speed?: float|null, battery_level?: int|null}>  $logs
     * @return Collection<int, GpsLog>
     */
    public function ingest(User $user, array $logs): Collection
    {
        return collect($logs)->map(fn (array $log) => $this->create([
            'user_id' => $user->id,
            'lat' => $log['lat'],
            'lng' => $log['lng'],
            'recorded_at' => $log['recorded_at'],
            'accuracy' => $log['accuracy'] ?? null,
            'speed' => $log['speed'] ?? null,
            'battery_level' => $log['battery_level'] ?? null,
        ]));
    }

    /**
     * @return Collection<int, GpsLog>
     */
    public function historyForUserOnDate(int $userId, string $date, ?string $trashed = null): Collection
    {
        return $this->gpsLogs->historyForUserOnDate($userId, $date, $trashed);
    }

    /**
     * Total distance traveled across an already-fetched, time-ordered set of
     * pings — takes the collection rather than re-querying so the figure
     * always matches exactly what's currently displayed (e.g. under a
     * "trashed" filter).
     *
     * @param  Collection<int, GpsLog>  $logs
     */
    public function distanceForLogs(Collection $logs): float
    {
        $points = $logs->map(fn (GpsLog $log) => ['lat' => (float) $log->lat, 'lng' => (float) $log->lng]);

        return round(DistanceCalculator::totalDistanceKm($points), 2);
    }

    public function latestLocation(int $userId): ?GpsLog
    {
        return $this->gpsLogs->latestForUser($userId);
    }
}
