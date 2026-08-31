<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_order_vs_collection_chart_is_company_wide_for_a_user_with_no_territories(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');

        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        Order::factory()->create(['dealer_id' => $dealerA->id, 'order_date' => now()->toDateString(), 'total_amount' => 1234.56, 'status' => 'approved']);
        Order::factory()->create(['dealer_id' => $dealerB->id, 'order_date' => now()->toDateString(), 'total_amount' => 8765.44, 'status' => 'approved']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // A manager with no territories assigned sees the company-wide sum
        // across every dealer, not just one territory's.
        $response->assertOk()->assertDontSee('Your territories only')->assertSee('10000');
    }

    public function test_order_vs_collection_chart_breaks_each_bar_down_by_approval_status(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');
        $dealer = Dealer::factory()->create();

        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 1234.56, 'status' => 'approved']);
        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 9999, 'status' => 'pending']);
        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 8888, 'status' => 'rejected']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // All three statuses appear as their own slice of the bar — none
        // are dropped, and they aren't merged into a single combined total
        // (which would read 20121.56 and never appear anywhere on the page).
        $response->assertOk()
            ->assertSee('1234.56')
            ->assertSee('9999')
            ->assertSee('8888')
            ->assertDontSee('20121.56');
    }

    public function test_order_vs_collection_chart_is_scoped_to_the_users_own_territories(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $dealerA = Dealer::factory()->create(['territory_id' => $territoryA->id]);
        $dealerB = Dealer::factory()->create(['territory_id' => $territoryB->id]);

        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $manager->territories()->attach($territoryA);

        Order::factory()->create(['dealer_id' => $dealerA->id, 'order_date' => now()->toDateString(), 'total_amount' => 1234.56, 'status' => 'approved']);
        Order::factory()->create(['dealer_id' => $dealerB->id, 'order_date' => now()->toDateString(), 'total_amount' => 8765.44, 'status' => 'approved']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // Only territoryA's dealer (1234.56) counts — the company-wide
        // combined total (10000, which includes territoryB) must not leak.
        $response->assertOk()->assertSee('Your territories only')->assertSee('1234.56')->assertDontSee('10000');
    }

    public function test_todays_sales_and_collection_cards_show_only_todays_approved_amounts(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');
        $dealer = Dealer::factory()->create();

        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 5000, 'status' => 'approved']);
        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 9000, 'status' => 'pending']);
        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => now()->subDay()->toDateString(), 'total_amount' => 7000, 'status' => 'approved']);

        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 3000, 'status' => 'approved']);
        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 4000, 'status' => 'pending']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // Only today's approved order (5000) counts toward Today's Sales —
        // today's pending order and yesterday's approved order are excluded.
        // Only today's approved collection (3000) counts toward Today's
        // Collection — today's pending collection is excluded.
        $response->assertOk()
            ->assertSee("Today's Sales")
            ->assertSee('5,000.00')
            ->assertDontSee('9,000.00')
            ->assertDontSee('7,000.00')
            ->assertSee("Today's Collection")
            ->assertSee('3,000.00')
            ->assertDontSee('4,000.00');
    }

    public function test_payment_mode_summary_breaks_todays_approved_collections_down_by_mode(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');
        $dealer = Dealer::factory()->create();

        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 750, 'status' => 'approved', 'payment_method' => 'cash']);
        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 250, 'status' => 'approved', 'payment_method' => 'bank_transfer']);
        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'amount' => 999, 'status' => 'pending', 'payment_method' => 'cheque']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // The pending cheque collection (999) is excluded entirely, so the
        // approved cash/bank-transfer split is 750/250 out of 1000 — a
        // clean 75%/25%, not diluted by the pending entry.
        $response->assertOk()
            ->assertSee('Payment Mode Summary')
            ->assertSee('Cash')
            ->assertSee('75.00%')
            ->assertSee('Bank Transfer')
            ->assertSee('25.00%')
            ->assertDontSee('999.00');
    }

    public function test_the_dealer_and_territory_counts_are_scoped_to_a_territory_managers_own_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        Dealer::factory()->count(2)->create(['territory_id' => $territoryA->id, 'status' => true]);
        Dealer::factory()->create(['territory_id' => $territoryB->id, 'status' => true]);

        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $manager->territories()->attach($territoryA);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // 2 dealers (territoryA only) and 1 territory (their own), not the
        // company-wide totals of 3 dealers / 2 territories.
        $response->assertOk();
        $this->assertStringContainsString('data-countup="2"', $response->getContent());
        $this->assertStringContainsString('data-countup="1"', $response->getContent());
    }

    public function test_a_sales_executive_only_sees_their_own_sales_and_collection_on_the_dashboard(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $otherExecutive = User::factory()->create();
        $otherExecutive->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();

        Order::factory()->create(['user_id' => $executive->id, 'dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 1500, 'status' => 'approved']);
        Order::factory()->create(['user_id' => $otherExecutive->id, 'dealer_id' => $dealer->id, 'order_date' => now()->toDateString(), 'total_amount' => 9500, 'status' => 'approved']);

        $response = $this->actingAs($executive)->get(route('dashboard'));

        $response->assertOk()->assertSee('1,500.00')->assertDontSee('9,500.00');
    }
}
