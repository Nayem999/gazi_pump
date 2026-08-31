<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\ApprovalStatus;
use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\AchievementEntry;
use App\Models\Attendance;
use App\Models\Dealer;
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

        return view('dashboard.index', [
            'activeUserCount' => $this->scopedUsers($viewer)->where('status', true)->count(),
            'salesExecutiveCount' => $this->scopedUsers($viewer)->role('Sales Executive')->count(),
            'territoryCount' => Territory::query()->visibleTo($viewer)->where('status', true)->count(),
            'dealerCount' => Dealer::query()->visibleTo($viewer)->where('status', true)->count(),
            'productCount' => Product::query()->visibleTo($viewer)->where('status', true)->count(),
            'presentTodayCount' => Attendance::query()->visibleTo($viewer)->whereDate('date', Carbon::today())->whereNotNull('check_in_at')->count(),
            'todaysOrderAchieved' => $this->todaysOrderAchieved($viewer),
            'todaysCollectionAchieved' => $this->todaysCollectionAchieved($viewer),
            'attendanceTrend' => $this->attendanceTrend($viewer),
            'achievementTrend' => $this->achievementTrend($viewer),
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
     * Today's approved order-value achieved, scoped to what the viewer is
     * allowed to see (see AchievementEntry::scopeVisibleTo()).
     */
    private function todaysOrderAchieved(User $viewer): float
    {
        return (float) AchievementEntry::query()
            ->visibleTo($viewer)
            ->whereDate('entry_date', Carbon::today())
            ->where('status', ApprovalStatus::Approved)
            ->sum('order_value_achieved');
    }

    /**
     * Today's approved collection achieved, scoped the same way.
     */
    private function todaysCollectionAchieved(User $viewer): float
    {
        return (float) AchievementEntry::query()
            ->visibleTo($viewer)
            ->whereDate('entry_date', Carbon::today())
            ->where('status', ApprovalStatus::Approved)
            ->sum('collection_achieved');
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
     * Order value vs collection amount achieved for each of the last 6
     * months (oldest first), each split into its Pending/Approved/Rejected
     * slice so the chart can render two stacked-and-grouped bars per month
     * (one for order value, one for collection) — scoped to what the viewer
     * is allowed to see (see AchievementEntry::scopeVisibleTo()). Both
     * figures now live on the same AchievementEntry row (unlike the old
     * Order/CollectionEntry split), so this only needs one query. Grouped
     * in PHP rather than a DB date-format function so it behaves the same
     * on MySQL (prod) and SQLite (tests).
     *
     * @return list<array{label: string, order_pending: float, order_approved: float, order_rejected: float, collection_pending: float, collection_approved: float, collection_rejected: float}>
     */
    private function achievementTrend(User $viewer): array
    {
        $months = collect(range(5, 0))->map(fn (int $i) => Carbon::now()->subMonths($i)->startOfMonth());
        $since = $months->first();

        $entriesByMonth = AchievementEntry::query()
            ->visibleTo($viewer)
            ->where('entry_date', '>=', $since)
            ->get(['entry_date', 'order_value_achieved', 'collection_achieved', 'status'])
            ->groupBy(fn (AchievementEntry $entry) => $entry->entry_date->format('Y-m'));

        return $months->map(function (Carbon $month) use ($entriesByMonth) {
            $key = $month->format('Y-m');
            $entries = $entriesByMonth->get($key, collect());

            return [
                'label' => $month->format('M Y'),
                'order_pending' => (float) $entries->where('status', ApprovalStatus::Pending)->sum('order_value_achieved'),
                'order_approved' => (float) $entries->where('status', ApprovalStatus::Approved)->sum('order_value_achieved'),
                'order_rejected' => (float) $entries->where('status', ApprovalStatus::Rejected)->sum('order_value_achieved'),
                'collection_pending' => (float) $entries->where('status', ApprovalStatus::Pending)->sum('collection_achieved'),
                'collection_approved' => (float) $entries->where('status', ApprovalStatus::Approved)->sum('collection_achieved'),
                'collection_rejected' => (float) $entries->where('status', ApprovalStatus::Rejected)->sum('collection_achieved'),
            ];
        })->all();
    }
}
