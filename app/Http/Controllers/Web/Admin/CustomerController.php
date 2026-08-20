<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\CustomerType;
use App\Exports\CustomersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomerRequest;
use App\Http\Requests\Admin\UpdateCustomerRequest;
use App\Imports\CustomersImport;
use App\Models\Customer;
use App\Models\Territory;
use App\Services\CustomerService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Customer::class);

        return view('customers.index', [
            'customers' => $this->customers->paginate($request->only(['search', 'type', 'territory_id', 'status', 'trashed']), 15),
            'territories' => Territory::orderBy('name')->get(),
            'types' => CustomerType::cases(),
            'filters' => $request->only(['search', 'type', 'territory_id', 'status', 'trashed']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Customer::class);

        return view('customers.create', [
            'territories' => Territory::orderBy('name')->get(),
            'types' => CustomerType::cases(),
        ]);
    }

    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $this->customers->create($request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer): View
    {
        $this->authorize('view', $customer);

        return view('customers.show', ['customer' => $customer->load('territory')]);
    }

    public function edit(Customer $customer): View
    {
        $this->authorize('update', $customer);

        return view('customers.edit', [
            'customer' => $customer,
            'territories' => Territory::orderBy('name')->get(),
            'types' => CustomerType::cases(),
        ]);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $this->customers->update($customer, $request->validated());

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $this->authorize('delete', $customer);

        $this->customers->delete($customer);

        return redirect()->route('customers.index')->with('success', 'Customer moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $this->authorize('restore', $customer);

        $this->customers->restore($id);

        return redirect()->route('customers.index')->with('success', 'Customer restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $customer);

        $this->customers->forceDelete($id);

        return redirect()->route('customers.index')->with('success', 'Customer permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('customers.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:customers,id']]);

        $count = $this->customers->bulkDelete($request->input('ids'));

        return redirect()->route('customers.index')->with('success', "{$count} customer(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('customers.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:customers,id']]);

        $count = $this->customers->bulkRestore($request->input('ids'));

        return redirect()->route('customers.index')->with('success', "{$count} customer(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Customer::class);

        $customers = $this->customers->paginate($request->only(['search', 'type', 'territory_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new CustomersExport($customers), 'customers-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Customer::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new CustomersImport, $request->file('file'));

        return redirect()->route('customers.index')->with('success', 'Customers imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Customer::class);

        $customers = $this->customers->paginate($request->only(['search', 'type', 'territory_id', 'status', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('customers.print', ['customers' => $customers])
            ->stream('customers-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function toggleStatus(Customer $customer): RedirectResponse
    {
        $this->authorize('update', $customer);

        $this->customers->update($customer, ['status' => ! $customer->status]);

        return back()->with('success', 'Customer status updated.');
    }
}
