<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web\Admin;

use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Http\Controllers\Controller;
use App\Models\Dealer;
use App\Models\SyncLog;
use App\Models\SyncQueue;
use App\Models\TallyConnection;
use App\Services\TallyLedgerSyncService;
use App\Services\TallyMappingService;
use App\Services\TallyMasterPushService;
use App\Services\TallyReconciliationService;
use App\Services\TallyRetryService;
use App\Services\TallySyncQueueService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Read-mostly screens (Dashboard/Sync Queue/Sync Logs/Mapping/
 * Reconciliation) plus one action (retry) — same shape as ActivityLogController:
 * a direct permission check per action rather than a Policy class, since
 * none of these screens have their own CRUD-owned model to authorize
 * against (SyncQueue/SyncLog/TallyMapping are system-managed, never
 * created/edited by hand).
 */
class TallyIntegrationController extends Controller
{
    public function __construct(
        private readonly TallySyncQueueService $queue,
        private readonly TallyRetryService $retry,
        private readonly TallyReconciliationService $reconciliation,
        private readonly TallyMappingService $mappings,
        private readonly TallyLedgerSyncService $ledgerSync,
        private readonly TallyMasterPushService $masterPush,
    ) {}

    public function dashboard(Request $request): View
    {
        abort_unless($request->user()?->can('tally-integration.view'), 403);

        $today = now()->startOfDay();

        return view('tally-integration.dashboard', [
            'successfulToday' => SyncQueue::where('status', SyncStatus::Success)->where('completed_at', '>=', $today)->count(),
            'pending' => SyncQueue::where('status', SyncStatus::Pending)->count(),
            'failed' => SyncQueue::where('status', SyncStatus::Failed)->count(),
            'retrying' => SyncQueue::where('status', SyncStatus::Retry)->count(),
            'connections' => TallyConnection::withoutTrashed()->get(),
            'lastSuccessfulSync' => TallyConnection::max('last_successful_sync_at'),
            // Named per entity type so the card can say exactly what would
            // be created in Tally — writing into someone's accounting
            // system should never be a surprise.
            // Agent liveness beside the queue depth: either alone reads as
            // fine, together they show a stall.
            'waitingJobs' => SyncQueue::whereIn('status', [SyncStatus::Pending, SyncStatus::Processing])->count(),
            'agentOnline' => TallyConnection::withoutTrashed()->where('is_active', true)->get()
                ->contains(fn (TallyConnection $connection) => $connection->isOnline()),
            'lastHeartbeat' => TallyConnection::withoutTrashed()->max('last_heartbeat_at')
                ? \Illuminate\Support\Carbon::parse(TallyConnection::withoutTrashed()->max('last_heartbeat_at'))
                : null,
            'pushEnabled' => $this->masterPush->enabled(),
            'pendingPush' => collect($this->masterPush->pending())
                ->map(fn ($records) => $records->pluck('name')->all())
                ->all(),
        ]);
    }

    public function syncQueue(Request $request): View
    {
        abort_unless($request->user()?->can('tally-integration.view'), 403);

        $filters = $request->only(['entity_type', 'status', 'direction']);

        return view('tally-integration.sync-queue', [
            'items' => $this->queue->paginate($filters, 20),
            'filters' => $filters,
            'entityTypes' => TallyEntityType::cases(),
            'statuses' => SyncStatus::cases(),
        ]);
    }

    public function retrySyncQueueItem(Request $request, SyncQueue $syncQueue): RedirectResponse
    {
        abort_unless($request->user()?->can('tally-integration.retry'), 403);

        $this->retry->manualRetry($syncQueue);

        return back()->with('success', "Sync job {$syncQueue->external_reference} queued for retry.");
    }

    public function syncLogs(Request $request): View
    {
        abort_unless($request->user()?->can('tally-integration.view'), 403);

        $filters = $request->only(['entity_type', 'status', 'direction', 'date_from', 'date_to']);

        $logs = SyncLog::query()
            ->when($filters['entity_type'] ?? null, fn ($q, $type) => $q->where('entity_type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['direction'] ?? null, fn ($q, $direction) => $q->where('direction', $direction))
            ->when($filters['date_from'] ?? null, fn ($q, $date) => $q->whereDate('request_time', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($q, $date) => $q->whereDate('request_time', '<=', $date))
            ->latest('request_time')
            ->paginate(20)
            ->withQueryString();

        return view('tally-integration.sync-logs', [
            'logs' => $logs,
            'filters' => $filters,
            'entityTypes' => TallyEntityType::cases(),
        ]);
    }

    public function mapping(Request $request): View
    {
        abort_unless($request->user()?->can('tally-integration.view'), 403);

        $filters = $request->only(['entity_type', 'sync_status', 'search']);

        return view('tally-integration.mapping', [
            'mappings' => $this->mappings->paginate($filters, 20),
            'filters' => $filters,
            'entityTypes' => TallyEntityType::cases(),
        ]);
    }

    public function reconciliation(Request $request): View
    {
        abort_unless($request->user()?->can('tally-integration.reconcile'), 403);

        return view('tally-integration.reconciliation', [
            'dealers' => $this->reconciliation->compareDealers(),
            'retailers' => $this->reconciliation->compareRetailers(),
            'products' => $this->reconciliation->compareProducts(),
            'dealerBalances' => $this->reconciliation->compareDealerBalances(),
            'failedSyncs' => $this->reconciliation->failedSyncs(),
        ]);
    }

    /**
     * "Sync Now" — queues a full Tally→SFA refresh in one action: every
     * master list, plus a ledger pull for each mapped dealer.
     *
     * These are queued, not performed here: only the Sync Agent can reach
     * Tally (it sits on the customer's own PC and SFA never connects
     * inward), so this hands it work and it collects on its next poll. The
     * flash message says so rather than implying the data has already
     * landed.
     *
     * Stock is deliberately not queueable: the agent pushes a full snapshot
     * on its own timer, so there is no job for SFA to create.
     */
    public function syncAll(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('tally-integration.sync'), 403);

        $masters = [
            TallyEntityType::Dealer,
            TallyEntityType::Retailer,
            TallyEntityType::Product,
            TallyEntityType::Depot,
        ];

        $queuedMasters = 0;

        foreach ($masters as $entityType) {
            if ($this->queue->enqueueMasterPull($entityType)) {
                $queuedMasters++;
            }
        }

        $mappedDealers = Dealer::whereNotNull('tally_guid')->get();
        $queuedLedgers = 0;

        foreach ($mappedDealers as $dealer) {
            if ($this->ledgerSync->hasPullQueued($dealer)) {
                continue;
            }

            $this->ledgerSync->enqueuePull(
                $dealer,
                $this->ledgerSync->financialYearStart()->toDateString(),
                now()->toDateString(),
            );

            $queuedLedgers++;
        }

        // The other direction: SFA records with no counterpart in Tally.
        // Gated off by default — enqueueAll() reports that rather than
        // silently doing nothing, so the summary can say so too.
        $push = $this->masterPush->enqueueAll();

        return back()->with('success', $this->syncAllSummary(
            $queuedMasters,
            $queuedLedgers,
            $mappedDealers->count(),
            $push,
        ));
    }

    /**
     * Why a queue with pending work is not draining, phrased for the
     * operator — or null when the agent is alive and it really is just
     * "already queued".
     */
    private function stalledQueueWarning(): ?string
    {
        $waiting = SyncQueue::whereIn('status', [SyncStatus::Pending, SyncStatus::Processing])->count();

        if ($waiting === 0) {
            return null;
        }

        $offline = $this->offlineAgentWarning();

        if ($offline === null) {
            return null;
        }

        return "Nothing new to queue: {$waiting} job(s) are already waiting. ".$offline;
    }

    /**
     * A description of the agent's absence, or null if one is checking in.
     */
    private function offlineAgentWarning(): ?string
    {
        $connections = TallyConnection::withoutTrashed()->where('is_active', true)->get();

        if ($connections->isEmpty()) {
            return 'No active Tally connection is configured, so nothing can collect this work.';
        }

        if ($connections->contains(fn (TallyConnection $connection) => $connection->isOnline())) {
            return null;
        }

        $latest = $connections->max('last_heartbeat_at');

        $when = $latest
            ? 'last checked in '.\Illuminate\Support\Carbon::parse($latest)->diffForHumans()
            : 'has never checked in';

        return "The Sync Agent {$when}, so this work will not move until it is running on the machine beside Tally.";
    }

    /**
     * @param  array{queued: int, skipped: array<int, string>, disabled: bool}  $push
     */
    private function syncAllSummary(int $queuedMasters, int $queuedLedgers, int $mappedDealers, array $push): string
    {
        if ($queuedMasters === 0 && $queuedLedgers === 0 && $push['queued'] === 0) {
            // "Already waiting for the agent" was technically true and
            // practically useless: it is exactly what a *stalled* queue
            // says, so clicking Sync Now with a dead agent reported success
            // forever while nothing moved. If work is waiting, the agent's
            // liveness is the only thing worth reporting here.
            $stall = $this->stalledQueueWarning();

            if ($stall !== null) {
                return $stall;
            }

            return $mappedDealers === 0
                ? 'Nothing new to queue — everything already queued, and no dealer has a Tally mapping yet.'
                : 'Nothing new to queue — a sync for all of this is already waiting, and the Sync Agent is online to collect it.';
        }

        $parts = [];

        if ($queuedMasters > 0) {
            $parts[] = "{$queuedMasters} master list".($queuedMasters === 1 ? '' : 's');
        }

        if ($queuedLedgers > 0) {
            $parts[] = "{$queuedLedgers} dealer ledger".($queuedLedgers === 1 ? '' : 's');
        }

        if ($push['queued'] > 0) {
            $parts[] = $push['queued'].' record'.($push['queued'] === 1 ? '' : 's').' to create in Tally';
        }

        $summary = 'Queued '.implode(', ', $parts).' for the Sync Agent — it collects within about a minute. '
            .'The Sync Queue shows what each job changed once it reports back.';

        // Queueing succeeded, but nothing will happen if no agent is
        // listening — say so now rather than let the operator wait.
        if (($offline = $this->offlineAgentWarning()) !== null) {
            $summary .= ' '.$offline;
        }

        if ($mappedDealers === 0) {
            $summary .= ' No dealer ledgers were queued: none have a Tally mapping yet.';
        }

        if ($push['skipped'] !== []) {
            $summary .= ' Not pushed to Tally: '.implode('; ', $push['skipped']).'.';
        }

        return $summary;
    }

    /**
     * Enqueues a fresh pull of this dealer's Tally ledger vouchers, from
     * the opening of the current Tally financial year through today — not
     * the calendar year, since Tally only serves data inside the company's
     * own active period (this customer's runs 1-Sep to 31-Aug, so a
     * calendar-year start would fall outside it and return nothing). A
     * manual trigger for now; each pull is its own queue job, so clicking
     * again later just re-pulls (upserted by tally_guid, never duplicated
     * — see TallyLedgerSyncService).
     */
    public function syncDealerLedger(Request $request, Dealer $dealer): RedirectResponse
    {
        abort_unless($request->user()?->can('tally-integration.sync'), 403);

        $this->ledgerSync->enqueuePull(
            $dealer,
            $this->ledgerSync->financialYearStart()->toDateString(),
            now()->toDateString(),
        );

        return back()->with('success', "Ledger sync queued for {$dealer->name}.");
    }
}
