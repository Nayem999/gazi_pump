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
    ];

    /**
     * Retired reports (Order/Collection Entry/Dealer Ledger — see the
     * "version 1" Achievement pivot): the `report.{key}` permission still
     * exists so Super Admin keeps historical access via Permission::all(),
     * but it's no longer assigned to any other role below.
     */
    private const RETIRED_REPORTS = [
        'order-performance',
        'collections',
        'dealer-ledger',
    ];

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
        $this->createModulePermissions('dealers');
        Permission::firstOrCreate(['name' => PermissionName::api('dealers', ButtonAction::Add), 'guard_name' => 'web']);
        $this->createModulePermissions('retailers');
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
        $this->createModulePermissions('cash-handovers', withApi: false);
        $this->createModulePermissions('targets');
        $this->createModulePermissions('achievements');
        Permission::firstOrCreate(['name' => PermissionName::api('achievements', ButtonAction::Add), 'guard_name' => 'web']);

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

    private function assignPermissions(): void
    {
        Role::findByName('Super Admin', 'web')->syncPermissions(Permission::all());

        $orgModules = ['sales-teams', 'holidays', 'territories', 'divisions', 'districts', 'thanas'];

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

        // Orders, Collection Entries, and Cash Handovers are retired
        // (version 1 pivot to daily Achievement reporting) — no longer
        // assigned here. Their permissions still exist (created in run())
        // so Super Admin keeps historical access via Permission::all().

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
                // Orders, Collection Entries, and Cash Handovers are retired
                // (version 1 pivot to daily Achievement reporting).
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
            PermissionName::api('attendance', ButtonAction::View),
            PermissionName::api('attendance', ButtonAction::Add),
            PermissionName::api('gps-logs', ButtonAction::View),
            PermissionName::api('gps-logs', ButtonAction::Add),
            PermissionName::api('visit-plans', ButtonAction::View),
            PermissionName::api('visit-plans', ButtonAction::Add),
            PermissionName::api('visits', ButtonAction::View),
            PermissionName::api('visits', ButtonAction::Add),
            PermissionName::api('targets', ButtonAction::View),
            PermissionName::api('achievements', ButtonAction::View),
            PermissionName::api('achievements', ButtonAction::Add),
            PermissionName::menu('notifications'),
            PermissionName::button('notifications', ButtonAction::View),
            PermissionName::api('notifications', ButtonAction::View),
            // Admin-panel access to their own historical records. Order/
            // Collection Entry are retired (version 1 pivot to daily
            // Achievement reporting) — Achievement takes their place:
            // add only (editing an already-reviewed entry still stays a
            // manager action once approved/rejected). Target/
            // AchievementEntry::scopeVisibleTo() and the matching Policy
            // checks enforce that a plain Sales Executive only ever sees or
            // acts on their own rows here, never another executive's.
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
