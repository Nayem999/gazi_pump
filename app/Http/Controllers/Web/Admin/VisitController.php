<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\VisitsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVisitRequest;
use App\Http\Requests\Admin\UpdateVisitRequest;
use App\Imports\VisitsImport;
use App\Models\Customer;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VisitController extends Controller
{
    public function __construct(private readonly VisitService $visits) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Visit::class);

        return view('visits.index', [
            'visits' => $this->visits->paginate($request->only(['search', 'user_id', 'customer_id', 'date_from', 'date_to', 'trashed']), 15),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'filters' => $request->only(['search', 'user_id', 'customer_id', 'date_from', 'date_to', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Visit::class);

        return view('visits.create', [
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function store(StoreVisitRequest $request): RedirectResponse
    {
        $this->visits->create($request->validated());

        return redirect()->route('visits.index')->with('success', 'Visit recorded successfully.');
    }

    public function show(Visit $visit): View
    {
        $this->authorize('view', $visit);

        return view('visits.show', ['visit' => $visit->load(['user', 'customer', 'visitPlan'])]);
    }

    public function edit(Visit $visit): View
    {
        $this->authorize('update', $visit);

        return view('visits.edit', [
            'visit' => $visit,
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateVisitRequest $request, Visit $visit): RedirectResponse
    {
        $this->visits->update($visit, $request->validated());

        return redirect()->route('visits.index')->with('success', 'Visit updated successfully.');
    }

    public function destroy(Visit $visit): RedirectResponse
    {
        $this->authorize('delete', $visit);

        $this->visits->delete($visit);

        return redirect()->route('visits.index')->with('success', 'Visit moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $visit = Visit::withTrashed()->findOrFail($id);
        $this->authorize('restore', $visit);

        $this->visits->restore($id);

        return redirect()->route('visits.index')->with('success', 'Visit restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $visit = Visit::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $visit);

        $this->visits->forceDelete($id);

        return redirect()->route('visits.index')->with('success', 'Visit permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('visits.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:visits,id']]);

        $count = $this->visits->bulkDelete($request->input('ids'));

        return redirect()->route('visits.index')->with('success', "{$count} visit(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('visits.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:visits,id']]);

        $count = $this->visits->bulkRestore($request->input('ids'));

        return redirect()->route('visits.index')->with('success', "{$count} visit(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Visit::class);

        $visits = $this->visits->paginate($request->only(['search', 'user_id', 'customer_id', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new VisitsExport($visits), 'visits-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Visit::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new VisitsImport, $request->file('file'));

        return redirect()->route('visits.index')->with('success', 'Visits imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Visit::class);

        $visits = $this->visits->paginate($request->only(['search', 'user_id', 'customer_id', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('visits.print', ['visits' => $visits])
            ->stream('visits-'.now()->format('Y-m-d-His').'.pdf');
    }
}
