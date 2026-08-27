<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Dealer;
use App\Models\Retailer;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetailerTest extends TestCase
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

    public function test_retailer_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/retailers')->assertStatus(401);
    }

    public function test_sales_executive_can_list_retailers(): void
    {
        $executive = $this->executive();
        Retailer::factory()->count(3)->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/retailers')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(3, 'data');
    }

    public function test_the_list_can_be_filtered_to_a_single_dealer(): void
    {
        $executive = $this->executive();
        $dealerA = Dealer::factory()->create();
        $dealerB = Dealer::factory()->create();
        Retailer::factory()->count(2)->create(['dealer_id' => $dealerA->id]);
        Retailer::factory()->create(['dealer_id' => $dealerB->id]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/retailers?dealer_id={$dealerA->id}");

        $response->assertOk()->assertJsonCount(2, 'data');
        $this->assertSame($dealerA->id, $response->json('data.0.dealer.id'));
    }

    public function test_inactive_retailers_are_excluded(): void
    {
        $executive = $this->executive();
        Retailer::factory()->create(['status' => true]);
        Retailer::factory()->create(['status' => false]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/retailers')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_sales_executive_can_get_a_single_retailer(): void
    {
        $executive = $this->executive();
        $retailer = Retailer::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson("/api/v1/retailers/{$retailer->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $retailer->id)
            ->assertJsonPath('data.dealer.id', $retailer->dealer_id);
    }
}
