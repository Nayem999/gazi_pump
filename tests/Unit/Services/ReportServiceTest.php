<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AttendanceStatus;
use App\Enums\VisitPlanStatus;
use App\Models\AchievementEntry;
use App\Models\Attendance;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Territory;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPlan;
use App\Services\ReportService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // territoryPerformance() calls User::role('Sales Executive'), which
        // requires the role to exist.
        $this->seed(RolePermissionSeeder::class);
    }

    private function service(): ReportService
    {
        return app(ReportService::class);
    }

    public function test_date_range_defaults_to_the_current_month(): void
    {
        $range = $this->service()->dateRange([]);

        $this->assertTrue($range['from']->isSameDay(Carbon::now()->startOfMonth()));
        $this->assertTrue($range['to']->isSameDay(Carbon::now()->endOfMonth()));
    }

    public function test_date_range_uses_explicit_filters_when_given(): void
    {
        $range = $this->service()->dateRange(['date_from' => '2026-01-01', 'date_to' => '2026-01-31']);

        $this->assertSame('2026-01-01', $range['from']->toDateString());
        $this->assertSame('2026-01-31', $range['to']->toDateString());
    }

    public function test_approved_leave_is_reported_but_never_counted_against_the_rate(): void
    {
        // The whole point of the Leave module's attendance integration:
        // time off a manager approved must not read as poor attendance.
        $user = User::factory()->create();
        $date = Carbon::create(2026, 8, 10);

        Attendance::factory()->create(['user_id' => $user->id, 'date' => $date->toDateString(), 'status' => AttendanceStatus::Present, 'late_minutes' => 0]);
        Attendance::factory()->create(['user_id' => $user->id, 'date' => $date->copy()->addDay()->toDateString(), 'status' => AttendanceStatus::Leave, 'late_minutes' => 0]);
        Attendance::factory()->create(['user_id' => $user->id, 'date' => $date->copy()->addDays(2)->toDateString(), 'status' => AttendanceStatus::Leave, 'late_minutes' => 0]);

        $rows = $this->service()->attendanceSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(2, $row->leave_count);
        $this->assertSame(3, $row->total_days, 'the leave days are still reported');
        // Leave comes out of BOTH sides: one working day, and they were
        // present for it, so 100% rather than 33%.
        $this->assertSame(1, $row->judged_days);
        $this->assertSame(100.0, $row->attendance_rate);
    }

    public function test_a_period_that_is_entirely_leave_has_no_rate_rather_than_zero(): void
    {
        // There were no working days to judge, so a rate of 0% would be a
        // statement the data does not support.
        $user = User::factory()->create();

        Attendance::factory()->create([
            'user_id' => $user->id,
            'date' => Carbon::create(2026, 8, 10)->toDateString(),
            'status' => AttendanceStatus::Leave,
            'late_minutes' => 0,
        ]);

        $row = $this->service()
            ->attendanceSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31'])
            ->firstWhere('user.id', $user->id);

        $this->assertSame(0, $row->judged_days);
        $this->assertSame(0.0, $row->attendance_rate, 'the view renders a dash when judged_days is 0');
    }

    public function test_attendance_summary_counts_each_status_and_computes_the_rate(): void
    {
        $user = User::factory()->create();
        $date = Carbon::create(2026, 8, 10);

        Attendance::factory()->create(['user_id' => $user->id, 'date' => $date->toDateString(), 'status' => AttendanceStatus::Present, 'late_minutes' => 0]);
        Attendance::factory()->create(['user_id' => $user->id, 'date' => $date->copy()->addDay()->toDateString(), 'status' => AttendanceStatus::Late, 'late_minutes' => 20]);
        Attendance::factory()->create(['user_id' => $user->id, 'date' => $date->copy()->addDays(2)->toDateString(), 'status' => AttendanceStatus::Absent, 'late_minutes' => 0]);

        $rows = $this->service()->attendanceSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(1, $row->present_count);
        $this->assertSame(1, $row->late_count);
        $this->assertSame(1, $row->absent_count);
        $this->assertSame(20, $row->total_late_minutes);
        $this->assertSame(0, $row->leave_count);
        $this->assertSame(3, $row->total_days);
        // No leave here, so every day is judged: 2 of 3 count toward the
        // rate (present + late, not absent).
        $this->assertSame(3, $row->judged_days);
        $this->assertSame(66.7, $row->attendance_rate);
    }

    public function test_attendance_summary_excludes_days_outside_the_range(): void
    {
        $user = User::factory()->create();
        Attendance::factory()->create(['user_id' => $user->id, 'date' => '2026-07-15', 'status' => AttendanceStatus::Present]);

        $rows = $this->service()->attendanceSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);

        $this->assertNull($rows->firstWhere('user.id', $user->id));
    }

    public function test_attendance_summary_filters_by_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $userA = User::factory()->inTerritory($territoryA)->create();
        $userB = User::factory()->inTerritory($territoryB)->create();

        Attendance::factory()->create(['user_id' => $userA->id, 'date' => '2026-08-05', 'status' => AttendanceStatus::Present]);
        Attendance::factory()->create(['user_id' => $userB->id, 'date' => '2026-08-05', 'status' => AttendanceStatus::Present]);

        $rows = $this->service()->attendanceSummary([
            'date_from' => '2026-08-01',
            'date_to' => '2026-08-31',
            'territory_id' => $territoryA->id,
        ]);

        $this->assertCount(1, $rows);
        $this->assertSame($userA->id, $rows->first()->user->id);
    }

    public function test_visit_compliance_computes_completion_and_gps_verified_rates(): void
    {
        $user = User::factory()->create();
        $dealer = Dealer::factory()->create();

        VisitPlan::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'planned_date' => '2026-08-05', 'status' => VisitPlanStatus::Completed]);
        VisitPlan::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'planned_date' => '2026-08-06', 'status' => VisitPlanStatus::Completed]);
        VisitPlan::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'planned_date' => '2026-08-07', 'status' => VisitPlanStatus::Cancelled]);

        Visit::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'check_in_at' => Carbon::create(2026, 8, 5, 10), 'is_gps_verified' => true]);
        Visit::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'check_in_at' => Carbon::create(2026, 8, 6, 10), 'is_gps_verified' => false]);

        $rows = $this->service()->visitCompliance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(3, $row->planned_count);
        $this->assertSame(2, $row->completed_count);
        $this->assertSame(66.7, $row->completion_rate);
        $this->assertSame(2, $row->total_visits);
        $this->assertSame(1, $row->gps_verified_count);
        $this->assertSame(50.0, $row->gps_verified_rate);
    }

    public function test_order_performance_sums_value_and_quantity_across_line_items(): void
    {
        $user = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $entry = Order::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'order_date' => '2026-08-10', 'total_amount' => 1500]);
        $entry->items()->create(['product_id' => Product::factory()->create()->id, 'quantity' => 5, 'unit_price' => 200, 'discount_amount' => 0, 'total_amount' => 1000]);
        $entry->items()->create(['product_id' => Product::factory()->create()->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $rows = $this->service()->orderPerformance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(1, $row->order_count);
        $this->assertSame(10, $row->total_quantity);
        $this->assertSame(1500.0, $row->total_order_value);
    }

    public function test_collection_summary_breaks_down_by_payment_method(): void
    {
        $user = User::factory()->create();
        $dealer = Dealer::factory()->create();

        CollectionEntry::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'collection_date' => '2026-08-10', 'amount' => 100, 'payment_method' => 'cash']);
        CollectionEntry::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'collection_date' => '2026-08-11', 'amount' => 200, 'payment_method' => 'cheque']);

        $rows = $this->service()->collectionSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(2, $row->collections_count);
        $this->assertSame(300.0, $row->total_amount);
        $this->assertSame(100.0, $row->cash_total);
        $this->assertSame(200.0, $row->cheque_total);
        $this->assertSame(0.0, $row->bank_transfer_total);
    }

    public function test_sales_return_summary_breaks_down_by_status_and_credits_only_received_amounts(): void
    {
        $user = User::factory()->create();
        $dealer = Dealer::factory()->create();

        $received = \App\Models\SalesReturn::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'status' => 'received', 'created_at' => '2026-08-10']);
        \App\Models\SalesReturnItem::factory()->create(['sales_return_id' => $received->id, 'requested_qty' => 5, 'received_qty' => 3, 'unit_price' => 100]);

        \App\Models\SalesReturn::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'status' => 'requested', 'created_at' => '2026-08-12']);
        \App\Models\SalesReturn::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'status' => 'rejected', 'created_at' => '2026-08-13']);

        $rows = $this->service()->salesReturnSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(3, $row->returns_count);
        $this->assertSame(1, $row->pending_count);
        $this->assertSame(1, $row->rejected_count);
        $this->assertSame(1, $row->received_count);
        // Credited on received_qty (3 * 100 = 300), not requested_qty (5 * 100 = 500).
        $this->assertSame(300.0, $row->total_credited);
    }

    public function test_dealer_ledger_falls_back_to_the_order_collection_estimate_when_never_synced(): void
    {
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => '2026-08-01', 'total_amount' => 1000]);
        CollectionEntry::factory()->create(['dealer_id' => $dealer->id, 'collection_date' => '2026-08-05', 'amount' => 400]);

        $rows = $this->service()->dealerLedger($dealer);

        $this->assertCount(2, $rows);
        $this->assertSame(600.0, $rows->last()->balance);
    }

    public function test_dealer_ledger_prefers_real_tally_ledger_entries_once_any_exist(): void
    {
        $dealer = Dealer::factory()->create();
        Order::factory()->create(['dealer_id' => $dealer->id, 'order_date' => '2026-08-01', 'total_amount' => 1000]);

        \App\Models\LedgerEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'voucher_date' => '2026-08-02',
            'voucher_type' => 'Sales',
            'voucher_number' => 'SV-1',
            'debit_amount' => 1500,
            'credit_amount' => 0,
        ]);
        \App\Models\LedgerEntry::factory()->create([
            'dealer_id' => $dealer->id,
            'voucher_date' => '2026-08-03',
            'voucher_type' => 'Receipt',
            'voucher_number' => 'RC-1',
            'debit_amount' => 0,
            'credit_amount' => 500,
        ]);

        $rows = $this->service()->dealerLedger($dealer);

        // Only the real ledger rows — the Order factory row above is
        // ignored once any real Tally ledger entry exists for this dealer.
        $this->assertCount(2, $rows);
        $this->assertSame(1000.0, $rows->last()->balance);
    }

    public function test_territory_performance_aggregates_orders_collections_and_visits_per_territory(): void
    {
        $territory = Territory::factory()->create();
        $user = User::factory()->inTerritory($territory)->create();
        $user->assignRole('Sales Executive');
        $dealer = Dealer::factory()->create();

        AchievementEntry::factory()->approved()->create(['user_id' => $user->id, 'entry_date' => '2026-08-05', 'order_value_achieved' => 1000, 'collection_achieved' => 400]);
        Visit::factory()->create(['user_id' => $user->id, 'dealer_id' => $dealer->id, 'check_in_at' => Carbon::create(2026, 8, 5, 10), 'is_gps_verified' => true]);

        $rows = $this->service()->territoryPerformance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31', 'territory_id' => $territory->id]);
        $row = $rows->first();

        $this->assertSame($territory->id, $row->territory->id);
        $this->assertSame(1, $row->executive_count);
        $this->assertSame(1000.0, $row->total_order_value);
        $this->assertSame(400.0, $row->total_collection_amount);
        $this->assertSame(1, $row->total_visits);
        $this->assertSame(100.0, $row->gps_verified_rate);
    }

    public function test_territory_performance_counts_a_multi_territory_executive_in_every_assigned_territory(): void
    {
        $territoryA = Territory::factory()->create();
        $territoryB = Territory::factory()->create();
        $user = User::factory()->create();
        $user->territories()->attach([$territoryA->id, $territoryB->id]);
        $user->assignRole('Sales Executive');

        AchievementEntry::factory()->approved()->create(['user_id' => $user->id, 'entry_date' => '2026-08-05', 'order_value_achieved' => 1000]);

        $rows = $this->service()->territoryPerformance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31'])
            ->keyBy(fn ($row) => $row->territory->id);

        $this->assertSame(1, $rows->get($territoryA->id)->executive_count);
        $this->assertSame(1000.0, $rows->get($territoryA->id)->total_order_value);
        $this->assertSame(1, $rows->get($territoryB->id)->executive_count);
        $this->assertSame(1000.0, $rows->get($territoryB->id)->total_order_value);
    }
}
