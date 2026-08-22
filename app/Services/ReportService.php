<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Achievement;
use App\Models\Attendance;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\GpsLog;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Target;
use App\Models\Territory;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPlan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Read-only aggregations over data already captured by other modules
 * (Attendance, Visit Plan/Visit, Sales Entry, Collection Entry). Nothing
 * here is stored — every report recomputes from source rows for the
 * requested date range each time it's viewed.
 */
class ReportService
{
    /**
     * @param  array{date_from?: string, date_to?: string}  $filters
     * @return array{from: Carbon, to: Carbon}
     */
    public function dateRange(array $filters): array
    {
        $from = ! empty($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : Carbon::now()->startOfMonth();
        $to = ! empty($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : Carbon::now()->endOfMonth();

        return ['from' => $from, 'to' => $to];
    }

    /**
     * @param  array{date_from?: string, date_to?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function attendanceSummary(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);

        $rows = Attendance::query()
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw(
                'user_id,
                SUM(CASE WHEN status = "present" THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN status = "late" THEN 1 ELSE 0 END) as late_count,
                SUM(CASE WHEN status = "half_day" THEN 1 ELSE 0 END) as half_day_count,
                SUM(CASE WHEN status = "absent" THEN 1 ELSE 0 END) as absent_count,
                SUM(late_minutes) as total_late_minutes,
                COUNT(*) as total_days'
            )
            ->groupBy('user_id')
            ->get();

        $users = $this->usersFor($rows->pluck('user_id'));

        return $rows->map(function ($row) use ($users) {
            $totalDays = (int) $row->total_days;
            $presentLikeDays = (int) $row->present_count + (int) $row->late_count + (int) $row->half_day_count;

            return (object) [
                'user' => $users->get($row->user_id),
                'present_count' => (int) $row->present_count,
                'late_count' => (int) $row->late_count,
                'half_day_count' => (int) $row->half_day_count,
                'absent_count' => (int) $row->absent_count,
                'total_late_minutes' => (int) $row->total_late_minutes,
                'total_days' => $totalDays,
                'attendance_rate' => $totalDays > 0 ? round(($presentLikeDays / $totalDays) * 100, 1) : 0.0,
            ];
        })->sortByDesc('attendance_rate')->values();
    }

    /**
     * @param  array{date_from?: string, date_to?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function visitCompliance(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);

        $planRows = VisitPlan::query()
            ->whereBetween('planned_date', [$from->toDateString(), $to->toDateString()])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw(
                'user_id,
                COUNT(*) as planned_count,
                SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_count,
                SUM(CASE WHEN status = "planned" AND planned_date < ? THEN 1 ELSE 0 END) as missed_count',
                [Carbon::today()->toDateString()]
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $visitRows = Visit::query()
            ->whereBetween('check_in_at', [$from, $to])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw(
                'user_id,
                COUNT(*) as total_visits,
                SUM(CASE WHEN is_gps_verified = 1 THEN 1 ELSE 0 END) as gps_verified_count,
                SUM(CASE WHEN is_gps_verified = 0 THEN 1 ELSE 0 END) as gps_unverified_count'
            )
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        $userIds = $planRows->keys()->merge($visitRows->keys())->unique();
        $users = $this->usersFor($userIds);

        return $userIds->map(function ($userId) use ($planRows, $visitRows, $users) {
            $plan = $planRows->get($userId);
            $visit = $visitRows->get($userId);

            $plannedCount = (int) ($plan->planned_count ?? 0);
            $completedCount = (int) ($plan->completed_count ?? 0);
            $gpsVerified = (int) ($visit->gps_verified_count ?? 0);
            $gpsUnverified = (int) ($visit->gps_unverified_count ?? 0);
            $gpsJudged = $gpsVerified + $gpsUnverified;

            return (object) [
                'user' => $users->get($userId),
                'planned_count' => $plannedCount,
                'completed_count' => $completedCount,
                'missed_count' => (int) ($plan->missed_count ?? 0),
                'completion_rate' => $plannedCount > 0 ? round(($completedCount / $plannedCount) * 100, 1) : 0.0,
                'total_visits' => (int) ($visit->total_visits ?? 0),
                'gps_verified_count' => $gpsVerified,
                'gps_verified_rate' => $gpsJudged > 0 ? round(($gpsVerified / $gpsJudged) * 100, 1) : 0.0,
            ];
        })->sortByDesc('completion_rate')->values();
    }

    /**
     * @param  array{date_from?: string, date_to?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function orderPerformance(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);

        $rows = Order::query()
            ->whereBetween('order_date', [$from->toDateString(), $to->toDateString()])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw('user_id, COUNT(*) as order_count, SUM(total_amount) as total_order_value')
            ->groupBy('user_id')
            ->get();

        $quantityByUser = OrderItem::query()
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereBetween('orders.order_date', [$from->toDateString(), $to->toDateString()])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('orders.user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'order.user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw('orders.user_id as user_id, SUM(order_items.quantity) as total_quantity')
            ->groupBy('orders.user_id')
            ->pluck('total_quantity', 'user_id');

        $users = $this->usersFor($rows->pluck('user_id'));

        return $rows->map(fn ($row) => (object) [
            'user' => $users->get($row->user_id),
            'order_count' => (int) $row->order_count,
            'total_quantity' => (int) ($quantityByUser->get($row->user_id) ?? 0),
            'total_order_value' => (float) $row->total_order_value,
        ])->sortByDesc('total_order_value')->values();
    }

    /**
     * @param  array{date_from?: string, date_to?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function collectionSummary(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);

        $rows = CollectionEntry::query()
            ->whereBetween('collection_date', [$from->toDateString(), $to->toDateString()])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw(
                'user_id,
                COUNT(*) as collections_count,
                SUM(amount) as total_amount,
                SUM(CASE WHEN payment_method = "cash" THEN amount ELSE 0 END) as cash_total,
                SUM(CASE WHEN payment_method = "cheque" THEN amount ELSE 0 END) as cheque_total,
                SUM(CASE WHEN payment_method = "bank_transfer" THEN amount ELSE 0 END) as bank_transfer_total,
                SUM(CASE WHEN payment_method = "mobile_banking" THEN amount ELSE 0 END) as mobile_banking_total'
            )
            ->groupBy('user_id')
            ->get();

        $users = $this->usersFor($rows->pluck('user_id'));

        return $rows->map(fn ($row) => (object) [
            'user' => $users->get($row->user_id),
            'collections_count' => (int) $row->collections_count,
            'total_amount' => (float) $row->total_amount,
            'cash_total' => (float) $row->cash_total,
            'cheque_total' => (float) $row->cheque_total,
            'bank_transfer_total' => (float) $row->bank_transfer_total,
            'mobile_banking_total' => (float) $row->mobile_banking_total,
        ])->sortByDesc('total_amount')->values();
    }

    /**
     * @param  array{date_from?: string, date_to?: string, territory_id?: string}  $filters
     */
    public function territoryPerformance(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);
        $dateFrom = $from->toDateString();
        $dateTo = $to->toDateString();

        $orderValueByTerritory = Order::query()
            ->join('users', 'users.id', '=', 'orders.user_id')
            ->whereBetween('orders.order_date', [$dateFrom, $dateTo])
            ->selectRaw('users.territory_id as territory_id, SUM(orders.total_amount) as total_order_value')
            ->groupBy('users.territory_id')
            ->pluck('total_order_value', 'territory_id');

        $collectionsByTerritory = CollectionEntry::query()
            ->join('users', 'users.id', '=', 'collection_entries.user_id')
            ->whereBetween('collection_entries.collection_date', [$dateFrom, $dateTo])
            ->selectRaw('users.territory_id as territory_id, SUM(collection_entries.amount) as total_collection_amount')
            ->groupBy('users.territory_id')
            ->pluck('total_collection_amount', 'territory_id');

        $visitsByTerritory = Visit::query()
            ->join('users', 'users.id', '=', 'visits.user_id')
            ->whereBetween('visits.check_in_at', [$from, $to])
            ->selectRaw(
                'users.territory_id as territory_id,
                COUNT(*) as total_visits,
                SUM(CASE WHEN visits.is_gps_verified = 1 THEN 1 ELSE 0 END) as gps_verified_count,
                SUM(CASE WHEN visits.is_gps_verified = 0 THEN 1 ELSE 0 END) as gps_unverified_count'
            )
            ->groupBy('users.territory_id')
            ->get()
            ->keyBy('territory_id');

        $executiveCounts = User::role('Sales Executive')
            ->whereNotNull('territory_id')
            ->selectRaw('territory_id, COUNT(*) as executive_count')
            ->groupBy('territory_id')
            ->pluck('executive_count', 'territory_id');

        $territories = Territory::query()
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->where('id', $territoryId))
            ->orderBy('name')
            ->get();

        return $territories->map(function (Territory $territory) use ($orderValueByTerritory, $collectionsByTerritory, $visitsByTerritory, $executiveCounts) {
            $visit = $visitsByTerritory->get($territory->id);
            $gpsVerified = (int) ($visit->gps_verified_count ?? 0);
            $gpsUnverified = (int) ($visit->gps_unverified_count ?? 0);
            $gpsJudged = $gpsVerified + $gpsUnverified;

            return (object) [
                'territory' => $territory,
                'executive_count' => (int) ($executiveCounts->get($territory->id) ?? 0),
                'total_order_value' => (float) ($orderValueByTerritory->get($territory->id) ?? 0),
                'total_collection_amount' => (float) ($collectionsByTerritory->get($territory->id) ?? 0),
                'total_visits' => (int) ($visit->total_visits ?? 0),
                'gps_verified_rate' => $gpsJudged > 0 ? round(($gpsVerified / $gpsJudged) * 100, 1) : 0.0,
            ];
        })->sortByDesc('total_order_value')->values();
    }

    /**
     * @param  array{month?: string, year?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function targetAchievement(array $filters): Collection
    {
        $month = (int) ($filters['month'] ?? Carbon::now()->month);
        $year = (int) ($filters['year'] ?? Carbon::now()->year);

        $targets = Target::query()
            ->with(['user.territory', 'achievement'])
            ->where('month', $month)
            ->where('year', $year)
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->get();

        return $targets->map(fn (Target $target) => (object) [
            'user' => $target->user,
            'month' => $target->month,
            'year' => $target->year,
            'order_target' => (float) $target->order_value_target,
            'order_achieved' => (float) ($target->achievement?->order_achieved ?? 0),
            'order_pct' => (float) ($target->achievement?->order_pct ?? 0),
            'collection_target' => (float) $target->collection_target,
            'collection_achieved' => (float) ($target->achievement?->collection_achieved ?? 0),
            'collection_pct' => (float) ($target->achievement?->collection_pct ?? 0),
            'quantity_target' => (int) $target->quantity_target,
            'quantity_achieved' => (int) ($target->achievement?->quantity_achieved ?? 0),
            'quantity_pct' => (float) ($target->achievement?->quantity_pct ?? 0),
            'overall_pct' => (float) ($target->achievement?->overall_pct ?? 0),
            'grade' => $target->achievement?->grade,
        ])->sortByDesc('overall_pct')->values();
    }

    /**
     * A per-executive scorecard for one month, combining metrics already
     * computed by the other reports (attendance, visits, orders,
     * collections) with that same month's Target/Achievement — nothing new
     * is queried that isn't already exposed elsewhere, this just merges by
     * user_id into a single row per executive.
     *
     * @param  array{month?: string, year?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function executivePerformance(array $filters): Collection
    {
        $month = (int) ($filters['month'] ?? Carbon::now()->month);
        $year = (int) ($filters['year'] ?? Carbon::now()->year);
        $periodStart = Carbon::create($year, $month, 1)->startOfMonth();

        $dateFilters = [
            'date_from' => $periodStart->toDateString(),
            'date_to' => $periodStart->copy()->endOfMonth()->toDateString(),
            'user_id' => $filters['user_id'] ?? null,
            'territory_id' => $filters['territory_id'] ?? null,
        ];

        $attendance = $this->attendanceSummary($dateFilters)->keyBy(fn ($row) => $row->user->id);
        $visits = $this->visitCompliance($dateFilters)->keyBy(fn ($row) => $row->user->id);
        $orders = $this->orderPerformance($dateFilters)->keyBy(fn ($row) => $row->user->id);
        $collections = $this->collectionSummary($dateFilters)->keyBy(fn ($row) => $row->user->id);

        $achievements = Achievement::query()
            ->join('targets', 'targets.id', '=', 'achievements.target_id')
            ->where('targets.month', $month)
            ->where('targets.year', $year)
            ->select('achievements.*', 'targets.user_id as target_user_id')
            ->get()
            ->keyBy('target_user_id');

        $userIds = $attendance->keys()
            ->merge($visits->keys())
            ->merge($orders->keys())
            ->merge($collections->keys())
            ->merge($achievements->keys())
            ->unique();

        $users = $this->usersFor($userIds);

        return $userIds->map(function ($userId) use ($attendance, $visits, $orders, $collections, $achievements, $users) {
            $achievement = $achievements->get($userId);

            return (object) [
                'user' => $users->get($userId),
                'attendance_rate' => $attendance->get($userId)->attendance_rate ?? 0.0,
                'visit_completion_rate' => $visits->get($userId)->completion_rate ?? 0.0,
                'gps_verified_rate' => $visits->get($userId)->gps_verified_rate ?? 0.0,
                'total_order_value' => $orders->get($userId)->total_order_value ?? 0.0,
                'total_collection_amount' => $collections->get($userId)->total_amount ?? 0.0,
                'overall_achievement_pct' => $achievement ? (float) $achievement->overall_pct : 0.0,
                'grade' => $achievement?->grade,
            ];
        })->sortByDesc('overall_achievement_pct')->values();
    }

    /**
     * Per-territory dealer coverage: how many of that territory's
     * dealers received at least one visit in the period. Sorted
     * ascending by coverage rate — the least-covered territories (the most
     * actionable ones) surface first.
     *
     * @param  array{date_from?: string, date_to?: string, territory_id?: string}  $filters
     */
    public function dealerCoverage(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);

        $totalsByTerritory = Dealer::query()
            ->whereNotNull('territory_id')
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->where('territory_id', $territoryId))
            ->selectRaw('territory_id, COUNT(*) as total_dealers')
            ->groupBy('territory_id')
            ->pluck('total_dealers', 'territory_id');

        $visitedByTerritory = Visit::query()
            ->join('dealers', 'dealers.id', '=', 'visits.dealer_id')
            ->whereBetween('visits.check_in_at', [$from, $to])
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->where('dealers.territory_id', $territoryId))
            ->selectRaw('dealers.territory_id as territory_id, COUNT(DISTINCT visits.dealer_id) as visited_dealers')
            ->groupBy('dealers.territory_id')
            ->pluck('visited_dealers', 'territory_id');

        $territoryIds = $totalsByTerritory->keys();
        $territories = Territory::query()->whereIn('id', $territoryIds)->get()->keyBy('id');

        return $territoryIds->map(function ($territoryId) use ($totalsByTerritory, $visitedByTerritory, $territories) {
            $total = (int) $totalsByTerritory->get($territoryId, 0);
            $visited = (int) $visitedByTerritory->get($territoryId, 0);

            return (object) [
                'territory' => $territories->get($territoryId),
                'total_dealers' => $total,
                'visited_dealers' => $visited,
                'not_visited_dealers' => max(0, $total - $visited),
                'coverage_rate' => $total > 0 ? round(($visited / $total) * 100, 1) : 0.0,
            ];
        })->sortBy('coverage_rate')->values();
    }

    /**
     * Raw GPS telemetry coverage per executive (ping volume, accuracy,
     * battery, last-seen) — distinct from Visit Compliance's "was this
     * specific visit GPS-verified" metric.
     *
     * @param  array{date_from?: string, date_to?: string, user_id?: string, territory_id?: string}  $filters
     */
    public function gpsReport(array $filters): Collection
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($filters);

        $rows = GpsLog::query()
            ->whereBetween('recorded_at', [$from, $to])
            ->when($filters['user_id'] ?? null, fn (Builder $q, $userId) => $q->where('user_id', $userId))
            ->when($filters['territory_id'] ?? null, fn (Builder $q, $territoryId) => $q->whereHas(
                'user', fn (Builder $u) => $u->where('territory_id', $territoryId)
            ))
            ->selectRaw(
                'user_id,
                COUNT(*) as ping_count,
                AVG(accuracy) as avg_accuracy,
                AVG(battery_level) as avg_battery_level,
                MAX(recorded_at) as last_seen_at'
            )
            ->groupBy('user_id')
            ->get();

        $users = $this->usersFor($rows->pluck('user_id'));

        return $rows->map(fn ($row) => (object) [
            'user' => $users->get($row->user_id),
            'ping_count' => (int) $row->ping_count,
            'avg_accuracy' => $row->avg_accuracy !== null ? round((float) $row->avg_accuracy, 1) : null,
            'avg_battery_level' => $row->avg_battery_level !== null ? round((float) $row->avg_battery_level, 1) : null,
            'last_seen_at' => $row->last_seen_at !== null ? Carbon::parse($row->last_seen_at) : null,
        ])->sortByDesc('ping_count')->values();
    }

    /**
     * @param  Collection<int, int>  $userIds
     * @return Collection<int, User>
     */
    private function usersFor(Collection $userIds): Collection
    {
        return User::with('territory')->whereIn('id', $userIds->unique())->get()->keyBy('id');
    }
}
