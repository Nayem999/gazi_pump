<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DispatchSalesReturnRequest;
use App\Http\Requests\Admin\ReceiveSalesReturnRequest;
use App\Http\Requests\Admin\StoreSalesReturnRequest;
use App\Models\Depot;
use App\Models\Driver;
use App\Models\Order;
use App\Models\SalesReturn;
use App\Models\Vehicle;
use App\Services\SalesReturnService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The full Sales Return workflow (spec's Phase 6): request (from an
 * Order's own detail page) -> approve/reject -> dispatch -> depot
 * receiving (which triggers the Tally Credit Note push). No standalone
 * create form outside an Order's context, same reasoning as Delivery.
 */
class SalesReturnController extends Controller
{
    public function __construct(private readonly SalesReturnService $returns) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesReturn::class);

        $filters = $request->only(['status', 'dealer_id', 'search']);

        return view('sales-returns.index', [
            'returns' => $this->returns->paginate($filters, 15, $request->user()),
            'filters' => $filters,
        ]);
    }

    public function store(StoreSalesReturnRequest $request, Order $order): RedirectResponse
    {
        $return = $this->returns->requestReturn($order, $request->user(), $request->validated());

        return redirect()->route('sales-returns.show', $return)->with('success', 'Return requested.');
    }

    public function show(Request $request, SalesReturn $salesReturn): View
    {
        $this->authorize('view', $salesReturn);

        $salesReturn->load(['order.dealer', 'user', 'approvedBy', 'vehicle', 'driver', 'receivingDepot', 'receivedBy', 'items.product']);

        return view('sales-returns.show', [
            'salesReturn' => $salesReturn,
            'vehicles' => Vehicle::where('status', true)->orderBy('registration_number')->get(),
            'drivers' => Driver::where('status', true)->orderBy('name')->get(),
            'depots' => Depot::where('status', true)->orderBy('name')->get(),
        ]);
    }

    public function approve(Request $request, SalesReturn $salesReturn): RedirectResponse
    {
        $this->authorize('approve', $salesReturn);

        $this->returns->approve($salesReturn, $request->user()->id);

        return back()->with('success', 'Return approved.');
    }

    public function reject(Request $request, SalesReturn $salesReturn): RedirectResponse
    {
        $this->authorize('approve', $salesReturn);

        $this->returns->reject($salesReturn, $request->user()->id);

        return back()->with('success', 'Return rejected.');
    }

    public function dispatch(DispatchSalesReturnRequest $request, SalesReturn $salesReturn): RedirectResponse
    {
        $this->returns->dispatch($salesReturn, $request->validated());

        return back()->with('success', 'Return dispatched.');
    }

    public function receive(ReceiveSalesReturnRequest $request, SalesReturn $salesReturn): RedirectResponse
    {
        $this->returns->receive(
            $salesReturn,
            $request->validated('items'),
            (int) $request->validated('receiving_depot_id'),
            $request->user()->id,
        );

        return back()->with('success', 'Return received — Tally sync queued.');
    }
}
