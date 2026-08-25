<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreDealerRequest;
use App\Http\Resources\DealerResource;
use App\Models\Dealer;
use App\Services\CollectionEntryService;
use App\Services\DealerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Dealer APIs for the field mobile app: a Sales Executive sees their own
 * territory's dealers by default and can register new dealers/retailers
 * they onboard during a visit.
 */
class DealerController extends Controller
{
    public function __construct(
        private readonly DealerService $dealers,
        private readonly CollectionEntryService $collectionEntries,
    ) {}

    #[OA\Get(
        path: '/dealers',
        tags: ['Dealers'],
        summary: 'List dealers (defaults to the authenticated user\'s own territory)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'territory_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['dealer', 'retailer', 'distributor'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated dealer list')],
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Dealer::class);

        $filters = $request->only(['search', 'type', 'territory_id']);
        $filters['territory_id'] ??= $request->user()?->territories->pluck('id')->all();
        $filters['status'] = 'active';

        $dealers = $this->dealers->paginate($filters, (int) $request->integer('per_page', 20));

        return ApiResponse::success(
            DealerResource::collection($dealers->items()),
            'Dealers retrieved.',
            200,
            [
                'current_page' => $dealers->currentPage(),
                'per_page' => $dealers->perPage(),
                'total' => $dealers->total(),
                'last_page' => $dealers->lastPage(),
            ],
        );
    }

    #[OA\Get(
        path: '/dealers/{id}',
        tags: ['Dealers'],
        summary: 'Get a single dealer',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Dealer detail'), new OA\Response(response: 404, description: 'Not found')],
    )]
    public function show(Dealer $dealer): JsonResponse
    {
        $this->authorize('view', $dealer);

        return ApiResponse::success(new DealerResource($dealer->load(['territory', 'thana', 'district', 'division'])));
    }

    #[OA\Post(
        path: '/dealers',
        tags: ['Dealers'],
        summary: 'Register a new dealer (dealer/retailer/distributor) from the field',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['dealer_code', 'name', 'type', 'phone'],
                properties: [
                    new OA\Property(property: 'dealer_code', type: 'string', example: 'DLR-00099'),
                    new OA\Property(property: 'name', type: 'string', example: 'Karim Traders'),
                    new OA\Property(property: 'type', type: 'string', enum: ['dealer', 'retailer', 'distributor']),
                    new OA\Property(property: 'phone', type: 'string', example: '01712345678'),
                    new OA\Property(property: 'email', type: 'string', nullable: true),
                    new OA\Property(property: 'address', type: 'string', nullable: true),
                    new OA\Property(property: 'gps_lat', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'gps_lng', type: 'number', format: 'float', nullable: true),
                    new OA\Property(property: 'territory_id', type: 'integer', nullable: true),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Dealer created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreDealerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['territory_id'] ??= $request->user()?->territories->first()?->id;

        $dealer = $this->dealers->create($data);

        return ApiResponse::success(new DealerResource($dealer), 'Dealer registered successfully.', 201);
    }

    #[OA\Get(
        path: '/dealers/{id}/outstanding-balance',
        tags: ['Dealers'],
        summary: 'Get a dealer\'s outstanding balance (total ordered minus total collected)',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Outstanding balance'), new OA\Response(response: 404, description: 'Not found')],
    )]
    public function outstandingBalance(Dealer $dealer): JsonResponse
    {
        $this->authorize('view', $dealer);

        return ApiResponse::success([
            'dealer_id' => $dealer->id,
            'outstanding_balance' => $this->collectionEntries->outstandingBalance($dealer->id),
        ]);
    }
}
