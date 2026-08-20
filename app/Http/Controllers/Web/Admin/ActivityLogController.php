<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\ActivityLogExport;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Activitylog\Models\Activity;

/**
 * Read-only browsing of the audit trail. Activity is a package model (not
 * under App\Models), so — like Reports — authorization is a direct
 * permission check per action rather than a Policy class.
 */
class ActivityLogController extends Controller
{
    /**
     * Unlike Reports' aggregate rows (bounded by user/territory count), the
     * audit trail grows without limit. Export/print are capped to the most
     * recent N matching rows rather than "all of them" — anyone needing
     * more should narrow the date range first. The two limits differ
     * because DomPDF's memory use grows much faster than Excel's per row:
     * measured directly against this table, ~2,000 rows exhausts PHP's
     * 512MB limit rendering the PDF, while Excel handled ~10,000 rows fine.
     */
    private const MAX_EXPORT_ROWS = 5000;

    private const MAX_PRINT_ROWS = 1000;

    public function __construct(private readonly ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('activity-log.view'), 403);

        $filters = $request->only(['search', 'log_name', 'event', 'causer_id', 'date_from', 'date_to']);

        return view('activity-log.index', [
            'activities' => $this->activityLog->paginate($filters, 20),
            'logNames' => $this->activityLog->logNames(),
            'events' => $this->activityLog->events(),
            'causers' => User::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function show(Request $request, Activity $activity): View
    {
        abort_unless($request->user()?->can('activity-log.view'), 403);

        return view('activity-log.show', [
            'activity' => $activity->load(['causer', 'subject']),
        ]);
    }

    public function export(Request $request): mixed
    {
        abort_unless($request->user()?->can('activity-log.export'), 403);

        $filters = $request->only(['search', 'log_name', 'event', 'causer_id', 'date_from', 'date_to']);
        $rows = $this->activityLog->paginate($filters, self::MAX_EXPORT_ROWS)->getCollection();

        return Excel::download(new ActivityLogExport($rows), 'activity-log-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function print(Request $request): mixed
    {
        abort_unless($request->user()?->can('activity-log.print'), 403);

        $filters = $request->only(['search', 'log_name', 'event', 'causer_id', 'date_from', 'date_to']);
        $page = $this->activityLog->paginate($filters, self::MAX_PRINT_ROWS);

        return Pdf::loadView('activity-log.print', [
            'activities' => $page->getCollection(),
            'totalMatching' => $page->total(),
            'limit' => self::MAX_PRINT_ROWS,
        ])->stream('activity-log-'.now()->format('Y-m-d-His').'.pdf');
    }
}
