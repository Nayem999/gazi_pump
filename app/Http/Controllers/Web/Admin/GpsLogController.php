<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\GpsLogsExport;
use App\Http\Controllers\Controller;
use App\Models\GpsLog;
use App\Models\User;
use App\Services\GpsLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Read-mostly "Location History" view: pick a Sales Executive and a date to
 * see their trail on a map, the distance they covered, and every raw ping.
 * There's no create/edit here — pings only ever arrive via the mobile check-in
 * API (Api\V1\GpsLogController) — admins can only clean up bad readings
 * (delete/restore/permanent-delete), which is why this doesn't reuse the
 * $registerManagementRoutes closure from routes/web.php (same reasoning as
 * Attendance: no toggle-status, and here also no create/edit at all).
 */
class GpsLogController extends Controller
{
    public function __construct(private readonly GpsLogService $gpsLogs) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', GpsLog::class);

        $executives = $this->scopedExecutives($request->user());
        $selectedUser = $executives->firstWhere('id', (int) $request->input('user_id')) ?? $executives->first();
        $date = $request->input('date') ?: Carbon::today()->toDateString();
        $trashed = $request->input('trashed');

        $logs = $selectedUser
            ? $this->gpsLogs->historyForUserOnDate($selectedUser->id, $date, $trashed)
            : collect();

        return view('gps-logs.index', [
            'executives' => $executives,
            'selectedUser' => $selectedUser,
            'selectedDate' => $date,
            'logs' => $logs,
            'distanceKm' => $this->gpsLogs->distanceForLogs($logs),
            'filters' => $request->only(['user_id', 'date', 'trashed']),
        ]);
    }

    public function destroy(GpsLog $gpsLog): RedirectResponse
    {
        $this->authorize('delete', $gpsLog);

        $this->gpsLogs->delete($gpsLog);

        return back()->with('success', 'GPS log moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $gpsLog = GpsLog::withTrashed()->findOrFail($id);
        $this->authorize('restore', $gpsLog);

        $this->gpsLogs->restore($id);

        return back()->with('success', 'GPS log restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $gpsLog = GpsLog::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $gpsLog);

        $this->gpsLogs->forceDelete($id);

        return back()->with('success', 'GPS log permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('gps-logs.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:gps_logs,id']]);

        $count = $this->gpsLogs->bulkDelete($request->input('ids'));

        return back()->with('success', "{$count} GPS log(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('gps-logs.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:gps_logs,id']]);

        $count = $this->gpsLogs->bulkRestore($request->input('ids'));

        return back()->with('success', "{$count} GPS log(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', GpsLog::class);

        $logs = $this->logsForRequest($request);

        return Excel::download(new GpsLogsExport($logs), 'gps-logs-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', GpsLog::class);

        $logs = $this->logsForRequest($request);
        $user = $this->scopedExecutives($request->user())->firstWhere('id', (int) $request->input('user_id'));

        return Pdf::loadView('gps-logs.print', [
            'logs' => $logs,
            'user' => $user,
            'date' => $request->input('date') ?: Carbon::today()->toDateString(),
            'distanceKm' => $this->gpsLogs->distanceForLogs($logs),
        ])->stream('gps-logs-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function logsForRequest(Request $request): Collection
    {
        // Resolved against the viewer's own scoped executive list rather
        // than trusting the raw `user_id` query param directly — otherwise
        // export/print could be pointed at another territory's executive.
        $userId = $this->scopedExecutives($request->user())->firstWhere('id', (int) $request->input('user_id'))?->id;
        $date = $request->input('date') ?: Carbon::today()->toDateString();

        return $userId
            ? $this->gpsLogs->historyForUserOnDate($userId, $date, $request->input('trashed'))
            : collect();
    }

    /**
     * Sales Executives selectable in the "Location History" dropdown/export —
     * restricted to the viewer's own territories when they have any assigned,
     * or to themself alone when Sales Executive is their sole role.
     */
    private function scopedExecutives(User $viewer): Collection
    {
        $territoryIds = $viewer->territories->pluck('id')->all();

        return User::role('Sales Executive')
            ->when($territoryIds !== [], fn ($q) => $q->whereHas('territories', fn ($t) => $t->whereIn('territories.id', $territoryIds)))
            ->when($viewer->isSalesExecutiveOnly(), fn ($q) => $q->where('id', $viewer->id))
            ->orderBy('name')
            ->get();
    }
}
