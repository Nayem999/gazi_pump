<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DriversExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDriverRequest;
use App\Http\Requests\Admin\UpdateDriverRequest;
use App\Imports\DriversImport;
use App\Models\Driver;
use App\Services\DriverService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DriverController extends Controller
{
    public function __construct(private readonly DriverService $drivers) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Driver::class);

        return view('drivers.index', [
            'drivers' => $this->drivers->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Driver::class);

        return view('drivers.create');
    }

    public function store(StoreDriverRequest $request): RedirectResponse
    {
        $this->drivers->create($request->validated());

        return redirect()->route('drivers.index')->with('success', 'Driver created successfully.');
    }

    public function edit(Driver $driver): View
    {
        $this->authorize('update', $driver);

        return view('drivers.edit', ['driver' => $driver]);
    }

    public function update(UpdateDriverRequest $request, Driver $driver): RedirectResponse
    {
        $this->drivers->update($driver, $request->validated());

        return redirect()->route('drivers.index')->with('success', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver): RedirectResponse
    {
        $this->authorize('delete', $driver);

        $this->drivers->delete($driver);

        return redirect()->route('drivers.index')->with('success', 'Driver moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $driver = Driver::withTrashed()->findOrFail($id);
        $this->authorize('restore', $driver);

        $this->drivers->restore($id);

        return redirect()->route('drivers.index')->with('success', 'Driver restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $driver = Driver::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $driver);

        $this->drivers->forceDelete($id);

        return redirect()->route('drivers.index')->with('success', 'Driver permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('drivers.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:drivers,id']]);

        $count = $this->drivers->bulkDelete($request->input('ids'));

        return redirect()->route('drivers.index')->with('success', "{$count} driver(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('drivers.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:drivers,id']]);

        $count = $this->drivers->bulkRestore($request->input('ids'));

        return redirect()->route('drivers.index')->with('success', "{$count} driver(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Driver::class);

        $drivers = $this->drivers->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new DriversExport($drivers), 'drivers-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Driver::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new DriversImport, $request->file('file'));

        return redirect()->route('drivers.index')->with('success', 'Drivers imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Driver::class);

        $drivers = $this->drivers->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('drivers.print', ['drivers' => $drivers])
            ->stream('drivers-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Driver $driver): RedirectResponse
    {
        $this->authorize('update', $driver);

        $this->drivers->update($driver, ['status' => ! $driver->status]);

        return back()->with('success', 'Driver status updated.');
    }
}
