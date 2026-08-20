<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CheckInRequest;
use App\Http\Requests\Api\V1\CheckOutRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * Self-service attendance for the mobile app: a Sales Executive checks in
 * and out with GPS + a selfie photo, and can view their own history. There
 * is no general "list everyone's attendance" endpoint here on purpose —
 * cross-user attendance is an Admin Dashboard concern (Module 5 web CRUD),
 * not a mobile API concern.
 */
class AttendanceController extends Controller
{
    public function __construct(private readonly AttendanceService $attendances) {}

    #[OA\Post(
        path: '/attendance/check-in',
        tags: ['Attendance'],
        summary: 'Check in for today with GPS + photo',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(
                required: ['lat', 'lng', 'photo'],
                properties: [
                    new OA\Property(property: 'lat', type: 'number', format: 'float'),
                    new OA\Property(property: 'lng', type: 'number', format: 'float'),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary'),
                ],
            )),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Checked in'),
            new OA\Response(response: 422, description: 'Already checked in today'),
        ],
    )]
    public function checkIn(CheckInRequest $request): JsonResponse
    {
        $attendance = $this->attendances->checkIn(
            $request->user(),
            (float) $request->input('lat'),
            (float) $request->input('lng'),
            $request->file('photo'),
        );

        return ApiResponse::success(new AttendanceResource($attendance), 'Checked in successfully.', 201);
    }

    #[OA\Post(
        path: '/attendance/check-out',
        tags: ['Attendance'],
        summary: 'Check out for today with GPS + photo',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(mediaType: 'multipart/form-data', schema: new OA\Schema(
                required: ['lat', 'lng', 'photo'],
                properties: [
                    new OA\Property(property: 'lat', type: 'number', format: 'float'),
                    new OA\Property(property: 'lng', type: 'number', format: 'float'),
                    new OA\Property(property: 'photo', type: 'string', format: 'binary'),
                ],
            )),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Checked out'),
            new OA\Response(response: 422, description: 'Not checked in yet, or already checked out'),
        ],
    )]
    public function checkOut(CheckOutRequest $request): JsonResponse
    {
        $attendance = $this->attendances->checkOut(
            $request->user(),
            (float) $request->input('lat'),
            (float) $request->input('lng'),
            $request->file('photo'),
        );

        return ApiResponse::success(new AttendanceResource($attendance), 'Checked out successfully.');
    }

    #[OA\Get(
        path: '/attendance/today',
        tags: ['Attendance'],
        summary: "Get the authenticated user's attendance status for today",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: "Today's attendance, or null if not checked in yet")],
    )]
    public function today(Request $request): JsonResponse
    {
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->whereDate('date', Carbon::today())
            ->first();

        return ApiResponse::success($attendance ? new AttendanceResource($attendance) : null);
    }

    #[OA\Get(
        path: '/attendance/history',
        tags: ['Attendance'],
        summary: "List the authenticated user's own attendance history",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date_from', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'date_to', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated attendance history')],
    )]
    public function history(Request $request): JsonResponse
    {
        $query = Attendance::where('user_id', $request->user()->id);

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->string('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->string('date_to'));
        }

        $attendances = $query->latest('date')->paginate((int) $request->integer('per_page', 20));

        return ApiResponse::success(
            AttendanceResource::collection($attendances->items()),
            'Attendance history retrieved.',
            200,
            [
                'current_page' => $attendances->currentPage(),
                'per_page' => $attendances->perPage(),
                'total' => $attendances->total(),
                'last_page' => $attendances->lastPage(),
            ],
        );
    }
}
