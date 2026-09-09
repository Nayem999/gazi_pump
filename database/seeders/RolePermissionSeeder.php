<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ButtonAction;
use App\Helpers\PermissionName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Creates the six roles from the hierarchy and every permission for the
 * modules that exist so far (Dashboard, Users, Roles, Permissions). Each
 * later module's seeder adds its own menu/button/api/report permissions and
 * assigns them to the appropriate roles here.
 */
class RolePermissionSeeder extends Seeder
{
    private const ROLES = [
        'Super Admin',
        'General Manager',
        'Sales Manager',
        'Area Manager',
        'Territory Manager',
        'Sales Executive',
    ];

    /**
     * Read-only aggregate reports — no button/add/edit actions, just a
     * single `report.{key}` permission gating each report page. Assigned to
     * General Manager, Sales/Area/Territory Manager, and (a subset of)
     * Sales Executive below.
     */
    private const REPORTS = [
        'attendance',
        'visits',
        'territories',
        'target-achievement',
        'achievement-summary',
        'executive-performance',
        'dealer-coverage',
        'gps',
        'movement-summary',
        // Order/Collection Entry were retired then revived (see Order Performance /
        // Collections' own history below) — the reports came back with them.
        'order-performance',
        'collections',
        // Retired when Order/Collection Entry were (its debit/credit rows
        // came from them), revived again in Phase 5: ReportService::dealerLedger()
        // now shows a dealer's real Tally-synced ledger once one exists,
        // falling back to the same SFA-computed estimate as before for a
        // dealer not yet synced — never an empty/broken report either way.
        'dealer-ledger',
        // Phase 6/7: per-executive Sales Return activity.
        'sales-return-summary',
    ];

    /**
     * No reports currently retired. Kept as an empty array (rather than
     * removed) so a future retirement has an obvious place to go, and
     * `self::REPORTS, ...self::RETIRED_REPORTS` below doesn't need editing.
     */
    private const RETIRED_REPORTS = [];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $this->createDashboardPermissions();
        $this->createModulePermissions('users');
        $this->createModulePermissions('roles');
        $this->createModulePermissions('permissions', withApi: false);
        $this->createModulePermissions('sales-teams');
        $this->createModulePermissions('holidays', withApi: false);
        $this->createModulePermissions('territories');
        $this->createModulePermissions('divisions');
        $this->createModulePermissions('districts');
        $this->createModulePermissions('thanas');
        $this->createModulePermissions('depots');
        // Master data, admin-only: the mobile app reads leave types
        // through the leave endpoints, not a leave-types API of its own.
        $this->createModulePermissions('leave-types', withApi: false);
        $this->createModulePermissions('leave-requests');
        // Entitlements are HR policy and admin-only - the mobile app reads
        // a balance through /leave/balance, never this module.
        $this->createModulePermissions('leave-balances', withApi: false);
        $this->createModulePermissions('vehicles');
        $this->createModulePermissions('drivers');
        $this->createModulePermissions('dealers');
        Permission::firstOrCreate(['name' => PermissionName::api('dealers', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('retailers');
        Permission::firstOrCreate(['name' => PermissionName::api('retailers', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('product-categories');
        $this->createModulePermissions('products');
        $this->createModulePermissions('attendance');
        Permission::firstOrCreate(['name' => PermissionName::api('attendance', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('gps-logs');
        Permission::firstOrCreate(['name' => PermissionName::api('gps-logs', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('visit-plans');
        Permission::firstOrCreate(['name' => PermissionName::api('visit-plans', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('visits');
        Permission::firstOrCreate(['name' => PermissionName::api('visits', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('orders');
        Permission::firstOrCreate(['name' => PermissionName::api('orders', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('collection-entries');
        Permission::firstOrCreate(['name' => PermissionName::api('collection-entries', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('deliveries', withApi: false);
        $this->createModulePermissions('sales-returns');
        Permission::firstOrCreate(['name' => PermissionName::api('sales-returns', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('cash-handovers', withApi: false);
        $this->createModulePermissions('targets');
        $this->createModulePermissions('achievements');
        Permission::firstOrCreate(['name' => PermissionName::api('achievements', ButtonAction::Add), 'guard_name' => 'web']);
        // Submitting leave from the phone. createModulePermissions() only
        // makes the api.*.view half, so the add half is declared here like
        // every other self-service mobile action above.
        Permission::firstOrCreate(['name' => PermissionName::api('leave-requests', ButtonAction::Add), 'guard_name' => 'web']);

        foreach ([...self::REPORTS, ...self::RETIRED_REPORTS] as $reportKey) {
            Permission::firstOrCreate(['name' => PermissionName::report($reportKey), 'guard_name' => 'web']);
        }

        $this->createModulePermissions('notifications');
        $this->createModulePermissions('announcements', withApi: false);
        $this->createModulePermissions('activity-log', withApi: false);
        $this->createModulePermissions('territory-map', withApi: false);
        $this->createModulePermissions('live-gps', withApi: false);
        $this->createModulePermissions('settings', withApi: false);
        $this->createModulePermissions('inquiries', withApi: false);
        $this->createModulePermissions('visit-requests', withApi: false);
        $this->createModulePermissions('news', withApi: false);
        $this->createModulePermissions('promotions', withApi: false);
        $this->createModulePermissions('faqs', withApi: false);
        $this->createModulePermissions('service-centers', withApi: false);
        $this->createModulePermissions('brochures', withApi: false);
        $this->createTallyIntegrationPermissions();

        $this->assignPermissions();
    }

    private function createDashboardPermissions(): void
    {
        Permission::firstOrCreate(['name' => PermissionName::menu('dashboard'), 'guard_name' => 'web']);
    }

    private function createModulePermissions(string $module, bool $withApi = true): void
    {
        Permission::firstOrCreate(['name' => PermissionName::menu($module), 'guard_name' => 'web']);

        foreach (PermissionName::buttons($module) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        if ($withApi) {
            Permission::firstOrCreate(['name' => PermissionName::api($module, ButtonAction::View), 'guard_name' => 'web']);
        }
    }

    /**
     * One flat permission set covers the whole Tally Integration section
     * (Dashboard, Connections, Sync Queue, Sync Logs, Mapping,
     * Reconciliation) — there's no per-screen CRUD shape here worth
     * splitting into separate menu/button permissions per sub-page, same
     * reasoning as Reports' single report.{key} permission per page. Not
     * exposed to the mobile API at all (no api.tally-integration.*).
     */
    private function createTallyIntegrationPermissions(): void
    {
        Permission::firstOrCreate(['name' => PermissionName::menu('tally-integration'), 'guard_name' => 'web']);

        foreach (['view', 'configure', 'sync', 'retry', 'reconcile'] as $action) {
            Permission::firstOrCreate(['name' => "tally-integration.{$action}", 'guard_name' => 'web']);
        }
    }

    private function assignPermissions(): void
    {
        Role::findByName('Super Admin', 'web')->syncPermissions(Permission::all());

        $orgModules = ['sales-teams', 'holidays', 'territories', 'divisions', 'districts', 'thanas', 'depots', 'vehicles', 'drivers', 'leave-types'];

        $generalManagerPermissions = [
            PermissionName::menu('dashboard'),
            PermissionName::menu('users'),
            PermissionName::button('users', ButtonAction::View),
            PermissionName::button('users', ButtonAction::Add),
            PermissionName::button('users', ButtonAction::Edit),
            PermissionName::button('users', ButtonAction::Export),
            PermissionName::button('users', ButtonAction::Print),
            PermissionName::menu('roles'),
            PermissionName::button('roles', ButtonAction::View),
            PermissionName::menu('permissions'),
            PermissionName::button('permissions', ButtonAction::View),
        ];

        $productModules = ['product-categories', 'products'];

        foreach ([...$orgModules, 'dealers', 'retailers', ...$productModules] as $module) {
            $generalManagerPermissions[] = PermissionName::menu($module);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::View);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Add);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Edit);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Export);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Print);
        }

        // Leave: a General Manager both files and decides it. Approve is
        // the permission that separates deciding someone's leave from
        // merely requesting your own, and it is never granted to Sales
        // Executive below.
        // Granting days is HR work, so this sits with the General Manager
        // and Super Admin only. It is deliberately kept out of
        // $orgModules: that list hands view rights to every line manager,
        // and one manager browsing the whole company's entitlements is not
        // the same as them approving their own team's leave.
        $generalManagerPermissions[] = PermissionName::menu('leave-balances');
        foreach ([ButtonAction::View, ButtonAction::Add, ButtonAction::Edit, ButtonAction::Delete, ButtonAction::Export, ButtonAction::Print] as $action) {
            $generalManagerPermissions[] = PermissionName::button('leave-balances', $action);
        }

        $generalManagerPermissions[] = PermissionName::menu('leave-requests');
        foreach ([ButtonAction::View, ButtonAction::Add, ButtonAction::Edit, ButtonAction::Approve, ButtonAction::Export, ButtonAction::Print] as $action) {
            $generalManagerPermissions[] = PermissionName::button('leave-requests', $action);
        }
        $generalManagerPermissions[] = PermissionName::api('leave-requests', ButtonAction::View);

        $generalManagerPermissions[] = PermissionName::menu('attendance');
        $generalManagerPermissions[] = PermissionName::button('attendance', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('attendance', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('attendance', ButtonAction::Edit);
        $generalManagerPermissions[] = PermissionName::button('attendance', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('attendance', ButtonAction::Print);

        // GPS logs have no create/edit UI at all (pings only arrive via the
        // mobile API), so General Manager only gets the read/report actions.
        $generalManagerPermissions[] = PermissionName::menu('gps-logs');
        $generalManagerPermissions[] = PermissionName::button('gps-logs', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('gps-logs', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('gps-logs', ButtonAction::Print);

        // Same read-only shape as gps-logs — no create/edit, pings are API-only.
        $generalManagerPermissions[] = PermissionName::menu('live-gps');
        $generalManagerPermissions[] = PermissionName::button('live-gps', ButtonAction::View);

        foreach (['visit-plans', 'visits'] as $module) {
            $generalManagerPermissions[] = PermissionName::menu($module);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::View);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Add);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Edit);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Export);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Print);
        }

        // Orders and Collection Entries were revived as the live
        // transactional backbone for Tally sync (see docs/tally-sfa-integration.md,
        // Phase 2) — Achievement stays for daily target-tracking, but real
        // transactions go through these again. Cash Handover stays retired
        // (a separate, permanent decision, unrelated to the Tally work).
        $generalManagerPermissions[] = PermissionName::menu('orders');
        $generalManagerPermissions[] = PermissionName::button('orders', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('orders', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('orders', ButtonAction::Edit);
        $generalManagerPermissions[] = PermissionName::button('orders', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('orders', ButtonAction::Print);
        // Approve/reject sits with General Manager and Super Admin only —
        // same separation-of-accountability rule as achievements below.
        $generalManagerPermissions[] = PermissionName::button('orders', ButtonAction::Approve);

        $generalManagerPermissions[] = PermissionName::menu('collection-entries');
        $generalManagerPermissions[] = PermissionName::button('collection-entries', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('collection-entries', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('collection-entries', ButtonAction::Edit);
        $generalManagerPermissions[] = PermissionName::button('collection-entries', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('collection-entries', ButtonAction::Print);
        $generalManagerPermissions[] = PermissionName::button('collection-entries', ButtonAction::Approve);

        // Delivery/Challan (Phase 4): dispatching a fully-allocated order and
        // marking it delivered are both gated by deliveries.add — there's no
        // separate edit/delete UI for an append-only dispatch record (see
        // App\Models\Delivery's own doc comment).
        $generalManagerPermissions[] = PermissionName::menu('deliveries');
        $generalManagerPermissions[] = PermissionName::button('deliveries', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('deliveries', ButtonAction::Add);

        // Full Sales Return workflow (Phase 6): view/approve the request,
        // edit covers the dispatch/receive status transitions (see
        // SalesReturnPolicy::update()'s own doc comment).
        $generalManagerPermissions[] = PermissionName::menu('sales-returns');
        $generalManagerPermissions[] = PermissionName::button('sales-returns', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('sales-returns', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('sales-returns', ButtonAction::Edit);
        $generalManagerPermissions[] = PermissionName::button('sales-returns', ButtonAction::Approve);
        $generalManagerPermissions[] = PermissionName::button('sales-returns', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('sales-returns', ButtonAction::Print);

        $generalManagerPermissions[] = PermissionName::menu('targets');
        $generalManagerPermissions[] = PermissionName::button('targets', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('targets', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('targets', ButtonAction::Edit);
        $generalManagerPermissions[] = PermissionName::button('targets', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('targets', ButtonAction::Print);

        $generalManagerPermissions[] = PermissionName::menu('achievements');
        $generalManagerPermissions[] = PermissionName::button('achievements', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('achievements', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('achievements', ButtonAction::Edit);
        $generalManagerPermissions[] = PermissionName::button('achievements', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('achievements', ButtonAction::Print);
        // Approve/reject sits with General Manager and Super Admin only —
        // same separation-of-accountability rule Orders/Collections used.
        $generalManagerPermissions[] = PermissionName::button('achievements', ButtonAction::Approve);

        foreach (self::REPORTS as $reportKey) {
            $generalManagerPermissions[] = PermissionName::report($reportKey);
        }

        $generalManagerPermissions[] = PermissionName::menu('notifications');
        $generalManagerPermissions[] = PermissionName::button('notifications', ButtonAction::View);

        $generalManagerPermissions[] = PermissionName::menu('announcements');
        $generalManagerPermissions[] = PermissionName::button('announcements', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('announcements', ButtonAction::Add);
        $generalManagerPermissions[] = PermissionName::button('announcements', ButtonAction::Delete);
        $generalManagerPermissions[] = PermissionName::button('announcements', ButtonAction::Restore);

        // Audit trail — read-only, General Manager and Super Admin only.
        $generalManagerPermissions[] = PermissionName::menu('activity-log');
        $generalManagerPermissions[] = PermissionName::button('activity-log', ButtonAction::View);
        $generalManagerPermissions[] = PermissionName::button('activity-log', ButtonAction::Export);
        $generalManagerPermissions[] = PermissionName::button('activity-log', ButtonAction::Print);

        // Same audience as the Territory Performance report — read-only.
        $generalManagerPermissions[] = PermissionName::menu('territory-map');
        $generalManagerPermissions[] = PermissionName::button('territory-map', ButtonAction::View);

        // Customer Web Portal inquiries/visit requests — view + status update only.
        foreach (['inquiries', 'visit-requests'] as $module) {
            $generalManagerPermissions[] = PermissionName::menu($module);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::View);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Edit);
        }

        // Customer Web Portal content management — full CRUD, General Manager
        // and Super Admin only (same audience as Settings), since managing
        // marketing content isn't a Sales/Area/Territory Manager concern.
        foreach (['news', 'promotions', 'faqs', 'service-centers', 'brochures'] as $module) {
            $generalManagerPermissions[] = PermissionName::menu($module);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::View);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Add);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Edit);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Delete);
            $generalManagerPermissions[] = PermissionName::button($module, ButtonAction::Restore);
        }

        // Tally Integration — back-office financial-integration concern,
        // same audience as Settings: Super Admin and General Manager only.
        // Sales Officers get no Tally administrative access (spec §40).
        $generalManagerPermissions[] = PermissionName::menu('tally-integration');
        foreach (['view', 'configure', 'sync', 'retry', 'reconcile'] as $action) {
            $generalManagerPermissions[] = "tally-integration.{$action}";
        }

        Role::findByName('General Manager', 'web')->syncPermissions($generalManagerPermissions);

        foreach (['Sales Manager', 'Area Manager', 'Territory Manager'] as $managerRole) {
            $permissions = [
                PermissionName::menu('dashboard'),
                PermissionName::menu('users'),
                PermissionName::button('users', ButtonAction::View),
                PermissionName::menu('dealers'),
                PermissionName::button('dealers', ButtonAction::View),
                PermissionName::button('dealers', ButtonAction::Add),
                PermissionName::button('dealers', ButtonAction::Edit),
                PermissionName::menu('retailers'),
                PermissionName::button('retailers', ButtonAction::View),
                PermissionName::button('retailers', ButtonAction::Add),
                PermissionName::button('retailers', ButtonAction::Edit),
                PermissionName::menu('attendance'),
                PermissionName::button('attendance', ButtonAction::View),
                PermissionName::button('attendance', ButtonAction::Export),
                PermissionName::button('attendance', ButtonAction::Print),
                PermissionName::menu('gps-logs'),
                PermissionName::button('gps-logs', ButtonAction::View),
                PermissionName::button('gps-logs', ButtonAction::Export),
                PermissionName::button('gps-logs', ButtonAction::Print),
                PermissionName::menu('live-gps'),
                PermissionName::button('live-gps', ButtonAction::View),
                PermissionName::menu('visit-plans'),
                PermissionName::button('visit-plans', ButtonAction::View),
                PermissionName::button('visit-plans', ButtonAction::Add),
                PermissionName::button('visit-plans', ButtonAction::Edit),
                PermissionName::button('visit-plans', ButtonAction::Export),
                PermissionName::button('visit-plans', ButtonAction::Print),
                PermissionName::menu('visits'),
                PermissionName::button('visits', ButtonAction::View),
                PermissionName::button('visits', ButtonAction::Export),
                PermissionName::button('visits', ButtonAction::Print),
                // Orders and Collection Entries were revived (see the
                // General Manager block above) — Cash Handover stays retired.
                PermissionName::menu('orders'),
                PermissionName::button('orders', ButtonAction::View),
                PermissionName::button('orders', ButtonAction::Export),
                PermissionName::button('orders', ButtonAction::Print),
                PermissionName::menu('collection-entries'),
                PermissionName::button('collection-entries', ButtonAction::View),
                PermissionName::button('collection-entries', ButtonAction::Export),
                PermissionName::button('collection-entries', ButtonAction::Print),
                // Same dispatch/deliver ability as General Manager — this
                // tier runs day-to-day logistics.
                PermissionName::menu('deliveries'),
                PermissionName::button('deliveries', ButtonAction::View),
                PermissionName::button('deliveries', ButtonAction::Add),
                // Same full workflow ability as General Manager — this
                // tier approves/dispatches/receives returns day-to-day.
                PermissionName::menu('sales-returns'),
                PermissionName::button('sales-returns', ButtonAction::View),
                PermissionName::button('sales-returns', ButtonAction::Edit),
                PermissionName::button('sales-returns', ButtonAction::Approve),
                PermissionName::button('sales-returns', ButtonAction::Export),
                PermissionName::button('sales-returns', ButtonAction::Print),
                PermissionName::menu('targets'),
                PermissionName::button('targets', ButtonAction::View),
                PermissionName::button('targets', ButtonAction::Add),
                PermissionName::button('targets', ButtonAction::Edit),
                PermissionName::button('targets', ButtonAction::Export),
                PermissionName::button('targets', ButtonAction::Print),
                // View/export/print only, same as Targets — approval stays
                // with General Manager.
                PermissionName::menu('achievements'),
                PermissionName::button('achievements', ButtonAction::View),
                PermissionName::button('achievements', ButtonAction::Export),
                PermissionName::button('achievements', ButtonAction::Print),
                PermissionName::menu('notifications'),
                PermissionName::button('notifications', ButtonAction::View),
                PermissionName::menu('territory-map'),
                PermissionName::button('territory-map', ButtonAction::View),
                // View-only, same as the "visits" module — only General Manager updates status.
                PermissionName::menu('inquiries'),
                PermissionName::button('inquiries', ButtonAction::View),
                PermissionName::menu('visit-requests'),
                PermissionName::button('visit-requests', ButtonAction::View),
                // Deciding leave for their own people is the whole point
                // of a line manager here. HasVisibilityScope limits that
                // to executives in their territories, and the policy
                // refuses self-approval, so Approve cannot become a way to
                // sign off their own time off.
                PermissionName::menu('leave-requests'),
                PermissionName::button('leave-requests', ButtonAction::View),
                PermissionName::button('leave-requests', ButtonAction::Add),
                PermissionName::button('leave-requests', ButtonAction::Approve),
                PermissionName::button('leave-requests', ButtonAction::Export),
                PermissionName::button('leave-requests', ButtonAction::Print),
                PermissionName::api('leave-requests', ButtonAction::View),
            ];

            foreach (self::REPORTS as $reportKey) {
                $permissions[] = PermissionName::report($reportKey);
            }

            foreach ([...$orgModules, ...$productModules] as $module) {
                $permissions[] = PermissionName::menu($module);
                $permissions[] = PermissionName::button($module, ButtonAction::View);
            }

            Role::findByName($managerRole, 'web')->syncPermissions($permissions);
        }

        $salesExecutivePermissions = [
            PermissionName::menu('dashboard'),
            PermissionName::menu('dealers'),
            PermissionName::button('dealers', ButtonAction::View),
            PermissionName::button('dealers', ButtonAction::Add),
            PermissionName::api('dealers', ButtonAction::View),
            PermissionName::api('dealers', ButtonAction::Add),
            PermissionName::menu('retailers'),
            PermissionName::button('retailers', ButtonAction::View),
            PermissionName::button('retailers', ButtonAction::Add),
            PermissionName::api('retailers', ButtonAction::View),
            PermissionName::api('retailers', ButtonAction::Add),
            PermissionName::api('attendance', ButtonAction::View),
            PermissionName::api('attendance', ButtonAction::Add),
            PermissionName::api('gps-logs', ButtonAction::View),
            PermissionName::api('gps-logs', ButtonAction::Add),
            PermissionName::api('visit-plans', ButtonAction::View),
            PermissionName::api('visit-plans', ButtonAction::Add),
            PermissionName::api('visits', ButtonAction::View),
            PermissionName::api('visits', ButtonAction::Add),
            PermissionName::api('orders', ButtonAction::View),
            PermissionName::api('orders', ButtonAction::Add),
            PermissionName::api('collection-entries', ButtonAction::View),
            PermissionName::api('collection-entries', ButtonAction::Add),
            PermissionName::api('sales-returns', ButtonAction::View),
            PermissionName::api('sales-returns', ButtonAction::Add),
            PermissionName::api('targets', ButtonAction::View),
            PermissionName::api('achievements', ButtonAction::View),
            PermissionName::api('achievements', ButtonAction::Add),
            PermissionName::menu('notifications'),
            PermissionName::button('notifications', ButtonAction::View),
            PermissionName::api('notifications', ButtonAction::View),
            // Admin-panel access to their own historical records, plus
            // recording their own new ones the same as the mobile app
            // allows (add only — editing an already-recorded order/
            // collection still stays a manager action). Order/
            // CollectionEntry::scopeVisibleTo() and the matching Policy
            // checks enforce that a plain Sales Executive only ever sees or
            // acts on their own rows here, never another executive's.
            PermissionName::menu('orders'),
            PermissionName::button('orders', ButtonAction::View),
            PermissionName::button('orders', ButtonAction::Add),
            PermissionName::button('orders', ButtonAction::Export),
            PermissionName::button('orders', ButtonAction::Print),
            PermissionName::menu('collection-entries'),
            PermissionName::button('collection-entries', ButtonAction::View),
            PermissionName::button('collection-entries', ButtonAction::Add),
            PermissionName::button('collection-entries', ButtonAction::Export),
            PermissionName::button('collection-entries', ButtonAction::Print),
            // View only — dispatching stays a manager/logistics action.
            PermissionName::menu('deliveries'),
            PermissionName::button('deliveries', ButtonAction::View),
            // Request their own returns (mirrors Order/Collection Entry's
            // add-only shape); approving/dispatching/receiving stays a
            // manager action.
            PermissionName::menu('sales-returns'),
            PermissionName::button('sales-returns', ButtonAction::View),
            PermissionName::button('sales-returns', ButtonAction::Add),
            // Their own leave: submit and track it, on the phone and in
            // the admin panel. Deliberately no Approve - LeaveRequest::
            // scopeVisibleTo() plus LeaveRequestPolicy keep an executive
            // to their own requests, and nobody decides their own leave.
            PermissionName::menu('leave-requests'),
            PermissionName::button('leave-requests', ButtonAction::View),
            PermissionName::button('leave-requests', ButtonAction::Add),
            PermissionName::api('leave-requests', ButtonAction::View),
            PermissionName::api('leave-requests', ButtonAction::Add),
            PermissionName::menu('targets'),
            PermissionName::button('targets', ButtonAction::View),
            PermissionName::button('targets', ButtonAction::Export),
            PermissionName::button('targets', ButtonAction::Print),
            PermissionName::menu('achievements'),
            PermissionName::button('achievements', ButtonAction::View),
            PermissionName::button('achievements', ButtonAction::Add),
            PermissionName::button('achievements', ButtonAction::Export),
            PermissionName::button('achievements', ButtonAction::Print),
            // Only the report types with a meaningful "just mine" view —
            // territories/dealer-coverage/executive-performance are
            // cross-executive aggregate/comparison views with no sensible
            // per-executive projection, so they're deliberately withheld.
            PermissionName::report('attendance'),
            PermissionName::report('visits'),
            PermissionName::report('order-performance'),
            PermissionName::report('collections'),
            PermissionName::report('sales-return-summary'),
            PermissionName::report('achievement-summary'),
            PermissionName::report('target-achievement'),
            PermissionName::report('gps'),
            PermissionName::report('movement-summary'),
        ];

        foreach ($productModules as $module) {
            $salesExecutivePermissions[] = PermissionName::menu($module);
            $salesExecutivePermissions[] = PermissionName::button($module, ButtonAction::View);
            $salesExecutivePermissions[] = PermissionName::api($module, ButtonAction::View);
        }

        Role::findByName('Sales Executive', 'web')->syncPermissions($salesExecutivePermissions);
    }
}
