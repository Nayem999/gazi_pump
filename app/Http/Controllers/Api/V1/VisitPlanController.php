<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\VisitPlanStatus;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreVisitPlanRequest;
use App\Http\Resources\VisitPlanResource;
use App\Models\VisitPlan;
use App\Services\VisitPlanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service daily planning for the mobile app: a Sales Executive plans
 * which customers to visit and pulls back their own plan list. Cross-rep
 * planning/assignment is an Admin Dashboard concern (Module 7 web CRUD).
 */
class VisitPlanController extends Controller
{
    public function __construct(private readonly VisitPlanService $visitPlans) {}

    #[OA\Post(
        path: '/visit-plans',
        tags: ['Visit Plans'],
        summary: 'Plan a visit to a customer for a given date',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customer_id', 'planned_date'],
                properties: [
                    new OA\Property(property: 'customer_id', type: 'integer'),
                    new OA\Property(property: 'planned_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'notes', type: 'string', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Visit plan created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreVisitPlanRequest $request): JsonResponse
    {
        $plan = $this->visitPlans->create([
            ...$request->validated(),
            'user_id' => $request->user()->id,
            'status' => VisitPlanStatus::Planned->value,
        ]);

        return ApiResponse::success(new VisitPlanResource($plan), 'Visit plan created.', 201);
    }

    #[OA\Get(
        path: '/visit-plans',
        tags: ['Visit Plans'],
        summary: "List the authenticated user's own visit plans",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated visit plans')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = VisitPlan::where('user_id', $request->user()->id)->with('customer');

        if ($request->filled('date_from')) {
            $query->whereDate('planned_date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('planned_date', '<=', $request->string('date_to'));
        }

        $plans = $query->latest('planned_date')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            VisitPlanResource::collection($plans->items()),
            'Visit plans retrieved.',
            200,
            [
                'current_page' => $plans->currentPage(),
                'per_page' => $plans->perPage(),
                'total' => $plans->total(),
                'last_page' => $plans->lastPage(),
            ],
        );
    }
}
