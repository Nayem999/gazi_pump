<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Enums\ButtonAction;
use App\Enums\PermissionType;
use App\Helpers\PermissionName;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PermissionNameTest extends TestCase
{
    public function test_menu_permission_name(): void
    {
        $this->assertSame('menu.users', PermissionName::menu('users'));
    }

    public function test_submenu_permission_name(): void
    {
        $this->assertSame('submenu.reports.sales', PermissionName::submenu('reports', 'sales'));
    }

    public function test_button_permission_name(): void
    {
        $this->assertSame('users.view', PermissionName::button('users', ButtonAction::View));
        $this->assertSame('users.delete', PermissionName::button('users', ButtonAction::Delete));
    }

    public function test_buttons_generates_one_permission_per_action(): void
    {
        $permissions = PermissionName::buttons('users');

        $this->assertCount(count(ButtonAction::cases()), $permissions);
        $this->assertContains('users.view', $permissions);
        $this->assertContains('users.approve', $permissions);
    }

    public function test_buttons_can_be_scoped_to_a_subset_of_actions(): void
    {
        $permissions = PermissionName::buttons('roles', [ButtonAction::View, ButtonAction::Edit]);

        $this->assertSame(['roles.view', 'roles.edit'], $permissions);
    }

    public function test_api_permission_name(): void
    {
        $this->assertSame('api.users.view', PermissionName::api('users', ButtonAction::View));
    }

    public function test_report_permission_name(): void
    {
        $this->assertSame('report.sales-summary', PermissionName::report('sales-summary'));
    }

    #[DataProvider('typeClassificationProvider')]
    public function test_type_of_classifies_permission_names_correctly(string $name, PermissionType $expected): void
    {
        $this->assertSame($expected, PermissionName::typeOf($name));
    }

    /**
     * @return array<string, array{string, PermissionType}>
     */
    public static function typeClassificationProvider(): array
    {
        return [
            'menu' => ['menu.users', PermissionType::Menu],
            'submenu' => ['submenu.reports.sales', PermissionType::Submenu],
            'api' => ['api.users.view', PermissionType::Api],
            'report' => ['report.sales-summary', PermissionType::Report],
            'button' => ['users.view', PermissionType::Button],
        ];
    }

    public function test_module_of_extracts_the_owning_module(): void
    {
        $this->assertSame('users', PermissionName::moduleOf('users.view'));
        $this->assertSame('users', PermissionName::moduleOf('menu.users'));
        $this->assertSame('users', PermissionName::moduleOf('api.users.view'));
        $this->assertSame('sales-summary', PermissionName::moduleOf('report.sales-summary'));
    }
}
