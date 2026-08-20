<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\SalesEntry;
use App\Models\SalesEntryItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $account = $request->user('customer');
        $customer = $account->resolveCustomer();

        return view('portal.dashboard', [
            'account' => $account,
            'recentInquiries' => $account->inquiries()->latest()->limit(5)->get(),
            'recentVisitRequests' => $account->visitRequests()->latest()->limit(5)->get(),
            'totalSpent' => $customer ? (float) $customer->salesEntries()->sum('total_amount') : 0.0,
            'totalOrders' => $customer ? $customer->salesEntries()->count() : 0,
            'monthlyPurchases' => $this->monthlyPurchases($customer),
            'topProducts' => $this->topProducts($customer),
        ]);
    }

    /**
     * Purchase totals for each of the last 6 months (oldest first), with
     * months that had no sales filled in as zero — grouped in PHP rather
     * than a DB date-format function so it works the same on MySQL (prod)
     * and SQLite (tests).
     *
     * @return list<array{label: string, total: float}>
     */
    private function monthlyPurchases(?Customer $customer): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());

        if (! $customer) {
            return $months->map(fn (Carbon $month) => ['label' => $month->format('M Y'), 'total' => 0.0])->all();
        }

        $totalsByMonth = $customer->salesEntries()
            ->where('sale_date', '>=', $months->first())
            ->get(['sale_date', 'total_amount'])
            ->groupBy(fn (SalesEntry $entry) => $entry->sale_date->format('Y-m'))
            ->map(fn ($entries) => (float) $entries->sum('total_amount'));

        return $months->map(fn (Carbon $month) => [
            'label' => $month->format('M Y'),
            'total' => $totalsByMonth->get($month->format('Y-m'), 0.0),
        ])->all();
    }

    /**
     * How much of each product this customer has bought, most-purchased
     * (by amount) first.
     *
     * @return list<array{name: string, quantity: int, total: float}>
     */
    private function topProducts(?Customer $customer, int $limit = 6): array
    {
        if (! $customer) {
            return [];
        }

        return SalesEntryItem::whereHas('salesEntry', fn ($query) => $query->where('customer_id', $customer->id))
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(total_amount) as total')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->with('product:id,name')
            ->limit($limit)
            ->get()
            ->map(fn (SalesEntryItem $row) => [
                'name' => $row->product?->name ?? 'Unknown product',
                'quantity' => (int) $row->quantity,
                'total' => (float) $row->total,
            ])
            ->all();
    }
}
