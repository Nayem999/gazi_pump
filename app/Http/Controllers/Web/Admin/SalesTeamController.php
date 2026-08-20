<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\SalesTeamsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSalesTeamRequest;
use App\Http\Requests\Admin\UpdateSalesTeamRequest;
use App\Imports\SalesTeamsImport;
use App\Models\SalesTeam;
use App\Services\SalesTeamService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesTeamController extends Controller
{
    public function __construct(private readonly SalesTeamService $salesTeams) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesTeam::class);

        return view('sales-teams.index', [
            'salesTeams' => $this->salesTeams->paginate($request->only(['search', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SalesTeam::class);

        return view('sales-teams.create');
    }

    public function store(StoreSalesTeamRequest $request): RedirectResponse
    {
        $this->salesTeams->create($request->validated());

        return redirect()->route('sales-teams.index')->with('success', 'Sales team created successfully.');
    }

    public function edit(SalesTeam $salesTeam): View
    {
        $this->authorize('update', $salesTeam);

        return view('sales-teams.edit', ['salesTeam' => $salesTeam]);
    }

    public function update(UpdateSalesTeamRequest $request, SalesTeam $salesTeam): RedirectResponse
    {
        $this->salesTeams->update($salesTeam, $request->validated());

        return redirect()->route('sales-teams.index')->with('success', 'Sales team updated successfully.');
    }

    public function destroy(SalesTeam $salesTeam): RedirectResponse
    {
        $this->authorize('delete', $salesTeam);

        $this->salesTeams->delete($salesTeam);

        return redirect()->route('sales-teams.index')->with('success', 'Sales team moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $salesTeam = SalesTeam::withTrashed()->findOrFail($id);
        $this->authorize('restore', $salesTeam);

        $this->salesTeams->restore($id);

        return redirect()->route('sales-teams.index')->with('success', 'Sales team restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $salesTeam = SalesTeam::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $salesTeam);

        $this->salesTeams->forceDelete($id);

        return redirect()->route('sales-teams.index')->with('success', 'Sales team permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('sales-teams.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:sales_teams,id']]);

        $count = $this->salesTeams->bulkDelete($request->input('ids'));

        return redirect()->route('sales-teams.index')->with('success', "{$count} sales team(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('sales-teams.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:sales_teams,id']]);

        $count = $this->salesTeams->bulkRestore($request->input('ids'));

        return redirect()->route('sales-teams.index')->with('success', "{$count} sales team(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', SalesTeam::class);

        $salesTeams = $this->salesTeams->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new SalesTeamsExport($salesTeams), 'sales-teams-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', SalesTeam::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new SalesTeamsImport, $request->file('file'));

        return redirect()->route('sales-teams.index')->with('success', 'Sales teams imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', SalesTeam::class);

        $salesTeams = $this->salesTeams->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('sales-teams.print', ['salesTeams' => $salesTeams])
            ->stream('sales-teams-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(SalesTeam $salesTeam): RedirectResponse
    {
        $this->authorize('update', $salesTeam);

        $this->salesTeams->update($salesTeam, ['status' => ! $salesTeam->status]);

        return back()->with('success', 'Sales team status updated.');
    }
}
