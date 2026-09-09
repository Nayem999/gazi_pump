# Tally ↔ SFA Integration

Design and reference documentation for the Tally integration built on top
of the existing architecture (`docs/existing-sfa-architecture.md`). All
seven phases (integration foundation; Order/Collection Entry revival +
Preview; Depot/Stock + Alternative/Split Depot; Delivery/Challan +
Vehicle/Driver + Cash/Cheque ledger rules; Ledger/Credit-Debit
Note/Outstanding; full Sales Return workflow; Reconciliation + mobile
Retailer-create + reports + hardening) are implemented and described in
detail below.

## Principle

SFA stays a field-operations/sales-execution system; Tally stays the
accounting/financial/stock system of record. The integration layer keeps
both synchronized, traceable, secure, retryable, and auditable — it does
not turn SFA into a second accounting system.

```
                    SFA
                     |
       +-------------+--------------+
       |             |              |
   Field Sales    Dealer/Retailer  Mobile
       |             |              |
       +-------------+--------------+
                     v
                  ORDER
                     v
              DEPOT / DELIVERY
                     v
                   TALLY
                     v
            ACCOUNTING / STOCK
```

## Ownership model (source of truth)

| Data | Source of Truth |
|---|---|
| Sales Officer, Territory, Retailer Profile, Dealer Operational Profile, Sales Order, Collection Entry, Return Request, GPS, Market Audit | SFA |
| Product Accounting Master, Stock, Invoice, Payment Accounting, Ledger, Outstanding, Credit Note, Debit Note, Return Accounting | Tally |
| Delivery/Dispatch | Tally/SFA linked |

SFA never allows uncontrolled editing of Tally-owned accounting data.

## Architecture

```
SFA CLOUD
    |
    | HTTPS
    |
SFA Integration API  (routes/api/v1.php, "Tally Sync Agent" group)
    |
    |
Tally Sync Agent  (tally-sync-agent/, Node.js — customer's own PC/server)
    |
    | HTTP
    |
TallyPrime  (local HTTP-XML gateway, e.g. http://localhost:9000)
```

Tally's local port is never exposed to the public internet — only the Sync
Agent, running on the same machine or LAN as Tally, talks to it directly.
Everything SFA-side only ever talks to the Sync Agent over HTTPS. The
format (JSON vs XML) is per-connection (`tally_connections.api_format`),
never hardcoded to one Tally version.

## Data model

- **`tally_connections`** — one row per configured Tally company/install:
  `connection_name`, `tally_company_name`, `tally_company_guid`, `host`,
  `port`, `protocol`, `api_format`, `sync_agent_id`, `sync_agent_token`
  (hashed, never returned in any response), `is_active`,
  `last_heartbeat_at`, `last_successful_sync_at`. "Connected" vs "Offline"
  is computed from heartbeat recency (`TallyConnection::isOnline()`,
  `config('sfa.tally.heartbeat_stale_after_minutes')`), never stored — same
  principle as `VisitPlan::isMissed()`.
- **`tally_mappings`** — the only place SFA↔Tally identity resolution
  happens: `entity_type` (`App\Enums\TallyEntityType`: dealer, retailer,
  product, depot, sales_order, invoice, delivery, collection, return,
  credit_note, debit_note, ledger), `sfa_id`, `tally_guid`, `tally_name`,
  `tally_alter_id`, `last_synced_at`, `sync_status`
  (`App\Enums\TallyMappingSyncStatus`: pending/synced/failed/conflict).
  Unique on `(entity_type, sfa_id)` and `(entity_type, tally_guid)` — never
  resolved by name alone. A Tally GUID already claimed by a *different*
  `sfa_id` is flagged `conflict`, never silently reassigned
  (`TallyMappingService::findOrCreateMapping()`).
- **`sync_queues`** — the working set every sync transaction passes
  through: `entity_type`, `entity_id`, `direction` (`push_to_tally` /
  `pull_from_tally`), `external_reference` (unique — the idempotency key),
  `payload`, `status` (`App\Enums\SyncStatus`: pending/processing/success/
  failed/retry/cancelled), `attempt_count`, `last_attempt_at`,
  `next_attempt_at`, `response`, `error_code`
  (`App\Enums\TallySyncErrorCode`), `error_message`, `completed_at`.
- **`sync_logs`** — append-only history of every attempt (never updated or
  deleted), independent of the queue's working set.
- **`dealers.tally_guid`, `retailers.tally_guid`, `products.tally_guid`** —
  Phase 1's direct-mapping columns (in addition to the generic
  `tally_mappings` table) for the three simplest, proven sync targets.

No `company_id`/tenant column anywhere — this app has no multi-tenancy
concept today (confirmed by search); adding one is out of scope until a
real second tenant exists.

## Synchronization matrix

| Module | Direction | Phase |
|---|---|---|
| Dealer / Retailer Master | Both ways | 1 (mapping proven; full both-way ledger creation is Phase 2) |
| Product / Item | Tally → SFA | 1 (mapping proven; full stock/rate sync is Phase 3) |
| Sales Order | SFA → Tally | 2 |
| Sales Invoice, Delivery/Challan, Sales Return | Tally → SFA | 4, 4, 6 |
| Payment / Collection | SFA → Tally | 2 |
| Stock / Inventory | Tally → SFA | 3 |
| Ledger / Accounts, Credit Note, Debit Note | Tally → SFA | 5 |
| Retailer ↔ Dealer Transaction | Both ways | 7 |

## Sync process

1. A caller enqueues a job: `TallySyncQueueService::enqueue($entityType,
   $entityId, $direction, $externalReference, $payload)`. Idempotent —
   `firstOrCreate` on `external_reference`, so a retried call from an
   at-least-once caller never creates a second row.
2. The Sync Agent polls `GET /api/v1/integration/tally/agent/jobs`, which
   calls `TallySyncQueueService::claimNext()` — a locked
   (`lockForUpdate()`) transaction marks up to `limit` `Pending` rows
   `Processing` and bumps `attempt_count`, so two concurrent polls (or a
   poll racing the retry command) can never claim the same row twice.
3. The agent executes the job against local Tally
   (`tally-sync-agent/src/handlers.js`) and reports the outcome:
   `POST /api/v1/integration/tally/agent/jobs/{id}/result`.
4. `TallySyncQueueService::markSuccess()`/`markFailed()` updates the queue
   row, writes a `sync_logs` entry, and — on failure — asks
   `TallyRetryService` whether the error is retryable and attempts remain.

## Retry logic

`App\Services\TallyRetryService`, exact backoff table:

| Attempt | Delay |
|---|---|
| 1 | Immediate |
| 2 | 1 minute |
| 3 | 5 minutes |
| 4 | 15 minutes |
| 5 | 30 minutes |
| 6+ | `Failed` — no further automatic retry |

`tally:process-retries` (scheduled every minute, `->withoutOverlapping()`)
flips due `Retry` rows back to `Pending` so the agent's normal poll picks
them up — it never dispatches anything itself. An admin can manually retry
any `Failed` row from the Sync Queue screen
(`TallyRetryService::manualRetry()` resets the attempt counter for a clean
new run of the table).

Only `TallySyncErrorCode::isRetryable()` codes (`TALLY_OFFLINE`,
`TALLY_TIMEOUT`) are ever auto-retried — a missing mapping or an invalid
amount will fail identically every time until a human fixes the underlying
data, so those go straight to `Failed`.

## Error classification

`App\Enums\TallySyncErrorCode`: `TALLY_OFFLINE`, `TALLY_TIMEOUT`,
`TALLY_COMPANY_NOT_FOUND`, `TALLY_LEDGER_NOT_FOUND`,
`TALLY_PRODUCT_NOT_FOUND`, `TALLY_INVALID_VOUCHER`, `STOCK_NOT_AVAILABLE`,
`DUPLICATE_TRANSACTION`, `CUSTOMER_MAPPING_MISSING`,
`PRODUCT_MAPPING_MISSING`, `INVALID_AMOUNT`, `INVALID_QUANTITY`. The raw
Tally response is preserved (`sync_queues.response` / `sync_logs`) for
debugging without exposing it to non-technical users beyond the
error code's plain-English `label()`.

## Security

- Tally's port is never public — only the Sync Agent (customer's own
  machine) reaches it, over plain local HTTP.
- The Sync Agent authenticates to SFA with its own machine credential
  (`tally_connections.sync_agent_token`, hashed at rest, shown in plaintext
  exactly once at creation/regeneration —
  `TallyConnectionService::createWithAgentCredentials()`/
  `regenerateAgentToken()`), via the `tally.agent` middleware
  (`App\Http\Middleware\AuthenticateTallySyncAgent`) — **not** a human
  Sanctum token, so a Sales Executive's own login can never reach these
  endpoints (verified in `tests/Api/TallyIntegrationAgentTest.php`).
- Admin access to Tally Integration (Connections/Dashboard/Queue/Logs/
  Mapping/Reconciliation) requires `tally-integration.{view,configure,
  sync,retry,reconcile}` permissions, assigned only to Super Admin and
  General Manager — Sales Officers get none.
- Credentials are never logged, never returned in any JSON/Blade
  serialization (`TallyConnection::$hidden`), never hardcoded (host/port/
  company name/GUID are all per-connection config, not constants).

## Reconciliation

Phase 1 (`App\Services\TallyReconciliationService`) can only compare
Dealer/Retailer/Product masters against their `tally_mappings` row —
`Matched` / `NotSynced` / `Conflict` (using the same enum,
`App\Enums\ReconciliationStatus`, that later phases extend with
`SfaOnly`/`TallyOnly`/`AmountMismatch`/`QuantityMismatch`/
`CustomerMismatch` once Order/Invoice/Stock/Return exist to compare
against with real Tally-side data).

## Admin UI

`Admin → Tally Integration` (sidebar, gated by `menu.tally-integration`):
Dashboard (today's success/pending/failed/retrying counts, connection
health), Connections (full CRUD + test-connection + regenerate-token),
Sync Queue (filterable, manual retry), Sync Logs (filterable, read-only),
Mapping (read-only), Reconciliation (Phase 1 stub). Connections
deliberately has no export/import/print — nothing to report on, and
importing a spreadsheet of Tally credentials would be a real security hole.

## Phase 2 — Order/Collection Entry revived, Sales Order Preview

Order and Collection Entry are live again for General Manager, Sales/Area/
Territory Manager, and Sales Executive (their own records) — permissions
restored to their pre-retirement shape, including `report.order-performance`
and `report.collections`. Cash Handover and the computed Dealer Ledger
report stay retired (the latter superseded by Phase 5's real Tally ledger
sync); Achievement is untouched and remains the daily rollup mechanism.

- **Schema**: `orders` and `collection_entries` each gained
  `external_reference`, `tally_guid`, `tally_order_number` /
  `tally_voucher_number`, `sync_status` (`App\Enums\TallyRecordSyncStatus`),
  `sync_error`, `synced_at`. `dealers` gained `tally_ledger_name` (spec §7)
  — Tally's voucher-import XML addresses ledgers by name, not GUID, so the
  Sync Agent needs a real name to put in a voucher even though the GUID is
  what identifies the ledger for mapping purposes.
- **Two-dimensional status** (spec §46): an Order/CollectionEntry's existing
  `status` (`ApprovalStatus`: Pending/Approved/Rejected) is its business
  status, untouched; `sync_status` is a wholly separate column tracking
  whether *that already-decided* record has reached Tally. `external_reference`
  is generated once, at creation (`SFA-SO-{Ymd}-{id}` /
  `SFA-COL-{Ymd}-{id}`), so it exists before approval and never changes.
- **Sync trigger**: only `OrderService::approve()`/`CollectionEntryService::approve()`
  enqueue a Tally push (`TallyEntityType::SalesOrder`/`Collection`,
  `SyncDirection::PushToTally`) — a Pending or Rejected record never
  syncs. If the dealer or any line's product has no `tally_guid` yet, the
  record's own `sync_status` is set `Failed` with a plain-English
  `sync_error` immediately (no queue row created) rather than enqueueing
  something the Sync Agent could never complete — the business approval
  itself still succeeds either way, per the status separation above.
- **Sales Order Preview** (spec §11): `OrderService::previewOrder()`
  computes exactly what a real submission would persist (same discount-cap
  validation, same server-authoritative pricing) without writing anything.
  Mobile: `POST /api/v1/orders/preview` (same body as `POST /orders`) returns
  the computed items/subtotal/grand_total for the app to render a
  confirm screen before actually submitting. Web: the existing Order
  create/edit form's Submit button now opens a Preview modal (built from
  the form's own current state via JS, no extra round-trip) with
  Edit/Submit Order/Cancel actions — Edit returns to the form, Cancel
  abandons and returns to the list, Submit Order performs the real
  `form.requestSubmit()`. Depot and Payment Mode aren't in the preview yet
  since those fields don't exist until Phases 3/4.
- **Sync Agent**: `tally-sync-agent/src/vouchers.js` builds Tally
  voucher-import XML for `sales_order` (Sales voucher) and `collection`
  (Receipt voucher) jobs — ledger-level only (one debit/credit line each),
  since itemized per-product stock entries need Phase 3's Stock Item
  mapping to mean anything. `TallyClient.createVoucher()` posts it and
  parses Tally's `<RESPONSE>` (`CREATED`/`ERRORS`/`LASTVCHID`).
  **Not exercised against the real "GDN tally" instance** — unlike Phase
  1's read-only `ping()`/`fetchLedgers()`, creating a voucher is a mutating
  write against the customer's real accounting data, so this was verified
  by generating and parsing the XML structure only (see
  `tally-sync-agent/src/vouchers.js`'s own doc comment) and with PHP-side
  mocked-HTTP tests, never a live write.

## Phase 3 — Depot/Stock, Alternative Depot, Split Depot Fulfillment

- **Schema**: new `depots` (own `tally_guid`), `product_stocks` (one row per
  depot+product, unique on that pair), `depot_allocations` (one row per
  depot an order line actually draws from — `depot_id` is a required FK,
  never nullable, so an unfulfillable remainder is reported as a shortfall
  rather than forced into a fake row).
- **Ownership split** (spec §14, enforced in code, not just convention):
  `product_stocks.opening_qty/in_qty/out_qty/closing_qty/available_qty` are
  Tally-owned — the only writer of these columns anywhere in the app is
  `TallyStockSyncService::applySnapshot()`. `reserved_qty/allocated_qty` are
  SFA's own overlay, written only by `DepotAllocationService`, and never
  sent back to Tally. `ProductStock::sellableQty()` = `available_qty -
  reserved_qty`.
- **Alternative Depot / Split Depot Fulfillment**:
  `DepotAllocationService::findAvailableDepots()` returns only depots with
  sellable stock for a product, preferred depot first; `autoAllocate()`
  draws from as many depots as needed to cover a line, reporting whatever
  no depot combination can cover as `shortfall` (never a placeholder
  allocation); `allocateManually()` is the admin-driven path used by the
  Order detail page's new Depot Allocation card
  (`orders.items.allocate`) for an explicit (optionally
  alternative-depot) pick.
- **Stock sync is a bulk snapshot, not a queued job**: unlike
  Dealer/Retailer/Product/Sales Order/Collection, a stock quantity isn't
  one entity with one `entity_id` — it's a godown+item pair — so it never
  goes through `sync_queues`. The Sync Agent pulls a full godown-wise
  snapshot on its own interval (`STOCK_SYNC_INTERVAL_SECONDS`, default 15
  minutes — see `tally-sync-agent/src/stockSnapshot.js`) and pushes it in
  one call to `POST /api/v1/integration/tally/stock-sync`
  (`TallyStockSyncService::applySnapshot()`), which wholesale-overwrites
  the Tally-owned columns for every row it can match by GUID and skips
  (and counts) any row whose depot/product GUID isn't mapped yet — it
  never guesses by name. The stable-identifier rule still holds even
  though Tally's own "Stock Item Godown" pair object carries only names:
  the agent resolves each row's real GUIDs itself, from the same
  Godown/Stock Item master lists it already pulls for Depot/Product
  mapping, before pushing.
- **Depot admin module**: full CRUD (`depots.*` permissions — General
  Manager gets full CRUD, the Manager tier view-only, Sales Executive
  none, same tier shape as every other org module) plus a read-only Depot
  Stock page (`depot-stock.index`, permission-gated the same way as
  Activity Log/Reports — no Policy class, since there's no CRUD action to
  authorize beyond viewing Tally-owned data).

## Phase 4 — Delivery/Challan, Vehicle/Driver masters, Cash/Cheque ledger rules

- **Vehicle/Driver masters**: pure SFA-only data (`vehicles`, `drivers` —
  registration number/type/capacity and name/license/phone respectively) —
  **no `tally_guid`, no sync of any kind**. A Delivery Note voucher
  references them as plain narration text (see below), never as a Tally
  ledger/party, so there is nothing to map. Same org-module permission
  tier shape as Depot (General Manager full CRUD, Manager tier view-only,
  Sales Executive none).
- **Delivery/Challan**: a new `deliveries` row is created only through
  Order's own "Dispatch" action (`orders.dispatch`, on the Order detail
  page) — never a standalone create form — and only once the order is
  Approved *and* every line is fully depot-allocated
  (`DepotAllocationService::lineStatus()` = Allocated for every item);
  otherwise the dispatch is rejected with a plain validation error. A
  Delivery is append-only after that (dispatched → delivered/cancelled,
  no edit/delete UI), same reasoning as Achievement — `dispatched_by` +
  timestamps are audit enough. `DeliveryService::dispatch()` mirrors
  Order/CollectionEntry's own guard-then-enqueue shape exactly: a missing
  dealer/product Tally mapping fails the sync in place (`sync_status` =
  Failed, plain-English `sync_error`) without ever enqueueing something
  the Sync Agent could never complete — the dispatch itself still
  succeeds either way. Pushed to Tally as a `Delivery Note` voucher
  (`vouchers.js`'s `buildDeliveryNoteVoucherXml()`), ledger-level only,
  vehicle/driver folded into the narration text. **Not exercised against
  the real instance**, same reasoning and same verification method
  (structural XML build/parse only) as Phase 2's Sales/Receipt vouchers.
- **Cash/Cheque payment-mode display rules**: `CollectionEntry`'s
  `payment_method`/`reference_no`/cheque fields already existed before
  this Tally work — what Phase 4 actually adds is making the *Receipt
  voucher* respect them: `buildReceiptVoucherXml()` now debits "Cash" only
  for an actual cash payment, and a "Bank" ledger (with a
  `BANKALLOCATIONS.LIST` carrying the instrument date/number) for
  cheque/bank-transfer/mobile-banking — previously every collection was
  lumped into "Cash" regardless of `payment_method`, which would have
  misstated the customer's real cash-in-hand. "Cash"/"Bank" are still the
  common Tally defaults, not a guarantee for this customer's own chart of
  accounts (same caveat as every other ledger name in this file).

## Phase 5 — Ledger, Credit Note, Debit Note, Outstanding

- **Schema**: new `ledger_entries` — one row per Tally voucher affecting a
  dealer's ledger (`voucher_type` is a plain string: Sales/Receipt/Credit
  Note/Debit Note/whatever Tally itself uses — Credit Note and Debit Note
  are deliberately *not* separate tables, since in Tally they're just
  another voucher against the same party ledger). Upserted by `tally_guid`
  (unique) — a repeated pull of the same period never duplicates a row.
  Tally is the sole source of truth (pull-only, like `product_stocks`); no
  soft deletes/created_by/updated_by, same reasoning as ProductStock.
- **Sync shape differs from stock on purpose**: a ledger pull is
  naturally scoped to one dealer already (unlike stock's whole-company
  snapshot), so it reuses the existing `sync_queues` job-claim mechanism
  directly — `TallyLedgerSyncService::enqueuePull()` enqueues one
  `entity_type=Ledger` job per pull attempt (its own fresh
  external_reference each time; only the resulting rows are deduped, by
  their own `tally_guid`), and the Sync Agent's `pullLedgerVouchers()`
  handler normalizes Tally's response into rows *in the agent*
  (`ledgerSnapshot.js`) before sending them back — the first entity type
  where a pull job's result actually gets materialized into its own table
  rather than just recorded in `sync_queues`/`sync_logs` (see
  `Api\V1\TallyIntegrationController::jobResult()`). A "Sync Ledger"
  button on the Dealer detail page (`tally-integration.sync` permission)
  triggers it manually for now.
- **`ReportService::dealerLedger()`/`dealerLedgerSummary()` now prefer
  real Tally data**: a dealer with any `ledger_entries` rows gets its
  actual statement/balance from them; a dealer never synced yet still
  falls back to the original SFA-computed estimate (Order=debit,
  Collection=credit) rather than showing an empty/broken report. The
  `reports.dealer-ledger` screen shows which one it's looking at (a green
  "Synced from Tally" banner vs. an amber "Estimated" one). This is why
  `report.dealer-ledger` — retired in Phase 2 specifically because "Phase
  5's real ledger sync supersedes this computed report" — is revived now:
  it's no longer purely a computed guess.
- **Outstanding**: `CollectionEntryService::outstandingBalance()` (used to
  validate a new collection's amount) is deliberately left untouched —
  changing its data source would change validation behavior for every
  dealer, including ones never synced. `TallyLedgerSyncService::outstandingBalance()`
  is a new, separate method returning the real Tally-derived figure (or
  `null` if never synced) for display purposes only.

## Phase 6 — Full Sales Return workflow

- **Schema**: `sales_returns` (a real business record like Order — soft
  deletes/audit — since a request can still be corrected before approval)
  + `sales_return_items` (wholly owned, plain timestamps, same shape as
  OrderItem). `sales_returns` carries the same two-dimensional status
  pair as Order/CollectionEntry/Delivery: `status`
  (`App\Enums\SalesReturnStatus`: Requested/Approved/Rejected/Dispatched/
  Received) is the business workflow stage; `sync_status`
  (`TallyRecordSyncStatus`) tracks the separate question of whether a
  *Received* return has reached Tally yet.
- **Forward-only workflow**, enforced by `SalesReturnService`, one
  status-transition method per step: `requestReturn()` (only against an
  Approved order, capped per line at what's actually left to return —
  `OrderItem::returnedQuantity()` excludes Rejected requests, which never
  actually happened) → `approve()`/`reject()` → `dispatch()` (records
  vehicle/driver, reusing Phase 4's masters — no Tally mapping needed for
  either, same as Delivery) → `receive()` (the depot confirms what
  *actually* arrived per line, which can be less than requested — e.g.
  some units damaged/missing in transit — and this triggers the Tally
  push automatically, same guard-then-enqueue shape as Order/
  CollectionEntry/Delivery's own `enqueueTallySync()`).
- **Depot receiving never touches `product_stocks` directly** — Tally
  owns those columns (`TallyStockSyncService` is the only writer, Phase
  3). A received return's items only show up in stock again once the
  next stock-sync pull reflects the Credit Note Tally posts as a result
  of this push — receiving in SFA is a confirmation/tracking step, not a
  local inventory adjustment.
- **Credit Note amounts are based on `received_qty`, never
  `requested_qty`** — the whole point of the depot-receiving step is that
  what's confirmed on arrival is what actually gets credited, not what
  was merely asked for.
- **Sync Agent**: `vouchers.js`'s `buildCreditNoteVoucherXml()` reverses
  the Sales voucher's ledger effect (party ledger credited, Sales Account
  debited — the mirror image of `buildSalesVoucherXml()`'s own entries).
  `handlers.js`'s `'return'` case (`TallyEntityType::SalesReturn`, value
  `'return'`) pushes it — entity_type describes the SFA source object
  (the return), not the Tally voucher type it produces, same convention
  as Order pushing under `SalesOrder` rather than under a `'sales_voucher'`
  entity. **Not exercised against the real instance**, same reasoning and
  verification method (structural XML build/parse only) as every other
  push voucher in this project.
- **Mobile API**: `POST/GET /api/v1/sales-returns` — a Sales Executive can
  request a return against one of their *own* orders (checked via
  `Order::where('user_id', ...)`, mirroring Order/CollectionEntry's own
  mobile ownership scoping) and see their own return history. Approval/
  dispatch/depot-receiving stay Admin Dashboard actions
  (`Web\Admin\SalesReturnController`) — deliberately not exposed to
  mobile, same reasoning as every other approval-gated workflow in this
  project.
- **Permissions**: new `sales-returns.*` module. General Manager and the
  Manager tier both get the full workflow (view/add/edit/approve/export/
  print — `edit` covers dispatch/receive, since there's no separate "edit
  the request" form to distinguish from those transitions, see
  `SalesReturnPolicy::update()`'s own doc comment); Sales Executive gets
  view/add only (request their own, mirrors Order/Collection Entry's
  add-only shape) — approving/dispatching/receiving stays a manager
  action.

## Phase 7 — Reconciliation, mobile Retailer-create, reports, hardening

- **Reconciliation extended beyond the Phase 1 master-data check**:
  `TallyReconciliationService::compareDealerBalances()` compares, for
  every dealer whose Tally ledger has actually been pulled (Phase 5), the
  SFA-computed Order/CollectionEntry estimate against the real
  Tally-derived balance — a genuine accounting reconciliation, flagging
  dealers where they disagree beyond rounding (an order/collection SFA
  has that never reached Tally, or something Tally-side SFA doesn't know
  about). `failedSyncs()` is a one-page triage view across every kind of
  sync failure at once (Order/Delivery/CollectionEntry/SalesReturn),
  rather than checking four separate list pages for a `sync_status =
  Failed` row.
- **Mobile Retailer-create**: `POST /api/v1/retailers` — a Sales
  Executive registers a dealer's downstream shop on the spot, mirroring
  Order/CollectionEntry's own mobile create shape and the same
  server-side territory backstop as `StoreOrderRequest`
  (`dealer_id` must be visible via `Dealer::scopeVisibleTo()`).
- **Mobile Return/Collection extensions**: the mobile Sales Return
  endpoints (`POST`/`GET /api/v1/sales-returns`) built in Phase 6 continue
  under this phase's banner. Separately, hardening this phase surfaced
  that `Api\V1\DealerController::outstandingBalance()`/`ledger()` — real,
  fully-implemented, Swagger-documented methods — had **no route pointing
  to them** in `routes/api/v1.php` (dead code, apparently orphaned when
  Order/Collection Entry were retired and a route got removed without the
  controller method going with it; a stale comment in `tests/Api/DealerTest.php`
  said as much). Both routes are restored, and both methods now prefer
  the real Tally-synced balance/ledger (Phase 5) over the SFA-only
  estimate, `source`/the transaction list saying which one is in play —
  the same Tally-awareness upgrade every other outstanding-balance-shaped
  thing in this project got.
- **Reports**: `ReportService::salesReturnSummary()` — per-executor
  return counts (pending/rejected/received) and total credited amount,
  credited on `received_qty` (what the depot actually confirmed), never
  `requested_qty` — same permission tier shape as Order Performance/
  Collection Summary (General Manager + Manager tier full, Sales
  Executive their own).
- **Hardening**: the `Api\V1\DealerController` dead-code fix above is this
  phase's main hardening finding — a broader sweep of every mobile
  controller for the same orphaned-method pattern turned up nothing else
  (see the audit note in this project's own session history if this needs
  re-verifying later). A duplicate outstanding-balance endpoint was nearly
  added to `Api\V1\CollectionEntryController` before that existing
  endpoint was found — removed before shipping, in favor of upgrading the
  original.

## Master sync — how Tally records become SFA records

"Sync Now" on the Integration Dashboard queues a pull of all four master
lists (dealer, retailer, product, depot) plus a ledger pull per mapped
dealer. The Sync Agent collects those within about a minute, fetches each
list from Tally, and posts back normalized `{ name, guid }` rows.

`TallyMasterSyncService::applyPulledMasters()` then decides, per row:

| Outcome | When | What happens |
|---|---|---|
| `already` | An SFA record already holds this GUID | Nothing. Includes the renamed-in-Tally case. |
| `linked` | An SFA record matches by name **and holds no GUID yet** | GUID written, `tally_mappings` row created. |
| `conflicts` | Name matches, but that record holds a *different* GUID | Reported for a human. Never re-pointed. |
| `imported` | No SFA record at all | Created as an **inactive stub** (see below). |
| `unmatched` | No SFA record, and no honest stub is possible | Reported. Currently only Products, when no ProductCategory exists. |
| `deleted_here` | The SFA record holding this GUID is soft-deleted | Reported, **not** re-imported — see below. |

**Name bootstraps a mapping; GUID resolves identity.** On the first pull
the name is the only bridge between the two systems, so an exact (trimmed,
case-insensitive) name match against an unmapped record is accepted. After
that, every sync matches on GUID alone — which is why a rename in Tally
does not create a duplicate, and why a same-name/different-GUID row is
surfaced rather than silently re-pointed. Re-pointing would move a
dealer's entire ledger history onto the wrong account.

**Records that exist only in Tally are imported inactive.** A Tally master
carries a name and a GUID and nothing else, while SFA requires a dealer
phone and a product price and category. Those get placeholders
(`0000000000`, `0`, the first category on file) and a `TLY-`-prefixed
stub code, and `status` is false — so the record reaches no rep's device
and no report until a human opens it, fills in the real values and
activates it. Importing them live would put invented prices in front of
the field team.

The counts and the names behind them appear in the **Outcome** column on
the Sync Queue screen, because "Success" alone cannot distinguish a pull
that mapped everything from one that mapped nothing.

**A record deleted in SFA but still live in Tally is reported, never
re-imported.** The GUID lookup deliberately searches `withTrashed()`;
without that, a deleted dealer would fall through to "exists only in
Tally" and be re-created as a fresh stub on every single sync, forever.
Restoring it (or deleting it in Tally) is a human decision.

**Two unique keys, and mappings soft-delete.** `tally_mappings` is unique
on both `(entity_type, sfa_id)` and `(entity_type, tally_guid)`, and the
model soft-deletes — so a trashed row still occupies both keys while being
invisible to a default-scoped `updateOrCreate`. That combination turned a
re-sync after any mapping deletion into a 1062 duplicate-key 500 (seen
live). `writeMapping()` looks on both keys `withTrashed()` and restores
whatever it finds.

## SFA → Tally master push

The other direction: an SFA dealer, retailer, product or depot with no
counterpart in Tally becomes a Ledger, Stock Item or Godown there.

This is the **only** part of the integration that creates permanent
masters inside the customer's accounting system, and it is shaped by that:

- **Off unless a deployment opts in** — `SFA_TALLY_MASTER_PUSH_ENABLED`,
  default false. Without it, Sync Now reads from Tally and writes nothing.
- **Only active, unmapped records are eligible.** An unmapped *inactive*
  record is almost always a stub imported *from* Tally that nobody has
  completed; pushing it back would duplicate the master it came from.
- **One queue row per record**, keyed `SFA-MPUSH-{type}-{id}` — one bad
  name cannot block the rest, and a re-run is idempotent while the record
  stays unmapped.
- **The dashboard names every record** that would be created, before the
  operator clicks anything.

After a successful create the agent reads the new master back to get the
GUID Tally assigned it, and SFA records that on the record and in
`tally_mappings`. A create whose GUID cannot be read back is reported as a
**failure** even though the master exists: SFA would otherwise hold a
record it wrote but cannot recognise. The message says so, because a blind
retry would collide on the name.

### What live Tally taught this code

Four findings, each of which had silently broken something:

| Finding | Consequence |
|---|---|
| `ACTION="Create"` against an existing name does **not** error — Tally silently **ALTERS** that master (`CREATED 0 / ALTERED 1`) | A push could overwrite an accountant's own record. The agent now **reads first and refuses**, pointing the operator at a Tally → SFA sync, which links an unmapped record to the existing master by name. |
| A rejected master is reported in **`EXCEPTIONS`/`LINEERROR`, not `ERRORS`** | Reading `ERRORS` alone calls a rejection a success. Both are checked, and Tally's own wording is passed straight through — it names the real problem far better than anything inferred. |
| A godown's `PARENT` must be the **empty element `<PARENT/>`** | `<PARENT>Primary</PARENT>`, the numeric entity `&#32;Primary`, a literal leading space, and omitting `PARENT` altogether *all* failed with `Godown 'Primary' does not exist!`. This company's real root is stored as `" Primary"` — with a leading space, Tally's marker for a reserved internal name, which XML will not carry. Do not "tidy" the empty element into an omission. |
| Stock items are filed under real user groups (`Pump`, `TV`), not `Primary` | `SFA_TALLY_STOCK_ITEM_GROUP` is blank by default so Tally picks its own root; set it to a group the customer actually uses. |

### First production run

Executed against the live "GDN Tally" company on 2026-09-08, at the
project owner's request: **45 masters created — 5 dealer Ledgers and 40
Stock Items — 0 failures.** Verified from Tally's own answers, not the run
log: 8 ledgers under Sundry Debtors, 42 stock items, 3 godowns, matching
SFA exactly. A second push run queued 0, and a follow-up pull reported
every record as `already mapped` with no conflicts and no duplicates,
which is the proof that both directions agree on identity.

`npm run run-once` in the agent drains the queue once and prints each
result, which is the way to watch a bulk push rather than discover
refusals later on the Sync Queue screen.

### What is deliberately not pushed

Prices and stock quantities. Tally owns those in this integration's
ownership model, so a pushed Stock Item carries a name and a unit and
nothing else — an SFA price written as a Tally item rate would be SFA
writing into a column it does not own.

## Running the agent (and the two ways this bites)

Nothing syncs unless the Sync Agent is **running on the machine beside
Tally**. SFA only ever *queues* work — it cannot reach Tally itself — so a
missing agent means jobs pile up as Pending indefinitely.

```bash
cd tally-sync-agent
npm start           # long-running: heartbeat, poll, stock snapshots
npm run run-once    # drain the queue once and print each result, then exit
```

For unattended running on Windows, `tally-sync-agent/deploy/` registers a
scheduled task that starts at logon and needs no administrator rights:

```powershell
cd tally-sync-agent\deploy
powershell -ExecutionPolicy Bypass -File .\install-task.ps1
powershell -ExecutionPolicy Bypass -File .\task-status.ps1
```

It writes `tally-sync-agent/logs/agent-YYYY-MM-DD.log`, kept 14 days.

**Logon, not boot, and this is a real operational limit:** Tally serves its
gateway from its own UI thread, so nothing syncs while the machine sits at
the login screen or while Tally is closed. A boot-time task would start an
agent with nothing to talk to.

**One agent at a time.** The task runs `node.exe` directly rather than
through a shell wrapper, because Task Scheduler's Stop only kills the
process it launched: a wrapped agent survives as an orphan and keeps
claiming jobs. That happened during development and produced a genuinely
confusing symptom - jobs completing successfully with **no trace in the
log**, because the orphan was doing the work and writing to a deleted file
handle while the new instance logged only its own heartbeats. If jobs
succeed that the log never mentions, look for a stray `node.exe` before
anything else.

On Linux use systemd - the same pattern `docs/deployment.md` uses for the
Laravel queue worker.

### "Nothing new to queue" with a dead agent

Sync Now skips work already queued, so with a stalled queue every click
answered *"a sync for all of this is already waiting for the agent"* — the
same words a healthy queue uses. The sync looked fine and moved nothing.

Fixed: when work is waiting, the agent's liveness is what gets reported —
the flash message and a dashboard banner both name the queue depth and
when the agent last checked in, and say that clicking again will not help.
`TallyManualSyncTest` pins both the stalled and the healthy wording.

### Apache drops the Authorization header

**Symptom:** every bearer-token API request 401s through Apache while the
identical token works under `php artisan serve`. This breaks the whole
mobile API, not only the Sync Agent, and it looks exactly like a bad
credential.

**Cause:** application routes are rewritten to `index.php` in the *project
root* (the front-controller shim that keeps `SCRIPT_NAME` aligned — see
the root `.htaccess`), so `public/.htaccess` never runs for them. Its
`HTTP:Authorization` rewrite therefore never applied, and Apache/CGI
strips the header before PHP sees it.

**Fix:** the same two header rewrites now live in the root `.htaccess`.
Any future change to that file must keep them — routing works without
them, authentication does not.

## Known limitations

- **Live-verified against the real instance on 2026-09-07** (company name
  is actually **"GDN Tally"** — capital T, not "GDN tally" — port 9000
  confirmed as both the ODBC and HTTP-XML gateway port via Tally's own
  About panel). This is the first time `List of Ledgers`/`List of
  Godowns`/`List of Stock Items` were captured against real data, and it
  corrected two wrong assumptions baked into `stockSnapshot.js` since
  Phase 3:
  - **Response shape**: a collection's rows actually nest at
    `ENVELOPE.BODY.DATA.COLLECTION.<TAG>` (e.g.
    `<ENVELOPE><BODY><DATA><COLLECTION><LEDGER NAME="Cash">…`), not
    directly under `ENVELOPE` or under a bare `ENVELOPE.COLLECTION` as
    both originally guessed. `stockSnapshot.js`'s `rowsFromEnvelope()`
    tries the confirmed shape first, keeping the old guesses only as a
    fallback for a possible different Tally version.
  - **A row's own name is an XML attribute** (`<LEDGER NAME="Cash">` →
    `row['@_NAME']`), not a `<NAME>` child element — `nameToGuidMap()` was
    reading the wrong field entirely and would have silently resolved
    nothing. Fixed and re-verified against the real captured XML.
  - **`<PARENT>` inside a `<COLLECTION>` is silently ignored** — this was
    the real cause of Phase 1's "the Sundry Debtors filter didn't
    restrict results" mystery, not the customer's chart of accounts.
    Tally returned every ledger regardless (including "Cash" under
    Cash-in-Hand and "Profit & Loss A/c" under Primary). Filtering needs
    a System Formulae `<FILTER>` instead:
    `<FILTER>SFALedgerParentFilter</FILTER>` +
    `<SYSTEM TYPE="Formulae" NAME="SFALedgerParentFilter">$Parent = "…"</SYSTEM>`.
    Verified live both ways: the `<FILTER>` form correctly returns 1
    ledger for Cash-in-Hand and, once dealer ledgers existed, exactly the
    2 under Sundry Debtors. `fetchLedgers()` now uses the `<FILTER>` form.
  - **`$Parent` is the immediate parent group, not an ancestor chain.**
    "Current Assets" returns zero ledgers even though Sundry Debtors sits
    directly beneath it; "Sundry Debtors" returns the dealers. Verified
    live both ways in the same session. The configured group must name the
    group the ledgers hang off *directly*.
  - `fetchStockItemGodownStock()`'s original `<TYPE>Stock Item Godown</TYPE>`
    is **not a valid type in TallyPrime 7.1 and hung the HTTP gateway
    outright** — every later request timed out, including a `ping()` that
    had worked seconds earlier, until the resulting error dialog was
    dismissed on the Tally desktop. Replaced with the plain, proven-safe
    `<TYPE>Stock Item</TYPE>` plus `BATCHALLOCATIONS.LIST` in the FETCH
    list, which is Tally's own per-godown structure for a stock item and
    was verified live as accepted (STATUS 1, each item returning a
    `BATCHALLOCATIONS.LIST` element).
  - **Any unsupported element in a Tally request can hang the gateway —
    `<FETCH>` fields included.** An earlier revision of this document
    claimed a wrong FETCH field was "merely omitted"; that was wrong and
    caused a second wedge. Adding just `BASEUNITS, OPENINGBALANCE` to the
    already-verified Stock Item request hung the gateway exactly as hard
    as the invalid `<TYPE>` had. The practical rule: **a live-verified
    request is a fixed artifact — do not edit any part of it (TYPE, FETCH,
    static variables) without re-verifying live**, and re-verify the exact
    string you intend to ship, not an approximation of it.
  - **Tally's gateway serves one request at a time.** `buildSnapshot()`
    originally fired its three reads via `Promise.all`, which timed the
    gateway out (and left it stuck) against the real instance. It is now
    deliberately sequential — re-verified live end to end afterwards, with
    the gateway healthy before and after. Anything new that talks to Tally
    must be sequential too.
  - **A listening port does not mean Tally is serving.** Its gateway is
    the UI thread, so it goes silent identically whether a dialog is open,
    it is mid-operation, or a previous instance did not exit cleanly (that
    last one showed a ~390 MB `tally.exe` still holding port 9000 while
    answering nothing — a browser hitting `http://localhost:9000` hung
    too, which is the quickest way to prove it is Tally-side and not this
    integration). `npm run doctor` in the agent walks these checks in
    order and prints the fix for each outcome.
  - **Quantity fields need `#text` unwrapping.** `<CLOSINGBALANCE
    TYPE="Quantity">13 PCS</CLOSINGBALANCE>` parses to
    `{'@_TYPE': …, '#text': '13 PCS'}`, so the old `String(value)` in
    `stockSnapshot.js`'s `numberOf()` produced `"[object Object]"` and
    every quantity silently became 0. Fixed and re-verified.
- **The whole read path is now live-verified against the real instance**
  (2026-09-07): `ping`, `fetchGodowns`, `fetchStockItems`, `fetchLedgers`
  (with the corrected `<FILTER>`), `fetchStockItemGodownStock`, and
  `fetchLedgerVouchers` all return STATUS 1, and `buildSnapshot()`'s full
  three-read pipeline runs end to end with the gateway healthy before and
  after. `npm run doctor` reproduces this check on demand.
- **A real voucher was finally captured (1-Sep-2026 Journal) and it
  disproved three assumptions.** All three are fixed and the fixes are
  verified end to end through the actual agent job handler:
  1. **Tally caches collection results by NAME, and omits unchanged
     fields on a repeat request — GUID included.** The same godown request
     under a reused name returned `GUID: MISSING` on every row; a fresh
     name returned the real GUIDs. Since GUID is the whole basis of SFA's
     matching, the original fixed collection names meant the agent would
     have worked on its first run and then **silently matched nothing
     forever after, with no error**. Every collection request now gets a
     unique name (`uniqueCollectionName()`), asserted by `npm test`.
  2. **Filtering vouchers by `PARTYLEDGERNAME` misses vouchers entirely.**
     A Journal has an *empty* `PARTYLEDGERNAME` and names its ledgers only
     inside `ALLLEDGERENTRIES.LIST`. The old `$PartyLedgerName = "<dealer>"`
     filter returned zero rows for precisely the hand-entered vouchers an
     accountant creates. The request (`fetchVouchers`) is now unfiltered and
     the dealer's own lines are selected while parsing.
  3. **Debit/credit is per ledger entry, not per voucher.** The captured
     Journal debits "Supplier 1" 50 and credits "Dealer 02" 50 — one
     voucher moving two ledgers in opposite directions, which the old
     classify-by-`voucher_type` rule cannot represent. Each entry carries
     its own signed `AMOUNT` (negative = debit to that ledger, positive =
     credit) alongside `ISDEEMEDPOSITIVE`. One row is now emitted per
     ledger entry, with `tally_guid` suffixed by entry index so the two
     sides of a double entry can't collapse into one upserted row.
  The captured row is pinned as a fixture in
  `TallyLedgerSyncServiceTest::test_it_stores_a_real_captured_tally_journal_row()`
  so the agent and PHP sides can't drift apart.
- **The ledger groups are customer-specific and must be configured.**
  `TALLY_DEALER_LEDGER_GROUP` (agent-side) names the group dealer ledgers
  hang off *directly* — `Sundry Debtors` on this instance, verified live.
  An earlier revision of this document claimed the default was wrong and
  that dealers sat under `Current Assets`; that was written while
  `<PARENT>` was being silently ignored, so unfiltered results were being
  read as evidence. It is corrected above. `npm run doctor` prints which
  group it checked and what it found.
- **Retailers need a group of their own, and there is deliberately no
  default.** A Tally ledger carries no dealer-vs-retailer flag — the only
  signal is which group the customer filed it under. While both pulls read
  one group, every dealer was imported a second time as a retailer of the
  same name (observed live: "Dealer 1" and "Dealer 2" landed in both
  tables). `TALLY_RETAILER_LEDGER_GROUP` must be set to a *different*
  group; left blank, or set to the dealer group, the retailer pull imports
  nothing and reports why rather than guessing.
### Godown-wise stock: what the real instance actually allows

Investigated against a real purchase (50 PCS into Warehouse) and sale
(5 PCS out of Main Location), with "Maintain multiple Godowns" confirmed
enabled and the voucher confirmed showing a godown in Tally's own UI.

**Godown is not obtainable from voucher/collection exports.** Three
distinct attempts all returned no godown, and the batch-allocation object
has no godown key whatsoever — its keys are exactly
`BATCHNAME|INDENTNO|ORDERNO|TRACKINGNUMBER|ADDLAMOUNT|BATCHDISCOUNT|AMOUNT|ACTUALQTY|BILLEDQTY|BATCHRATE`:
1. the voucher's default object view;
2. a dotted FETCH path
   (`ALLINVENTORYENTRIES.LIST.BATCHALLOCATIONS.LIST.GODOWNNAME`) — accepted
   without error, but populated nothing;
3. an explicit `FETCH ALLINVENTORYENTRIES.LIST` for a fuller subtree.

**It IS obtainable from Tally's built-in display reports**
(`TALLYREQUEST=Export`, `TYPE=Data`), both verified live and now
implemented in `tally.js` (`fetchGodownSummary`/`fetchStockSummary`) with
parsing in `stockReports.js`:

| Report | Gives | Live result |
|---|---|---|
| `Godown Summary` | quantity per **godown**, all items summed | Main Location −5, Warehouse 50 |
| `Stock Summary` (+`EXPLODEFLAG`) | quantity per **stock item**, all godowns summed | Pump Model 1 = 45 |

**The open problem: neither gives the (item × godown) pair**, and it is not
derivable from the two — row and column totals do not determine the cells.
`product_stocks` is keyed depot+product precisely because Alternative Depot
and Split Depot Fulfilment (spec §12/§13) need per-cell quantities.
`SVGODOWNNAME` does not scope `Stock Summary` (all three godown values
returned the same 45), and `EXPLODEFLAG` on `Godown Summary` explodes only
to stock **group** level, in a flat stream with no depth markers.

**Decision taken: single-depot mode**, as the working interim.

- The agent runs `buildSingleDepotSnapshot()` (`stockSnapshot.js`), which
  reads `Stock Summary` for per-item closing quantities and emits rows with
  **no godown at all** rather than inventing one. Verified live: Pump Model
  1 → 45 (50 purchased − 5 sold).
- SFA attributes those rows to the depot named by
  `sfa.tally.default_depot_code` (`SFA_TALLY_DEFAULT_DEPOT_CODE`). Leaving
  it blank **disables** the mode: godown-less rows are then skipped and
  counted, never landed on an arbitrary depot.
- Because that mode has no movement data, it writes **only** the closing
  position (`closing_qty`/`available_qty`); `opening_qty`/`in_qty`/`out_qty`
  are left untouched, so "unknown" is never recorded as "none" over figures
  a per-godown sync had stored. `reserved_qty`/`allocated_qty` remain
  SFA-owned and untouched as always.
- The per-godown path (`buildSnapshot()`, and GUID-matched depots in
  `TallyStockSyncService`) is retained and still exercised by tests, ready
  for a Tally that can serve item×godown.

**What this costs:** Alternative Depot and Split Depot Fulfilment (spec
§12/§13) can't be driven from Tally stock while in this mode — all stock
reads as belonging to one depot. The SFA-side allocation logic is unchanged
and still correct; it simply has one depot to allocate from.

**To lift it later**, either of:
1. **Customer-specific TDL** — have the customer's Tally consultant expose
   an item×godown collection/report; then set godowns' `tally_guid` and the
   existing per-godown path takes over with no code change.
2. **Further probing of Tally's report catalogue.** Note the cost: a wrong
   `<TYPE>` in a custom collection **hangs the gateway** until someone
   dismisses a dialog at the Tally desktop, so this should be deliberate,
   not speculative.

Also learned, and worth recording because it refines the earlier rule: an
**unknown but cheap** FETCH field is ignored harmlessly (the dotted path
above), whereas an **expensive computed** one hangs the gateway
(`OPENINGBALANCE` on a Stock Item collection did). The original blanket
claim that "a wrong FETCH is merely omitted" was wrong in the other
direction too — the distinction is cost, not validity.
- **The company's financial year runs 1-Sep to 31-Aug**, not a calendar
  year (verified from its own Company Info screen: "Current period 1-Sep-26
  to 31-Aug-27"). Tally only serves data inside the active period, so a
  ledger pull starting 1-Jan would fall outside it and return nothing —
  `TallyLedgerSyncService::financialYearStart()` computes the real opening
  from `config('sfa.tally.financial_year_start_month')` (9 for this
  customer) and rolls back a year when today precedes it.
- **This instance runs in Educational Mode** ("Application status:
  Educational Mode", with a Tally Gateway Server licence error). Tally's
  educational build restricts voucher entry to a few dates per month,
  which will affect *push* testing specifically (Sales/Receipt/Delivery
  Note/Credit Note imports) even once the ledger names are right. Getting
  a licensed instance — or accepting date-restricted test windows — is a
  prerequisite for validating the push side end to end.
- **Voucher creation was never tested against the real instance** (Phase
  2) — deliberately: posting a Sales/Receipt voucher would create real
  accounting data in the customer's live company. The XML is verified
  structurally (built and parsed back correctly) and the PHP-side
  enqueue/mapping-guard logic is tested against mocks; the exact ledger
  names this customer's chart of accounts actually uses ("Sales Account",
  "Cash") need confirming with the customer before Phase 2's voucher push
  is used for real — this is exactly the kind of customer-specific Tally
  configuration the project's own guidance calls out.
- Order/Collection Entry's Preview screen (Phase 2) only shows the fields
  that exist today — Depot (Phase 3) and Payment Mode/Cash-Cheque display
  (Phase 4) will extend it once those fields exist, not before.
- Only Dealer/Retailer/Product have real mapping columns and a Sync Agent
  handler; Depot/Stock/Order/Invoice/Delivery/Collection/Ledger/Credit
  Note/Debit Note/Return are modeled in `TallyEntityType` and the sync
  tables now (so no future schema change is needed for them), but have no
  working code path yet — each later phase adds its own handler.
- The Sync Agent's XML client (`tally-sync-agent/src/tally.js`) has no JSON
  counterpart yet; a `TallyApiFormat::Json` connection needs a sibling
  client added the same way.
- No queue worker has been proven necessary yet — Phase 1's
  `claimNext()`/`markSuccess()`/`markFailed()` are called synchronously
  from the agent-facing HTTP endpoints, not via a Laravel queued job. If a
  later phase needs to enqueue work from a slow background process, verify
  a `php artisan queue:work` worker actually runs in the deploy target
  first (see `docs/deployment.md`) — nothing in this app has needed one for
  real yet.
