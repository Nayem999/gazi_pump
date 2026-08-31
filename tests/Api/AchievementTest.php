<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\AchievementEntry;
use App\Models\Product;
use App\Models\SalesTeam;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AchievementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function tokenFor(User $user): string
    {
        return $user->createToken('phpunit')->plainTextToken;
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_store_requires_authentication(): void
    {
        $this->postJson('/api/v1/achievements', [])->assertStatus(401);
    }

    public function test_a_sales_executive_can_record_a_single_achievement_for_today(): void
    {
        $executive = $this->executive();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/achievements', [
                'mode' => 'single',
                'order_value_achieved' => 5000,
                'collection_achieved' => 2000,
                'quantity_achieved' => 20,
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true)->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('achievement_entries', [
            'user_id' => $executive->id,
            'entry_date' => Carbon::today()->toDateString(),
            'order_value_achieved' => 5000,
            'status' => 'pending',
        ]);
    }

    public function test_a_sales_executive_can_record_a_product_wise_achievement(): void
    {
        $executive = $this->executive();
        $product = Product::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/achievements', [
                'mode' => 'product_wise',
                'items' => [
                    ['product_id' => $product->id, 'order_achieved' => 1000, 'collection_achieved' => 400, 'quantity_achieved' => 10],
                ],
            ]);

        $response->assertStatus(201)->assertJsonPath('data.is_product_wise', true);
        $this->assertDatabaseHas('achievement_entries', ['user_id' => $executive->id, 'order_value_achieved' => 1000]);
        $this->assertDatabaseCount('achievement_items', 1);
    }

    public function test_submitting_again_the_same_day_updates_the_pending_entry_in_place(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/achievements', ['mode' => 'single', 'order_value_achieved' => 1000, 'collection_achieved' => 0, 'quantity_achieved' => 1]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/achievements', ['mode' => 'single', 'order_value_achieved' => 2000, 'collection_achieved' => 0, 'quantity_achieved' => 2])
            ->assertStatus(201);

        $this->assertDatabaseCount('achievement_entries', 1);
        $this->assertDatabaseHas('achievement_entries', ['user_id' => $executive->id, 'order_value_achieved' => 2000]);
    }

    public function test_an_approved_entry_can_no_longer_be_edited_via_the_mobile_api(): void
    {
        $executive = $this->executive();
        AchievementEntry::factory()->create([
            'user_id' => $executive->id,
            'entry_date' => Carbon::today()->toDateString(),
            'status' => 'approved',
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/achievements', ['mode' => 'single', 'order_value_achieved' => 999, 'collection_achieved' => 0, 'quantity_achieved' => 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors('entry_date');
    }

    public function test_current_returns_null_when_nothing_submitted_today(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/achievements/current')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_current_returns_todays_entry(): void
    {
        $executive = $this->executive();
        AchievementEntry::factory()->create([
            'user_id' => $executive->id,
            'entry_date' => Carbon::today()->toDateString(),
            'order_value_achieved' => 4200,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/achievements/current')
            ->assertOk()
            ->assertJsonPath('data.order_value_achieved', 4200);
    }

    public function test_index_only_returns_the_authenticated_users_own_history(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => Carbon::yesterday()->toDateString()]);
        AchievementEntry::factory()->create(['user_id' => $otherExecutive->id, 'entry_date' => Carbon::yesterday()->toDateString()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/achievements')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_index_can_be_filtered_by_a_date_range(): void
    {
        $executive = $this->executive();

        AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => '2026-08-10', 'order_value_achieved' => 111]);
        AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => '2026-08-20', 'order_value_achieved' => 222]);
        AchievementEntry::factory()->create(['user_id' => $executive->id, 'entry_date' => '2026-09-05', 'order_value_achieved' => 333]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/achievements?date_from=2026-08-15&date_to=2026-08-31');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.order_value_achieved', 222);
    }

    public function test_a_product_outside_the_executives_sales_team_is_rejected(): void
    {
        $teamA = SalesTeam::factory()->create();
        $teamB = SalesTeam::factory()->create();
        $executive = User::factory()->create(['sales_team_id' => $teamA->id]);
        $executive->assignRole('Sales Executive');
        $otherTeamProduct = Product::factory()->create(['sales_team_id' => $teamB->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/achievements', [
                'mode' => 'product_wise',
                'items' => [['product_id' => $otherTeamProduct->id, 'order_achieved' => 100, 'collection_achieved' => 0, 'quantity_achieved' => 1]],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('items.0.product_id');
    }
}
