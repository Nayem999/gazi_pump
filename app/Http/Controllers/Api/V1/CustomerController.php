<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Customer APIs for the field mobile app: a Sales Executive sees their own
 * territory's customers by default and can register new dealers/retailers
 * they onboard during a visit.
 */
class CustomerController extends Controller
{
    public function __construct(private readonly CustomerService $customers) {}

    #[OA\Get(
        path: '/customers',
        tags: ['Customers'],
        summary: 'List customers (defaults to the authenticated user\'s own territory)',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'territory_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['dealer', 'retailer', 'distributor'])),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated customer list')],
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $filters = $request->only(['search', 'type', 'territory_id']);
        $filters['territory_id'] ??= $request->user()?->territory_id;
        $filters['status'] = 'active';

        $customers = $this->customers->paginate($filters, (int) $request->integer('per_page', 20));

        return ApiResponse::success(
            CustomerResource::collection($customers->items()),
            'Customers retrieved.',
            200,
            [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
            ],
        );
    }

    #[OA\Get(
        path: '/customers/{id}',
        tags: ['Customers'],
        summary: 'Get a single customer',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Customer detail'), new OA\Response(response: 404, description: 'Not found')],
    )]
    public function show(Customer $customer): JsonResponse
    {
        $this->authorize('view', $customer);

        return ApiResponse::success(new CustomerResource($customer->load('territory')));
    }

    #[OA\Post(
        path: '/customers',
        tags: ['Customers'],
        summary: 'Register a new customer (dealer/retailer/distributor) from the field',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['customer_code', 'name', 'type', 'phone'],
                properties: [
                    new OA\Property(property: 'customer_code', type: 'string', example: 'CUST-00099'),
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
            new OA\Response(response: 201, description: 'Customer created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['territory_id'] ??= $request->user()?->territory_id;

        $customer = $this->customers->create($data);

        return ApiResponse::success(new CustomerResource($customer), 'Customer registered successfully.', 201);
    }
}
