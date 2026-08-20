<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Exports\AttendanceSummaryExport;
use App\Exports\CollectionSummaryExport;
use App\Exports\CustomerCoverageExport;
use App\Exports\ExecutivePerformanceExport;
use App\Exports\GpsReportExport;
use App\Exports\SalesPerformanceExport;
use App\Exports\TargetAchievementExport;
use App\Exports\TerritoryPerformanceExport;
use App\Exports\VisitComplianceExport;
use App\Helpers\PermissionName;
use App\Http\Controllers\Controller;
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
    public function __construct(private readonly ReportService $reports) {}

    public function index(Request $request): View
    {
        return view('reports.index');
    }

    public function attendanceSummary(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('attendance')), 403);

        return view('reports.attendance-summary', [
            'rows' => $this->paginate($this->reports->attendanceSummary($request->only(['date_from', 'date_to', 'user_id', 'territory_id'])), $request),
            'executives' => $this->executives(),
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'user_id', 'territory_id']),
        ]);
    }

    public function attendanceSummaryExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('attendance')), 403);

        $rows = $this->reports->attendanceSummary($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Excel::download(new AttendanceSummaryExport($rows), 'attendance-summary-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function attendanceSummaryPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('attendance')), 403);

        $rows = $this->reports->attendanceSummary($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.attendance-summary-print', ['rows' => $rows])
            ->stream('attendance-summary-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function visitCompliance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('visits')), 403);

        return view('reports.visit-compliance', [
            'rows' => $this->paginate($this->reports->visitCompliance($request->only(['date_from', 'date_to', 'user_id', 'territory_id'])), $request),
            'executives' => $this->executives(),
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'user_id', 'territory_id']),
        ]);
    }

    public function visitComplianceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('visits')), 403);

        $rows = $this->reports->visitCompliance($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Excel::download(new VisitComplianceExport($rows), 'visit-compliance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function visitCompliancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('visits')), 403);

        $rows = $this->reports->visitCompliance($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.visit-compliance-print', ['rows' => $rows])
            ->stream('visit-compliance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function salesPerformance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('sales')), 403);

        $rows = $this->reports->salesPerformance($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return view('reports.sales-performance', [
            'rows' => $this->paginate($rows, $request),
            'totals' => [
                'sales_count' => $rows->sum('sales_count'),
                'total_quantity' => $rows->sum('total_quantity'),
                'total_sales_value' => $rows->sum('total_sales_value'),
            ],
            'executives' => $this->executives(),
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'user_id', 'territory_id']),
        ]);
    }

    public function salesPerformanceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('sales')), 403);

        $rows = $this->reports->salesPerformance($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Excel::download(new SalesPerformanceExport($rows), 'sales-performance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function salesPerformancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('sales')), 403);

        $rows = $this->reports->salesPerformance($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.sales-performance-print', ['rows' => $rows])
            ->stream('sales-performance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function collectionSummary(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('collections')), 403);

        $rows = $this->reports->collectionSummary($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

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
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'user_id', 'territory_id']),
        ]);
    }

    public function collectionSummaryExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('collections')), 403);

        $rows = $this->reports->collectionSummary($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Excel::download(new CollectionSummaryExport($rows), 'collection-summary-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function collectionSummaryPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('collections')), 403);

        $rows = $this->reports->collectionSummary($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.collection-summary-print', ['rows' => $rows])
            ->stream('collection-summary-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function territoryPerformance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('territories')), 403);

        return view('reports.territory-performance', [
            'rows' => $this->paginate($this->reports->territoryPerformance($request->only(['date_from', 'date_to', 'territory_id'])), $request),
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'territory_id']),
        ]);
    }

    public function territoryPerformanceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('territories')), 403);

        $rows = $this->reports->territoryPerformance($request->only(['date_from', 'date_to', 'territory_id']));

        return Excel::download(new TerritoryPerformanceExport($rows), 'territory-performance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function territoryPerformancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('territories')), 403);

        $rows = $this->reports->territoryPerformance($request->only(['date_from', 'date_to', 'territory_id']));

        return Pdf::loadView('reports.territory-performance-print', ['rows' => $rows])
            ->stream('territory-performance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function targetAchievement(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('target-achievement')), 403);

        return view('reports.target-achievement', [
            'rows' => $this->paginate($this->reports->targetAchievement($request->only(['month', 'year', 'user_id', 'territory_id'])), $request),
            'executives' => $this->executives(),
            'territories' => $this->territories(),
            'filters' => $this->periodFilters($request),
        ]);
    }

    public function targetAchievementExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('target-achievement')), 403);

        $rows = $this->reports->targetAchievement($request->only(['month', 'year', 'user_id', 'territory_id']));

        return Excel::download(new TargetAchievementExport($rows), 'target-achievement-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function targetAchievementPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('target-achievement')), 403);

        $rows = $this->reports->targetAchievement($request->only(['month', 'year', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.target-achievement-print', ['rows' => $rows])
            ->stream('target-achievement-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function executivePerformance(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('executive-performance')), 403);

        return view('reports.executive-performance', [
            'rows' => $this->paginate($this->reports->executivePerformance($request->only(['month', 'year', 'user_id', 'territory_id'])), $request),
            'executives' => $this->executives(),
            'territories' => $this->territories(),
            'filters' => $this->periodFilters($request),
        ]);
    }

    public function executivePerformanceExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('executive-performance')), 403);

        $rows = $this->reports->executivePerformance($request->only(['month', 'year', 'user_id', 'territory_id']));

        return Excel::download(new ExecutivePerformanceExport($rows), 'executive-performance-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function executivePerformancePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('executive-performance')), 403);

        $rows = $this->reports->executivePerformance($request->only(['month', 'year', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.executive-performance-print', ['rows' => $rows])
            ->stream('executive-performance-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function customerCoverage(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('customer-coverage')), 403);

        return view('reports.customer-coverage', [
            'rows' => $this->paginate($this->reports->customerCoverage($request->only(['date_from', 'date_to', 'territory_id'])), $request),
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'territory_id']),
        ]);
    }

    public function customerCoverageExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('customer-coverage')), 403);

        $rows = $this->reports->customerCoverage($request->only(['date_from', 'date_to', 'territory_id']));

        return Excel::download(new CustomerCoverageExport($rows), 'customer-coverage-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function customerCoveragePrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('customer-coverage')), 403);

        $rows = $this->reports->customerCoverage($request->only(['date_from', 'date_to', 'territory_id']));

        return Pdf::loadView('reports.customer-coverage-print', ['rows' => $rows])
            ->stream('customer-coverage-'.now()->format('Y-m-d-His').'.pdf');
    }

    public function gpsReport(Request $request): View
    {
        abort_unless($request->user()?->can(PermissionName::report('gps')), 403);

        return view('reports.gps-report', [
            'rows' => $this->paginate($this->reports->gpsReport($request->only(['date_from', 'date_to', 'user_id', 'territory_id'])), $request),
            'executives' => $this->executives(),
            'territories' => $this->territories(),
            'filters' => $request->only(['date_from', 'date_to', 'user_id', 'territory_id']),
        ]);
    }

    public function gpsReportExport(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('gps')), 403);

        $rows = $this->reports->gpsReport($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Excel::download(new GpsReportExport($rows), 'gps-report-'.now()->format('Y-m-d-His').'.xlsx');
    }

    public function gpsReportPrint(Request $request): mixed
    {
        abort_unless($request->user()?->can(PermissionName::report('gps')), 403);

        $rows = $this->reports->gpsReport($request->only(['date_from', 'date_to', 'user_id', 'territory_id']));

        return Pdf::loadView('reports.gps-report-print', ['rows' => $rows])
            ->stream('gps-report-'.now()->format('Y-m-d-His').'.pdf');
    }

    /**
     * @return array{month: int, year: int, user_id: string|null, territory_id: string|null}
     */
    private function periodFilters(Request $request): array
    {
        return [
            'month' => (int) ($request->integer('month') ?: Carbon::now()->month),
            'year' => (int) ($request->integer('year') ?: Carbon::now()->year),
            'user_id' => $request->input('user_id'),
            'territory_id' => $request->input('territory_id'),
        ];
    }

    /**
     * @return Collection<int, User>
     */
    private function executives(): Collection
    {
        return User::role('Sales Executive')->orderBy('name')->get();
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
