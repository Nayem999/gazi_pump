<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DispatchDeliveryRequest;
use App\Models\Delivery;
use App\Models\Order;
use App\Services\DeliveryService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Deliveries are created only through Order's own "Dispatch" action
 * (dispatchOrder(), routed under orders.dispatch) — there is no standalone
 * create form, so this is a lean read + one status-transition controller,
 * direct-permission-checked like DepotStockController rather than backed by
 * a full Policy class (there's no CRUD action here beyond viewing and
 * marking delivered).
 */
class DeliveryController extends Controller
{
    public function __construct(private readonly DeliveryService $deliveries) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('deliveries.view'), 403);

        $filters = $request->only(['status', 'search']);

        $deliveries = Delivery::query()
            ->with(['order.dealer', 'vehicle', 'driver'])
            ->whereHas('order', fn ($q) => $q->visibleTo($request->user()))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('external_reference', 'like', "%{$search}%"))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('deliveries.index', ['deliveries' => $deliveries, 'filters' => $filters]);
    }

    public function show(Request $request, Delivery $delivery): View
    {
        abort_unless($request->user()?->can('deliveries.view'), 403);
        abort_unless(Order::visibleTo($request->user())->whereKey($delivery->order_id)->exists(), 403);

        $delivery->load(['order.items.product', 'order.dealer', 'vehicle', 'driver', 'dispatchedBy']);

        return view('deliveries.show', ['delivery' => $delivery]);
    }

    public function dispatchOrder(DispatchDeliveryRequest $request, Order $order): RedirectResponse
    {
        $this->deliveries->dispatch($order, $request->validated(), $request->user()->id);

        return back()->with('success', 'Order dispatched.');
    }

    public function markDelivered(Request $request, Delivery $delivery): RedirectResponse
    {
        abort_unless($request->user()?->can('deliveries.add'), 403);

        $this->deliveries->markDelivered($delivery);

        return back()->with('success', 'Delivery marked as delivered.');
    }
}
