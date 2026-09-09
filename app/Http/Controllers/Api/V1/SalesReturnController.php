<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSalesReturnRequest;
use App\Http\Resources\SalesReturnResource;
use App\Models\Order;
use App\Models\SalesReturn;
use App\Services\SalesReturnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service field return request for the mobile app: a Sales Executive
 * requests a return against one of their own orders. Approval/dispatch/
 * depot-receiving/Tally sync are all Admin Dashboard actions
 * (Web\Admin\SalesReturnController) — the mobile surface here is
 * deliberately just "request" + "see my own history", same shape as
 * Order/CollectionEntry's own mobile split.
 */
class SalesReturnController extends Controller
{
    public function __construct(private readonly SalesReturnService $returns) {}

    #[OA\Post(
        path: '/sales-returns',
        tags: ['Sales Returns'],
        summary: 'Request a return against one of the authenticated user\'s own orders',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['order_id', 'items'],
                properties: [
                    new OA\Property(property: 'order_id', type: 'integer'),
                    new OA\Property(property: 'reason', type: 'string', nullable: true),
                    new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                        required: ['order_item_id', 'quantity'],
                        properties: [
                            new OA\Property(property: 'order_item_id', type: 'integer'),
                            new OA\Property(property: 'quantity', type: 'number', format: 'float'),
                        ],
                    )),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Return requested'),
            new OA\Response(response: 404, description: 'Order not found, or not owned by the authenticated user'),
            new OA\Response(response: 422, description: 'Validation error, or a requested quantity exceeds what is left to return'),
        ],
    )]
    public function store(StoreSalesReturnRequest $request): JsonResponse
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($request->integer('order_id'));

        $return = $this->returns->requestReturn($order, $request->user(), $request->only(['reason', 'items']));

        return ApiResponse::success(new SalesReturnResource($return->load('items.product')), 'Return requested.', 201);
    }

    #[OA\Get(
        path: '/sales-returns',
        tags: ['Sales Returns'],
        summary: "List the authenticated user's own return requests",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated return history')],
    )]
    public function index(Request $request): JsonResponse
    {
        $returns = SalesReturn::where('user_id', $request->user()->id)
            ->with('items.product')
            ->latest()
            ->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            SalesReturnResource::collection($returns->items()),
            'Return history retrieved.',
            200,
            [
                'current_page' => $returns->currentPage(),
                'per_page' => $returns->perPage(),
                'total' => $returns->total(),
                'last_page' => $returns->lastPage(),
            ],
        );
    }
}
