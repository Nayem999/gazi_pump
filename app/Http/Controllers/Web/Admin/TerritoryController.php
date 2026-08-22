<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\TerritoriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTerritoryRequest;
use App\Http\Requests\Admin\UpdateTerritoryRequest;
use App\Imports\TerritoriesImport;
use App\Models\Division;
use App\Models\Territory;
use App\Models\User;
use App\Services\TerritoryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TerritoryController extends Controller
{
    public function __construct(private readonly TerritoryService $territories) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Territory::class);

        $filterKeys = ['search', 'division_id', 'district_id', 'thana_id', 'geo', 'status', 'trashed'];

        return view('territories.index', [
            'territories' => $this->territories->paginate($request->only($filterKeys), 15),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
            'filters' => $request->only($filterKeys),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Territory::class);

        return view('territories.create', [
            'managers' => User::role('Territory Manager')->orderBy('name')->get(),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreTerritoryRequest $request): RedirectResponse
    {
        $this->territories->create($this->prepareData($request->validated()));

        return redirect()->route('territories.index')->with('success', 'Territory created successfully.');
    }

    public function edit(Territory $territory): View
    {
        $this->authorize('update', $territory);

        return view('territories.edit', [
            'territory' => $territory->load(['division', 'district', 'thana']),
            'managers' => User::role('Territory Manager')->orderBy('name')->get(),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateTerritoryRequest $request, Territory $territory): RedirectResponse
    {
        $this->territories->update($territory, $this->prepareData($request->validated()));

        return redirect()->route('territories.index')->with('success', 'Territory updated successfully.');
    }

    public function destroy(Territory $territory): RedirectResponse
    {
        $this->authorize('delete', $territory);

        $this->territories->delete($territory);

        return redirect()->route('territories.index')->with('success', 'Territory moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $territory = Territory::withTrashed()->findOrFail($id);
        $this->authorize('restore', $territory);

        $this->territories->restore($id);

        return redirect()->route('territories.index')->with('success', 'Territory restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $territory = Territory::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $territory);

        $this->territories->forceDelete($id);

        return redirect()->route('territories.index')->with('success', 'Territory permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('territories.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:territories,id']]);

        $count = $this->territories->bulkDelete($request->input('ids'));

        return redirect()->route('territories.index')->with('success', "{$count} territory(ies) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('territories.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:territories,id']]);

        $count = $this->territories->bulkRestore($request->input('ids'));

        return redirect()->route('territories.index')->with('success', "{$count} territory(ies) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Territory::class);

        $territories = $this->territories->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new TerritoriesExport($territories), 'territories-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Territory::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new TerritoriesImport, $request->file('file'));

        return redirect()->route('territories.index')->with('success', 'Territories imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Territory::class);

        $territories = $this->territories->paginate($request->only(['search', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('territories.print', ['territories' => $territories])
            ->stream('territories-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Territory $territory): RedirectResponse
    {
        $this->authorize('update', $territory);

        $this->territories->update($territory, ['status' => ! $territory->status]);

        return back()->with('success', 'Territory status updated.');
    }

    public function options(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Territory::class);

        if ($request->has('search')) {
            $search = trim((string) $request->string('search'));

            return response()->json(
                Territory::query()
                    ->where('status', true)
                    ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                    ->orderBy('name')
                    ->limit(50)
                    ->get(['id', 'name'])
            );
        }

        if (! $request->integer('thana_id')) {
            return response()->json([]);
        }

        return response()->json(
            Territory::query()
                ->where('status', true)
                ->when($request->integer('thana_id'), fn ($query, $id) => $query->where('thana_id', $id))
                ->orderBy('name')
                ->get(['id', 'name'])
        );
    }

    /**
     * Decode the raw GeoJSON textarea input into an array before it hits the
     * model's `array` cast (assigning a raw JSON string there would get
     * double-encoded instead of stored as a single JSON document).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareData(array $data): array
    {
        $data['boundary'] = ! empty($data['boundary']) ? json_decode((string) $data['boundary'], true) : null;

        return $data;
    }
}
