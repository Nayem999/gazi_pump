<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AttendanceStatus;
use App\Enums\VisitPlanStatus;
use App\Models\Attendance;
use App\Models\CollectionEntry;
use App\Models\Customer;
use App\Models\Product;
use App\Models\SalesEntry;
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
        $this->assertSame(3, $row->total_days);
        // 2 of 3 days count toward the rate (present + late, not absent).
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
        $userA = User::factory()->create(['territory_id' => $territoryA->id]);
        $userB = User::factory()->create(['territory_id' => $territoryB->id]);

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
        $customer = Customer::factory()->create();

        VisitPlan::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'planned_date' => '2026-08-05', 'status' => VisitPlanStatus::Completed]);
        VisitPlan::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'planned_date' => '2026-08-06', 'status' => VisitPlanStatus::Completed]);
        VisitPlan::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'planned_date' => '2026-08-07', 'status' => VisitPlanStatus::Cancelled]);

        Visit::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'check_in_at' => Carbon::create(2026, 8, 5, 10), 'is_gps_verified' => true]);
        Visit::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'check_in_at' => Carbon::create(2026, 8, 6, 10), 'is_gps_verified' => false]);

        $rows = $this->service()->visitCompliance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(3, $row->planned_count);
        $this->assertSame(2, $row->completed_count);
        $this->assertSame(66.7, $row->completion_rate);
        $this->assertSame(2, $row->total_visits);
        $this->assertSame(1, $row->gps_verified_count);
        $this->assertSame(50.0, $row->gps_verified_rate);
    }

    public function test_sales_performance_sums_value_and_quantity_across_line_items(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $entry = SalesEntry::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'sale_date' => '2026-08-10', 'total_amount' => 1500]);
        $entry->items()->create(['product_id' => Product::factory()->create()->id, 'quantity' => 5, 'unit_price' => 200, 'discount_amount' => 0, 'total_amount' => 1000]);
        $entry->items()->create(['product_id' => Product::factory()->create()->id, 'quantity' => 5, 'unit_price' => 100, 'discount_amount' => 0, 'total_amount' => 500]);

        $rows = $this->service()->salesPerformance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(1, $row->sales_count);
        $this->assertSame(10, $row->total_quantity);
        $this->assertSame(1500.0, $row->total_sales_value);
    }

    public function test_collection_summary_breaks_down_by_payment_method(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        CollectionEntry::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'collection_date' => '2026-08-10', 'amount' => 100, 'payment_method' => 'cash']);
        CollectionEntry::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'collection_date' => '2026-08-11', 'amount' => 200, 'payment_method' => 'cheque']);

        $rows = $this->service()->collectionSummary(['date_from' => '2026-08-01', 'date_to' => '2026-08-31']);
        $row = $rows->firstWhere('user.id', $user->id);

        $this->assertSame(2, $row->collections_count);
        $this->assertSame(300.0, $row->total_amount);
        $this->assertSame(100.0, $row->cash_total);
        $this->assertSame(200.0, $row->cheque_total);
        $this->assertSame(0.0, $row->bank_transfer_total);
    }

    public function test_territory_performance_aggregates_sales_collections_and_visits_per_territory(): void
    {
        $territory = Territory::factory()->create();
        $user = User::factory()->create(['territory_id' => $territory->id]);
        $user->assignRole('Sales Executive');
        $customer = Customer::factory()->create();

        SalesEntry::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'sale_date' => '2026-08-05', 'total_amount' => 1000]);
        CollectionEntry::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'collection_date' => '2026-08-05', 'amount' => 400]);
        Visit::factory()->create(['user_id' => $user->id, 'customer_id' => $customer->id, 'check_in_at' => Carbon::create(2026, 8, 5, 10), 'is_gps_verified' => true]);

        $rows = $this->service()->territoryPerformance(['date_from' => '2026-08-01', 'date_to' => '2026-08-31', 'territory_id' => $territory->id]);
        $row = $rows->first();

        $this->assertSame($territory->id, $row->territory->id);
        $this->assertSame(1, $row->executive_count);
        $this->assertSame(1000.0, $row->total_sales_value);
        $this->assertSame(400.0, $row->total_collection_amount);
        $this->assertSame(1, $row->total_visits);
        $this->assertSame(100.0, $row->gps_verified_rate);
    }
}
