<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Territory;
use App\Repositories\Contracts\DealerRepositoryInterface;
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
        private readonly DealerRepositoryInterface $dealers,
    ) {}

    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('territory-map.view'), 403);

        $filters = $request->only(['month', 'year', 'division_id', 'district_id', 'thana_id']);

        return view('territory-map.index', [
            'territories' => $this->territoryMap->markers($filters),
            'divisions' => Division::where('status', true)->orderBy('name')->get(),
            'filters' => [
                'month' => (int) ($filters['month'] ?? Carbon::now()->month),
                'year' => (int) ($filters['year'] ?? Carbon::now()->year),
                'division_id' => $filters['division_id'] ?? '',
                'district_id' => $filters['district_id'] ?? '',
                'thana_id' => $filters['thana_id'] ?? '',
            ],
        ]);
    }

    /**
     * Drill-down payload for one territory: performance summary for the
     * requested month + a paginated dealer list — fetched only when a
     * user actually clicks a territory, not for the whole map upfront.
     */
    public function show(Request $request, Territory $territory): JsonResponse
    {
        abort_unless($request->user()?->can('territory-map.view'), 403);

        $month = (int) ($request->integer('month') ?: Carbon::now()->month);
        $year = (int) ($request->integer('year') ?: Carbon::now()->year);

        $performance = $this->territoryMap->performanceFor($territory, $month, $year);

        $dealers = $this->dealers->paginateWithFilters([
            'territory_id' => $territory->id,
        ], 10);

        $territory->load(['division', 'district', 'thana']);

        return response()->json([
            'territory' => [
                'id' => $territory->id,
                'name' => $territory->name,
                'code' => $territory->code,
                'divisionName' => $territory->division?->name,
                'districtName' => $territory->district?->name,
                'thanaName' => $territory->thana?->name,
                'managerName' => $territory->manager?->name,
                'executiveCount' => $performance->executive_count ?? 0,
                'totalOrderValue' => $performance->total_order_value ?? 0.0,
                'totalCollectionAmount' => $performance->total_collection_amount ?? 0.0,
                'totalVisits' => $performance->total_visits ?? 0,
                'gpsVerifiedRate' => $performance->gps_verified_rate ?? 0.0,
                'avgAchievementPct' => $performance->avg_achievement_pct,
                'grade' => $performance->grade?->value,
                'gradeLabel' => $performance->grade?->label(),
            ],
            'dealers' => [
                'data' => $dealers->getCollection()->map(fn ($dealer) => [
                    'id' => $dealer->id,
                    'name' => $dealer->name,
                    'code' => $dealer->dealer_code,
                    'type' => $dealer->type->label(),
                    'phone' => $dealer->phone,
                    'url' => route('dealers.show', $dealer),
                ]),
                'currentPage' => $dealers->currentPage(),
                'lastPage' => $dealers->lastPage(),
                'total' => $dealers->total(),
            ],
        ]);
    }
}
