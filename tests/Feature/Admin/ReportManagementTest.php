<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Attendance;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\GpsLog;
use App\Models\Order;
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
        $this->actingAs($executive)->get(route('reports.order-performance'))->assertOk();
        $this->actingAs($executive)->get(route('reports.collection-summary'))->assertOk();
        $this->actingAs($executive)->get(route('reports.target-achievement'))->assertOk();
        $this->actingAs($executive)->get(route('reports.gps-report'))->assertOk();
        $this->actingAs($executive)->get(route('reports.movement-summary'))->assertOk();

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

    public function test_sales_executives_order_performance_report_is_scoped_to_their_own_orders(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();
        Order::factory()->create(['user_id' => $executive->id, 'order_date' => now()->toDateString(), 'total_amount' => 12345]);
        Order::factory()->create(['user_id' => $otherExecutive->id, 'order_date' => now()->toDateString(), 'total_amount' => 54321]);

        $response = $this->actingAs($executive)->get(route('reports.order-performance'));

        $response->assertOk()->assertSee('12,345.00')->assertDontSee('54,321.00');
    }

    public function test_a_territory_managers_order_performance_report_is_scoped_to_their_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $executiveA = User::factory()->inTerritory($territoryA)->create();
        $executiveB = User::factory()->inTerritory($territoryB)->create();
        Order::factory()->create(['user_id' => $executiveA->id, 'order_date' => now()->toDateString(), 'total_amount' => 11111]);
        Order::factory()->create(['user_id' => $executiveB->id, 'order_date' => now()->toDateString(), 'total_amount' => 22222]);

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        $response = $this->actingAs($manager)->get(route('reports.order-performance'));

        $response->assertOk()->assertSee('11,111.00')->assertDontSee('22,222.00');
    }

    public function test_a_territory_manager_cannot_widen_a_report_by_requesting_another_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $executiveB = User::factory()->inTerritory($territoryB)->create();
        Order::factory()->create(['user_id' => $executiveB->id, 'order_date' => now()->toDateString(), 'total_amount' => 99999]);

        $manager = $this->territoryManager();
        $manager->territories()->attach($territoryA);

        // Explicitly requesting territoryB's id doesn't widen access — it
        // just intersects with the manager's own (empty) result.
        $response = $this->actingAs($manager)->get(route('reports.order-performance', ['territory_id' => $territoryB->id]));

        $response->assertOk()->assertDontSee('99,999.00');
    }

    public function test_general_manager_can_view_every_report(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.index'))->assertOk();
        $this->actingAs($manager)->get(route('reports.attendance-summary'))->assertOk();
        $this->actingAs($manager)->get(route('reports.visit-compliance'))->assertOk();
        $this->actingAs($manager)->get(route('reports.order-performance'))->assertOk();
        $this->actingAs($manager)->get(route('reports.collection-summary'))->assertOk();
        $this->actingAs($manager)->get(route('reports.territory-performance'))->assertOk();
    }

    public function test_territory_manager_can_also_view_reports(): void
    {
        $manager = $this->territoryManager();

        $this->actingAs($manager)->get(route('reports.order-performance'))->assertOk();
    }

    public function test_order_performance_report_reflects_real_data(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        Order::factory()->create(['user_id' => $executive->id, 'order_date' => now()->toDateString(), 'total_amount' => 5000]);

        $response = $this->actingAs($manager)->get(route('reports.order-performance'));

        $response->assertOk()->assertSee($executive->name)->assertSee('5,000.00');
    }

    public function test_order_performance_report_can_be_filtered_by_approval_status(): void
    {
        $manager = $this->generalManager();
        $approvedExecutive = $this->executive();
        $pendingExecutive = $this->executive();
        Order::factory()->create(['user_id' => $approvedExecutive->id, 'order_date' => now()->toDateString(), 'total_amount' => 55555, 'status' => 'approved']);
        Order::factory()->create(['user_id' => $pendingExecutive->id, 'order_date' => now()->toDateString(), 'total_amount' => 33333, 'status' => 'pending']);

        $response = $this->actingAs($manager)->get(route('reports.order-performance', ['status' => 'approved']));

        // Both executives always appear in the filter dropdown regardless of
        // report data, so assert on the aggregated totals (scoped to the
        // filtered status) rather than the executives' names.
        $response->assertOk()->assertSee('55,555.00')->assertDontSee('33,333.00');
    }

    public function test_collection_summary_report_can_be_filtered_by_approval_status(): void
    {
        $manager = $this->generalManager();
        $approvedExecutive = $this->executive();
        $pendingExecutive = $this->executive();
        CollectionEntry::factory()->create(['user_id' => $approvedExecutive->id, 'collection_date' => now()->toDateString(), 'amount' => 11111, 'status' => 'approved']);
        CollectionEntry::factory()->create(['user_id' => $pendingExecutive->id, 'collection_date' => now()->toDateString(), 'amount' => 77777, 'status' => 'pending']);

        $response = $this->actingAs($manager)->get(route('reports.collection-summary', ['status' => 'approved']));

        $response->assertOk()->assertSee('11,111.00')->assertDontSee('77,777.00');
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

        $this->actingAs($manager)->get(route('reports.order-performance.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_reports_can_be_printed_as_pdf(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('reports.order-performance.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_sales_executive_has_no_access_to_the_dealer_ledger_report(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('reports.dealer-ledger'))->assertForbidden();
    }

    public function test_dealer_ledger_summary_shows_every_dealer_with_its_due_amount(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create(['name' => 'Sunrise Traders']);

        Order::factory()->create(['user_id' => $executive->id, 'dealer_id' => $dealer->id, 'total_amount' => 10000]);
        CollectionEntry::factory()->create(['user_id' => $executive->id, 'dealer_id' => $dealer->id, 'amount' => 4000, 'payment_method' => 'cash']);

        $response = $this->actingAs($manager)->get(route('reports.dealer-ledger'));

        $response->assertOk()->assertSee('Sunrise Traders')->assertSee('6,000.00');
    }

    public function test_dealer_ledger_detail_shows_a_running_balance(): void
    {
        $manager = $this->generalManager();
        $executive = $this->executive();
        $dealer = Dealer::factory()->create(['name' => 'Sunrise Traders']);

        Order::factory()->create([
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'order_date' => now()->subDays(5)->toDateString(),
            'total_amount' => 10000,
        ]);
        CollectionEntry::factory()->create([
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'collection_date' => now()->subDays(2)->toDateString(),
            'amount' => 4000,
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($manager)->get(route('reports.dealer-ledger.show', $dealer));

        $response->assertOk()->assertSee('10,000.00')->assertSee('4,000.00')->assertSee('6,000.00');
    }

    public function test_dealer_ledger_report_can_be_exported_and_printed(): void
    {
        $manager = $this->generalManager();
        $dealer = Dealer::factory()->create();

        $this->actingAs($manager)->get(route('reports.dealer-ledger.export'))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->actingAs($manager)->get(route('reports.dealer-ledger.print'))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');

        $this->actingAs($manager)->get(route('reports.dealer-ledger.show-print', $dealer))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }
}
