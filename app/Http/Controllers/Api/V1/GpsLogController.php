<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreGpsLogRequest;
use App\Http\Resources\GpsLogResource;
use App\Services\GpsLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use OpenApi\Attributes as OA;

/**
 * GPS ingestion + self-service history for the mobile app: the device
 * streams a batch of pings periodically (or all at once after being offline)
 * while a Sales Executive is in the field, and can pull its own trail back
 * to render on-device. There is no general "list everyone's location" here —
 * that's the Admin Dashboard's Location History page (Module 6 web) and,
 * later, the Live GPS Dashboard (Module 15).
 */
class GpsLogController extends Controller
{
    public function __construct(private readonly GpsLogService $gpsLogs) {}

    #[OA\Post(
        path: '/gps-logs',
        tags: ['GPS Tracking'],
        summary: 'Ingest a batch of GPS pings (1 or more) for the authenticated user',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['logs'],
                properties: [
                    new OA\Property(property: 'logs', type: 'array', items: new OA\Items(
                        required: ['lat', 'lng', 'recorded_at'],
                        properties: [
                            new OA\Property(property: 'lat', type: 'number', format: 'float', example: 23.8103),
                            new OA\Property(property: 'lng', type: 'number', format: 'float', example: 90.4125),
                            new OA\Property(property: 'recorded_at', type: 'string', format: 'date-time'),
                            new OA\Property(property: 'accuracy', type: 'number', format: 'float', nullable: true, description: 'meters'),
                            new OA\Property(property: 'speed', type: 'number', format: 'float', nullable: true, description: 'km/h'),
                            new OA\Property(property: 'battery_level', type: 'integer', nullable: true, description: '0-100'),
                        ],
                    )),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Pings recorded'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreGpsLogRequest $request): JsonResponse
    {
        $ingested = $this->gpsLogs->ingest($request->user(), $request->validated('logs'));

        return ApiResponse::success(['ingested' => $ingested->count()], 'GPS logs recorded.', 201);
    }

    #[OA\Get(
        path: '/gps-logs/history',
        tags: ['GPS Tracking'],
        summary: "Get the authenticated user's own GPS trail for a date",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'date', in: 'query', required: false, schema: new OA\Schema(type: 'string', format: 'date'), description: 'Defaults to today'),
        ],
        responses: [new OA\Response(response: 200, description: 'Ordered list of pings for that date, plus distance_km in meta')],
    )]
    public function history(Request $request): JsonResponse
    {
        $date = $request->input('date') ?: Carbon::today()->toDateString();
        $logs = $this->gpsLogs->historyForUserOnDate($request->user()->id, $date);

        return ApiResponse::success(
            GpsLogResource::collection($logs),
            'Location history retrieved.',
            200,
            [
                'date' => $date,
                'distance_km' => $this->gpsLogs->distanceForLogs($logs),
            ],
        );
    }
}
