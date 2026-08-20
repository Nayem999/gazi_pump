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

        $executives = User::role('Sales Executive')->orderBy('name')->get();
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
        $user = User::find((int) $request->input('user_id'));

        return Pdf::loadView('gps-logs.print', [
            'logs' => $logs,
            'user' => $user,
            'date' => $request->input('date') ?: Carbon::today()->toDateString(),
            'distanceKm' => $this->gpsLogs->distanceForLogs($logs),
        ])->stream('gps-logs-'.now()->format('Y-m-d-His').'.pdf');
    }

    private function logsForRequest(Request $request): Collection
    {
        $userId = (int) $request->input('user_id');
        $date = $request->input('date') ?: Carbon::today()->toDateString();

        return $userId
            ? $this->gpsLogs->historyForUserOnDate($userId, $date, $request->input('trashed'))
            : collect();
    }
}
