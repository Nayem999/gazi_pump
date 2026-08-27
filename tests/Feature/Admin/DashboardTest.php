<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

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
}
