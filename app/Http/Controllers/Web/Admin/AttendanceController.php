<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\AttendanceStatus;
use App\Exports\AttendancesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAttendanceRequest;
use App\Http\Requests\Admin\UpdateAttendanceRequest;
use App\Imports\AttendancesImport;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AttendanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendances) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Attendance::class);

        return view('attendance.index', [
            'attendances' => $this->attendances->paginate($request->only(['search', 'status', 'date_from', 'date_to', 'trashed']), 15),
            'statuses' => AttendanceStatus::cases(),
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Attendance::class);

        return view('attendance.create', [
            'users' => User::role('Sales Executive')->orderBy('name')->get(),
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function store(StoreAttendanceRequest $request): RedirectResponse
    {
        $this->attendances->create($request->validated());

        return redirect()->route('attendance.index')->with('success', 'Attendance recorded successfully.');
    }

    public function show(Attendance $attendance): View
    {
        $this->authorize('view', $attendance);

        return view('attendance.show', ['attendance' => $attendance->load('user')]);
    }

    public function edit(Attendance $attendance): View
    {
        $this->authorize('update', $attendance);

        return view('attendance.edit', [
            'attendance' => $attendance,
            'statuses' => AttendanceStatus::cases(),
        ]);
    }

    public function update(UpdateAttendanceRequest $request, Attendance $attendance): RedirectResponse
    {
        $this->attendances->update($attendance, $request->validated());

        return redirect()->route('attendance.index')->with('success', 'Attendance updated successfully.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->authorize('delete', $attendance);

        $this->attendances->delete($attendance);

        return redirect()->route('attendance.index')->with('success', 'Attendance record moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $attendance = Attendance::withTrashed()->findOrFail($id);
        $this->authorize('restore', $attendance);

        $this->attendances->restore($id);

        return redirect()->route('attendance.index')->with('success', 'Attendance record restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $attendance = Attendance::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $attendance);

        $this->attendances->forceDelete($id);

        return redirect()->route('attendance.index')->with('success', 'Attendance record permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('attendance.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:attendances,id']]);

        $count = $this->attendances->bulkDelete($request->input('ids'));

        return redirect()->route('attendance.index')->with('success', "{$count} attendance record(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('attendance.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:attendances,id']]);

        $count = $this->attendances->bulkRestore($request->input('ids'));

        return redirect()->route('attendance.index')->with('success', "{$count} attendance record(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Attendance::class);

        $attendances = $this->attendances->paginate($request->only(['search', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new AttendancesExport($attendances), 'attendance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Attendance::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new AttendancesImport, $request->file('file'));

        return redirect()->route('attendance.index')->with('success', 'Attendance imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Attendance::class);

        $attendances = $this->attendances->paginate($request->only(['search', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('attendance.print', ['attendances' => $attendances])
            ->stream('attendance-'.now()->format('Y-m-d-His').'.pdf');
    }
}
