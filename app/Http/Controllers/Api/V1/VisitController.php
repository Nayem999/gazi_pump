<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckInVisitRequest;
use App\Http\Requests\Api\V1\CheckOutVisitRequest;
use App\Http\Resources\VisitResource;
use App\Models\Visit;
use App\Services\VisitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service customer-visit check-in/out for the mobile app: a Sales
 * Executive starts a visit (GPS + photo, verified against the customer's
 * registered location), later ends it with feedback. Cross-rep visit
 * history is an Admin Dashboard concern (Module 7 web CRUD).
 */
class VisitController extends Controller
{
    public function __construct(private readonly VisitService $visits) {}

    #[OA\Post(
        path: '/visits/check-in',
        tags: ['Visits'],
        summary: 'Check in to a customer visit with GPS + photo',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(
                required: ['customer_id', 'lat', 'lng', 'photo'],
                properties: [
                    new OA\Property(property: 'customer_id', type: 'integer'),
                    new OA\Property(property: 'visit_plan_id', type: 'integer', nullable: true),
                    new OA\Property(property: 'lat', type: 'number', format: 'float'),
                    new OA\Property(property: 'lng', type: 'number', format: 'float'),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary'),
                ],
            )),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Checked in — response includes is_gps_verified against the customer\'s registered location'),
            new OA\Response(response: 422, description: 'Validation error, or an open visit is already in progress'),
        ],
    )]
    public function checkIn(CheckInVisitRequest $request): JsonResponse
    {
        $visit = $this->visits->checkIn(
            $request->user(),
            (int) $request->input('customer_id'),
            $request->filled('visit_plan_id') ? (int) $request->input('visit_plan_id') : null,
            (float) $request->input('lat'),
            (float) $request->input('lng'),
            $request->file('photo'),
        );

        return ApiResponse::success(new VisitResource($visit), 'Checked in to visit.', 201);
    }

    #[OA\Post(
        path: '/visits/check-out',
        tags: ['Visits'],
        summary: 'Check out of the current open visit with feedback',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(
                required: ['lat', 'lng', 'feedback'],
                properties: [
                    new OA\Property(property: 'lat', type: 'number', format: 'float'),
                    new OA\Property(property: 'lng', type: 'number', format: 'float'),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary', nullable: true),
                    new OA\Property(property: 'feedback', type: 'string'),
                ],
            )),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Checked out'),
            new OA\Response(response: 422, description: 'No open visit to check out from'),
        ],
    )]
    public function checkOut(CheckOutVisitRequest $request): JsonResponse
    {
        $visit = $this->visits->checkOut(
            $request->user(),
            (float) $request->input('lat'),
            (float) $request->input('lng'),
            $request->file('photo'),
            (string) $request->input('feedback'),
        );

        return ApiResponse::success(new VisitResource($visit), 'Checked out of visit.');
    }

    #[OA\Get(
        path: '/visits/current',
        tags: ['Visits'],
        summary: "Get the authenticated user's currently open visit, if any",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'The open visit, or null if not checked in anywhere')],
    )]
    public function current(Request $request): JsonResponse
    {
        $visit = Visit::where('user_id', $request->user()->id)->whereNull('check_out_at')->latest('check_in_at')->first();

        return ApiResponse::success($visit ? new VisitResource($visit) : null);
    }

    #[OA\Get(
        path: '/visits/history',
        tags: ['Visits'],
        summary: "List the authenticated user's own visit history",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated visit history')],
    )]
    public function history(Request $request): JsonResponse
    {
        $query = Visit::where('user_id', $request->user()->id)->with('customer');

        if ($request->filled('date_from')) {
            $query->whereDate('check_in_at', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('check_in_at', '<=', $request->string('date_to'));
        }

        $visits = $query->latest('check_in_at')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            VisitResource::collection($visits->items()),
            'Visit history retrieved.',
            200,
            [
                'current_page' => $visits->currentPage(),
                'per_page' => $visits->perPage(),
                'total' => $visits->total(),
                'last_page' => $visits->lastPage(),
            ],
        );
    }
}
