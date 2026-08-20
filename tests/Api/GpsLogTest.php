<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\GpsLog;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class GpsLogTest extends TestCase
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
        $this->postJson('/api/v1/gps-logs', ['logs' => []])->assertStatus(401);
    }

    public function test_sales_executive_can_ingest_a_single_ping(): void
    {
        $executive = $this->executive();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/gps-logs', [
                'logs' => [
                    ['lat' => 23.8103, 'lng' => 90.4125, 'recorded_at' => now()->toIso8601String(), 'battery_level' => 80],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.ingested', 1);

        $this->assertDatabaseHas('gps_logs', ['user_id' => $executive->id, 'battery_level' => 80]);
    }

    public function test_sales_executive_can_ingest_a_batch_of_pings(): void
    {
        $executive = $this->executive();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/gps-logs', [
                'logs' => [
                    ['lat' => 23.8103, 'lng' => 90.4125, 'recorded_at' => now()->subMinutes(20)->toIso8601String()],
                    ['lat' => 23.8110, 'lng' => 90.4130, 'recorded_at' => now()->subMinutes(10)->toIso8601String()],
                    ['lat' => 23.8115, 'lng' => 90.4140, 'recorded_at' => now()->toIso8601String()],
                ],
            ]);

        $response->assertStatus(201)->assertJsonPath('data.ingested', 3);
        $this->assertSame(3, GpsLog::where('user_id', $executive->id)->count());
    }

    public function test_ingest_validates_required_fields(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/gps-logs', [
                'logs' => [
                    ['lat' => 200, 'lng' => 90.4125, 'recorded_at' => now()->toIso8601String()],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_history_only_returns_the_authenticated_users_own_pings(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();
        $today = Carbon::today()->toDateString();

        GpsLog::factory()->count(3)->create(['user_id' => $executive->id, 'recorded_at' => now()]);
        GpsLog::factory()->count(2)->create(['user_id' => $otherExecutive->id, 'recorded_at' => now()]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/gps-logs/history');

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.date', $today);
    }

    public function test_history_includes_a_distance_km_figure(): void
    {
        $executive = $this->executive();

        GpsLog::factory()->create(['user_id' => $executive->id, 'lat' => 23.8103, 'lng' => 90.4125, 'recorded_at' => now()->subMinutes(10)]);
        GpsLog::factory()->create(['user_id' => $executive->id, 'lat' => 23.8200, 'lng' => 90.4200, 'recorded_at' => now()]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/gps-logs/history');

        $response->assertOk();
        $this->assertGreaterThan(0, $response->json('meta.distance_km'));
    }
}
