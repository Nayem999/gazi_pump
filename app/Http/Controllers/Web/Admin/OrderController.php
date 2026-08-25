<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\OrdersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreOrderRequest;
use App\Http\Requests\Admin\UpdateOrderRequest;
use App\Imports\OrdersImport;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Territory;
use App\Models\User;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $filters = $request->only(['search', 'user_id', 'dealer_id', 'territory_id', 'product_id', 'date_from', 'date_to', 'trashed']);

        return view('orders.index', [
            'orders' => $this->orders->paginate($filters, 15),
            'total' => $this->orders->total($filters),
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'territories' => Territory::where('status', true)->orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Order::class);

        return view('orders.create', [
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'dealers' => Dealer::orderBy('name')->get(),
            'products' => Product::where('status', true)->visibleTo($request->user())->orderBy('name')->get(),
        ]);
    }

    public function store(StoreOrderRequest $request): RedirectResponse
    {
        $this->orders->create($request->validated());

        return redirect()->route('orders.index')->with('success', 'Order recorded successfully.');
    }

    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        return view('orders.show', [
            'order' => $order->load(['user', 'dealer', 'items.product']),
        ]);
    }

    public function downloadPdf(Order $order): mixed
    {
        $this->authorize('view', $order);

        $order->load(['user', 'dealer', 'items.product']);

        return Pdf::loadView('orders.detail-pdf', ['order' => $order, 'setting' => Setting::current()])
            ->stream('order-'.$order->id.'-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function edit(Request $request, Order $order): View
    {
        $this->authorize('update', $order);

        $order->load('items');

        return view('orders.edit', [
            'order' => $order,
            'executives' => User::role('Sales Executive')->orderBy('name')->get(),
            'dealers' => Dealer::orderBy('name')->get(),
            // Team-scoped like create(), but also keeps whatever products
            // this order already has on it — otherwise re-editing an order
            // placed before a product's team changed (or before the item's
            // own product left the executive's team) would drop that line's
            // selection silently.
            'products' => Product::where('status', true)->visibleTo($request->user())
                ->orWhereIn('id', $order->items->pluck('product_id'))
                ->orderBy('name')->get(),
        ]);
    }

    public function update(UpdateOrderRequest $request, Order $order): RedirectResponse
    {
        $this->orders->update($order, $request->validated());

        return redirect()->route('orders.index')->with('success', 'Order updated successfully.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorize('delete', $order);

        $this->orders->delete($order);

        return redirect()->route('orders.index')->with('success', 'Order moved to trash.');
    }

    public function restore(int $id): RedirectResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        $this->authorize('restore', $order);

        $this->orders->restore($id);

        return redirect()->route('orders.index')->with('success', 'Order restored successfully.');
    }

    public function forceDestroy(int $id): RedirectResponse
    {
        $order = Order::withTrashed()->findOrFail($id);
        $this->authorize('forceDelete', $order);

        $this->orders->forceDelete($id);

        return redirect()->route('orders.index')->with('success', 'Order permanently deleted.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('orders.delete'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:orders,id']]);

        $count = $this->orders->bulkDelete($request->input('ids'));

        return redirect()->route('orders.index')->with('success', "{$count} order(s) moved to trash.");
    }

    public function bulkRestore(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('orders.restore'), 403);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer', 'exists:orders,id']]);

        $count = $this->orders->bulkRestore($request->input('ids'));

        return redirect()->route('orders.index')->with('success', "{$count} order(s) restored.");
    }

    public function export(Request $request): mixed
    {
        $this->authorize('export', Order::class);

        $orders = $this->orders->paginate($request->only(['search', 'user_id', 'dealer_id', 'territory_id', 'product_id', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Excel::download(new OrdersExport($orders), 'orders-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorize('import', Order::class);

        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,xls']]);

        Excel::import(new OrdersImport, $request->file('file'));

        return redirect()->route('orders.index')->with('success', 'Orders imported successfully.');
    }

    public function print(Request $request): mixed
    {
        $this->authorize('print', Order::class);

        $orders = $this->orders->paginate($request->only(['search', 'user_id', 'dealer_id', 'territory_id', 'product_id', 'date_from', 'date_to', 'trashed']), PHP_INT_MAX)->getCollection();

        return Pdf::loadView('orders.print', ['orders' => $orders])
            ->stream('orders-'.now()->format('Y-m-d-His').'.pdf');
    }
}
