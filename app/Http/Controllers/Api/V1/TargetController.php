<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\TargetResource;
use App\Models\Target;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * Read-only self-service target/achievement lookup for the mobile app: a
 * Sales Executive can see what they're being measured against and how
 * they're tracking, but never creates or edits their own targets — that's
 * an Admin Dashboard concern (Module 10 web CRUD, assigned by managers).
 */
class TargetController extends Controller
{
    #[OA\Get(
        path: '/targets/current',
        tags: ['Targets'],
        summary: "Get the authenticated user's target and achievement for the current month",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'The current-month target with its computed achievement, or null if none is assigned')],
    )]
    public function current(Request $request): JsonResponse
    {
        $today = Carbon::today();

        $target = Target::where('user_id', $request->user()->id)
            ->where('month', $today->month)
            ->where('year', $today->year)
            ->with('achievement')
            ->first();

        return ApiResponse::success($target ? new TargetResource($target) : null);
    }

    #[OA\Get(
        path: '/targets',
        tags: ['Targets'],
        summary: "List the authenticated user's own targets and achievements",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'year', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated target/achievement history')],
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Target::where('user_id', $request->user()->id)->with('achievement');

        if ($request->filled('year')) {
            $query->where('year', $request->integer('year'));
        }

        $targets = $query->orderByDesc('year')->orderByDesc('month')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            TargetResource::collection($targets->items()),
            'Targets retrieved.',
            200,
            [
                'current_page' => $targets->currentPage(),
                'per_page' => $targets->perPage(),
                'total' => $targets->total(),
                'last_page' => $targets->lastPage(),
            ],
        );
    }
}
