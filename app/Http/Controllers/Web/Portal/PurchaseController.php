<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $dealer = $request->user('customer')->resolveCustomer();

        $purchases = $dealer
            ? $dealer->orders()->withCount('items')->latest('order_date')->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('portal.purchases.index', ['purchases' => $purchases]);
    }

    /**
     * Route-model-bound by raw id, so the ownership check is what stops one
     * dealer from viewing another's order by guessing the URL — there's no
     * broader scoping (like a global query scope) upstream of this.
     *
     * The route parameter is still named {salesEntry} (routes/portal.php is
     * out of scope for this rename), so the argument keeps that name for
     * Laravel's implicit route-model binding to match it up — only the
     * type-hint changes, to the renamed Order model.
     */
    public function show(Request $request, Order $salesEntry): View
    {
        $dealer = $request->user('customer')->resolveCustomer();

        abort_unless($dealer && $salesEntry->dealer_id === $dealer->id, 404);

        $salesEntry->load('items.product');

        return view('portal.purchases.show', ['order' => $salesEntry]);
    }
}
