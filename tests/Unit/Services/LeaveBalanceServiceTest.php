<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Services\LeaveBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Balances are entitlement stored, consumption derived.
 *
 * Most of what is asserted here is that the derivation cannot be fooled -
 * by pending requests, by another year, by another person, or by a
 * cancelled request - because a stored counter getting any of those wrong
 * is exactly the drift this design exists to avoid.
 */
class LeaveBalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): LeaveBalanceService
    {
        return app(LeaveBalanceService::class);
    }

    private function monday(): Carbon
    {
        return Carbon::parse('2026-09-14');
    }

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('sfa.attendance.weekend_days', ['Friday', 'Saturday']);
    }

    public function test_only_approved_requests_consume_a_balance(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        LeaveRequest::factory()->for($user)->for($type)->approved()->onDate($this->monday())->create();
        LeaveRequest::factory()->for($user)->for($type)->onDate($this->monday()->addWeek())->create();
        LeaveRequest::factory()->for($user)->for($type)->rejected()->onDate($this->monday()->addWeeks(2))->create();
        LeaveRequest::factory()->for($user)->for($type)->cancelled()->onDate($this->monday()->addWeeks(3))->create();

        $this->assertSame(1.0, $this->service()->usedDays($user, $type, $this->monday()->year));
    }

    public function test_carried_forward_days_add_to_the_entitlement(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        LeaveBalance::factory()->create([
            'user_id' => $user->id,
            'leave_type_id' => $type->id,
            'year' => $this->monday()->year,
            'entitled_days' => 10,
            'carried_forward_days' => 3,
        ]);

        $this->assertSame(13.0, $this->service()->remainingDays($user, $type, $this->monday()->year));
    }

    public function test_an_individual_entitlement_overrides_the_types_quota(): void
    {
        // Two people can hold different entitlements for the same type.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        LeaveBalance::factory()->create([
            'user_id' => $user->id,
            'leave_type_id' => $type->id,
            'year' => $this->monday()->year,
            'entitled_days' => 20,
        ]);

        $this->assertSame(20.0, $this->service()->remainingDays($user, $type, $this->monday()->year));
    }

    public function test_another_years_leave_does_not_count(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        LeaveRequest::factory()->for($user)->for($type)->approved()
            ->onDate($this->monday()->subYear())->create();

        $this->assertSame(0.0, $this->service()->usedDays($user, $type, $this->monday()->year));
    }

    public function test_another_persons_leave_does_not_count(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        LeaveRequest::factory()->for($type)->approved()->onDate($this->monday())->create();

        $this->assertSame(0.0, $this->service()->usedDays($user, $type, $this->monday()->year));
    }

    public function test_a_balance_can_go_negative_and_is_reported_as_such(): void
    {
        // A manager may approve beyond entitlement; hiding the overdraft
        // behind a max(0) would make it invisible.
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 1]);

        LeaveRequest::factory()->for($user)->for($type)->approved()->create([
            'from_date' => $this->monday()->toDateString(),
            'to_date' => $this->monday()->addDays(2)->toDateString(),
            'days' => 3,
        ]);

        $this->assertSame(-2.0, $this->service()->remainingDays($user, $type, $this->monday()->year));
    }

    public function test_setting_up_entitlements_seeds_from_each_types_quota(): void
    {
        $user = User::factory()->create();
        LeaveType::factory()->create(['annual_quota' => 10]);
        LeaveType::factory()->create(['annual_quota' => 5]);
        LeaveType::factory()->inactive()->create(['annual_quota' => 99]);

        $created = $this->service()->setUpFor($user, 2026);

        $this->assertSame(2, $created, 'inactive types are not set up');
        $this->assertDatabaseHas('leave_balances', ['user_id' => $user->id, 'entitled_days' => '10.0']);
    }

    public function test_setting_up_twice_does_not_overwrite_an_adjusted_entitlement(): void
    {
        $user = User::factory()->create();
        $type = LeaveType::factory()->create(['annual_quota' => 10]);

        $this->service()->setUpFor($user, 2026);
        LeaveBalance::query()->update(['entitled_days' => 25]);

        $created = $this->service()->setUpFor($user, 2026);

        $this->assertSame(0, $created);
        $this->assertDatabaseHas('leave_balances', ['user_id' => $user->id, 'entitled_days' => '25.0']);
    }

    public function test_the_per_user_view_lists_every_active_type(): void
    {
        $user = User::factory()->create();
        LeaveType::factory()->create(['name' => 'Casual Leave', 'annual_quota' => 10]);
        LeaveType::factory()->create(['name' => 'Sick Leave', 'annual_quota' => 7]);

        $rows = $this->service()->forUser($user, 2026);

        $this->assertCount(2, $rows);
        $this->assertEqualsCanonicalizing(
            ['Casual Leave', 'Sick Leave'],
            $rows->pluck('leave_type.name')->all(),
        );
    }
}
