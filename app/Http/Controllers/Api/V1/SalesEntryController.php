<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSalesEntryRequest;
use App\Http\Resources\SalesEntryResource;
use App\Models\SalesEntry;
use App\Services\SalesEntryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service field sale entry for the mobile app: a Sales Executive
 * records one or more products sold to a customer in a single stop, and
 * pulls back their own sales history. Cross-rep sales reporting is an Admin
 * Dashboard concern (Module 8 web CRUD).
 */
class SalesEntryController extends Controller
{
    public function __construct(private readonly SalesEntryService $salesEntries) {}

    #[OA\Post(
        path: '/sales-entries',
        tags: ['Sales Entries'],
        summary: 'Record one or more products sold to a customer',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customer_id', 'items'],
                properties: [
                    new OA\Property(property: 'customer_id', type: 'integer'),
                    new OA\Property(property: 'sale_date', type: 'string', format: 'date', nullable: true),
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
            new OA\Response(response: 201, description: 'Sale recorded — each line\'s unit price is snapshotted server-side from the product\'s current price'),
            new OA\Response(response: 422, description: 'Validation error, or a line\'s discount exceeds the configured maximum'),
        ],
    )]
    public function store(StoreSalesEntryRequest $request): JsonResponse
    {
        $entry = $this->salesEntries->recordSale(
            $request->user(),
            (int) $request->input('customer_id'),
            $request->input('items'),
            $request->input('sale_date'),
            $request->input('remarks'),
        );

        return ApiResponse::success(new SalesEntryResource($entry), 'Sale recorded.', 201);
    }

    #[OA\Get(
        path: '/sales-entries',
        tags: ['Sales Entries'],
        summary: "List the authenticated user's own sales history",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated sales history')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = SalesEntry::where('user_id', $request->user()->id)->with(['customer', 'items.product']);

        if ($request->filled('date_from')) {
            $query->whereDate('sale_date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('sale_date', '<=', $request->string('date_to'));
        }

        $salesEntries = $query->latest('sale_date')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            SalesEntryResource::collection($salesEntries->items()),
            'Sales history retrieved.',
            200,
            [
                'current_page' => $salesEntries->currentPage(),
                'per_page' => $salesEntries->perPage(),
                'total' => $salesEntries->total(),
                'last_page' => $salesEntries->lastPage(),
            ],
        );
    }
}
