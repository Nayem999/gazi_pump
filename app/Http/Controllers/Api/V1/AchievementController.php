<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreAchievementEntryRequest;
use App\Http\Resources\AchievementEntryResource;
use App\Models\AchievementEntry;
use App\Services\AchievementEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * Self-service daily achievement entry for the mobile app: a Sales
 * Executive reports one day's overall figure — or a product-wise breakdown,
 * matching whatever shape that month's Target was set in — and can keep
 * editing it until a manager reviews it. Cross-rep achievement reporting is
 * an Admin Dashboard concern (web CRUD with approve/reject).
 */
class AchievementController extends Controller
{
    public function __construct(private readonly AchievementEntryService $achievementEntries) {}

    #[OA\Post(
        path: '/achievements',
        tags: ['Achievements'],
        summary: 'Record or update the caller\'s own achievement for a date',
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Achievement recorded or updated'),
            new OA\Response(response: 422, description: 'Validation error, or the entry for that date is already reviewed'),
        ],
    )]
    public function store(StoreAchievementEntryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['achievement_items'] = $data['items'] ?? null;
        unset($data['items']);

        $entry = $this->achievementEntries->recordAchievement(
            $request->user(),
            $data['entry_date'] ?? null,
            $data,
        );

        return ApiResponse::success(new AchievementEntryResource($entry->load('items.product')), 'Achievement recorded.', 201);
    }

    #[OA\Get(
        path: '/achievements/current',
        tags: ['Achievements'],
        summary: "Get the authenticated user's achievement entry for today",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: "Today's achievement entry, or null if not yet submitted")],
    )]
    public function current(Request $request): JsonResponse
    {
        $entry = AchievementEntry::where('user_id', $request->user()->id)
            ->whereDate('entry_date', Carbon::today())
            ->with('items.product')
            ->first();

        return ApiResponse::success($entry ? new AchievementEntryResource($entry) : null);
    }

    #[OA\Get(
        path: '/achievements',
        tags: ['Achievements'],
        summary: "List the authenticated user's own achievement history",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated achievement history')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = AchievementEntry::where('user_id', $request->user()->id)->with('items.product');

        if ($request->filled('date_from')) {
            $query->whereDate('entry_date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('entry_date', '<=', $request->string('date_to'));
        }

        $entries = $query->latest('entry_date')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            AchievementEntryResource::collection($entries->items()),
            'Achievement history retrieved.',
            200,
            [
                'current_page' => $entries->currentPage(),
                'per_page' => $entries->perPage(),
                'total' => $entries->total(),
                'last_page' => $entries->lastPage(),
            ],
        );
    }
}
