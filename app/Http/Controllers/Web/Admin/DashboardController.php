<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $territoryIds = $request->user()?->territories->pluck('id')->all() ?? [];

        return view('dashboard.index', [
            'activeUserCount' => User::where('status', true)->count(),
            'salesExecutiveCount' => User::role('Sales Executive')->count(),
            'territoryCount' => Territory::where('status', true)->count(),
            'dealerCount' => Dealer::where('status', true)->count(),
            'productCount' => Product::where('status', true)->count(),
            'presentTodayCount' => Attendance::whereDate('date', Carbon::today())->whereNotNull('check_in_at')->count(),
            'attendanceTrend' => $this->attendanceTrend(),
            'orderVsCollectionTrend' => $this->orderVsCollectionTrend($territoryIds),
            'scopedToOwnTerritories' => $territoryIds !== [],
            'recentActivity' => Activity::with('causer')->latest()->limit(10)->get(),
        ]);
    }

    /**
     * Last 14 days of Present/Late/Absent counts, shaped for the ApexCharts
     * area chart on the dashboard.
     *
     * @return array<int, array{date: string, present: int, late: int, absent: int}>
     */
    private function attendanceTrend(): array
    {
        $dateFrom = Carbon::today()->subDays(13);

        $rows = Attendance::selectRaw('date, status, COUNT(*) as total')
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
     * for Orders, one for Collections) — scoped to the viewing admin's own
     * territories when they have any assigned (via each Order/
     * CollectionEntry's dealer), otherwise company-wide. Same shape as the
     * customer portal's Purchases vs Payments chart it's modeled on, just
     * with each bar broken down by approval status instead of a single
     * total. Grouped in PHP rather than a DB date-format function so it
     * behaves the same on MySQL (prod) and SQLite (tests).
     *
     * @param  array<int, int>  $territoryIds
     * @return list<array{label: string, order_pending: float, order_approved: float, order_rejected: float, collection_pending: float, collection_approved: float, collection_rejected: float}>
     */
    private function orderVsCollectionTrend(array $territoryIds): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());
        $since = $months->first();

        $ordersByMonth = Order::query()
            ->when($territoryIds, fn ($query) => $query->whereHas('dealer', fn ($d) => $d->whereIn('territory_id', $territoryIds)))
            ->where('order_date', '>=', $since)
            ->get(['order_date', 'total_amount', 'status'])
            ->groupBy(fn (Order $order) => $order->order_date->format('Y-m'));

        $collectionsByMonth = CollectionEntry::query()
            ->when($territoryIds, fn ($query) => $query->whereHas('dealer', fn ($d) => $d->whereIn('territory_id', $territoryIds)))
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
