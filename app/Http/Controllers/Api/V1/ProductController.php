<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Read-only product catalog for the mobile app: browsing/searching products
 * to reference when entering a sale (Module 8) or, later, for the Customer
 * Web Portal's product catalog (Module 17).
 */
class ProductController extends Controller
{
    public function __construct(private readonly ProductService $products) {}

    #[OA\Get(
        path: '/products',
        tags: ['Products'],
        summary: 'List active products',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated product list')],
    )]
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Product::class);

        $filters = $request->only(['search', 'category_id']);
        $filters['status'] = 'active';

        $products = $this->products->paginate($filters, (int) $request->integer('per_page', 20));

        return ApiResponse::success(
            ProductResource::collection($products->items()),
            'Products retrieved.',
            200,
            [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
        );
    }

    #[OA\Get(
        path: '/products/{id}',
        tags: ['Products'],
        summary: 'Get a single product',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [new OA\Response(response: 200, description: 'Product detail'), new OA\Response(response: 404, description: 'Not found')],
    )]
    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);

        return ApiResponse::success(new ProductResource($product->load('category')));
    }
}
