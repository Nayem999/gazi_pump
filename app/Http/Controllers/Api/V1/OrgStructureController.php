<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\SalesTeam;
use App\Models\Territory;
use Illuminate\Http\JsonResponse;
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
        responses: [new OA\Response(response: 200, description: 'Territory list')],
    )]
    public function territories(): JsonResponse
    {
        return ApiResponse::success(
            Territory::query()
                ->where('status', true)
                ->orderBy('name')
                ->get(['id', 'name', 'code', 'center_lat', 'center_lng'])
        );
    }
}
