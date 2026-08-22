<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Portal;

use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $account = $request->user('customer');
        $dealer = $account->resolveCustomer();

        return view('portal.dashboard', [
            'account' => $account,
            'recentInquiries' => $account->inquiries()->latest()->limit(5)->get(),
            'recentVisitRequests' => $account->visitRequests()->latest()->limit(5)->get(),
            'totalSpent' => $dealer ? (float) $dealer->orders()->sum('total_amount') : 0.0,
            'totalOrders' => $dealer ? $dealer->orders()->count() : 0,
            'monthlyPurchases' => $this->monthlyPurchases($dealer),
            'topProducts' => $this->topProducts($dealer),
        ]);
    }

    /**
     * Purchase totals for each of the last 6 months (oldest first), with
     * months that had no orders filled in as zero — grouped in PHP rather
     * than a DB date-format function so it works the same on MySQL (prod)
     * and SQLite (tests).
     *
     * @return list<array{label: string, total: float}>
     */
    private function monthlyPurchases(?Dealer $dealer): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());

        if (! $dealer) {
            return $months->map(fn (Carbon $month) => ['label' => $month->format('M Y'), 'total' => 0.0])->all();
        }

        $totalsByMonth = $dealer->orders()
            ->where('order_date', '>=', $months->first())
            ->get(['order_date', 'total_amount'])
            ->groupBy(fn (Order $order) => $order->order_date->format('Y-m'))
            ->map(fn ($orders) => (float) $orders->sum('total_amount'));

        return $months->map(fn (Carbon $month) => [
            'label' => $month->format('M Y'),
            'total' => $totalsByMonth->get($month->format('Y-m'), 0.0),
        ])->all();
    }

    /**
     * How much of each product this dealer has bought, most-purchased
     * (by amount) first.
     *
     * @return list<array{name: string, quantity: int, total: float}>
     */
    private function topProducts(?Dealer $dealer, int $limit = 6): array
    {
        if (! $dealer) {
            return [];
        }

        return OrderItem::whereHas('order', fn ($query) => $query->where('dealer_id', $dealer->id))
            ->selectRaw('product_id, SUM(quantity) as quantity, SUM(total_amount) as total')
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->with('product:id,name')
            ->limit($limit)
            ->get()
            ->map(fn (OrderItem $row) => [
                'name' => $row->product?->name ?? 'Unknown product',
                'quantity' => (int) $row->quantity,
                'total' => (float) $row->total,
            ])
            ->all();
    }
}
