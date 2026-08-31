<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\AttendanceStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $viewer = $request->user();
        $paymentModeSummary = $this->paymentModeSummary($viewer);

        return view('dashboard.index', [
            'activeUserCount' => $this->scopedUsers($viewer)->where('status', true)->count(),
            'salesExecutiveCount' => $this->scopedUsers($viewer)->role('Sales Executive')->count(),
            'territoryCount' => Territory::query()->visibleTo($viewer)->where('status', true)->count(),
            'dealerCount' => Dealer::query()->visibleTo($viewer)->where('status', true)->count(),
            'productCount' => Product::query()->visibleTo($viewer)->where('status', true)->count(),
            'presentTodayCount' => Attendance::query()->visibleTo($viewer)->whereDate('date', Carbon::today())->whereNotNull('check_in_at')->count(),
            'todaysSalesAmount' => $this->todaysSalesAmount($viewer),
            'todaysCollectionAmount' => $paymentModeSummary['total'],
            'paymentModeSummary' => $paymentModeSummary['breakdown'],
            'attendanceTrend' => $this->attendanceTrend($viewer),
            'orderVsCollectionTrend' => $this->orderVsCollectionTrend($viewer),
            'scopedToOwnTerritories' => $viewer->territories->isNotEmpty(),
            'recentActivity' => Activity::with('causer')
                ->whereIn('causer_id', $this->scopedUsers($viewer)->pluck('id'))
                ->latest()
                ->limit(10)
                ->get(),
        ]);
    }

    /**
     * Users the viewer is allowed to see: themself alone when Sales
     * Executive is their sole role, their own territories' users when they
     * have territories assigned, or everyone otherwise (Super Admin,
     * General Manager, and Sales/Area Manager — a `sales_team_id` doesn't
     * gate this, see Concerns\HasVisibilityScope for why). Same shape as
     * Order/CollectionEntry::scopeVisibleTo() and Concerns\HasVisibilityScope,
     * applied directly to User since it has no `user_id` column of its own
     * to scope by.
     */
    private function scopedUsers(User $viewer): Builder
    {
        if ($viewer->isSalesExecutiveOnly()) {
            return User::query()->whereKey($viewer->id);
        }

        $territoryIds = $viewer->territories->pluck('id')->all();

        return $territoryIds === []
            ? User::query()
            : User::query()->whereHas('territories', fn ($q) => $q->whereIn('territories.id', $territoryIds));
    }

    /**
     * Today's approved order value, scoped to what the viewer is allowed
     * to see (see Order::scopeVisibleTo()).
     */
    private function todaysSalesAmount(User $viewer): float
    {
        return (float) Order::query()
            ->visibleTo($viewer)
            ->whereDate('order_date', Carbon::today())
            ->where('status', ApprovalStatus::Approved)
            ->sum('total_amount');
    }

    /**
     * Today's approved collections broken down by payment mode (Cash, Bank
     * Transfer, Cheque, MFS) for the dashboard's donut chart, scoped to what
     * the viewer is allowed to see (see CollectionEntry::scopeVisibleTo()).
     * The total is derived from this same breakdown (rather than a separate
     * sum query) so the "Today's Collection" stat card can never drift out
     * of sync with the chart.
     *
     * @return array{total: float, breakdown: list<array{label: string, color: string, amount: float, percentage: float}>}
     */
    private function paymentModeSummary(User $viewer): array
    {
        $amountsByMethod = CollectionEntry::query()
            ->visibleTo($viewer)
            ->whereDate('collection_date', Carbon::today())
            ->where('status', ApprovalStatus::Approved)
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        $total = (float) $amountsByMethod->sum();

        // Same badge-color convention as the rest of the app (green/amber
        // etc. for status), just applied to payment modes here instead.
        $colors = [
            PaymentMethod::Cash->value => '#16a34a',
            PaymentMethod::BankTransfer->value => '#2563eb',
            PaymentMethod::Cheque->value => '#f59e0b',
            PaymentMethod::MobileBanking->value => '#7c3aed',
        ];

        $breakdown = collect(PaymentMethod::cases())->map(function (PaymentMethod $method) use ($amountsByMethod, $total, $colors) {
            $amount = (float) ($amountsByMethod->get($method->value) ?? 0);

            return [
                'label' => $method === PaymentMethod::MobileBanking ? 'MFS (bKash/Nagad)' : $method->label(),
                'color' => $colors[$method->value],
                'amount' => $amount,
                'percentage' => $total > 0 ? round($amount / $total * 100, 2) : 0.0,
            ];
        })->all();

        return ['total' => $total, 'breakdown' => $breakdown];
    }

    /**
     * Last 14 days of Present/Late/Absent counts, shaped for the ApexCharts
     * area chart on the dashboard — scoped to what the viewer is allowed to
     * see (see Attendance::scopeVisibleTo(), via Concerns\HasVisibilityScope).
     *
     * @return array<int, array{date: string, present: int, late: int, absent: int}>
     */
    private function attendanceTrend(User $viewer): array
    {
        $dateFrom = Carbon::today()->subDays(13);

        $rows = Attendance::selectRaw('date, status, COUNT(*) as total')
            ->visibleTo($viewer)
            ->where('date', '>=', $dateFrom->toDateString())
            ->groupBy('date', 'status')
            ->get()
            ->groupBy(fn (Attendance $row) => $row->date->toDateString());

        return collect(range(0, 13))->map(function (int $offset) use ($dateFrom, $rows) {
            $day = $dateFrom->copy()->addDays($offset);
            $dayRows = $rows->get($day->toDateString(), collect());

            return [
                'date' => $day->format('M d'),
                'present' => (int) ($dayRows->firstWhere('status', AttendanceStatus::Present)->total ?? 0),
                'late' => (int) ($dayRows->firstWhere('status', AttendanceStatus::Late)->total ?? 0),
                'absent' => (int) ($dayRows->firstWhere('status', AttendanceStatus::Absent)->total ?? 0),
            ];
        })->values()->all();
    }

    /**
     * Order value vs collection amount for each of the last 6 months
     * (oldest first), each split into its Pending/Approved/Rejected slice
     * so the chart can render two stacked-and-grouped bars per month (one
     * for Orders, one for Collections) — scoped to what the viewer is
     * allowed to see (see Order/CollectionEntry::scopeVisibleTo()). Same
     * shape as the customer portal's Purchases vs Payments chart it's
     * modeled on, just with each bar broken down by approval status instead
     * of a single total. Grouped in PHP rather than a DB date-format
     * function so it behaves the same on MySQL (prod) and SQLite (tests).
     *
     * @return list<array{label: string, order_pending: float, order_approved: float, order_rejected: float, collection_pending: float, collection_approved: float, collection_rejected: float}>
     */
    private function orderVsCollectionTrend(User $viewer): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());
        $since = $months->first();

        $ordersByMonth = Order::query()
            ->visibleTo($viewer)
            ->where('order_date', '>=', $since)
            ->get(['order_date', 'total_amount', 'status'])
            ->groupBy(fn (Order $order) => $order->order_date->format('Y-m'));

        $collectionsByMonth = CollectionEntry::query()
            ->visibleTo($viewer)
            ->where('collection_date', '>=', $since)
            ->get(['collection_date', 'amount', 'status'])
            ->groupBy(fn (CollectionEntry $entry) => $entry->collection_date->format('Y-m'));

        return $months->map(function (Carbon $month) use ($ordersByMonth, $collectionsByMonth) {
            $key = $month->format('Y-m');
            $orders = $ordersByMonth->get($key, collect());
            $collections = $collectionsByMonth->get($key, collect());

            return [
                'label' => $month->format('M Y'),
                'order_pending' => (float) $orders->where('status', ApprovalStatus::Pending)->sum('total_amount'),
                'order_approved' => (float) $orders->where('status', ApprovalStatus::Approved)->sum('total_amount'),
                'order_rejected' => (float) $orders->where('status', ApprovalStatus::Rejected)->sum('total_amount'),
                'collection_pending' => (float) $collections->where('status', ApprovalStatus::Pending)->sum('amount'),
                'collection_approved' => (float) $collections->where('status', ApprovalStatus::Approved)->sum('amount'),
                'collection_rejected' => (float) $collections->where('status', ApprovalStatus::Rejected)->sum('amount'),
            ];
        })->all();
    }
}
