<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubmitLeaveRequest;
use App\Http\Resources\LeaveRequestResource;
use App\Http\Resources\LeaveTypeResource;
use App\Models\LeaveRequest;
use App\Services\LeaveBalanceService;
use App\Services\LeaveRequestService;
use App\Services\LeaveTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Self-service leave for the mobile app: a Sales Executive sees the leave
 * types, checks what they have left, submits a request and withdraws one
 * they no longer need.
 *
 * There is deliberately no approve/reject endpoint here. Deciding someone
 * else's leave is a manager's action on the admin web UI, and exposing it
 * to the mobile API would put an approval button one permission mistake
 * away from every phone in the field.
 */
#[OA\Tag(name: 'Leave', description: 'Leave types, balances and the executive\'s own requests.')]
class LeaveController extends Controller
{
    public function __construct(
        private readonly LeaveRequestService $leaveRequests,
        private readonly LeaveTypeService $leaveTypes,
        private readonly LeaveBalanceService $balances,
    ) {}

    #[OA\Get(
        path: '/leave/types',
        tags: ['Leave'],
        summary: 'Active leave types available to request',
        security: [['sanctum' => []]],
        responses: [new OA\Response(response: 200, description: 'Leave types')],
    )]
    public function types(): JsonResponse
    {
        return ApiResponse::success(
            LeaveTypeResource::collection($this->leaveTypes->activeTypes()),
        );
    }

    #[OA\Get(
        path: '/leave/balance',
        tags: ['Leave'],
        summary: "The authenticated user's leave balance per type",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'year', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Balance per leave type')],
    )]
    public function balance(Request $request): JsonResponse
    {
        $year = (int) $request->input('year', now()->format('Y'));

        $rows = $this->balances->forUser($request->user(), $year)->map(fn ($row) => [
            'leave_type' => [
                'id' => $row->leave_type->id,
                'name' => $row->leave_type->name,
            ],
            'entitled_days' => $row->entitled,
            'carried_forward_days' => $row->carried_forward,
            'total_days' => $row->total,
            'used_days' => $row->used,
            // Can be negative when leave was approved beyond entitlement;
            // the app should show that rather than clamp it to zero.
            'remaining_days' => $row->remaining,
        ]);

        return ApiResponse::success($rows, "Leave balance for {$year}.");
    }

    #[OA\Get(
        path: '/leave/requests',
        tags: ['Leave'],
        summary: "The authenticated user's own leave requests",
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['pending', 'approved', 'rejected', 'cancelled'])),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Leave requests')],
    )]
    public function index(Request $request): JsonResponse
    {
        // user_id is pinned to the caller rather than taken from the query
        // string: this endpoint only ever returns your own leave.
        $filters = [
            'user_id' => $request->user()->id,
            'status' => $request->input('status'),
        ];

        $paginator = $this->leaveRequests->paginate(
            array_filter($filters),
            (int) $request->integer('per_page', 15),
            $request->user(),
        );

        $paginator->setCollection(
            LeaveRequestResource::collection($paginator->getCollection())->collection
        );

        return ApiResponse::paginated($paginator);
    }

    #[OA\Post(
        path: '/leave/requests',
        tags: ['Leave'],
        summary: 'Submit a leave request',
        security: [['sanctum' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['leave_type_id', 'from_date', 'to_date', 'reason'],
                properties: [
                    new OA\Property(property: 'leave_type_id', type: 'integer'),
                    new OA\Property(property: 'from_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'to_date', type: 'string', format: 'date'),
                    new OA\Property(property: 'is_half_day', type: 'boolean', nullable: true, description: 'Single date only; counts as 0.5 days'),
                    new OA\Property(property: 'reason', type: 'string'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 201, description: 'Submitted, awaiting approval'),
            new OA\Response(response: 422, description: 'Overlaps existing leave, or the range has no working days'),
        ],
    )]
    public function store(SubmitLeaveRequest $request): JsonResponse
    {
        $leaveRequest = $this->leaveRequests->submit($request->user(), $request->validated());

        return ApiResponse::success(
            new LeaveRequestResource($leaveRequest->load('leaveType')),
            'Leave request submitted and is awaiting approval.',
            201,
        );
    }

    #[OA\Get(
        path: '/leave/requests/{leaveRequest}',
        tags: ['Leave'],
        summary: 'One of the authenticated user\'s leave requests',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'leaveRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Leave request'),
            new OA\Response(response: 403, description: 'Not yours to view'),
        ],
    )]
    public function show(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('view', $leaveRequest);

        return ApiResponse::success(
            new LeaveRequestResource($leaveRequest->load(['leaveType', 'approver'])),
        );
    }

    #[OA\Delete(
        path: '/leave/requests/{leaveRequest}',
        tags: ['Leave'],
        summary: 'Withdraw a leave request',
        description: 'Allowed while pending, and for approved leave that has not started yet. Approved leave already under way cannot be withdrawn.',
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'leaveRequest', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Withdrawn'),
            new OA\Response(response: 403, description: 'Not yours to withdraw'),
            new OA\Response(response: 422, description: 'Already decided, or the leave has started'),
        ],
    )]
    public function cancel(LeaveRequest $leaveRequest): JsonResponse
    {
        $this->authorize('cancel', $leaveRequest);

        $cancelled = $this->leaveRequests->cancel($leaveRequest);

        return ApiResponse::success(
            new LeaveRequestResource($cancelled->load('leaveType')),
            'Leave request withdrawn.',
        );
    }
}
