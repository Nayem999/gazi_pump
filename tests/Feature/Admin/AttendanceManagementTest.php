<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('attendance.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_has_no_web_access_to_attendance(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($executive)->get(route('attendance.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_record_attendance(): void
    {
        $manager = $this->generalManager();
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');

        $this->actingAs($manager)->get(route('attendance.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('attendance.store'), [
            'user_id' => $executive->id,
            'date' => Carbon::yesterday()->toDateString(),
            'status' => 'absent',
            'remarks' => 'Approved sick leave.',
        ]);

        $response->assertRedirect(route('attendance.index'));
        $this->assertDatabaseHas('attendances', ['user_id' => $executive->id, 'status' => 'absent']);
    }

    public function test_cannot_record_two_attendance_rows_for_the_same_employee_and_date(): void
    {
        $manager = $this->generalManager();
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $date = Carbon::yesterday()->toDateString();

        Attendance::factory()->create(['user_id' => $executive->id, 'date' => $date]);

        $response = $this->actingAs($manager)->post(route('attendance.store'), [
            'user_id' => $executive->id,
            'date' => $date,
            'status' => 'present',
        ]);

        $response->assertSessionHasErrors('date');
    }

    public function test_general_manager_can_update_an_attendance_record(): void
    {
        $manager = $this->generalManager();
        $attendance = Attendance::factory()->create();

        $this->actingAs($manager)->put(route('attendance.update', $attendance), [
            'date' => $attendance->date->toDateString(),
            'status' => 'half_day',
            'remarks' => 'Left early for a medical appointment.',
        ])->assertRedirect(route('attendance.index'));

        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'status' => 'half_day']);
    }

    public function test_general_manager_cannot_delete_an_attendance_record(): void
    {
        $manager = $this->generalManager();
        $attendance = Attendance::factory()->create();

        $this->actingAs($manager)->delete(route('attendance.destroy', $attendance))->assertForbidden();
    }

    public function test_super_admin_can_delete_and_restore_an_attendance_record(): void
    {
        $admin = $this->superAdmin();
        $attendance = Attendance::factory()->create();

        $this->actingAs($admin)->delete(route('attendance.destroy', $attendance))
            ->assertRedirect(route('attendance.index'));
        $this->assertSoftDeleted('attendances', ['id' => $attendance->id]);

        $this->actingAs($admin)->post(route('attendance.restore', $attendance->id))
            ->assertRedirect(route('attendance.index'));
        $this->assertDatabaseHas('attendances', ['id' => $attendance->id, 'deleted_at' => null]);
    }

    public function test_territory_manager_can_view_but_not_edit_attendance(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $attendance = Attendance::factory()->create();

        $this->actingAs($manager)->get(route('attendance.index'))->assertOk();

        $this->actingAs($manager)->put(route('attendance.update', $attendance), [
            'date' => $attendance->date->toDateString(),
            'status' => 'present',
        ])->assertForbidden();
    }
}
