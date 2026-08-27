<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Target;
use App\Models\User;
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

    public function test_sales_executive_has_no_access_to_any_report(): void
    {
        $executive = $this->executive();

        $this->actingAs($executive)->get(route('reports.attendance-summary'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.visit-compliance'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.order-performance'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.collection-summary'))->assertForbidden();
        $this->actingAs($executive)->get(route('reports.territory-performance'))->assertForbidden();
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
