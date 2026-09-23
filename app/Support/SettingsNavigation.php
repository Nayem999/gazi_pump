<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\User;

/**
 * Everything reachable under Settings, in the order it should be listed.
 *
 * Settings is the home for what an administrator *configures*, as opposed
 * to what the business *operates* day to day. The main sidebar keeps the
 * operational screens - orders, collections, attendance, leave, reports -
 * and everything that is set up once and then left alone moves here. That
 * is what keeps the main navigation short enough to scan.
 *
 * Each entry names the permission that gates it, and entries the viewer
 * cannot reach are left out rather than shown and then refused with a 403.
 * A section with nothing left in it disappears entirely.
 */
final class SettingsNavigation
{
    /**
     * @return array<int, array{label: string, items: array<int, array{label: string, icon: string, route: string, permission: string, description?: string}>}>
     */
    public static function sections(): array
    {
        return [
            [
                'label' => 'General',
                'items' => [
                    ['label' => 'Company Profile', 'icon' => 'ti-building', 'route' => 'settings.edit', 'permission' => 'menu.settings', 'description' => 'Company name, logo, contact details and the defaults shown across the app.'],
                    ['label' => 'Holidays', 'icon' => 'ti-calendar-event', 'route' => 'holidays.index', 'permission' => 'menu.holidays', 'description' => 'Government and company holidays. Excluded from attendance and from leave day counts.'],
                ],
            ],
            [
                'label' => 'Access Control',
                'items' => [
                    ['label' => 'Users', 'icon' => 'ti-users', 'route' => 'users.index', 'permission' => 'menu.users', 'description' => 'Staff accounts, their roles and which territories they cover.'],
                    ['label' => 'Roles', 'icon' => 'ti-shield-lock', 'route' => 'roles.index', 'permission' => 'menu.roles', 'description' => 'What each role is allowed to see and do.'],
                    ['label' => 'Permissions', 'icon' => 'ti-key', 'route' => 'permissions.index', 'permission' => 'menu.permissions', 'description' => 'The full permission list, for reference.'],
                ],
            ],
            [
                'label' => 'Organization',
                'items' => [
                    ['label' => 'Sales Teams', 'icon' => 'ti-users-group', 'route' => 'sales-teams.index', 'permission' => 'menu.sales-teams', 'description' => 'Team structure and the product lines each team carries.'],
                    ['label' => 'Territories', 'icon' => 'ti-map-2', 'route' => 'territories.index', 'permission' => 'menu.territories', 'description' => 'Sales territories and the areas they cover.'],
                    ['label' => 'Divisions', 'icon' => 'ti-map-pin', 'route' => 'divisions.index', 'permission' => 'menu.divisions', 'description' => 'Top-level geography.'],
                    ['label' => 'Districts', 'icon' => 'ti-map-pin', 'route' => 'districts.index', 'permission' => 'menu.districts', 'description' => 'Districts within each division.'],
                    ['label' => 'Thanas', 'icon' => 'ti-map-pin', 'route' => 'thanas.index', 'permission' => 'menu.thanas', 'description' => 'Thanas within each district.'],
                ],
            ],
            [
                'label' => 'Logistics',
                'items' => [
                    ['label' => 'Depots', 'icon' => 'ti-building-warehouse', 'route' => 'depots.index', 'permission' => 'menu.depots', 'description' => 'Stock locations that orders are fulfilled from.'],
                    ['label' => 'Vehicles', 'icon' => 'ti-truck', 'route' => 'vehicles.index', 'permission' => 'menu.vehicles', 'description' => 'Delivery vehicles.'],
                    ['label' => 'Drivers', 'icon' => 'ti-id-badge-2', 'route' => 'drivers.index', 'permission' => 'menu.drivers', 'description' => 'Drivers assigned to deliveries.'],
                ],
            ],
            [
                'label' => 'Leave',
                'items' => [
                    ['label' => 'Leave Types', 'icon' => 'ti-beach', 'route' => 'leave-types.index', 'permission' => 'menu.leave-types', 'description' => 'Casual, Sick, Annual and so on, with the yearly quota each carries.'],
                    ['label' => 'Leave Entitlements', 'icon' => 'ti-scale', 'route' => 'leave-balances.index', 'permission' => 'menu.leave-balances', 'description' => 'How many days each employee is granted, per type, per year.'],
                ],
            ],
            [
                'label' => 'Content',
                'items' => [
                    ['label' => 'News', 'icon' => 'ti-news', 'route' => 'news.index', 'permission' => 'menu.news', 'description' => 'Articles shown in the customer portal and mobile app.'],
                    ['label' => 'Promotions', 'icon' => 'ti-discount', 'route' => 'promotions.index', 'permission' => 'menu.promotions', 'description' => 'Campaigns and offers.'],
                    ['label' => 'FAQs', 'icon' => 'ti-help-circle', 'route' => 'faqs.index', 'permission' => 'menu.faqs', 'description' => 'Questions answered in the portal.'],
                    ['label' => 'Service Centers', 'icon' => 'ti-tool', 'route' => 'service-centers.index', 'permission' => 'menu.service-centers', 'description' => 'Locations customers can be directed to.'],
                    ['label' => 'Brochures', 'icon' => 'ti-file-description', 'route' => 'brochures.index', 'permission' => 'menu.brochures', 'description' => 'Downloadable product literature.'],
                    ['label' => 'Announcements', 'icon' => 'ti-speakerphone', 'route' => 'announcements.index', 'permission' => 'menu.announcements', 'description' => 'Messages pushed to staff.'],
                ],
            ],
            [
                'label' => 'System',
                'items' => [
                    ['label' => 'Tally Integration', 'icon' => 'ti-plug-connected', 'route' => 'tally-integration.dashboard', 'permission' => 'menu.tally-integration', 'description' => 'Sync with TallyPrime: connections, queue, mapping and reconciliation.'],
                    ['label' => 'Activity Log', 'icon' => 'ti-history', 'route' => 'activity-log.index', 'permission' => 'menu.activity-log', 'description' => 'Who changed what, and when.'],
                ],
            ],
        ];
    }

    /**
     * The same list with anything this user cannot reach removed.
     *
     * @return array<int, array{label: string, items: array<int, array{label: string, icon: string, route: string, permission: string, description?: string}>}>
     */
    public static function for(?User $user): array
    {
        if ($user === null) {
            return [];
        }

        $sections = [];

        foreach (self::sections() as $section) {
            $items = array_values(array_filter(
                $section['items'],
                // A route that does not exist is skipped rather than
                // allowed to throw: a half-installed module should not take
                // the whole navigation down with it.
                fn (array $item) => $user->can($item['permission']) && app('router')->has($item['route']),
            ));

            if ($items !== []) {
                $sections[] = ['label' => $section['label'], 'items' => $items];
            }
        }

        return $sections;
    }

    /** Whether this user can reach anything under Settings at all. */
    public static function availableTo(?User $user): bool
    {
        return self::for($user) !== [];
    }

    /**
     * Where to send someone who asks for "Settings" with no particular page
     * in mind - the first thing they are actually allowed to open.
     *
     * Used as a fallback only: the Settings link goes to the hub, which
     * lists everything. This exists for the case where the hub itself is
     * bypassed.
     */
    public static function landingRouteFor(?User $user): ?string
    {
        foreach (self::for($user) as $section) {
            foreach ($section['items'] as $item) {
                return route($item['route']);
            }
        }

        return null;
    }

    /**
     * True when the current request is on one of the Settings screens, so
     * the pinned Settings link can show as active. Matched on route name
     * rather than URL because several of these live outside a /settings
     * prefix - they were operational screens before Settings adopted them.
     */
    public static function isActive(): bool
    {
        $current = request()->route()?->getName();

        if ($current === null) {
            return false;
        }

        foreach (self::sections() as $section) {
            foreach ($section['items'] as $item) {
                // Compare on the module prefix so a nested page (editing a
                // holiday, say) still lights the Settings link.
                $prefix = str_contains($item['route'], '.')
                    ? substr($item['route'], 0, strrpos($item['route'], '.'))
                    : $item['route'];

                if ($current === $item['route'] || str_starts_with($current, $prefix.'.')) {
                    return true;
                }
            }
        }

        return str_starts_with($current, 'settings.');
    }
}
