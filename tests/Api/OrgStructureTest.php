<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\SalesTeam;
use App\Models\Territory;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgStructureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    private function authHeader(): array
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');
        $token = $user->createToken('phpunit')->plainTextToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_org_lookups_require_authentication(): void
    {
        $this->getJson('/api/v1/sales-teams')->assertStatus(401);
        $this->getJson('/api/v1/territories')->assertStatus(401);
    }

    public function test_authenticated_user_can_list_sales_teams(): void
    {
        SalesTeam::factory()->count(2)->create(['status' => true]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/sales-teams')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_authenticated_user_can_list_territories(): void
    {
        Territory::factory()->count(3)->create(['status' => true]);

        $this->withHeaders($this->authHeader())
            ->getJson('/api/v1/territories')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }
}
