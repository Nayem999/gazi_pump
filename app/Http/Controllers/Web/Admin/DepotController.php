<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DepotsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDepotRequest;
use App\Http\Requests\Admin\UpdateDepotRequest;
use App\Imports\DepotsImport;
use App\Models\Depot;
use App\Models\Territory;
use App\Services\DepotService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DepotController extends Controller
{
    public function __construct(private readonly DepotService $depots) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Depot::class);

        return view('depots.index', [
            'depots' => $this->depots->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Depot::class);

        return view('depots.create', ['territories' => Territory::orderBy('name')->get()]);
    }

    public function store(StoreDepotRequest $request): RedirectResponse
    {
        $this->depots->create($request->validated());

        return redirect()->route('depots.index')->with('success', 'Depot created successfully.');
    }

    public function edit(Depot $depot): View
    {
        $this->authorize('update', $depot);

        return view('depots.edit', ['depot' => $depot, 'territories' => Territory::orderBy('name')->get()]);
    }

    public function update(UpdateDepotRequest $request, Depot $depot): RedirectResponse
    {
        $this->depots->update($depot, $request->validated());

        return redirect()->route('depots.index')->with('success', 'Depot updated successfully.');
    }

    public function destroy(Depot $depot): RedirectResponse
    {
        $this->authorize('delete', $depot);

        $this->depots->delete($depot);

        return redirect()->route('depots.index')->with('success', 'Depot moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $depot = Depot::withTrashed()->findOrFail($id);
        $this->authorize('restore', $depot);

        $this->depots->restore($id);

        return redirect()->route('depots.index')->with('success', 'Depot restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $depot = Depot::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $depot);

        $this->depots->forceDelete($id);

        return redirect()->route('depots.index')->with('success', 'Depot permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('depots.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:depots,id']]);

        $count = $this->depots->bulkDelete($request->input('ids'));

        return redirect()->route('depots.index')->with('success', "{$count} depot(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('depots.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:depots,id']]);

        $count = $this->depots->bulkRestore($request->input('ids'));

        return redirect()->route('depots.index')->with('success', "{$count} depot(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Depot::class);

        $depots = $this->depots->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new DepotsExport($depots), 'depots-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Depot::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new DepotsImport, $request->file('file'));

        return redirect()->route('depots.index')->with('success', 'Depots imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Depot::class);

        $depots = $this->depots->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('depots.print', ['depots' => $depots])
            ->stream('depots-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Depot $depot): RedirectResponse
    {
        $this->authorize('update', $depot);

        $this->depots->update($depot, ['status' => ! $depot->status]);

        return back()->with('success', 'Depot status updated.');
    }
}
