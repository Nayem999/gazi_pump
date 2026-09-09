<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\VehiclesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVehicleRequest;
use App\Http\Requests\Admin\UpdateVehicleRequest;
use App\Imports\VehiclesImport;
use App\Models\Vehicle;
use App\Services\VehicleService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VehicleController extends Controller
{
    public function __construct(private readonly VehicleService $vehicles) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Vehicle::class);

        return view('vehicles.index', [
            'vehicles' => $this->vehicles->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Vehicle::class);

        return view('vehicles.create');
    }

    public function store(StoreVehicleRequest $request): RedirectResponse
    {
        $this->vehicles->create($request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Vehicle created successfully.');
    }

    public function edit(Vehicle $vehicle): View
    {
        $this->authorize('update', $vehicle);

        return view('vehicles.edit', ['vehicle' => $vehicle]);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): RedirectResponse
    {
        $this->vehicles->update($vehicle, $request->validated());

        return redirect()->route('vehicles.index')->with('success', 'Vehicle updated successfully.');
    }

    public function destroy(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('delete', $vehicle);

        $this->vehicles->delete($vehicle);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        $this->authorize('restore', $vehicle);

        $this->vehicles->restore($id);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $vehicle = Vehicle::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $vehicle);

        $this->vehicles->forceDelete($id);

        return redirect()->route('vehicles.index')->with('success', 'Vehicle permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('vehicles.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:vehicles,id']]);

        $count = $this->vehicles->bulkDelete($request->input('ids'));

        return redirect()->route('vehicles.index')->with('success', "{$count} vehicle(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('vehicles.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:vehicles,id']]);

        $count = $this->vehicles->bulkRestore($request->input('ids'));

        return redirect()->route('vehicles.index')->with('success', "{$count} vehicle(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Vehicle::class);

        $vehicles = $this->vehicles->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new VehiclesExport($vehicles), 'vehicles-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Vehicle::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new VehiclesImport, $request->file('file'));

        return redirect()->route('vehicles.index')->with('success', 'Vehicles imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Vehicle::class);

        $vehicles = $this->vehicles->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('vehicles.print', ['vehicles' => $vehicles])
            ->stream('vehicles-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Vehicle $vehicle): RedirectResponse
    {
        $this->authorize('update', $vehicle);

        $this->vehicles->update($vehicle, ['status' => ! $vehicle->status]);

        return back()->with('success', 'Vehicle status updated.');
    }
}
