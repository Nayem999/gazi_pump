<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service field order entry for the mobile app: a Sales Executive
 * records one or more products sold to a dealer in a single stop, and
 * pulls back their own order history. Cross-rep order reporting is an Admin
 * Dashboard concern (Module 8 web CRUD).
 */
class OrderController extends Controller
{
    public function __construct(private readonly OrderService $orders) {}

    #[OA\Post(
        path: '/orders',
        tags: ['Orders'],
        summary: 'Record one or more products sold to a dealer',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['dealer_id', 'items'],
                properties: [
                    new OA\Property(property: 'dealer_id', type: 'integer'),
                    new OA\Property(property: 'retailer_id', type: 'integer', nullable: true, description: 'Optional: one of the dealer\'s own retailers, from GET /retailers?dealer_id='),
                    new OA\Property(property: 'order_date', type: 'string', format: 'date', nullable: true),
                    new OA\Property(property: 'remarks', type: 'string', nullable: true),
                    new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                        required: ['product_id', 'quantity'],
                        properties: [
                            new OA\Property(property: 'product_id', type: 'integer'),
                            new OA\Property(property: 'quantity', type: 'integer'),
                            new OA\Property(property: 'discount_amount', type: 'number', format: 'float', nullable: true),
                        ],
                    )),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Order recorded — each line\'s unit price is snapshotted server-side from the product\'s current price'),
            new OA\Response(response: 422, description: 'Validation error, or a line\'s discount exceeds the configured maximum'),
        ],
    )]
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $entry = $this->orders->recordOrder(
            $request->user(),
            (int) $request->input('dealer_id'),
            $request->input('items'),
            $request->input('order_date'),
            $request->input('remarks'),
            $request->integer('retailer_id') ?: null,
        );

        return ApiResponse::success(new OrderResource($entry), 'Order recorded.', 201);
    }

    #[OA\Get(
        path: '/orders',
        tags: ['Orders'],
        summary: "List the authenticated user's own order history",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'status', in: 'query', required: false, description: 'Filter by approval status', schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated order history')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Order::where('user_id', $request->user()->id)->with(['dealer', 'retailer', 'items.product']);

        if ($request->filled('date_from')) {
            $query->whereDate('order_date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('order_date', '<=', $request->string('date_to'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $orders = $query->latest('order_date')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            OrderResource::collection($orders->items()),
            'Order history retrieved.',
            200,
            [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ],
        );
    }
}
