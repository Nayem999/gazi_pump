<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Dealer;
use App\Models\Retailer;
use App\Models\Territory;
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

    public function test_sales_executive_can_register_a_new_retailer_under_a_dealer(): void
    {
        $executive = $this->executive();
        $dealer = Dealer::factory()->create();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/retailers', [
                'dealer_id' => $dealer->id,
                'name' => 'Corner Shop',
                'phone' => '01700000000',
            ]);

        $response->assertStatus(201)->assertJsonPath('data.name', 'Corner Shop');
        $this->assertDatabaseHas('retailers', ['name' => 'Corner Shop', 'dealer_id' => $dealer->id]);
    }

    public function test_registering_a_retailer_for_a_dealer_outside_the_executives_territory_is_rejected(): void
    {
        $territory = Territory::factory()->create();
        $executive = User::factory()->inTerritory($territory)->create();
        $executive->assignRole('Sales Executive');
        $outsideDealer = Dealer::factory()->create(['territory_id' => Territory::factory()->create()->id]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/retailers', [
                'dealer_id' => $outsideDealer->id,
                'name' => 'Corner Shop',
                'phone' => '01700000000',
            ])->assertJsonValidationErrors('dealer_id');

        $this->assertDatabaseMissing('retailers', ['name' => 'Corner Shop']);
    }
}
