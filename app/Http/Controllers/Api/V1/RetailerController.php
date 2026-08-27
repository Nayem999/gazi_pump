<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\RetailerResource;
use App\Models\Retailer;
use App\Services\RetailerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Retailer lookups for the field mobile app: a Sales Executive filters a
 * dealer's own retailers (its downstream shops) when placing an order for
 * one of them.
 */
class RetailerController extends Controller
{
    public function __construct(private readonly RetailerService $retailers) {}

    #[OA\Get(
        path: '/retailers',
        tags: ['Retailers'],
        summary: 'List retailers, optionally filtered to a single dealer',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'dealer_id', in: 'query', required: false, description: 'Filter to a single dealer\'s retailers', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated retailer list')],
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Retailer::class);

        $filters = $request->only(['search', 'dealer_id']);
        $filters['status'] = 'active';

        $retailers = $this->retailers->paginate($filters, (int) $request->integer('per_page', 20));

        return ApiResponse::success(
            RetailerResource::collection($retailers->items()),
            'Retailers retrieved.',
            200,
            [
                'current_page' => $retailers->currentPage(),
                'per_page' => $retailers->perPage(),
                'total' => $retailers->total(),
                'last_page' => $retailers->lastPage(),
            ],
        );
    }

    #[OA\Get(
        path: '/retailers/{id}',
        tags: ['Retailers'],
        summary: 'Get a single retailer',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Retailer detail'), new OA\Response(response: 404, description: 'Not found')],
    )]
    public function show(Retailer $retailer): JsonResponse
    {
        $this->authorize('view', $retailer);

        return ApiResponse::success(new RetailerResource($retailer->load('dealer')));
    }
}
