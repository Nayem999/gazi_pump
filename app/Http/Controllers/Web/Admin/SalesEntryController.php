<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\SalesEntriesExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSalesEntryRequest;
use App\Http\Requests\Admin\UpdateSalesEntryRequest;
use App\Imports\SalesEntriesImport;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesEntry;
use App\Models\User;
use App\Services\SalesEntryService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SalesEntryController extends Controller
{
    public function __construct(private readonly SalesEntryService $salesEntries) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesEntry::class);

        return view('sales-entries.index', [
            'salesEntries' => $this->salesEntries->paginate($request->only(['search', 'user_id', 'customer_id', 'product_id', 'date_from', 'date_to', 'trashed']), 15),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'filters' => $request->only(['search', 'user_id', 'customer_id', 'product_id', 'date_from', 'date_to', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', SalesEntry::class);

        return view('sales-entries.create', [
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreSalesEntryRequest $request): RedirectResponse
    {
        $this->salesEntries->create($request->validated());

        return redirect()->route('sales-entries.index')->with('success', 'Sale recorded successfully.');
    }

    public function show(SalesEntry $salesEntry): View
    {
        $this->authorize('view', $salesEntry);

        return view('sales-entries.show', [
            'salesEntry' => $salesEntry->load(['user', 'customer', 'items.product']),
        ]);
    }

    public function edit(SalesEntry $salesEntry): View
    {
        $this->authorize('update', $salesEntry);

        return view('sales-entries.edit', [
            'salesEntry' => $salesEntry->load('items'),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->get(),
            'products' => Product::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateSalesEntryRequest $request, SalesEntry $salesEntry): RedirectResponse
    {
        $this->salesEntries->update($salesEntry, $request->validated());

        return redirect()->route('sales-entries.index')->with('success', 'Sale updated successfully.');
    }

    public function destroy(SalesEntry $salesEntry): RedirectResponse
    {
        $this->authorize('delete', $salesEntry);

        $this->salesEntries->delete($salesEntry);

        return redirect()->route('sales-entries.index')->with('success', 'Sale moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $salesEntry = SalesEntry::withTrashed()->findOrFail($id);
        $this->authorize('restore', $salesEntry);

        $this->salesEntries->restore($id);

        return redirect()->route('sales-entries.index')->with('success', 'Sale restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $salesEntry = SalesEntry::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $salesEntry);

        $this->salesEntries->forceDelete($id);

        return redirect()->route('sales-entries.index')->with('success', 'Sale permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('sales-entries.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:sales_entries,id']]);

        $count = $this->salesEntries->bulkDelete($request->input('ids'));

        return redirect()->route('sales-entries.index')->with('success', "{$count} sale(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('sales-entries.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:sales_entries,id']]);

        $count = $this->salesEntries->bulkRestore($request->input('ids'));

        return redirect()->route('sales-entries.index')->with('success', "{$count} sale(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', SalesEntry::class);

        $salesEntries = $this->salesEntries->paginate($request->only(['search', 'user_id', 'customer_id', 'product_id', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new SalesEntriesExport($salesEntries), 'sales-entries-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', SalesEntry::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new SalesEntriesImport, $request->file('file'));

        return redirect()->route('sales-entries.index')->with('success', 'Sales entries imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', SalesEntry::class);

        $salesEntries = $this->salesEntries->paginate($request->only(['search', 'user_id', 'customer_id', 'product_id', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('sales-entries.print', ['salesEntries' => $salesEntries])
            ->stream('sales-entries-'.now()->format('Y-m-d-His').'.pdf');
    }
}
