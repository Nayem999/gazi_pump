<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsManagementTest extends TestCase
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

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('settings.edit'))->assertRedirect(route('login'));
    }

    public function test_general_manager_is_forbidden(): void
    {
        $this->actingAs($this->generalManager())->get(route('settings.edit'))->assertForbidden();
    }

    public function test_super_admin_can_view_the_settings_form(): void
    {
        $this->actingAs($this->superAdmin())->get(route('settings.edit'))
            ->assertOk()
            ->assertSee('Company Information');
    }

    public function test_super_admin_can_update_settings(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->validPayload(['company_name' => 'Updated Pump Co']);

        $this->actingAs($admin)->put(route('settings.update'), $payload)
            ->assertRedirect(route('settings.edit'));

        $this->assertSame('Updated Pump Co', Setting::current()->company_name);
    }

    public function test_grade_thresholds_must_be_in_descending_order(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->validPayload([
            'target_grade_a_min' => 50,
            'target_grade_b_min' => 75, // higher than A — invalid
        ]);

        $this->actingAs($admin)->put(route('settings.update'), $payload)
            ->assertSessionHasErrors('target_grade_a_min');
    }

    /**
     * Proves ApplySettingsToConfig actually reaches config() for a real
     * request — not just that the DB row was saved.
     */
    public function test_an_updated_setting_is_reflected_in_config_on_the_next_request(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->validPayload(['attendance_late_grace_minutes' => 42]);

        $this->actingAs($admin)->put(route('settings.update'), $payload);
        $this->actingAs($admin)->get(route('settings.edit'));

        $this->assertSame(42, config('sfa.attendance.late_grace_minutes'));
    }

    public function test_office_end_time_must_be_after_office_start_time(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->validPayload([
            'attendance_office_start_time' => '18:00',
            'attendance_office_end_time' => '09:00',
        ]);

        $this->actingAs($admin)->put(route('settings.update'), $payload)
            ->assertSessionHasErrors('attendance_office_end_time');
    }

    public function test_at_least_one_weekend_day_is_required(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->validPayload(['attendance_weekend_days' => []]);

        $this->actingAs($admin)->put(route('settings.update'), $payload)
            ->assertSessionHasErrors('attendance_weekend_days');
    }

    public function test_super_admin_can_update_the_weekend_days_and_it_persists(): void
    {
        $admin = $this->superAdmin();
        $payload = $this->validPayload(['attendance_weekend_days' => ['Friday']]);

        $this->actingAs($admin)->put(route('settings.update'), $payload)
            ->assertRedirect(route('settings.edit'));

        $this->assertSame(['Friday'], Setting::current()->attendance_weekend_days);
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'company_name' => 'Gazi Pump SFA',
            'attendance_office_start_time' => '09:00',
            'attendance_office_end_time' => '18:00',
            'attendance_late_grace_minutes' => 15,
            'attendance_weekend_days' => ['Friday', 'Saturday'],
            'visit_gps_radius_meters' => 300,
            'order_max_discount_percent' => 20,
            'collection_overpayment_tolerance_percent' => 10,
            'target_grade_a_min' => 90,
            'target_grade_b_min' => 75,
            'target_grade_c_min' => 60,
            'target_grade_d_min' => 40,
            'low_performance_grades' => ['D', 'F'],
            'target_reminder_days_before_month_end' => 5,
            'target_reminder_min_pct' => 70,
            'live_gps_stale_after_minutes' => 30,
        ], $overrides);
    }
}
