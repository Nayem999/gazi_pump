<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    public function test_ping_endpoint_is_reachable(): void
    {
        $this->getJson('/api/v1/ping')
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    public function test_api_routes_carry_a_rate_limit(): void
    {
        // Module 19 hardening: before this, api/v1/* had no throttle at all
        // beyond /auth/login's own flat limit. Presence of these headers is
        // proof the 'api' RateLimiter is actually applied, without needing
        // to fire 120+ requests to trigger a real 429.
        $this->getJson('/api/v1/ping')->assertHeader('X-RateLimit-Limit');
    }

    public function test_user_can_login_and_receive_a_token(): void
    {
        $user = User::factory()->create(['email' => 'mobile@gazipump.com', 'password' => bcrypt('secret123')]);
        $user->assignRole('Sales Executive');

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@gazipump.com',
            'password' => 'secret123',
            'device_name' => 'phpunit-test',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        User::factory()->create(['email' => 'mobile@gazipump.com', 'password' => bcrypt('secret123')]);

        $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@gazipump.com',
            'password' => 'wrong',
            'device_name' => 'phpunit-test',
        ])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_login_is_locked_out_after_five_failed_attempts(): void
    {
        User::factory()->create(['email' => 'mobile@gazipump.com', 'password' => bcrypt('secret123')]);

        $attempt = fn () => $this->postJson('/api/v1/auth/login', [
            'email' => 'mobile@gazipump.com',
            'password' => 'wrong',
            'device_name' => 'phpunit-test',
        ]);

        for ($i = 0; $i < 5; $i++) {
            $attempt()->assertStatus(422);
        }

        $response = $attempt()->assertStatus(422);

        $this->assertStringContainsString(
            'Too many login attempts.',
            $response->json('errors.email.0'),
        );
    }

    public function test_me_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401)->assertJsonPath('success', false);
    }

    public function test_me_endpoint_returns_authenticated_user(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');
        $token = $user->createToken('phpunit')->plainTextToken;

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');
        $newToken = $user->createToken('phpunit');

        $this->withHeader('Authorization', "Bearer {$newToken->plainTextToken}")
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Asserted against the database directly: Laravel's auth guard caches
        // the resolved user for the lifetime of the test's container, so a
        // second live request here would still authenticate against that
        // cached instance instead of re-checking the (now deleted) token.
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $newToken->accessToken->id]);
    }
}
