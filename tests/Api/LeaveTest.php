<?php

declare(strict_types=1);

namespace Tests\Api;

use App\Enums\LeaveStatus;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * The mobile app's self-service leave endpoints.
 *
 * The recurring assertion is containment: every one of these returns or
 * acts on the caller's own leave and nobody else's, whatever the request
 * says.
 */
class LeaveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        config()->set('sfa.attendance.weekend_days', ['Friday', 'Saturday']);
    }

    private function executive(): User
    {
        $user = User::factory()->create();
        $user->assignRole('Sales Executive');

        return $user;
    }

    private function monday(): Carbon
    {
        return Carbon::parse('2026-09-14');
    }

    public function test_the_endpoints_require_authentication(): void
    {
        $this->getJson('/api/v1/leave/types')->assertStatus(401);
        $this->getJson('/api/v1/leave/balance')->assertStatus(401);
        $this->getJson('/api/v1/leave/requests')->assertStatus(401);
    }

    public function test_it_lists_active_leave_types_only(): void
    {
        Sanctum::actingAs($this->executive());
        LeaveType::factory()->create(['name' => 'Casual Leave']);
        LeaveType::factory()->inactive()->create(['name' => 'Retired Leave']);

        $this->getJson('/api/v1/leave/types')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Casual Leave');
    }

    public function test_the_balance_derives_used_days_from_approved_requests(): void
    {
        $executive = $this->executive();
        Sanctum::actingAs($executive);

        $type = LeaveType::factory()->create(['annual_quota' => 10]);
        LeaveBalance::factory()->create([
            'user_id' => $executive->id,
            'leave_type_id' => $type->id,
            'year' => $this->monday()->year,
            'entitled_days' => 10,
        ]);

        // Approved counts; pending does not.
        LeaveRequest::factory()->for($executive)->for($type)->approved()
            ->onDate($this->monday())->create();
        LeaveRequest::factory()->for($executive)->for($type)
            ->onDate($this->monday()->addWeek())->create();

        $this->getJson('/api/v1/leave/balance?year='.$this->monday()->year)
            ->assertOk()
            ->assertJsonPath('data.0.entitled_days', 10)
            ->assertJsonPath('data.0.used_days', 1)
            ->assertJsonPath('data.0.remaining_days', 9);
    }

    public function test_the_balance_falls_back_to_the_types_quota_when_no_row_exists(): void
    {
        // Nobody has set this person up yet; the type's own quota is the
        // honest default rather than zero.
        Sanctum::actingAs($this->executive());
        LeaveType::factory()->create(['annual_quota' => 12]);

        $this->getJson('/api/v1/leave/balance')
            ->assertOk()
            ->assertJsonPath('data.0.entitled_days', 12)
            ->assertJsonPath('data.0.remaining_days', 12);
    }

    public function test_an_executive_submits_leave_from_the_app(): void
    {
        $executive = $this->executive();
        Sanctum::actingAs($executive);
        $type = LeaveType::factory()->create();

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDay()->toDateString(),
            'reason' => 'Family wedding.',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.status', 'pending')
            // Working days, not calendar days - the app must not recompute.
            ->assertJsonPath('data.days', 2);

        $this->assertDatabaseHas('leave_requests', ['user_id' => $executive->id]);
    }

    public function test_a_half_day_submits_as_half_a_day(): void
    {
        Sanctum::actingAs($this->executive());
        $type = LeaveType::factory()->create();

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->toDateString(),
            'is_half_day' => true,
            'reason' => 'Doctor.',
        ])
            ->assertStatus(201)
            ->assertJsonPath('data.days', 0.5);
    }

    public function test_a_range_of_only_weekends_is_rejected(): void
    {
        Sanctum::actingAs($this->executive());
        $type = LeaveType::factory()->create();
        $friday = Carbon::parse('2026-09-18');

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $type->id,
            'from_date' => $friday->toDateString(),
            'to_date' => $friday->copy()->addDay()->toDateString(),
            'reason' => 'Weekend.',
        ])->assertStatus(422);
    }

    public function test_overlapping_leave_is_rejected(): void
    {
        $executive = $this->executive();
        Sanctum::actingAs($executive);
        $type = LeaveType::factory()->create();

        LeaveRequest::factory()->for($executive)->for($type)->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDays(2)->toDateString(),
        ]);

        $this->postJson('/api/v1/leave/requests', [
            'leave_type_id' => $type->id,
            'from_date' => $this->monday()->addDay()->toDateString(),
            'to_date' => $this->monday()->addDays(3)->toDateString(),
            'reason' => 'Overlaps.',
        ])->assertStatus(422);
    }

    public function test_the_list_returns_only_the_callers_own_leave(): void
    {
        $executive = $this->executive();
        Sanctum::actingAs($executive);

        LeaveRequest::factory()->for($executive)->create();
        LeaveRequest::factory()->for($this->executive())->create();

        $response = $this->getJson('/api/v1/leave/requests')->assertOk();

        $this->assertCount(1, $response->json('data'));
    }

    public function test_a_colleagues_request_cannot_be_read(): void
    {
        Sanctum::actingAs($this->executive());
        $theirs = LeaveRequest::factory()->for($this->executive())->create();

        $this->getJson('/api/v1/leave/requests/'.$theirs->id)->assertStatus(403);
    }

    public function test_an_executive_withdraws_their_own_pending_request(): void
    {
        $executive = $this->executive();
        Sanctum::actingAs($executive);
        $request = LeaveRequest::factory()->for($executive)->create();

        $this->deleteJson('/api/v1/leave/requests/'.$request->id)
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame(LeaveStatus::Cancelled, $request->fresh()->status);
    }

    public function test_a_colleagues_request_cannot_be_withdrawn(): void
    {
        Sanctum::actingAs($this->executive());
        $theirs = LeaveRequest::factory()->for($this->executive())->create();

        $this->deleteJson('/api/v1/leave/requests/'.$theirs->id)->assertStatus(403);

        $this->assertSame(LeaveStatus::Pending, $theirs->fresh()->status);
    }

    public function test_approved_leave_already_under_way_cannot_be_withdrawn(): void
    {
        $executive = $this->executive();
        Sanctum::actingAs($executive);

        $request = LeaveRequest::factory()->for($executive)->approved()->create([
            'from_date' => Carbon::now()->subWeek()->toDateString(),
            'to_date' => Carbon::now()->subWeek()->addDay()->toDateString(),
        ]);

        $this->deleteJson('/api/v1/leave/requests/'.$request->id)->assertStatus(422);
    }

    public function test_there_is_no_approve_endpoint_on_the_mobile_api(): void
    {
        // Deciding someone else's leave stays a web-admin action; an
        // approve route here would put it one permission mistake away from
        // every phone in the field.
        Sanctum::actingAs($this->executive());
        $request = LeaveRequest::factory()->create();

        $this->patchJson('/api/v1/leave/requests/'.$request->id.'/approve')->assertStatus(404);
        $this->postJson('/api/v1/leave/requests/'.$request->id.'/approve')->assertStatus(404);
    }
}
