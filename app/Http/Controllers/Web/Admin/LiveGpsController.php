<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Territory;
use App\Models\User;
use App\Services\LiveGpsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * A polling-refreshed map, not a websocket-pushed one — nothing in this
 * stack sets up broadcasting (no Reverb/Pusher), so "live" here means the
 * browser re-fetches positions() on an interval and the map is redrawn from
 * whatever's freshest in gps_logs at that moment.
 */
class LiveGpsController extends Controller
{
    public function __construct(private readonly LiveGpsService $liveGps) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('live-gps.view'), 403);

        $filters = $request->only(['territory_id', 'user_id']);

        return view('live-gps.index', [
            'territories' => Territory::orderBy('name')->get(),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'filters' => $filters,
            'staleAfterMinutes' => (int) config('sfa.live_gps.stale_after_minutes'),
        ]);
    }

    public function positions(Request $request): JsonResponse
    {
        abort_unless($request->user()?->can('live-gps.view'), 403);

        $filters = $request->only(['territory_id', 'user_id']);
        $positions = $this->liveGps->latestPositions($filters);

        return response()->json([
            'data' => $positions->map(fn (object $row) => [
                'userId' => $row->user->id,
                'name' => $row->user->name,
                'employeeId' => $row->user->employee_id,
                'territory' => $row->user->territory?->name,
                'lat' => $row->lat,
                'lng' => $row->lng,
                'recordedAt' => $row->recorded_at->toIso8601String(),
                'secondsAgo' => $row->recorded_at->diffInSeconds(now()),
                'speed' => $row->speed,
                'batteryLevel' => $row->battery_level,
                'isStale' => $row->is_stale,
            ])->values(),
        ]);
    }
}
