<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Depot;
use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Read-only: Tally is the source of truth for these figures
 * (opening/in/out/closing/available), so there is no create/edit form here
 * — only a Sync Agent stock-pull job ever writes to product_stocks. Same
 * "direct permission check, no Policy class" shape as Activity Log/Reports,
 * since there's no CRUD-owned action to authorize beyond viewing.
 */
class DepotStockController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('depots.view'), 403);

        $filters = $request->only(['depot_id', 'product_id', 'search']);

        $stocks = ProductStock::query()
            ->with(['depot', 'product'])
            ->when($filters['depot_id'] ?? null, fn ($q, $id) => $q->where('depot_id', $id))
            ->when($filters['product_id'] ?? null, fn ($q, $id) => $q->where('product_id', $id))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->whereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%")))
            ->latest('last_synced_at')
            ->paginate(20)
            ->withQueryString();

        return view('depots.stock', [
            'stocks' => $stocks,
            'filters' => $filters,
            'depots' => Depot::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }
}
