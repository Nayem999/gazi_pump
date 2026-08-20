<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Customer;
use App\Models\User;
use App\Models\Visit;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VisitTest extends TestCase
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

    public function test_check_in_requires_authentication(): void
    {
        $this->postJson('/api/v1/visits/check-in', [])->assertStatus(401);
    }

    public function test_sales_executive_can_check_in_at_the_customers_location_and_is_gps_verified(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $customer = Customer::factory()->create(['gps_lat' => 23.8103, 'gps_lng' => 90.4125]);

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/visits/check-in', [
                'customer_id' => $customer->id,
                'lat' => 23.8103,
                'lng' => 90.4125,
                'photo' => UploadedFile::fake()->image('storefront.jpg'),
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);

        $visit = Visit::where('user_id', $executive->id)->firstOrFail();
        $this->assertTrue((bool) $visit->is_gps_verified);
        Storage::disk('public')->assertExists($visit->check_in_photo);
    }

    public function test_check_in_far_from_the_customer_is_flagged_unverified(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $customer = Customer::factory()->create(['gps_lat' => 23.8103, 'gps_lng' => 90.4125]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/visits/check-in', [
                'customer_id' => $customer->id,
                'lat' => 23.9000,
                'lng' => 90.5000,
                'photo' => UploadedFile::fake()->image('storefront.jpg'),
            ])->assertStatus(201);

        $visit = Visit::where('user_id', $executive->id)->firstOrFail();
        $this->assertFalse((bool) $visit->is_gps_verified);
    }

    public function test_check_in_against_a_customer_with_no_gps_pin_is_unknown_not_unverified(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $customer = Customer::factory()->create(['gps_lat' => null, 'gps_lng' => null]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/visits/check-in', [
                'customer_id' => $customer->id,
                'lat' => 23.8103,
                'lng' => 90.4125,
                'photo' => UploadedFile::fake()->image('storefront.jpg'),
            ])->assertStatus(201);

        $visit = Visit::where('user_id', $executive->id)->firstOrFail();
        $this->assertNull($visit->is_gps_verified);
    }

    public function test_cannot_check_in_twice_without_checking_out_first(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $headers = ['Authorization' => 'Bearer '.$this->tokenFor($executive)];

        $this->withHeaders($headers)->postJson('/api/v1/visits/check-in', [
            'customer_id' => $customer->id,
            'lat' => 23.8103,
            'lng' => 90.4125,
            'photo' => UploadedFile::fake()->image('storefront.jpg'),
        ])->assertStatus(201);

        $this->withHeaders($headers)->postJson('/api/v1/visits/check-in', [
            'customer_id' => $customer->id,
            'lat' => 23.8103,
            'lng' => 90.4125,
            'photo' => UploadedFile::fake()->image('storefront2.jpg'),
        ])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_sales_executive_can_check_out_after_checking_in(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $customer = Customer::factory()->create();
        $headers = ['Authorization' => 'Bearer '.$this->tokenFor($executive)];

        $this->withHeaders($headers)->postJson('/api/v1/visits/check-in', [
            'customer_id' => $customer->id,
            'lat' => 23.8103,
            'lng' => 90.4125,
            'photo' => UploadedFile::fake()->image('storefront.jpg'),
        ])->assertStatus(201);

        $response = $this->withHeaders($headers)->postJson('/api/v1/visits/check-out', [
            'lat' => 23.8110,
            'lng' => 90.4130,
            'feedback' => 'Placed a repeat order.',
        ]);

        $response->assertOk();

        $visit = Visit::where('user_id', $executive->id)->firstOrFail();
        $this->assertNotNull($visit->check_out_at);
        $this->assertSame('Placed a repeat order.', $visit->feedback);
    }

    public function test_cannot_check_out_before_checking_in(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/visits/check-out', [
                'lat' => 23.8103,
                'lng' => 90.4125,
                'feedback' => 'No visit in progress.',
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_current_endpoint_returns_null_when_no_open_visit(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/visits/current')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_history_only_returns_the_authenticated_users_own_visits(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        Visit::factory()->create(['user_id' => $executive->id, 'check_in_at' => Carbon::yesterday()]);
        Visit::factory()->create(['user_id' => $otherExecutive->id, 'check_in_at' => Carbon::yesterday()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/visits/history')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
