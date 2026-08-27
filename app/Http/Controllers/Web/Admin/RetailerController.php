<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\RetailersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRetailerRequest;
use App\Http\Requests\Admin\UpdateRetailerRequest;
use App\Imports\RetailersImport;
use App\Models\Dealer;
use App\Models\Retailer;
use App\Services\RetailerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class RetailerController extends Controller
{
    public function __construct(private readonly RetailerService $retailers) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Retailer::class);

        $filterKeys = ['search', 'dealer_id', 'status', 'trashed'];

        return view('retailers.index', [
            'retailers' => $this->retailers->paginate($request->only($filterKeys), 15),
            'dealers' => Dealer::orderBy('name')->get(),
            'filters' => $request->only($filterKeys),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Retailer::class);

        return view('retailers.create', [
            'dealers' => Dealer::orderBy('name')->get(),
        ]);
    }

    public function store(StoreRetailerRequest $request): RedirectResponse
    {
        $this->retailers->create($request->safe()->except('image'), $request->file('image'));

        return redirect()->route('retailers.index')->with('success', 'Retailer created successfully.');
    }

    public function edit(Retailer $retailer): View
    {
        $this->authorize('update', $retailer);

        return view('retailers.edit', [
            'retailer' => $retailer,
            'dealers' => Dealer::orderBy('name')->get(),
        ]);
    }

    public function update(UpdateRetailerRequest $request, Retailer $retailer): RedirectResponse
    {
        $this->retailers->update($retailer, $request->safe()->except('image'), $request->file('image'));

        return redirect()->route('retailers.index')->with('success', 'Retailer updated successfully.');
    }

    public function destroy(Retailer $retailer): RedirectResponse
    {
        $this->authorize('delete', $retailer);

        $this->retailers->delete($retailer);

        return redirect()->route('retailers.index')->with('success', 'Retailer moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $retailer = Retailer::withTrashed()->findOrFail($id);
        $this->authorize('restore', $retailer);

        $this->retailers->restore($id);

        return redirect()->route('retailers.index')->with('success', 'Retailer restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $retailer = Retailer::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $retailer);

        $this->retailers->forceDelete($id);

        return redirect()->route('retailers.index')->with('success', 'Retailer permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('retailers.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:retailers,id']]);

        $count = $this->retailers->bulkDelete($request->input('ids'));

        return redirect()->route('retailers.index')->with('success', "{$count} retailer(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('retailers.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:retailers,id']]);

        $count = $this->retailers->bulkRestore($request->input('ids'));

        return redirect()->route('retailers.index')->with('success', "{$count} retailer(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Retailer::class);

        $retailers = $this->retailers->paginate($request->only(['search', 'dealer_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new RetailersExport($retailers), 'retailers-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Retailer::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new RetailersImport, $request->file('file'));

        return redirect()->route('retailers.index')->with('success', 'Retailers imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Retailer::class);

        $retailers = $this->retailers->paginate($request->only(['search', 'dealer_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('retailers.print', ['retailers' => $retailers])
            ->stream('retailers-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Retailer $retailer): RedirectResponse
    {
        $this->authorize('update', $retailer);

        $this->retailers->update($retailer, ['status' => ! $retailer->status]);

        return back()->with('success', 'Retailer status updated.');
    }
}
