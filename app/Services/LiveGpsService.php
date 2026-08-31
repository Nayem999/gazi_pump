<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\GpsLog;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Each Sales Executive's most recent GPS ping, for the live map — not
 * "real-time" via websockets/broadcasting (nothing in this stack sets that
 * up), just each ping's own timestamp read fresh on every poll, with a
 * config-driven cutoff marking a position as stale rather than live.
 */
class LiveGpsService
{
    /**
     * @param  array{territory_id?: string, user_id?: string}  $filters
     * @return Collection<int, object>
     */
    public function latestPositions(array $filters, User $viewer): Collection
    {
        $cutoff = Carbon::now()->subMinutes((int) config('sfa.live_gps.stale_after_minutes'));
        $territoryIds = $viewer->territories->pluck('id')->all();

        $executives = User::role('Sales Executive')
            // The viewer's own territories (or themself, if they're a plain
            // Sales Executive) are enforced unconditionally — the filters
            // below only narrow further within what's already visible.
            ->when($territoryIds !== [], fn ($query) => $query->whereHas(
                'territories', fn ($t) => $t->whereIn('territories.id', $territoryIds)
            ))
            ->when($viewer->isSalesExecutiveOnly(), fn ($query) => $query->where('id', $viewer->id))
            ->when($filters['territory_id'] ?? null, fn ($query, $territoryId) => $query->whereHas(
                'territories', fn ($t) => $t->where('territories.id', $territoryId)
            ))
            ->when($filters['user_id'] ?? null, fn ($query, $userId) => $query->where('id', $userId))
            ->with('territories')
            ->get();

        return $executives
            ->map(function (User $user) use ($cutoff) {
                $log = GpsLog::where('user_id', $user->id)->latest('recorded_at')->first();

                if (! $log) {
                    return null;
                }

                return (object) [
                    'user' => $user,
                    'lat' => (float) $log->lat,
                    'lng' => (float) $log->lng,
                    'recorded_at' => $log->recorded_at,
                    'accuracy' => $log->accuracy !== null ? (float) $log->accuracy : null,
                    'speed' => $log->speed !== null ? (float) $log->speed : null,
                    'battery_level' => $log->battery_level,
                    'is_stale' => $log->recorded_at->lt($cutoff),
                ];
            })
            ->filter()
            ->sortBy(fn (object $row) => $row->user->name)
            ->values();
    }
}
