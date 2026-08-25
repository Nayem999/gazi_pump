<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\AttendanceSummaryExport;
use App\Exports\CollectionSummaryExport;
use App\Exports\DealerCoverageExport;
use App\Exports\DealerLedgerSummaryExport;
use App\Exports\ExecutivePerformanceExport;
use App\Exports\GpsReportExport;
use App\Exports\OrderPerformanceExport;
use App\Exports\TargetAchievementExport;
use App\Exports\TerritoryPerformanceExport;
use App\Exports\VisitComplianceExport;
use App\Helpers\PermissionName;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\Division;
use App\Models\Territory;
use App\Models\User;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Read-only aggregate reports over data captured by other modules. No
 * Eloquent model backs a "report", so authorization is a direct permission
 * check per action (`report.{key}`) rather than a Policy class.
 */
class ReportController extends Controller
{
    private const GEO_FILTER_KEYS = ['division_id', 'district_id', 'thana_id', 'territory_id'];

    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        return view('reports.index');
    }

    public function attendanceSummary(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('attendance')), 403);

        $filters = $request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]);

        return view('reports.attendance-summary', [
            'rows' => $this->paginate($this->reports->attendanceSummary($this->resolveGeoFilters($filters)), $request),
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function attendanceSummaryExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('attendance')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->attendanceSummary($filters);

        return Excel::download(new AttendanceSummaryExport($rows), 'attendance-summary-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function attendanceSummaryPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('attendance')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->attendanceSummary($filters);

        return Pdf::loadView('reports.attendance-summary-print', ['rows' => $rows])
            ->stream('attendance-summary-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function visitCompliance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('visits')), 403);

        $filters = $request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]);

        return view('reports.visit-compliance', [
            'rows' => $this->paginate($this->reports->visitCompliance($this->resolveGeoFilters($filters)), $request),
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function visitComplianceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('visits')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->visitCompliance($filters);

        return Excel::download(new VisitComplianceExport($rows), 'visit-compliance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function visitCompliancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('visits')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->visitCompliance($filters);

        return Pdf::loadView('reports.visit-compliance-print', ['rows' => $rows])
            ->stream('visit-compliance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function orderPerformance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('order-performance')), 403);

        $filters = $request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]);
        $rows = $this->reports->orderPerformance($this->resolveGeoFilters($filters));

        return view('reports.order-performance', [
            'rows' => $this->paginate($rows, $request),
            'totals' => [
                'order_count' => $rows->sum('order_count'),
                'total_quantity' => $rows->sum('total_quantity'),
                'total_order_value' => $rows->sum('total_order_value'),
            ],
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function orderPerformanceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('order-performance')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->orderPerformance($filters);

        return Excel::download(new OrderPerformanceExport($rows), 'order-performance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function orderPerformancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('order-performance')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->orderPerformance($filters);

        return Pdf::loadView('reports.order-performance-print', ['rows' => $rows])
            ->stream('order-performance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function collectionSummary(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('collections')), 403);

        $filters = $request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]);
        $rows = $this->reports->collectionSummary($this->resolveGeoFilters($filters));

        return view('reports.collection-summary', [
            'rows' => $this->paginate($rows, $request),
            'totals' => [
                'collections_count' => $rows->sum('collections_count'),
                'total_amount' => $rows->sum('total_amount'),
                'cash_total' => $rows->sum('cash_total'),
                'cheque_total' => $rows->sum('cheque_total'),
                'bank_transfer_total' => $rows->sum('bank_transfer_total'),
                'mobile_banking_total' => $rows->sum('mobile_banking_total'),
            ],
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function collectionSummaryExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('collections')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->collectionSummary($filters);

        return Excel::download(new CollectionSummaryExport($rows), 'collection-summary-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function collectionSummaryPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('collections')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->collectionSummary($filters);

        return Pdf::loadView('reports.collection-summary-print', ['rows' => $rows])
            ->stream('collection-summary-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function territoryPerformance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('territories')), 403);

        $filters = $request->only(['date_from', 'date_to', ...self::GEO_FILTER_KEYS]);

        return view('reports.territory-performance', [
            'rows' => $this->paginate($this->reports->territoryPerformance($this->resolveGeoFilters($filters)), $request),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function territoryPerformanceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('territories')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->territoryPerformance($filters);

        return Excel::download(new TerritoryPerformanceExport($rows), 'territory-performance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function territoryPerformancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('territories')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->territoryPerformance($filters);

        return Pdf::loadView('reports.territory-performance-print', ['rows' => $rows])
            ->stream('territory-performance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function targetAchievement(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('target-achievement')), 403);

        $filters = $this->periodFilters($request);

        return view('reports.target-achievement', [
            'rows' => $this->paginate($this->reports->targetAchievement($this->resolveGeoFilters($filters)), $request),
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function targetAchievementExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('target-achievement')), 403);

        $rows = $this->reports->targetAchievement($this->resolveGeoFilters($this->periodFilters($request)));

        return Excel::download(new TargetAchievementExport($rows), 'target-achievement-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function targetAchievementPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('target-achievement')), 403);

        $rows = $this->reports->targetAchievement($this->resolveGeoFilters($this->periodFilters($request)));

        return Pdf::loadView('reports.target-achievement-print', ['rows' => $rows])
            ->stream('target-achievement-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function executivePerformance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('executive-performance')), 403);

        $filters = $this->periodFilters($request);

        return view('reports.executive-performance', [
            'rows' => $this->paginate($this->reports->executivePerformance($this->resolveGeoFilters($filters)), $request),
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function executivePerformanceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('executive-performance')), 403);

        $rows = $this->reports->executivePerformance($this->resolveGeoFilters($this->periodFilters($request)));

        return Excel::download(new ExecutivePerformanceExport($rows), 'executive-performance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function executivePerformancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('executive-performance')), 403);

        $rows = $this->reports->executivePerformance($this->resolveGeoFilters($this->periodFilters($request)));

        return Pdf::loadView('reports.executive-performance-print', ['rows' => $rows])
            ->stream('executive-performance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function dealerCoverage(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-coverage')), 403);

        $filters = $request->only(['date_from', 'date_to', ...self::GEO_FILTER_KEYS]);

        return view('reports.dealer-coverage', [
            'rows' => $this->paginate($this->reports->dealerCoverage($this->resolveGeoFilters($filters)), $request),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function dealerCoverageExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-coverage')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->dealerCoverage($filters);

        return Excel::download(new DealerCoverageExport($rows), 'dealer-coverage-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function dealerCoveragePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-coverage')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->dealerCoverage($filters);

        return Pdf::loadView('reports.dealer-coverage-print', ['rows' => $rows])
            ->stream('dealer-coverage-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function gpsReport(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('gps')), 403);

        $filters = $request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]);

        return view('reports.gps-report', [
            'rows' => $this->paginate($this->reports->gpsReport($this->resolveGeoFilters($filters)), $request),
            'executives' => $this->executives(),
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function gpsReportExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('gps')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->gpsReport($filters);

        return Excel::download(new GpsReportExport($rows), 'gps-report-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function gpsReportPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('gps')), 403);

        $filters = $this->resolveGeoFilters($request->only(['date_from', 'date_to', 'user_id', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->gpsReport($filters);

        return Pdf::loadView('reports.gps-report-print', ['rows' => $rows])
            ->stream('gps-report-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function dealerLedgerSummary(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-ledger')), 403);

        $filters = $request->only(['search', ...self::GEO_FILTER_KEYS]);
        $rows = $this->reports->dealerLedgerSummary($this->resolveGeoFilters($filters));

        return view('reports.dealer-ledger-summary', [
            'rows' => $this->paginate($rows, $request),
            'totals' => [
                'total_ordered' => $rows->sum('total_ordered'),
                'total_collected' => $rows->sum('total_collected'),
                'due_amount' => $rows->sum('due_amount'),
            ],
            'divisions' => $this->divisions(),
            'territories' => $this->territories(),
            'filters' => $filters,
        ]);
    }

    public function dealerLedgerSummaryExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-ledger')), 403);

        $filters = $this->resolveGeoFilters($request->only(['search', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->dealerLedgerSummary($filters);

        return Excel::download(new DealerLedgerSummaryExport($rows), 'dealer-ledger-summary-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function dealerLedgerSummaryPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-ledger')), 403);

        $filters = $this->resolveGeoFilters($request->only(['search', ...self::GEO_FILTER_KEYS]));
        $rows = $this->reports->dealerLedgerSummary($filters);

        return Pdf::loadView('reports.dealer-ledger-summary-print', ['rows' => $rows])
            ->stream('dealer-ledger-summary-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function dealerLedger(Request $request, Dealer $dealer): View
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-ledger')), 403);

        $rows = $this->reports->dealerLedger($dealer);

        return view('reports.dealer-ledger', [
            'dealer' => $dealer->load('territory'),
            'rows' => $rows,
            'balance' => $rows->last()->balance ?? 0.0,
        ]);
    }

    public function dealerLedgerPrint(Request $request, Dealer $dealer): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('dealer-ledger')), 403);

        $rows = $this->reports->dealerLedger($dealer);

        return Pdf::loadView('reports.dealer-ledger-print', [
            'dealer' => $dealer->load('territory'),
            'rows' => $rows,
            'balance' => $rows->last()->balance ?? 0.0,
        ])->stream('dealer-ledger-'.$dealer->dealer_code.'-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * @return array{month: int, year: int, user_id: string|null, division_id: string|null, district_id: string|null, thana_id: string|null, territory_id: string|null}
     */
    private function periodFilters(Request $request): array
    {
        return [
            'month' => (int) ($request->integer('month') ?: Carbon::now()->month),
            'year' => (int) ($request->integer('year') ?: Carbon::now()->year),
            'user_id' => $request->input('user_id'),
            'division_id' => $request->input('division_id'),
            'district_id' => $request->input('district_id'),
            'thana_id' => $request->input('thana_id'),
            'territory_id' => $request->input('territory_id'),
        ];
    }

    /**
     * Every report ultimately filters by `territory_id`. When a Division/
     * District/Thana filter is chosen instead (or in addition), it's
     * resolved here into the matching territory id(s) so each report
     * method's existing `territory_id` handling — already written to accept
     * either a single id or an array via `whereIn` — keeps working
     * unchanged. An explicit `territory_id` (the most specific filter)
     * always wins over a broader geo filter if somehow both are present.
     *
     * @param  array<string, mixed>  $filters
     * @return array<string, mixed>
     */
    private function resolveGeoFilters(array $filters): array
    {
        if (! empty($filters['territory_id'])) {
            return $filters;
        }

        if (empty($filters['division_id']) && empty($filters['district_id']) && empty($filters['thana_id'])) {
            return $filters;
        }

        $territoryIds = Territory::query()
            ->when($filters['division_id'] ?? null, fn ($q, $id) => $q->where('division_id', $id))
            ->when($filters['district_id'] ?? null, fn ($q, $id) => $q->where('district_id', $id))
            ->when($filters['thana_id'] ?? null, fn ($q, $id) => $q->where('thana_id', $id))
            ->pluck('id')
            ->all();

        // No territory matches the chosen geo scope — use an id that can
        // never exist so the report correctly returns zero rows instead of
        // silently falling back to "no filter at all".
        $filters['territory_id'] = $territoryIds ?: [0];

        return $filters;
    }

    /**
     * @return Collection<int, User>
     */
    private function executives(): Collection
    {
        return User::role('Sales Executive')->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Division>
     */
    private function divisions(): Collection
    {
        return Division::where('status', true)->orderBy('name')->get();
    }

    /**
     * Only territories with at least one assigned user — with the real
     * Union Council boundaries imported (5,000+ rows), most territories
     * have no executives and therefore no report data to filter by.
     *
     * @return Collection<int, Territory>
     */
    private function territories(): Collection
    {
        return Territory::has('users')->orderBy('name')->get();
    }

    /**
     * Report aggregations are computed in-memory across several grouped
     * queries (not a single paginatable builder), so pagination happens by
     * slicing the already-computed Collection — export/print still work
     * from the full, unsliced Collection.
     *
     * @param  SupportCollection<int, object>  $items
     */
    private function paginate(SupportCollection $items, Request $request, int $perPage = 20): LengthAwarePaginator
    {
        $page = $request->integer('page', 1);

        return new LengthAwarePaginator(
            $items->forPage($page, $perPage)->values(),
            $items->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()],
        );
    }
}
