# API Documentation

The OpenAPI/Swagger spec is generated from PHP attributes on the `Api/V1` controllers
(see `App\Http\Controllers\Api\V1\AuthController` for the `Info`/`Server`/`SecurityScheme`
root annotations, added once in Module 1 and never repeated).

- **Swagger UI**: `/api/documentation`
- **Raw spec (JSON)**: `/docs?api-docs.json`
- **Regenerate after adding/annotating an endpoint**: `php artisan l5-swagger:generate`

Annotation convention for every new endpoint (PHP 8 attributes, not docblock `@OA\`
tags — this package's version does not parse docblock-style annotations):

```php
#[OA\Get(
    path: '/users',
    tags: ['Users'],
    summary: 'List users',
    security: [['sanctum' => []]],
    responses: [new OA\Response(response: 200, description: 'Paginated user list')],
)]
public function index(Request $request): JsonResponse { ... }
```

## Gotcha: policy checks on API routes

`App\Models\User::$guard_name` is pinned to `'web'`. Do not remove it. Without
it, any `$this->authorize()` / `$user->can()` call made from a `Sanctum`-authenticated
request 403s for every role, including Super Admin — Laravel's `Authenticate`
middleware calls `Auth::shouldUse('sanctum')` on a successful token check, which
mutates `config('auth.defaults.guard')` for the rest of that request, and Spatie's
`Guard::getDefaultName()` picks that up and looks for permissions under
`guard_name = 'sanctum'` instead of `'web'` (where every permission in this app is
actually seeded). Since one `User` model serves both the admin-dashboard session
guard and the mobile API token guard, pinning the property is the documented
Spatie fix for "same model, multiple guards" — see `App\Models\User` for the
full explanation.

## Postman

Import-ready collection + environments live in `docs/postman/`:

- `Gazi_Pump_SFA.postman_collection.json` — every `api/v1/*` endpoint, one folder
  per module, each request carrying a one-line description mirroring its Swagger
  `summary` (kept in sync during Module 18's finalization pass).
- `Gazi_Pump_SFA.postman_environment.json` — Local (`http://localhost:8000/api/v1`).
- `Gazi_Pump_SFA - Production.postman_environment.json` — Production, with a
  placeholder `base_url` to replace with the real deployed host before use.

The `Login` request's test script auto-captures the returned Sanctum token into
both the `token` collection variable and (if an environment is active) the
environment variable of the same name — the latter is required, since Postman
resolves environment variables ahead of collection variables of the same name,
so relying on the collection variable alone left every authenticated request
silently sending the environment's blank `token` default (found and fixed in
Module 20). `List My Notifications` similarly captures the first notification's
id into `notification_id`, which `Mark Notification as Read`'s `:id` path
variable references — Postman doesn't resolve unpopulated path variables, so
that request 404'd until this was wired up.

Each module appends its own folder of requests to the collection as it's
built. `Logout` is the very last item in the collection (not inside the
Authentication folder near the top) — it revokes the current token, so
running the whole collection start-to-finish only works if nothing after it
still needs to authenticate.

### Running the whole collection end-to-end (Newman)

```bash
cd docs/postman
npx newman run "Gazi_Pump_SFA.postman_collection.json" -e "Gazi_Pump_SFA.postman_environment.json" --working-dir .
```

`--working-dir .` is required so the photo-upload requests (Attendance,
Visit check-in/out, and the cheque-image field on Record Collection) can
resolve their file field against `docs/postman/fixtures/sample-photo.jpg` —
a genuine minimal JPEG committed for exactly this purpose, since
Postman/Newman can't send a `type: file` form-data field without a real file
to attach. The collection is safe to re-run against the same seeded database
repeatedly: `Register Dealer`'s `dealer_code` uses Postman's `{{$timestamp}}`
dynamic variable rather than a fixed string, so it never collides with a
dealer created by a previous run.

## Module 18 — API/Docs finalization

Audited the full API surface for parity across all three artifacts: the actual
routes (`php artisan route:list --path=api`), the generated OpenAPI spec
(`storage/api-docs/api-docs.json`), and the Postman collection. All 34 real
`api/v1/*` endpoints (excluding the trivial `/ping` health check) are documented
in both the Swagger spec and the Postman collection with zero gaps — each
module had already been shipping its own Swagger annotations and Postman
requests as part of its vertical slice, so this pass was a verification +
polish step (per-request Postman descriptions, second environment) rather than
backfilling missing coverage.

## Module 20 — Full verification pass

Ran the entire Postman collection end-to-end via Newman against the live dev
server and found four real, previously-undetected bugs the per-module builds
had each missed in isolation (every prior module only ever tested its own new
requests manually, never the whole collection run start-to-finish):

1. The token-capture/environment-precedence bug described above — every
   request after `Login` was silently unauthenticated.
2. `Logout` sat inside the `Authentication` folder near the top of the
   collection, revoking the token before any other folder could use it —
   moved to the end.
3. The four photo-upload requests had an empty file field (`src: ""`), and
   `Mark Notification as Read`'s `:id` path variable was never populated —
   both fixed as described above.
4. `Register Customer`'s hardcoded `customer_code` broke on any re-run
   against the same database — switched to a dynamic value.

All 35 requests now pass on a clean run. `php artisan test` (334 tests) and
`./vendor/bin/pint` remain green — this pass only touched `docs/postman/*`,
no application code.

## Post-launch sync — Approval layer + drift correction

The collection had drifted badly out of sync with the API since Module 20:
several modules later in the project renamed `customer_id` → `dealer_id`
and the `sales-entries` endpoint group → `orders` (Dealer/Order rename),
dropped the `type` column from dealers entirely, and added the OTP-secured
collection flow (`POST /collection-entries/send-otp`, plus `otp_id`/
`otp_code` on `Record Collection`) and the dealer outstanding-balance
endpoint — none of which had ever been reflected back into
`docs/postman/*`, only into the Swagger spec. Re-audited every
`Api/V1` controller and its `FormRequest` directly (the source of truth,
not the previously-drifted Postman collection) and rebuilt the collection
to match exactly:

- `Sales Entries` folder renamed to `Orders`, pointed at `/orders`, body
  fields renamed `customer_id` → `dealer_id`, `sale_date` → `order_date`.
- `Plan Visit` and `Check In Visit` bodies renamed `customer_id` →
  `dealer_id`.
- `Register Dealer`'s body and `List Dealers`' query no longer send/accept
  a `type` field — the column was dropped from the `dealers` table.
- Added `Dealer Outstanding Balance` (`GET /dealers/{id}/outstanding-balance`).
- Added `Send Collection OTP` (`POST /collection-entries/send-otp`), whose
  test script captures `otp_id` into a new `otp_id` collection/environment
  variable; `Record Collection` switched from a raw JSON body to
  `multipart/form-data` (it always could accept a `cheque_image` file, this
  was simply never modeled) and gained `reference_no`, `cheque_image`,
  `otp_id`, `otp_code` fields.
- `My Order History` and `My Collection History` gained the `status`
  (`pending|approved|rejected`) filter added alongside the approval-layer
  feature, mirrored from the same query param now in the Swagger spec.
- Added the `otp_id` variable to both environment files.

38 requests now cover all 38 registered `api/v1/*` routes (including
`/ping`) with zero gaps, verified by direct diff against
`php artisan route:list --path=api` and each endpoint's actual
`FormRequest` validation rules rather than against the (until now, stale)
Swagger annotations.

## Retailers endpoint + Order retailer_id

Retailers had a full web admin CRUD (with dealer-filtered picker on the
Order create/edit form, client-side) but no mobile API surface at all, and
`POST /orders` had no `retailer_id` input either — a Sales Executive
placing an order via the app had no way to attribute it to one of the
dealer's own retailers, only the dealer itself.

- Added `GET /retailers` (filters: `dealer_id`, `search`, `per_page` —
  `status` is forced to active server-side, same as `/dealers` and
  `/products`) and `GET /retailers/{id}`, gated by `RetailerPolicy` /
  `retailers.view` + `api.retailers.view` (granted to Sales Executive).
  `RetailerRepository::paginateWithFilters()` already supported a
  `dealer_id` filter from its web admin use — the API layer just needed to
  expose it.
- Added `retailer_id` (nullable, must exist in `retailers`) to
  `POST /orders`'s `StoreOrderRequest` and `OrderService::recordOrder()`;
  `OrderResource` now returns a `retailer` object alongside `dealer` when
  the relation is loaded (mirrors `dealer`'s shape/loading behavior
  exactly — same `whenLoaded()` gate, so it appears on `GET /orders` but
  not on the `POST /orders` create response, same as `dealer` already
  didn't).
- Swagger regenerated; Postman collection got a new `Retailers` folder
  (`List Retailers`, `Get Retailer`) and `Record Order`'s body gained
  `retailer_id`. Verified end-to-end with Newman (40/40 requests passing)
  and a direct authenticated request against `GET /retailers/{id}`.
