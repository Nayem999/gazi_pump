<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\SalesEntry;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class PurchaseController extends Controller
{
    public function index(Request $request): View
    {
        $customer = $request->user('customer')->resolveCustomer();

        $purchases = $customer
            ? $customer->salesEntries()->withCount('items')->latest('sale_date')->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('portal.purchases.index', ['purchases' => $purchases]);
    }

    /**
     * Route-model-bound by raw id, so the ownership check is what stops one
     * customer from viewing another's sale by guessing the URL — there's no
     * broader scoping (like a global query scope) upstream of this.
     */
    public function show(Request $request, SalesEntry $salesEntry): View
    {
        $customer = $request->user('customer')->resolveCustomer();

        abort_unless($customer && $salesEntry->customer_id === $customer->id, 404);

        $salesEntry->load('items.product');

        return view('portal.purchases.show', ['salesEntry' => $salesEntry]);
    }
}
