<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DivisionsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDivisionRequest;
use App\Http\Requests\Admin\UpdateDivisionRequest;
use App\Imports\DivisionsImport;
use App\Models\Division;
use App\Services\DivisionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DivisionController extends Controller
{
    public function __construct(private readonly DivisionService $divisions) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Division::class);

        return view('divisions.index', [
            'divisions' => $this->divisions->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Division::class);

        return view('divisions.create');
    }

    public function store(StoreDivisionRequest $request): RedirectResponse
    {
        $this->divisions->create($request->validated());

        return redirect()->route('divisions.index')->with('success', 'Division created successfully.');
    }

    public function edit(Division $division): View
    {
        $this->authorize('update', $division);

        return view('divisions.edit', [
            'division' => $division,
        ]);
    }

    public function update(UpdateDivisionRequest $request, Division $division): RedirectResponse
    {
        $this->divisions->update($division, $request->validated());

        return redirect()->route('divisions.index')->with('success', 'Division updated successfully.');
    }

    public function destroy(Division $division): RedirectResponse
    {
        $this->authorize('delete', $division);

        $this->divisions->delete($division);

        return redirect()->route('divisions.index')->with('success', 'Division moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $division = Division::withTrashed()->findOrFail($id);
        $this->authorize('restore', $division);

        $this->divisions->restore($id);

        return redirect()->route('divisions.index')->with('success', 'Division restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $division = Division::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $division);

        $this->divisions->forceDelete($id);

        return redirect()->route('divisions.index')->with('success', 'Division permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('divisions.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:divisions,id']]);

        $count = $this->divisions->bulkDelete($request->input('ids'));

        return redirect()->route('divisions.index')->with('success', "{$count} division(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('divisions.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:divisions,id']]);

        $count = $this->divisions->bulkRestore($request->input('ids'));

        return redirect()->route('divisions.index')->with('success', "{$count} division(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Division::class);

        $divisions = $this->divisions->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new DivisionsExport($divisions), 'divisions-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Division::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new DivisionsImport, $request->file('file'));

        return redirect()->route('divisions.index')->with('success', 'Divisions imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Division::class);

        $divisions = $this->divisions->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('divisions.print', ['divisions' => $divisions])
            ->stream('divisions-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Division $division): RedirectResponse
    {
        $this->authorize('update', $division);

        $this->divisions->update($division, ['status' => ! $division->status]);

        return back()->with('success', 'Division status updated.');
    }
}
