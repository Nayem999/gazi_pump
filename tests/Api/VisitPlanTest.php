<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Dealer;
use App\Models\User;
use App\Models\VisitPlan;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class VisitPlanTest extends TestCase
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
        $this->postJson('/api/v1/visit-plans', [])->assertStatus(401);
    }

    public function test_sales_executive_can_plan_a_visit(): void
    {
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/visit-plans', [
                'dealer_id' => $dealer->id,
                'planned_date' => Carbon::tomorrow()->toDateString(),
                'notes' => 'Follow up on last order.',
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);

        $this->assertDatabaseHas('visit_plans', [
            'user_id' => $executive->id,
            'dealer_id' => $dealer->id,
            'status' => 'planned',
        ]);
    }

    public function test_planned_date_is_required(): void
    {
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/visit-plans', ['dealer_id' => $dealer->id])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_index_only_returns_the_authenticated_users_own_plans(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        VisitPlan::factory()->create(['user_id' => $executive->id]);
        VisitPlan::factory()->create(['user_id' => $otherExecutive->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/visit-plans')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
