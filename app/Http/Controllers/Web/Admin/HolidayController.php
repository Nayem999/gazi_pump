<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\HolidaysExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreHolidayRequest;
use App\Http\Requests\Admin\UpdateHolidayRequest;
use App\Imports\HolidaysImport;
use App\Models\Holiday;
use App\Services\HolidayService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class HolidayController extends Controller
{
    public function __construct(private readonly HolidayService $holidays) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Holiday::class);

        return view('holidays.index', [
            'holidays' => $this->holidays->paginate($request->only(['search', 'year', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'year', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Holiday::class);

        return view('holidays.create');
    }

    public function store(StoreHolidayRequest $request): RedirectResponse
    {
        $this->holidays->create($request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday created successfully.');
    }

    public function edit(Holiday $holiday): View
    {
        $this->authorize('update', $holiday);

        return view('holidays.edit', ['holiday' => $holiday]);
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday): RedirectResponse
    {
        $this->holidays->update($holiday, $request->validated());

        return redirect()->route('holidays.index')->with('success', 'Holiday updated successfully.');
    }

    public function destroy(Holiday $holiday): RedirectResponse
    {
        $this->authorize('delete', $holiday);

        $this->holidays->delete($holiday);

        return redirect()->route('holidays.index')->with('success', 'Holiday moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $holiday = Holiday::withTrashed()->findOrFail($id);
        $this->authorize('restore', $holiday);

        $this->holidays->restore($id);

        return redirect()->route('holidays.index')->with('success', 'Holiday restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $holiday = Holiday::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $holiday);

        $this->holidays->forceDelete($id);

        return redirect()->route('holidays.index')->with('success', 'Holiday permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('holidays.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:holidays,id']]);

        $count = $this->holidays->bulkDelete($request->input('ids'));

        return redirect()->route('holidays.index')->with('success', "{$count} holiday(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('holidays.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:holidays,id']]);

        $count = $this->holidays->bulkRestore($request->input('ids'));

        return redirect()->route('holidays.index')->with('success', "{$count} holiday(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Holiday::class);

        $holidays = $this->holidays->paginate($request->only(['search', 'year', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new HolidaysExport($holidays), 'holidays-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Holiday::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new HolidaysImport, $request->file('file'));

        return redirect()->route('holidays.index')->with('success', 'Holidays imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Holiday::class);

        $holidays = $this->holidays->paginate($request->only(['search', 'year', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('holidays.print', ['holidays' => $holidays])
            ->stream('holidays-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Holiday $holiday): RedirectResponse
    {
        $this->authorize('update', $holiday);

        $this->holidays->update($holiday, ['status' => ! $holiday->status]);

        return back()->with('success', 'Holiday status updated.');
    }
}
