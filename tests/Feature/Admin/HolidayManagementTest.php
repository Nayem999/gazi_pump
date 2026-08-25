<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Holiday;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('holidays.index'))->assertRedirect(route('login'));
    }

    public function test_user_without_permission_gets_forbidden(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('holidays.index'))->assertForbidden();
    }

    public function test_a_territory_manager_can_view_but_not_create_holidays(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');

        $this->actingAs($manager)->get(route('holidays.index'))->assertOk();
        $this->actingAs($manager)->get(route('holidays.create'))->assertForbidden();
    }

    public function test_super_admin_can_view_and_create_a_holiday(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->get(route('holidays.index'))->assertOk();

        $response = $this->actingAs($admin)->post(route('holidays.store'), [
            'name' => 'Independence Day',
            'date' => '2026-03-26',
            'description' => 'National holiday',
            'status' => '1',
        ]);

        $response->assertRedirect(route('holidays.index'));
        $this->assertDatabaseHas('holidays', ['name' => 'Independence Day', 'date' => '2026-03-26']);
    }

    public function test_date_must_be_unique(): void
    {
        Holiday::factory()->create(['date' => '2026-12-16']);

        $response = $this->actingAs($this->superAdmin())->post(route('holidays.store'), [
            'name' => 'Duplicate Holiday',
            'date' => '2026-12-16',
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_the_year_filter_only_returns_holidays_in_that_year(): void
    {
        $admin = $this->superAdmin();
        Holiday::factory()->create(['name' => 'Holiday In 2026', 'date' => '2026-03-26']);
        Holiday::factory()->create(['name' => 'Holiday In 2027', 'date' => '2027-03-26']);

        $response = $this->actingAs($admin)->get(route('holidays.index', ['year' => 2026]));

        $response->assertOk()->assertSee('Holiday In 2026')->assertDontSee('Holiday In 2027');
    }

    public function test_super_admin_can_update_delete_and_restore_a_holiday(): void
    {
        $admin = $this->superAdmin();
        $holiday = Holiday::factory()->create();

        $this->actingAs($admin)->put(route('holidays.update', $holiday), [
            'name' => 'Renamed Holiday',
            'date' => $holiday->date->toDateString(),
            'status' => '1',
        ])->assertRedirect(route('holidays.index'));
        $this->assertDatabaseHas('holidays', ['id' => $holiday->id, 'name' => 'Renamed Holiday']);

        $this->actingAs($admin)->delete(route('holidays.destroy', $holiday))->assertRedirect(route('holidays.index'));
        $this->assertSoftDeleted('holidays', ['id' => $holiday->id]);

        $this->actingAs($admin)->post(route('holidays.restore', $holiday->id))->assertRedirect(route('holidays.index'));
        $this->assertDatabaseHas('holidays', ['id' => $holiday->id, 'deleted_at' => null]);
    }
}
