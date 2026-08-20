<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service notification inbox for the mobile app — every endpoint here
 * is scoped to the authenticated user's own notifications only.
 */
class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    #[OA\Get(
        path: '/notifications',
        tags: ['Notifications'],
        summary: "List the authenticated user's own notifications",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['read', 'unread'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Paginated notification list')],
    )]
    public function index(Request $request): JsonResponse
    {
        $notifications = $this->notifications->paginate(
            $request->user(),
            $request->only(['status']),
            (int) $request->integer('per_page', 20),
        );

        return ApiResponse::success(
            NotificationResource::collection($notifications->items()),
            'Notifications retrieved.',
            200,
            [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
            ],
        );
    }

    #[OA\Get(
        path: '/notifications/unread-count',
        tags: ['Notifications'],
        summary: 'Get the unread notification count for the bell badge',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Unread count')],
    )]
    public function unreadCount(Request $request): JsonResponse
    {
        return ApiResponse::success(['unread_count' => $this->notifications->unreadCount($request->user())]);
    }

    #[OA\Post(
        path: '/notifications/{id}/read',
        tags: ['Notifications'],
        summary: 'Mark a single notification as read',
        security: [['sanctum' => []]],
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
        responses: [new OA\Response(response: 200, description: 'Marked as read')],
    )]
    public function markRead(Request $request, string $id): JsonResponse
    {
        $this->notifications->markAsRead($request->user(), $id);

        return ApiResponse::success(message: 'Notification marked as read.');
    }

    #[OA\Post(
        path: '/notifications/read-all',
        tags: ['Notifications'],
        summary: "Mark all of the authenticated user's notifications as read",
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'All marked as read')],
    )]
    public function markAllRead(Request $request): JsonResponse
    {
        $count = $this->notifications->markAllAsRead($request->user());

        return ApiResponse::success(['marked_read' => $count], 'All notifications marked as read.');
    }
}
