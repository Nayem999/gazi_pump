<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\TallyConnection;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TallyConnectionManagementTest extends TestCase
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

    private function generalManager(): User
    {
        $user = User::factory()->create();
        $user->assignRole('General Manager');

        return $user;
    }

    private function salesExecutive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('tally-connections.index'))->assertRedirect(route('login'));
    }

    public function test_a_sales_executive_has_no_access_to_tally_integration(): void
    {
        $executive = $this->salesExecutive();

        $this->actingAs($executive)->get(route('tally-integration.dashboard'))->assertForbidden();
        $this->actingAs($executive)->get(route('tally-connections.index'))->assertForbidden();
    }

    public function test_general_manager_can_view_and_create_a_connection(): void
    {
        $manager = $this->generalManager();

        $this->actingAs($manager)->get(route('tally-connections.index'))->assertOk();

        $response = $this->actingAs($manager)->post(route('tally-connections.store'), [
            'connection_name' => 'GDN Tally (Primary)',
            'tally_company_name' => 'GDN tally',
            'host' => 'localhost',
            'port' => 9000,
            'protocol' => 'http',
            'api_format' => 'xml',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('tally-connections.index'));
        $this->assertDatabaseHas('tally_connections', ['connection_name' => 'GDN Tally (Primary)', 'host' => 'localhost']);

        $connection = TallyConnection::firstOrFail();
        $this->assertNotNull($connection->sync_agent_id);
        $this->assertNotNull($connection->sync_agent_token, 'the hashed credential should be stored');
        $response->assertSessionHas('plain_agent_token');
    }

    public function test_the_plain_agent_token_is_never_exposed_in_a_json_serialization(): void
    {
        $connection = TallyConnection::factory()->create();

        $this->assertArrayNotHasKey('sync_agent_token', $connection->toArray());
    }

    public function test_general_manager_can_update_a_connection(): void
    {
        $manager = $this->generalManager();
        $connection = TallyConnection::factory()->create(['connection_name' => 'Old Name']);

        $this->actingAs($manager)->put(route('tally-connections.update', $connection), [
            'connection_name' => 'New Name',
            'tally_company_name' => $connection->tally_company_name,
            'host' => $connection->host,
            'port' => $connection->port,
            'protocol' => $connection->protocol,
            'api_format' => $connection->api_format->value,
            'is_active' => '1',
        ])->assertRedirect(route('tally-connections.index'));

        $this->assertDatabaseHas('tally_connections', ['id' => $connection->id, 'connection_name' => 'New Name']);
    }

    public function test_toggle_status_flips_is_active(): void
    {
        $manager = $this->generalManager();
        $connection = TallyConnection::factory()->create(['is_active' => true]);

        $this->actingAs($manager)->patch(route('tally-connections.toggle-status', $connection))->assertRedirect();

        $this->assertFalse($connection->fresh()->is_active);
    }

    public function test_test_connection_records_a_heartbeat_on_success(): void
    {
        Http::fake([
            'localhost:9000' => Http::response('<ENVELOPE><HEADER><STATUS>1</STATUS></HEADER></ENVELOPE>', 200),
        ]);

        $manager = $this->generalManager();
        $connection = TallyConnection::factory()->create(['host' => 'localhost', 'port' => 9000, 'last_heartbeat_at' => null]);

        $this->actingAs($manager)->post(route('tally-connections.test-connection', $connection))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertNotNull($connection->fresh()->last_heartbeat_at);
    }

    public function test_test_connection_reports_failure_without_touching_the_heartbeat(): void
    {
        Http::fake(['localhost:9000' => Http::response('', 500)]);

        $manager = $this->generalManager();
        $connection = TallyConnection::factory()->create(['host' => 'localhost', 'port' => 9000, 'last_heartbeat_at' => null]);

        $this->actingAs($manager)->post(route('tally-connections.test-connection', $connection))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNull($connection->fresh()->last_heartbeat_at);
    }

    public function test_regenerate_token_issues_a_new_credential_and_invalidates_the_old_hash(): void
    {
        $manager = $this->generalManager();
        $connection = TallyConnection::factory()->create();
        $oldHash = $connection->sync_agent_token;

        $this->actingAs($manager)->post(route('tally-connections.regenerate-token', $connection))
            ->assertRedirect()
            ->assertSessionHas('plain_agent_token');

        $this->assertNotSame($oldHash, $connection->fresh()->sync_agent_token);
    }

    public function test_super_admin_can_delete_and_restore_a_connection(): void
    {
        $admin = $this->superAdmin();
        $connection = TallyConnection::factory()->create();

        $this->actingAs($admin)->delete(route('tally-connections.destroy', $connection))
            ->assertRedirect(route('tally-connections.index'));
        $this->assertSoftDeleted('tally_connections', ['id' => $connection->id]);

        $this->actingAs($admin)->post(route('tally-connections.restore', $connection->id))
            ->assertRedirect(route('tally-connections.index'));
        $this->assertDatabaseHas('tally_connections', ['id' => $connection->id, 'deleted_at' => null]);
    }
}
