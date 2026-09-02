<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Actions\CalculateAchievementAction;
use App\Models\AchievementEntry;
use App\Models\Product;
use App\Models\Target;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TargetTest extends TestCase
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

    public function test_current_requires_authentication(): void
    {
        $this->getJson('/api/v1/targets/current')->assertStatus(401);
    }

    public function test_current_returns_null_when_no_target_assigned_for_this_month(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/targets/current')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_current_returns_the_targets_computed_achievement(): void
    {
        $executive = $this->executive();
        $today = Carbon::today();
        $target = Target::factory()->create([
            'user_id' => $executive->id,
            'month' => $today->month,
            'year' => $today->year,
            'order_value_target' => 1000,
        ]);
        AchievementEntry::factory()->approved()->create(['user_id' => $executive->id, 'entry_date' => $today->toDateString(), 'order_value_achieved' => 500]);

        app(CalculateAchievementAction::class)($target);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/targets/current');

        $response->assertOk()->assertJsonPath('data.achievement.order_pct', 50);
    }

    public function test_current_indicates_a_single_target_is_not_product_wise(): void
    {
        $executive = $this->executive();
        $today = Carbon::today();
        Target::factory()->create([
            'user_id' => $executive->id,
            'month' => $today->month,
            'year' => $today->year,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/targets/current')
            ->assertOk()
            ->assertJsonPath('data.is_product_wise', false)
            ->assertJsonPath('data.items', []);
    }

    public function test_current_indicates_a_product_wise_target_and_lists_its_items(): void
    {
        $executive = $this->executive();
        $today = Carbon::today();
        $product = Product::factory()->create();
        $target = Target::factory()->create([
            'user_id' => $executive->id,
            'month' => $today->month,
            'year' => $today->year,
        ]);
        $target->items()->create([
            'product_id' => $product->id,
            'order_target' => 1000,
            'collection_target' => 500,
            'quantity_target' => 10,
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/targets/current');

        $response->assertOk()
            ->assertJsonPath('data.is_product_wise', true)
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.product.id', $product->id)
            ->assertJsonPath('data.items.0.order_target', 1000);
    }

    public function test_index_only_returns_the_authenticated_users_own_targets(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        Target::factory()->create(['user_id' => $executive->id]);
        Target::factory()->create(['user_id' => $otherExecutive->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/targets')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_there_is_no_store_endpoint_for_targets(): void
    {
        $executive = $this->executive();

        // The route exists for GET (index) but not POST — 405, not 404.
        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/targets', ['month' => 8, 'year' => 2026])
            ->assertStatus(405);
    }
}
