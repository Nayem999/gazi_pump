<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\TargetsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTargetRequest;
use App\Http\Requests\Admin\UpdateTargetRequest;
use App\Imports\TargetsImport;
use App\Models\Product;
use App\Models\Target;
use App\Models\User;
use App\Services\TargetService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class TargetController extends Controller
{
    public function __construct(private readonly TargetService $targets) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Target::class);

        return view('targets.index', [
            'targets' => $this->targets->paginate($request->only(['search', 'user_id', 'month', 'year', 'grade', 'trashed']), 15, $request->user()),
            'executives' => $this->scopedExecutives($request->user()),
            'filters' => $request->only(['search', 'user_id', 'month', 'year', 'grade', 'trashed']),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Target::class);

        return view('targets.create', [
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'products' => Product::where('status', true)->visibleTo($request->user())->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTargetRequest $request): RedirectResponse
    {
        $this->targets->create($request->validated());

        return redirect()->route('targets.index')->with('success', 'Target created and achievement calculated.');
    }

    public function show(Target $target): View
    {
        $this->authorize('view', $target);

        $target->load(['user', 'achievement', 'items.product']);

        return view('targets.show', [
            'target' => $target,
            'productAchievements' => $target->isProductWise() ? $this->targets->productAchievements($target) : collect(),
        ]);
    }

    public function edit(Request $request, Target $target): View
    {
        $this->authorize('update', $target);

        $target->load('items');

        return view('targets.edit', [
            'target' => $target,
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'products' => Product::where('status', true)->visibleTo($request->user())
                ->orWhereIn('id', $target->items->pluck('product_id'))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTargetRequest $request, Target $target): RedirectResponse
    {
        $this->targets->update($target, $request->validated());

        return redirect()->route('targets.index')->with('success', 'Target updated and achievement recalculated.');
    }

    public function recalculate(Target $target): RedirectResponse
    {
        $this->authorize('update', $target);

        $this->targets->recalculate($target);

        return redirect()->back()->with('success', 'Achievement recalculated.');
    }

    public function destroy(Target $target): RedirectResponse
    {
        $this->authorize('delete', $target);

        $this->targets->delete($target);

        return redirect()->route('targets.index')->with('success', 'Target moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $target = Target::withTrashed()->findOrFail($id);
        $this->authorize('restore', $target);

        $this->targets->restore($id);

        return redirect()->route('targets.index')->with('success', 'Target restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $target = Target::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $target);

        $this->targets->forceDelete($id);

        return redirect()->route('targets.index')->with('success', 'Target permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('targets.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:targets,id']]);

        $count = $this->targets->bulkDelete($request->input('ids'));

        return redirect()->route('targets.index')->with('success', "{$count} target(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('targets.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:targets,id']]);

        $count = $this->targets->bulkRestore($request->input('ids'));

        return redirect()->route('targets.index')->with('success', "{$count} target(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Target::class);

        $targets = $this->targets->paginate($request->only(['search', 'user_id', 'month', 'year', 'grade', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Excel::download(new TargetsExport($targets), 'targets-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Target::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new TargetsImport, $request->file('file'));

        return redirect()->route('targets.index')->with('success', 'Targets imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Target::class);

        $targets = $this->targets->paginate($request->only(['search', 'user_id', 'month', 'year', 'grade', 'trashed']), PHP_INT_MAX, $request->user())->getCollection();

        return Pdf::loadView('targets.print', ['targets' => $targets])
            ->stream('targets-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * Sales Executives selectable in the filter dropdown — restricted to the
     * viewer's own territories when they have any assigned, or to themself
     * alone when Sales Executive is their sole role.
     */
    private function scopedExecutives(User $viewer): Collection
    {
        $territoryIds = $viewer->territories->pluck('id')->all();

        return User::role('Sales Executive')
            ->when($territoryIds !== [], fn ($q) => $q->whereHas('territories', fn ($t) => $t->whereIn('territories.id', $territoryIds)))
            ->when($viewer->isSalesExecutiveOnly(), fn ($q) => $q->where('id', $viewer->id))
            ->orderBy('name')
            ->get();
    }
}
