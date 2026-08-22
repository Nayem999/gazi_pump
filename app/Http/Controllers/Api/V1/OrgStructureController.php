<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Division;
use App\Models\SalesTeam;
use App\Models\Territory;
use App\Models\Thana;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Read-only org-structure lookups for the mobile app (dropdowns/filters in
 * Attendance, Visit, Sales, Collection, etc. from Module 5 onward).
 */
class OrgStructureController extends Controller
{
    #[OA\Get(
        path: '/sales-teams',
        tags: ['Org Structure'],
        summary: 'List active sales teams',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Sales team list')],
    )]
    public function salesTeams(): JsonResponse
    {
        return ApiResponse::success(
            SalesTeam::query()->where('status', true)->orderBy('name')->get(['id', 'name', 'code'])
        );
    }

    #[OA\Get(
        path: '/territories',
        tags: ['Org Structure'],
        summary: 'List active territories',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'thana_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Territory list')],
    )]
    public function territories(Request $request): JsonResponse
    {
        return ApiResponse::success(
            Territory::query()
                ->where('status', true)
                ->when($request->integer('thana_id'), fn ($query, $id) => $query->where('thana_id', $id))
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'center_lat', 'center_lng'])
        );
    }

    #[OA\Get(
        path: '/divisions',
        tags: ['Org Structure'],
        summary: 'List active divisions',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Division list')],
    )]
    public function divisions(): JsonResponse
    {
        return ApiResponse::success(
            Division::query()->where('status', true)->orderBy('name')->get(['id', 'name', 'name_bn'])
        );
    }

    #[OA\Get(
        path: '/districts',
        tags: ['Org Structure'],
        summary: 'List active districts',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'division_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'District list')],
    )]
    public function districts(Request $request): JsonResponse
    {
        return ApiResponse::success(
            District::query()
                ->where('status', true)
                ->when($request->integer('division_id'), fn ($query, $id) => $query->where('division_id', $id))
                ->orderBy('name')
                ->get(['id', 'division_id', 'name', 'name_bn'])
        );
    }

    #[OA\Get(
        path: '/thanas',
        tags: ['Org Structure'],
        summary: 'List active thanas/upazilas',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'district_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Thana list')],
    )]
    public function thanas(Request $request): JsonResponse
    {
        return ApiResponse::success(
            Thana::query()
                ->where('status', true)
                ->when($request->integer('district_id'), fn ($query, $id) => $query->where('district_id', $id))
                ->orderBy('name')
                ->get(['id', 'district_id', 'name', 'name_bn'])
        );
    }
}
