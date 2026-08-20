<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Territory;
use App\Repositories\Contracts\CustomerRepositoryInterface;
use App\Services\TerritoryMapService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * A single read-only GIS dashboard page — no backing owned entity of its
 * own (it visualizes Territory + computed Achievement data), so this
 * mirrors Reports/Activity Log's direct-permission-check shape rather than
 * a Policy class.
 */
class TerritoryMapController extends Controller
{
    public function __construct(
        private readonly TerritoryMapService $territoryMap,
        private readonly CustomerRepositoryInterface $customers,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('territory-map.view'), 403);

        $filters = $request->only(['month', 'year']);

        return view('territory-map.index', [
            'territories' => $this->territoryMap->markers(),
            'filters' => [
                'month' => (int) ($filters['month'] ?? Carbon::now()->month),
                'year' => (int) ($filters['year'] ?? Carbon::now()->year),
            ],
        ]);
    }

    /**
     * Drill-down payload for one territory: performance summary for the
     * requested month + a paginated customer list — fetched only when a
     * user actually clicks a territory, not for the whole map upfront.
     */
    public function show(Request $request, Territory $territory): JsonResponse
    {
        abort_unless($request->user()?->can('territory-map.view'), 403);

        $month = (int) ($request->integer('month') ?: Carbon::now()->month);
        $year = (int) ($request->integer('year') ?: Carbon::now()->year);

        $performance = $this->territoryMap->performanceFor($territory, $month, $year);

        $customers = $this->customers->paginateWithFilters([
            'territory_id' => $territory->id,
        ], 10);

        return response()->json([
            'territory' => [
                'id' => $territory->id,
                'name' => $territory->name,
                'code' => $territory->code,
                'managerName' => $territory->manager?->name,
                'executiveCount' => $performance->executive_count ?? 0,
                'totalSalesValue' => $performance->total_sales_value ?? 0.0,
                'totalCollectionAmount' => $performance->total_collection_amount ?? 0.0,
                'totalVisits' => $performance->total_visits ?? 0,
                'gpsVerifiedRate' => $performance->gps_verified_rate ?? 0.0,
                'avgAchievementPct' => $performance->avg_achievement_pct,
                'grade' => $performance->grade?->value,
                'gradeLabel' => $performance->grade?->label(),
            ],
            'customers' => [
                'data' => $customers->getCollection()->map(fn ($customer) => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'code' => $customer->customer_code,
                    'type' => $customer->type->label(),
                    'phone' => $customer->phone,
                    'url' => route('customers.show', $customer),
                ]),
                'currentPage' => $customers->currentPage(),
                'lastPage' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
        ]);
    }
}
