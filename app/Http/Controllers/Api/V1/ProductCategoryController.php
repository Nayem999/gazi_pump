<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Read-only product category lookups for the mobile app's catalog browsing
 * and order-entry category filter (Module 8) — categories are at most one
 * level deep (top-level category -> sub-category), mirroring the admin
 * Product Categories module.
 */
class ProductCategoryController extends Controller
{
    #[OA\Get(
        path: '/product-categories',
        tags: ['Products'],
        summary: 'List active product categories',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(
                name: 'parent_id',
                in: 'query',
                required: false,
                description: 'Filter to sub-categories of this category id, or "none" for top-level categories only. Omit to list every category.',
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [new OA\Response(response: 200, description: 'Product category list')],
    )]
    public function index(Request $request): JsonResponse
    {
        $parentId = $request->query('parent_id');

        return ApiResponse::success(
            ProductCategory::query()
                ->where('status', true)
                ->when($parentId !== null, function ($query) use ($parentId) {
                    $parentId === 'none' ? $query->whereNull('parent_id') : $query->where('parent_id', $parentId);
                })
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'parent_id'])
        );
    }
}
