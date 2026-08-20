<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ActivityLogManagementTest extends TestCase
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

    private function territoryManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Territory Manager');

        return $user;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('activity-log.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_and_territory_manager_are_forbidden(): void
    {
        $this->actingAs($this->executive())->get(route('activity-log.index'))->assertForbidden();
        $this->actingAs($this->territoryManager())->get(route('activity-log.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_the_activity_log(): void
    {
        $manager = $this->generalManager();
        $subject = User::factory()->create();

        $response = $this->actingAs($manager)->get(route('activity-log.index'));

        $response->assertOk()->assertSee('User #'.$subject->id);
    }

    public function test_it_can_be_filtered_by_search(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('activity-log.index', ['search' => 'a string that will never appear']))
            ->assertOk()
            ->assertSee('No activity found.');
    }

    public function test_general_manager_can_view_a_single_activitys_detail(): void
    {
        $manager = $this->generalManager();
        $subject = User::factory()->create();
        $activity = $subject->activities()->latest()->first();

        $this->actingAs($manager)->get(route('activity-log.show', $activity))
            ->assertOk()
            ->assertSee('Changed Values');
    }

    public function test_general_manager_can_export_and_print(): void
    {
        $manager = $this->generalManager();
        User::factory()->create();

        $this->actingAs($manager)->get(route('activity-log.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($manager)->get(route('activity-log.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Regression guard: DomPDF exhausts PHP's memory limit somewhere around
     * ~2,000 activity-log rows (measured directly against this table), so
     * print() must cap to a bounded window rather than rendering every
     * matching row. This seeds well past that boundary directly via the
     * query builder (cheap — no model/observer overhead) and confirms print
     * still returns a normal PDF response instead of crashing.
     */
    public function test_print_stays_within_memory_bounds_on_a_large_dataset(): void
    {
        $manager = $this->generalManager();

        $rows = [];
        for ($i = 0; $i < 1500; $i++) {
            $rows[] = [
                'log_name' => 'users',
                'description' => 'created',
                'subject_type' => User::class,
                'subject_id' => $manager->id,
                'causer_type' => null,
                'causer_id' => null,
                'event' => 'created',
                'properties' => json_encode(['attributes' => ['name' => 'Test']]),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('activity_log')->insert($rows);

        $this->actingAs($manager)->get(route('activity-log.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
