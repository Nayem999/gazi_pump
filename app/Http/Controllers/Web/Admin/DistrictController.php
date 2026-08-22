<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\DistrictsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreDistrictRequest;
use App\Http\Requests\Admin\UpdateDistrictRequest;
use App\Imports\DistrictsImport;
use App\Models\District;
use App\Models\Division;
use App\Services\DistrictService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class DistrictController extends Controller
{
    public function __construct(private readonly DistrictService $districts) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', District::class);

        return view('districts.index', [
            'districts' => $this->districts->paginate($request->only(['search', 'division_id', 'status', 'trashed']), 15),
            'filters' => $request->only(['search', 'division_id', 'status', 'trashed']),
            'divisions' => Division::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', District::class);

        return view('districts.create', [
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDistrictRequest $request): RedirectResponse
    {
        $this->districts->create($request->validated());

        return redirect()->route('districts.index')->with('success', 'District created successfully.');
    }

    public function edit(District $district): View
    {
        $this->authorize('update', $district);

        return view('districts.edit', [
            'district' => $district,
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateDistrictRequest $request, District $district): RedirectResponse
    {
        $this->districts->update($district, $request->validated());

        return redirect()->route('districts.index')->with('success', 'District updated successfully.');
    }

    public function destroy(District $district): RedirectResponse
    {
        $this->authorize('delete', $district);

        $this->districts->delete($district);

        return redirect()->route('districts.index')->with('success', 'District moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $district = District::withTrashed()->findOrFail($id);
        $this->authorize('restore', $district);

        $this->districts->restore($id);

        return redirect()->route('districts.index')->with('success', 'District restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $district = District::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $district);

        $this->districts->forceDelete($id);

        return redirect()->route('districts.index')->with('success', 'District permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('districts.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:districts,id']]);

        $count = $this->districts->bulkDelete($request->input('ids'));

        return redirect()->route('districts.index')->with('success', "{$count} district(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('districts.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:districts,id']]);

        $count = $this->districts->bulkRestore($request->input('ids'));

        return redirect()->route('districts.index')->with('success', "{$count} district(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', District::class);

        $districts = $this->districts->paginate($request->only(['search', 'division_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new DistrictsExport($districts), 'districts-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', District::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new DistrictsImport, $request->file('file'));

        return redirect()->route('districts.index')->with('success', 'Districts imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', District::class);

        $districts = $this->districts->paginate($request->only(['search', 'division_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('districts.print', ['districts' => $districts])
            ->stream('districts-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(District $district): RedirectResponse
    {
        $this->authorize('update', $district);

        $this->districts->update($district, ['status' => ! $district->status]);

        return back()->with('success', 'District status updated.');
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', District::class);

        if (! $request->integer('division_id')) {
            return response()->json([]);
        }

        return response()->json(
            District::query()
                ->where('status', true)
                ->when($request->integer('division_id'), fn ($query, $id) => $query->where('division_id', $id))
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }
}
