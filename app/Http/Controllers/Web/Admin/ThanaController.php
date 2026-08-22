<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\ThanasExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreThanaRequest;
use App\Http\Requests\Admin\UpdateThanaRequest;
use App\Imports\ThanasImport;
use App\Models\Division;
use App\Models\Thana;
use App\Services\ThanaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ThanaController extends Controller
{
    public function __construct(private readonly ThanaService $thanas) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Thana::class);

        return view('thanas.index', [
            'thanas' => $this->thanas->paginate($request->only(['search', 'division_id', 'district_id', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'division_id', 'district_id', 'status', 'trashed']),
            'divisions' => Division::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Thana::class);

        return view('thanas.create', [
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreThanaRequest $request): RedirectResponse
    {
        $this->thanas->create($request->validated());

        return redirect()->route('thanas.index')->with('success', 'Thana created successfully.');
    }

    public function edit(Thana $thana): View
    {
        $this->authorize('update', $thana);

        return view('thanas.edit', [
            'thana' => $thana,
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
            'currentDivisionId' => $thana->district->division_id,
        ]);
    }

    public function update(UpdateThanaRequest $request, Thana $thana): RedirectResponse
    {
        $this->thanas->update($thana, $request->validated());

        return redirect()->route('thanas.index')->with('success', 'Thana updated successfully.');
    }

    public function destroy(Thana $thana): RedirectResponse
    {
        $this->authorize('delete', $thana);

        $this->thanas->delete($thana);

        return redirect()->route('thanas.index')->with('success', 'Thana moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $thana = Thana::withTrashed()->findOrFail($id);
        $this->authorize('restore', $thana);

        $this->thanas->restore($id);

        return redirect()->route('thanas.index')->with('success', 'Thana restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $thana = Thana::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $thana);

        $this->thanas->forceDelete($id);

        return redirect()->route('thanas.index')->with('success', 'Thana permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('thanas.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:thanas,id']]);

        $count = $this->thanas->bulkDelete($request->input('ids'));

        return redirect()->route('thanas.index')->with('success', "{$count} thana(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('thanas.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:thanas,id']]);

        $count = $this->thanas->bulkRestore($request->input('ids'));

        return redirect()->route('thanas.index')->with('success', "{$count} thana(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Thana::class);

        $thanas = $this->thanas->paginate($request->only(['search', 'division_id', 'district_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new ThanasExport($thanas), 'thanas-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Thana::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new ThanasImport, $request->file('file'));

        return redirect()->route('thanas.index')->with('success', 'Thanas imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Thana::class);

        $thanas = $this->thanas->paginate($request->only(['search', 'division_id', 'district_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('thanas.print', ['thanas' => $thanas])
            ->stream('thanas-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Thana $thana): RedirectResponse
    {
        $this->authorize('update', $thana);

        $this->thanas->update($thana, ['status' => ! $thana->status]);

        return back()->with('success', 'Thana status updated.');
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Thana::class);

        if (! $request->integer('district_id') && ! $request->integer('division_id')) {
            return response()->json([]);
        }

        return response()->json(
            Thana::query()
                ->where('status', true)
                ->when($request->integer('district_id'), fn ($query, $id) => $query->where('district_id', $id))
                ->when($request->integer('division_id'), fn ($query, $id) => $query->whereHas('district', fn ($dq) => $dq->where('division_id', $id)))
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }
}
