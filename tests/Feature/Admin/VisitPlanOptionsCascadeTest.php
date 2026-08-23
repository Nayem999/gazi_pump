<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Dealer;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the two options-endpoint filters that back the Visit Plan create
 * form's guided flow: Executive -> (their) Territories -> (that
 * territory's) Dealers, auto-adding every one of them.
 */
class VisitPlanOptionsCascadeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function superAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        return $user;
    }

    public function test_territories_options_filtered_by_user_id_returns_only_that_users_assigned_territories(): void
    {
        $admin = $this->superAdmin();
        $executive = User::factory()->create();
        $assigned = Territory::factory()->create(['name' => 'Assigned Territory']);
        $unassigned = Territory::factory()->create(['name' => 'Unassigned Territory']);
        $executive->territories()->attach($assigned);

        $response = $this->actingAs($admin)
            ->getJson(route('territories.options', ['user_id' => $executive->id]))
            ->assertOk();

        $ids = collect($response->json())->pluck('id');
        $this->assertTrue($ids->contains($assigned->id));
        $this->assertFalse($ids->contains($unassigned->id));
    }

    public function test_dealers_options_filtered_by_territory_id_returns_every_dealer_in_that_territory_uncapped(): void
    {
        $admin = $this->superAdmin();
        $territory = Territory::factory()->create();
        $otherTerritory = Territory::factory()->create();

        $inTerritory = Dealer::factory()->count(5)->create(['territory_id' => $territory->id]);
        Dealer::factory()->create(['territory_id' => $otherTerritory->id]);

        $response = $this->actingAs($admin)
            ->getJson(route('dealers.options', ['territory_id' => $territory->id]))
            ->assertOk();

        $ids = collect($response->json())->pluck('id');
        $this->assertSame($inTerritory->pluck('id')->sort()->values()->all(), $ids->sort()->values()->all());
    }

    public function test_dealers_options_filtered_by_territory_id_excludes_inactive_dealers(): void
    {
        $admin = $this->superAdmin();
        $territory = Territory::factory()->create();
        $active = Dealer::factory()->create(['territory_id' => $territory->id, 'status' => true]);
        Dealer::factory()->create(['territory_id' => $territory->id, 'status' => false]);

        $response = $this->actingAs($admin)
            ->getJson(route('dealers.options', ['territory_id' => $territory->id]))
            ->assertOk();

        $ids = collect($response->json())->pluck('id');
        $this->assertSame([$active->id], $ids->all());
    }
}
