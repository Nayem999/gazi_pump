<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\VisitPlanStatus;
use App\Exports\VisitPlansExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreVisitPlanRequest;
use App\Http\Requests\Admin\UpdateVisitPlanRequest;
use App\Imports\VisitPlansImport;
use App\Models\Customer;
use App\Models\User;
use App\Models\VisitPlan;
use App\Services\VisitPlanService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class VisitPlanController extends Controller
{
    public function __construct(private readonly VisitPlanService $visitPlans) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', VisitPlan::class);

        return view('visit-plans.index', [
            'visitPlans' => $this->visitPlans->paginate($request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']), 15),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'statuses' => VisitPlanStatus::cases(),
            'filters' => $request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', VisitPlan::class);

        return view('visit-plans.create', [
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'statuses' => VisitPlanStatus::cases(),
        ]);
    }

    public function store(StoreVisitPlanRequest $request): RedirectResponse
    {
        $this->visitPlans->create($request->validated());

        return redirect()->route('visit-plans.index')->with('success', 'Visit plan created successfully.');
    }

    public function edit(VisitPlan $visitPlan): View
    {
        $this->authorize('update', $visitPlan);

        return view('visit-plans.edit', [
            'visitPlan' => $visitPlan,
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'statuses' => VisitPlanStatus::cases(),
        ]);
    }

    public function update(UpdateVisitPlanRequest $request, VisitPlan $visitPlan): RedirectResponse
    {
        $this->visitPlans->update($visitPlan, $request->validated());

        return redirect()->route('visit-plans.index')->with('success', 'Visit plan updated successfully.');
    }

    public function destroy(VisitPlan $visitPlan): RedirectResponse
    {
        $this->authorize('delete', $visitPlan);

        $this->visitPlans->delete($visitPlan);

        return redirect()->route('visit-plans.index')->with('success', 'Visit plan moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $visitPlan = VisitPlan::withTrashed()->findOrFail($id);
        $this->authorize('restore', $visitPlan);

        $this->visitPlans->restore($id);

        return redirect()->route('visit-plans.index')->with('success', 'Visit plan restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $visitPlan = VisitPlan::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $visitPlan);

        $this->visitPlans->forceDelete($id);

        return redirect()->route('visit-plans.index')->with('success', 'Visit plan permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('visit-plans.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:visit_plans,id']]);

        $count = $this->visitPlans->bulkDelete($request->input('ids'));

        return redirect()->route('visit-plans.index')->with('success', "{$count} visit plan(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('visit-plans.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:visit_plans,id']]);

        $count = $this->visitPlans->bulkRestore($request->input('ids'));

        return redirect()->route('visit-plans.index')->with('success', "{$count} visit plan(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', VisitPlan::class);

        $visitPlans = $this->visitPlans->paginate($request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new VisitPlansExport($visitPlans), 'visit-plans-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', VisitPlan::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new VisitPlansImport, $request->file('file'));

        return redirect()->route('visit-plans.index')->with('success', 'Visit plans imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', VisitPlan::class);

        $visitPlans = $this->visitPlans->paginate($request->only(['search', 'user_id', 'status', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('visit-plans.print', ['visitPlans' => $visitPlans])
            ->stream('visit-plans-'.now()->format('Y-m-d-His').'.pdf');
    }
}
