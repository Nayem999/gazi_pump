<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AchievementEntry;
use App\Models\Dealer;
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

    public function test_achievement_trend_chart_is_company_wide_for_a_user_with_no_territories(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');

        AchievementEntry::factory()->approved()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 1234.56]);
        AchievementEntry::factory()->approved()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 8765.44]);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // A manager with no territories assigned sees the company-wide sum
        // across every executive, not just one territory's.
        $response->assertOk()->assertDontSee('Your territories only')->assertSee('10000');
    }

    public function test_achievement_trend_chart_breaks_each_bar_down_by_approval_status(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');

        AchievementEntry::factory()->approved()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 1234.56]);
        AchievementEntry::factory()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 9999, 'status' => 'pending']);
        AchievementEntry::factory()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 8888, 'status' => 'rejected']);

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

    public function test_achievement_trend_chart_is_scoped_to_the_users_own_territories(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $executiveA = User::factory()->create();
        $executiveA->territories()->attach($territoryA);
        $executiveB = User::factory()->create();
        $executiveB->territories()->attach($territoryB);

        $manager = User::factory()->create();
        $manager->assignRole('Territory Manager');
        $manager->territories()->attach($territoryA);

        AchievementEntry::factory()->approved()->create(['user_id' => $executiveA->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 1234.56]);
        AchievementEntry::factory()->approved()->create(['user_id' => $executiveB->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 8765.44]);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // Only territoryA's executive (1234.56) counts — the company-wide
        // combined total (10000, which includes territoryB) must not leak.
        $response->assertOk()->assertSee('Your territories only')->assertSee('1234.56')->assertDontSee('10000');
    }

    public function test_todays_order_and_collection_achieved_cards_show_only_todays_approved_amounts(): void
    {
        $manager = User::factory()->create();
        $manager->assignRole('General Manager');

        AchievementEntry::factory()->approved()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 5000, 'collection_achieved' => 0]);
        AchievementEntry::factory()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 9000, 'collection_achieved' => 0, 'status' => 'pending']);
        AchievementEntry::factory()->approved()->create(['entry_date' => now()->subDay()->toDateString(), 'order_value_achieved' => 7000, 'collection_achieved' => 0]);

        AchievementEntry::factory()->approved()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 0, 'collection_achieved' => 3000]);
        AchievementEntry::factory()->create(['entry_date' => now()->toDateString(), 'order_value_achieved' => 0, 'collection_achieved' => 4000, 'status' => 'pending']);

        $response = $this->actingAs($manager)->get(route('dashboard'));

        // Only today's approved order achievement (5000) counts toward
        // Today's Order Achieved — today's pending entry and yesterday's
        // approved entry are excluded. Only today's approved collection
        // (3000) counts toward Today's Collection Achieved — today's
        // pending collection is excluded.
        $response->assertOk()
            ->assertSee("Today's Order Achieved")
            ->assertSee('5,000.00')
            ->assertDontSee('9,000.00')
            ->assertDontSee('7,000.00')
            ->assertSee("Today's Collection Achieved")
            ->assertSee('3,000.00')
            ->assertDontSee('4,000.00');
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

    public function test_a_sales_executive_only_sees_their_own_achievement_on_the_dashboard(): void
    {
        $executive = User::factory()->create();
        $executive->assignRole('Sales Executive');
        $otherExecutive = User::factory()->create();
        $otherExecutive->assignRole('Sales Executive');

        AchievementEntry::factory()->approved()->create(['user_id' => $executive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 1500]);
        AchievementEntry::factory()->approved()->create(['user_id' => $otherExecutive->id, 'entry_date' => now()->toDateString(), 'order_value_achieved' => 9500]);

        $response = $this->actingAs($executive)->get(route('dashboard'));

        $response->assertOk()->assertSee('1,500.00')->assertDontSee('9,500.00');
    }
}
