<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use App\Enums\TallySyncErrorCode;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SubmitTallyStockSyncRequest;
use App\Http\Requests\Api\V1\SubmitTallySyncResultRequest;
use App\Models\SyncQueue;
use App\Models\TallyConnection;
use App\Services\TallyLedgerSyncService;
use App\Services\TallyMasterPushService;
use App\Services\TallyMasterSyncService;
use App\Services\TallyStockSyncService;
use App\Services\TallySyncQueueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Consumed exclusively by the Tally Sync Agent running on the customer's
 * own PC/server — never by the mobile app or a human user. Authenticated by
 * the tally.agent middleware (a machine credential hashed against one
 * tally_connections row), not Sanctum, so there is no risk of a Sales
 * Executive's own token reaching these routes (spec §39: never expose
 * Tally administrative operations to mobile users).
 */
#[OA\Tag(name: 'Tally Sync Agent', description: "Machine-to-machine endpoints for the customer's Tally Sync Agent — not for the mobile app.")]
class TallyIntegrationController extends Controller
{
    /** The four entity types that are masters, in either direction. */
    private const MASTER_ENTITY_TYPES = [
        TallyEntityType::Dealer,
        TallyEntityType::Retailer,
        TallyEntityType::Product,
        TallyEntityType::Depot,
    ];

    public function __construct(
        private readonly TallySyncQueueService $queue,
        private readonly TallyStockSyncService $stockSync,
        private readonly TallyLedgerSyncService $ledgerSync,
        private readonly TallyMasterSyncService $masterSync,
        private readonly TallyMasterPushService $masterPush,
    ) {}

    #[OA\Post(
        path: '/integration/tally/agent/heartbeat',
        tags: ['Tally Sync Agent'],
        summary: "Report the Sync Agent's liveness, and optionally the Tally company GUID it discovered",
        security: [['tallyAgent' => []]],
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'tally_company_guid', type: 'string', nullable: true),
                ],
            ),
        ),
        responses: [new OA\Response(response: 200, description: 'Heartbeat recorded')],
    )]
    public function heartbeat(Request $request): JsonResponse
    {
        /** @var TallyConnection $connection */
        $connection = $request->attributes->get('tallyConnection');

        $connection->update([
            'last_heartbeat_at' => now(),
            'tally_company_guid' => $request->filled('tally_company_guid')
                ? $request->string('tally_company_guid')->toString()
                : $connection->tally_company_guid,
        ]);

        return ApiResponse::success([
            'connection_name' => $connection->connection_name,
            'is_active' => $connection->is_active,
        ], 'Heartbeat recorded.');
    }

    #[OA\Get(
        path: '/integration/tally/agent/jobs',
        tags: ['Tally Sync Agent'],
        summary: 'Claim the next batch of pending sync jobs',
        security: [['tallyAgent' => []]],
        parameters: [
            new OA\Parameter(name: 'limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Claimed jobs (may be empty)')],
    )]
    public function jobs(Request $request): JsonResponse
    {
        $items = $this->queue->claimNext((int) $request->integer('limit', 10));

        return ApiResponse::success($items->map(fn (SyncQueue $item) => [
            'id' => $item->id,
            'entity_type' => $item->entity_type->value,
            'entity_id' => $item->entity_id,
            'direction' => $item->direction->value,
            'external_reference' => $item->external_reference,
            'payload' => $item->payload,
            'attempt_count' => $item->attempt_count,
        ]));
    }

    #[OA\Post(
        path: '/integration/tally/agent/jobs/{syncQueue}/result',
        tags: ['Tally Sync Agent'],
        summary: 'Report the outcome of one claimed sync job',
        security: [['tallyAgent' => []]],
        parameters: [
            new OA\Parameter(name: 'syncQueue', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['success'],
                properties: [
                    new OA\Property(property: 'success', type: 'boolean'),
                    new OA\Property(property: 'response', type: 'object', nullable: true),
                    new OA\Property(property: 'tally_guid', type: 'string', nullable: true),
                    new OA\Property(property: 'tally_voucher_number', type: 'string', nullable: true),
                    new OA\Property(property: 'error_code', type: 'string', nullable: true, description: 'Required when success is false'),
                    new OA\Property(property: 'error_message', type: 'string', nullable: true, description: 'Required when success is false'),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Result recorded'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function jobResult(SubmitTallySyncResultRequest $request, SyncQueue $syncQueue): JsonResponse
    {
        $data = $request->validated();

        $response = $data['response'] ?? [];

        if ($data['success'] && $syncQueue->entity_type === TallyEntityType::Ledger && $syncQueue->entity_id) {
            $this->ledgerSync->applyPulledEntries($syncQueue->entity_id, $response['rows'] ?? []);
        }

        // Master pulls used to stop here, with the agent's data recorded and
        // never acted on — so a sync reported success while leaving every
        // dealer and product unmapped. The outcome is folded back into the
        // stored response so the Sync Queue screen shows what actually
        // happened rather than a raw dump.
        if ($data['success'] && $this->isMasterPull($syncQueue)) {
            $response['applied'] = $this->masterSync->applyPulledMasters(
                $syncQueue->entity_type,
                $response['rows'] ?? [],
            );
        }

        // A pushed master is only useful once its Tally GUID is recorded
        // here: without it the record stays unmapped and the next push
        // would try to create it again, colliding on the name.
        if ($data['success'] && $this->isMasterPush($syncQueue) && filled($data['tally_guid'] ?? null)) {
            $response['mapped'] = $this->masterPush->applyPushResult($syncQueue, $data['tally_guid']);
        }

        $item = $data['success']
            ? $this->queue->markSuccess(
                $syncQueue,
                $response,
                $data['tally_guid'] ?? null,
                $data['tally_voucher_number'] ?? null,
            )
            : $this->queue->markFailed(
                $syncQueue,
                TallySyncErrorCode::from($data['error_code']),
                $data['error_message'],
                $response,
            );

        return ApiResponse::success(['status' => $item->status->value], 'Result recorded.');
    }


    /**
     * A master create pushed INTO Tally (the SFA -> Tally direction), as
     * opposed to a voucher push or any pull.
     */
    private function isMasterPush(SyncQueue $syncQueue): bool
    {
        return $syncQueue->direction === SyncDirection::PushToTally
            && in_array($syncQueue->entity_type, self::MASTER_ENTITY_TYPES, true);
    }

    /**
     * A master-list pull (dealers/retailers/products/depots), as opposed to
     * a voucher push or a per-dealer ledger pull.
     */
    private function isMasterPull(SyncQueue $syncQueue): bool
    {
        return $syncQueue->direction === SyncDirection::PullFromTally
            && in_array($syncQueue->entity_type, self::MASTER_ENTITY_TYPES, true);
    }

    #[OA\Post(
        path: '/integration/tally/stock-sync',
        tags: ['Tally Sync Agent'],
        summary: 'Push a full godown-wise stock snapshot (Tally is the source of truth for these figures)',
        security: [['tallyAgent' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['rows'],
                properties: [
                    new OA\Property(property: 'rows', type: 'array', items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'stock_item_name', type: 'string'),
                            new OA\Property(property: 'godown_name', type: 'string'),
                            new OA\Property(property: 'stock_item_tally_guid', type: 'string', nullable: true),
                            new OA\Property(property: 'godown_tally_guid', type: 'string', nullable: true),
                            new OA\Property(property: 'opening_qty', type: 'number'),
                            new OA\Property(property: 'in_qty', type: 'number'),
                            new OA\Property(property: 'out_qty', type: 'number'),
                            new OA\Property(property: 'closing_qty', type: 'number'),
                        ],
                    )),
                ],
            ),
        ),
        responses: [
            new OA\Response(response: 200, description: 'Snapshot applied'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function stockSync(SubmitTallyStockSyncRequest $request): JsonResponse
    {
        $result = $this->stockSync->applySnapshot($request->validated('rows'));

        /** @var TallyConnection $connection */
        $connection = $request->attributes->get('tallyConnection');
        $connection->update(['last_successful_sync_at' => now()]);

        return ApiResponse::success($result, "Stock snapshot applied: {$result['applied']} row(s), {$result['skipped']} skipped (unmapped depot/product).");
    }
}
