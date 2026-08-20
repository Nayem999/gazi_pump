<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttendanceTest extends TestCase
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
        $this->postJson('/api/v1/attendance/check-in', [])->assertStatus(401);
    }

    public function test_sales_executive_can_check_in_with_gps_and_photo(): void
    {
        Storage::fake('public');
        $executive = $this->executive();

        $response = $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/attendance/check-in', [
                'lat' => 23.8103,
                'lng' => 90.4125,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
            ]);

        $response->assertStatus(201)->assertJsonPath('success', true);

        $attendance = Attendance::where('user_id', $executive->id)->firstOrFail();
        $this->assertNotNull($attendance->check_in_at);
        Storage::disk('public')->assertExists($attendance->check_in_photo);
    }

    public function test_cannot_check_in_twice_on_the_same_day(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $headers = ['Authorization' => 'Bearer '.$this->tokenFor($executive)];

        $this->withHeaders($headers)->postJson('/api/v1/attendance/check-in', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertStatus(201);

        $this->withHeaders($headers)->postJson('/api/v1/attendance/check-in', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'photo' => UploadedFile::fake()->image('selfie2.jpg'),
        ])->assertStatus(422)->assertJsonPath('success', false);
    }

    public function test_sales_executive_can_check_out_after_checking_in(): void
    {
        Storage::fake('public');
        $executive = $this->executive();
        $headers = ['Authorization' => 'Bearer '.$this->tokenFor($executive)];

        $this->withHeaders($headers)->postJson('/api/v1/attendance/check-in', [
            'lat' => 23.8103,
            'lng' => 90.4125,
            'photo' => UploadedFile::fake()->image('selfie.jpg'),
        ])->assertStatus(201);

        $response = $this->withHeaders($headers)->postJson('/api/v1/attendance/check-out', [
            'lat' => 23.8110,
            'lng' => 90.4130,
            'photo' => UploadedFile::fake()->image('selfie-out.jpg'),
        ]);

        $response->assertOk();

        $attendance = Attendance::where('user_id', $executive->id)->firstOrFail();
        $this->assertNotNull($attendance->check_out_at);
    }

    public function test_cannot_check_out_before_checking_in(): void
    {
        Storage::fake('public');
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->postJson('/api/v1/attendance/check-out', [
                'lat' => 23.8103,
                'lng' => 90.4125,
                'photo' => UploadedFile::fake()->image('selfie.jpg'),
            ])
            ->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_today_endpoint_returns_null_when_not_checked_in(): void
    {
        $executive = $this->executive();

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/attendance/today')
            ->assertOk()
            ->assertJsonPath('data', null);
    }

    public function test_history_only_returns_the_authenticated_users_own_records(): void
    {
        $executive = $this->executive();
        $otherExecutive = $this->executive();

        Attendance::factory()->create(['user_id' => $executive->id, 'date' => Carbon::yesterday()->toDateString()]);
        Attendance::factory()->create(['user_id' => $otherExecutive->id, 'date' => Carbon::yesterday()->toDateString()]);

        $this->withHeader('Authorization', 'Bearer '.$this->tokenFor($executive))
            ->getJson('/api/v1/attendance/history')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }
}
