# Gazi Pump SFA — Existing Architecture

Written as the "inspect and document" step before building the Tally
integration (see `docs/tally-sfa-integration.md`). Reflects the codebase as
of the Tally integration Phase 1 work — a snapshot, not a living reference;
prefer reading the actual code for anything that matters operationally.

## Stack

- **Framework**: Laravel 12, PHP 8.2.
- **Auth**: two separate guards — `web`/`sanctum` for staff (`App\Models\User`,
  admin dashboard + mobile API), `customer` for the public Customer Web
  Portal (`App\Models\CustomerAccount`, a distinct self-registered login,
  lazily linked to a `Dealer` by email — not part of the sales/order
  hierarchy).
- **Permissions**: `spatie/laravel-permission`. Naming convention (built by
  `App\Helpers\PermissionName`): `menu.{module}`, `{module}.{action}` (action
  ∈ `App\Enums\ButtonAction`: view/add/edit/delete/restore/export/print/
  import/approve), `api.{module}.{action}`, `report.{key}`. One seeder,
  `database/seeders/RolePermissionSeeder.php`, is the single source of
  role→permission assignment for six roles (Super Admin, General Manager,
  Sales Manager, Area Manager, Territory Manager, Sales Executive — the
  three manager tiers share one permission set, differing only by
  territory/team scope at runtime).
- **Audit/soft-delete**: `App\Models\BaseModel` (SoftDeletes + `HasAudit`
  trait, stamping `created_by`/`updated_by`/`deleted_by` + `LogsActivity`)
  is the base every business-domain model extends. Wholly-owned child rows
  (e.g. `OrderItem`, `TargetItem`) skip it — no independent audit trail for
  a row that only ever exists attached to its parent.
- **CRUD layering**: `App\Services\BaseCrudService` (create/update/delete/
  restore/forceDelete/bulk*) + one `{Entity}RepositoryInterface`/
  `{Entity}Repository` pair per module (`App\Repositories\Contracts` /
  `\Eloquent`, bound in `App\Providers\RepositoryServiceProvider`). One
  `Policy` per module (`App\Policies`), auto-discovered by Laravel's
  `Model`→`{Model}Policy` convention; a couple of package-model exceptions
  (Role/Permission) are registered explicitly in `AppServiceProvider`.
  Read-mostly modules with no CRUD-owned model (Reports, Activity Log) skip
  the Policy layer entirely and use a direct `abort_unless($user->can(...))`
  check per controller action instead.
- **Packages**: `laravel/sanctum` (mobile token auth — plain
  `createToken()`, unscoped `['*']` abilities, no expiration),
  `spatie/laravel-activitylog`, `maatwebsite/excel`, `barryvdh/laravel-dompdf`,
  `darkaonline/l5-swagger` (mobile API docs).

## Data model

**Org/geo hierarchy**: `Division` → `District` → `Thana` → `Territory`
(assigned to `User`s many-to-many via `territory_user`, and to `Dealer`s
one-to-many). `SalesTeam` is a *separate*, product-catalog dimension (which
team's products a user can see/order), not an org-chart concept — `Product`
and `User` both carry an optional `sales_team_id`.

**Commercial hierarchy**: `Territory` → `Dealer` → `Retailer`. `Dealer` is
the primary CRM record sales staff manage; `Retailer` is a sub-customer
under one Dealer, with no territory/GPS of its own (inherits from its
parent). Neither has a persisted stock/ledger/account-code concept today.

**Sales recording**: `Order` (dealer- and optionally retailer-scoped,
line items via `OrderItem`, `App\Enums\ApprovalStatus` Pending→Approved/
Rejected) and `CollectionEntry` (payment against a dealer,
`App\Enums\PaymentMethod`, cheque sub-lifecycle via `App\Enums\ChequeStatus`,
same `ApprovalStatus`). **Both are functionally retired** — a recent
"version 1" pivot removed their permissions from every role except Super
Admin, replacing day-to-day reporting with `Target`/`AchievementEntry`
(a Sales Executive's simple daily/monthly self-reported rollup, optionally
product-wise via `AchievementItem`/`TargetItem`). Order/CollectionEntry's
code, routes, and historical data are all still fully intact — nothing was
deleted, only unassigned from role permission sets — and their tables
remain load-bearing for `CollectionEntryService::outstandingBalance()` and
the computed (not persisted) dealer ledger report
(`ReportService::dealerLedger()`: Order=debit, CollectionEntry=credit,
running balance).

**Field operations**: `Attendance` (daily check-in/out), `Visit`/`VisitPlan`
(dealer visits, GPS-verified against the dealer's registered pin),
`GpsLog` (high-frequency raw ping telemetry — deliberately skips
`LogsActivity` to avoid doubling write volume on an append-only table).

**No inventory/stock, no formal Invoice, no persisted ledger/chart-of-
accounts, no vehicle/driver/logistics, no credit-note/debit-note/sales-
return concept exists anywhere in the current schema** — confirmed by
repo-wide search. `Product` has only a catalog `price`, no
stock-on-hand field; Orders record quantities *sold* with no availability
check against anything.

## Mobile API (`routes/api/v1.php`)

Physically versioned (`/api/v1/*`, no version-negotiation middleware — a v2
would be a sibling `routes/api/v2.php`). Standard envelope,
`App\Helpers\ApiResponse::success()/error()/paginated()`, used by every
`Api/V1` controller with no exceptions. `#[OA\...]` PHP attributes document
every endpoint (pattern: one `Get`/`Post` attribute per method,
`security: [['sanctum' => []]]`, `RequestBody`/`JsonContent`/`Parameter`/
`Response` — global `Info`/`Server`/`SecurityScheme` declarations live once,
in `AuthController`).

The `auth:sanctum` route group has **no route-level permission
middleware** despite a header comment implying one — authorization is
enforced per-`FormRequest`/controller via Spatie's `$user->can(...)`, not at
the routing layer. Sanctum tokens are plain, unscoped, non-expiring.

Fifteen controllers exist under `Api/V1`: Auth, OrgStructure (read-only
lookups), Dealer (list/view/**create**), Retailer (list/view **only** — no
mobile create endpoint exists despite the web admin having one),
ProductCategory/Product (read-only), Attendance, GpsLog, VisitPlan, Visit,
Order, CollectionEntry, Target (read-only), Achievement, Notification.

## Background jobs / scheduling

`QUEUE_CONNECTION=database`. Exactly one job class exists today
(`RecalculateAchievementsJob`), and every call site uses `::dispatchSync()`
— synchronous, request-time execution — never true async `::dispatch()`.
No queue worker dependency has been proven out in this app yet.
`routes/console.php` holds six scheduled commands (five notification
checks + attendance backfill), each idempotent and `->withoutOverlapping()`.

## Where this leaves the Tally integration

See `docs/tally-sfa-integration.md` for the full design, but in short: the
lack of any stock/depot/vehicle/invoice/ledger concept means those parts of
the integration are greenfield, not an extension of something existing. The
Order/CollectionEntry retirement directly conflicts with the integration
spec's assumption that Sales Order is a live workflow — resolved (with the
user) by reviving Order/CollectionEntry as the transactional backbone for
Tally sync in a later phase, while Achievement remains as-is for its own
simpler daily-reporting purpose.
