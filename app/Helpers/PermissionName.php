<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Enums\ButtonAction;
use App\Enums\PermissionType;

/**
 * Builds consistent permission-name strings so every module names its
 * permissions the same way. Used by seeders (to create permissions) and by
 * controllers/Blade (to check them), so the two sides can never drift apart.
 */
final class PermissionName
{
    public static function menu(string $module): string
    {
        return "menu.{$module}";
    }

    public static function submenu(string $module, string $submodule): string
    {
        return "submenu.{$module}.{$submodule}";
    }

    public static function button(string $module, ButtonAction $action): string
    {
        return "{$module}.{$action->value}";
    }

    /**
     * @return array<int, string> one button permission per action, e.g. ["users.view", "users.add", ...]
     */
    public static function buttons(string $module, ?array $actions = null): array
    {
        $actions ??= ButtonAction::cases();

        return array_map(
            static fn (ButtonAction $action): string => self::button($module, $action),
            $actions,
        );
    }

    public static function api(string $module, ButtonAction $action): string
    {
        return "api.{$module}.{$action->value}";
    }

    public static function report(string $reportKey): string
    {
        return "report.{$reportKey}";
    }

    /**
     * Classifies a permission name back into its PermissionType, based on
     * the naming convention the builders above produce.
     */
    public static function typeOf(string $name): PermissionType
    {
        $prefix = explode('.', $name)[0];

        return PermissionType::tryFrom($prefix) ?? PermissionType::Button;
    }

    /**
     * The module a permission belongs to, e.g. "users" for both
     * "users.view" (button) and "menu.users" (menu).
     */
    public static function moduleOf(string $name): string
    {
        $segments = explode('.', $name);

        return match (self::typeOf($name)) {
            PermissionType::Button => $segments[0],
            default => $segments[1] ?? $segments[0],
        };
    }
}
