<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AchievementEntry;
use App\Models\Attendance;
use App\Models\Dealer;
use App\Models\GpsLog;
use App\Models\Target;
use App\Models\Territory;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportManagementTest extends TestCase
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

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_sales_executive_can_view_only_the_reports_with_a_per_executive_projection(): void
    {
        $executive = $this->executive();

        // Reports with a meaningful "just mine" view are granted...
        $this->actingAs($executive)->get(route('reports.attendance-summary'))->assertOk();
        $this->actingAs($executive)->get(route('reports.visit-compliance'))->assertOk();
        $this->actingAs($executive)->get(route('reports.achievement-summary'))->assertOk();
        $this->actingAs($executive)->get(route('reports.target-achievement'))->assertOk();
        $this->actingAs($executive)->get(route('reports.gps-report'))->assertOk();
        $this->actingAs($executive)->get(route('reports.movement-summary'))->assertOk();
        $this->actingAs($executive)->get(route('achievements.index'))->assertOk();

        // ...cross-executive aggregate/comparison reports are not.
        $this->actingAs($executive)->get(route('reports.territory-performance'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.executive-performance'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.dealer-coverage'))->assertForbidden();
    }

    public function test_movement_summary_computes_working_hours_active_idle_visits_and_location_from_the_days_data(): void
    {
        $today = now()->startOfDay();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create(['name' => 'Tangail Hardware']);

        Attendance::factory()->create([
            'user_id' => $executive->id,
            'date' => $today->toDateString(),
            'check_in_at' => $today->copy()->setTime(9, 0),
            'check_out_at' => $today->copy()->setTime(17, 0),
        ]);

        // Idle for 30 min (2nd ping reports 0 speed), then active for 30 min
        // (3rd ping reports 20 km/h) — 30m idle, 30m active.
        GpsLog::factory()->create(['user_id' => $executive->id, 'recorded_at' => $today->copy()->setTime(9, 0), 'speed' => 20]);
        GpsLog::factory()->create(['user_id' => $executive->id, 'recorded_at' => $today->copy()->setTime(9, 30), 'speed' => 0]);
        GpsLog::factory()->create(['user_id' => $executive->id, 'recorded_at' => $today->copy()->setTime(10, 0), 'speed' => 20]);

        Visit::factory()->create([
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'check_in_at' => $today->copy()->setTime(9, 15),
            'check_out_at' => $today->copy()->setTime(9, 45),
        ]);

        $response = $this->actingAs($executive)->get(route('reports.movement-summary', ['date' => $today->toDateString()]));

        $response->assertOk()
            ->assertSee('8h 0m') // working hours
            ->assertSee('Tangail Hardware'); // first/last location (only visit of the day)

        // Active Movement and Idle Time both independently compute the same
        // 30-minute duration — appears at least twice, not just once.
        $this->assertGreaterThanOrEqual(2, substr_count($response->getContent(), '0h 30m'));
    }

    public function test_a_plain_sales_executive_can_only_see_their_own_movement_summary(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        $response = $this->actingAs($executive)->get(route('reports.movement-summary', ['user_id' => $otherExecutive->id]));

        // The Executive filter is ignored entirely for a plain Sales
        // Executive — it's always their own day, never another's.
        $response->assertOk()->assertSee($executive->name)->assertDontSee($otherExecutive->name);
    }

    public function test_movement_summary_can_be_printed_as_pdf(): void
    {
        $manager = $this->generalManager();
        User::factory()->create()->assignRole('Sales Executive');

        $this->actingAs($manager)->get(route('reports.movement-summary.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_territory_managers_movement_summary_is_limited_to_their_own_territorys_executives(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $inTerritory = User::factory()->inTerritory($territoryA)->create();
        $inTerritory->assignRole('Sales Executive');
        $outsideTerritory = User::factory()->inTerritory($territoryB)->create();
        $outsideTerritory->assignRole('Sales Executive');

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        // The dropdown only offers their own territory's executive...
        $response = $this->actingAs($manager)->get(route('reports.movement-summary'));
        $response->assertOk()->assertSee($inTerritory->name)->assertDontSee($outsideTerritory->name);

        // ...and requesting the other one directly doesn't work either.
        $response = $this->actingAs($manager)->get(route('reports.movement-summary', ['user_id' => $outsideTerritory->id]));
        $response->assertOk()->assertDontSee($outsideTerritory->name);
    }

    public function test_sales_executives_achievement_summary_report_is_scoped_to_their_own_entries(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();
        AchievementEntry::factory()->approved()->create(['user_id' => $executive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 12345]);
        AchievementEntry::factory()->approved()->create(['user_id' => $otherExecutive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 54321]);

        $response = $this->actingAs($executive)->get(route('reports.achievement-summary'));

        $response->assertOk()->assertSee('12,345.00')->assertDontSee('54,321.00');
    }

    public function test_a_territory_managers_achievement_summary_report_is_scoped_to_their_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $executiveA = User::factory()->inTerritory($territoryA)->create();
        $executiveB = User::factory()->inTerritory($territoryB)->create();
        AchievementEntry::factory()->approved()->create(['user_id' => $executiveA->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 11111]);
        AchievementEntry::factory()->approved()->create(['user_id' => $executiveB->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 22222]);

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        $response = $this->actingAs($manager)->get(route('reports.achievement-summary'));

        $response->assertOk()->assertSee('11,111.00')->assertDontSee('22,222.00');
    }

    public function test_a_territory_manager_cannot_widen_a_report_by_requesting_another_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $executiveB = User::factory()->inTerritory($territoryB)->create();
        AchievementEntry::factory()->approved()->create(['user_id' => $executiveB->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 99999]);

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        // Explicitly requesting territoryB's id doesn't widen access — it
        // just intersects with the manager's own (empty) result.
        $response = $this->actingAs($manager)->get(route('reports.achievement-summary', ['territory_id' => $territoryB->id]));

        $response->assertOk()->assertDontSee('99,999.00');
    }

    public function test_general_manager_can_view_every_report(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.index'))->assertOk();
        $this->actingAs($manager)->get(route('reports.attendance-summary'))->assertOk();
        $this->actingAs($manager)->get(route('reports.visit-compliance'))->assertOk();
        $this->actingAs($manager)->get(route('reports.achievement-summary'))->assertOk();
        $this->actingAs($manager)->get(route('reports.territory-performance'))->assertOk();
    }

    public function test_territory_manager_can_also_view_reports(): void
    {
        $manager = $this->territoryManager();

        $this->actingAs($manager)->get(route('reports.achievement-summary'))->assertOk();
    }

    public function test_achievement_summary_report_reflects_real_data(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        AchievementEntry::factory()->approved()->create(['user_id' => $executive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 5000]);

        $response = $this->actingAs($manager)->get(route('reports.achievement-summary'));

        $response->assertOk()->assertSee($executive->name)->assertSee('5,000.00');
    }

    public function test_achievement_summary_report_only_counts_approved_entries(): void
    {
        $manager = $this->generalManager();
        $approvedExecutive = $this->executive();
        $pendingExecutive = $this->executive();
        AchievementEntry::factory()->approved()->create(['user_id' => $approvedExecutive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 55555]);
        AchievementEntry::factory()->create(['user_id' => $pendingExecutive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 33333, 'status' => 'pending']);

        $response = $this->actingAs($manager)->get(route('reports.achievement-summary'));

        $response->assertOk()->assertSee('55,555.00')->assertDontSee('33,333.00');
    }

    public function test_target_achievement_report_links_to_the_targets_product_wise_breakdown(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => now()->month, 'year' => now()->year]);

        $response = $this->actingAs($manager)->get(route('reports.target-achievement'));

        $response->assertOk()->assertSee(route('targets.show', $target));
    }

    public function test_reports_can_be_exported_to_excel(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.achievement-summary.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_reports_can_be_printed_as_pdf(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.achievement-summary.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    /**
     * Order Performance, Collection Summary, and Dealer Ledger are retired
     * (see the "version 1" Achievement pivot) — their `report.*` permission
     * is no longer assigned to any role but Super Admin, so every one of
     * these routes now 403s for everyone else, regardless of the role that
     * used to have full access.
     */
    public function test_retired_reports_403_for_every_role_except_super_admin(): void
    {
        $dealer = Dealer::factory()->create();

        foreach ([$this->generalManager(), $this->territoryManager(), $this->executive()] as $viewer) {
            $this->actingAs($viewer)->get(route('reports.order-performance'))->assertForbidden();
            $this->actingAs($viewer)->get(route('reports.collection-summary'))->assertForbidden();
            $this->actingAs($viewer)->get(route('reports.dealer-ledger'))->assertForbidden();
            $this->actingAs($viewer)->get(route('reports.dealer-ledger.show', $dealer))->assertForbidden();
        }

        $admin = $this->superAdmin();
        $this->actingAs($admin)->get(route('reports.order-performance'))->assertOk();
        $this->actingAs($admin)->get(route('reports.collection-summary'))->assertOk();
        $this->actingAs($admin)->get(route('reports.dealer-ledger'))->assertOk();
    }
}
