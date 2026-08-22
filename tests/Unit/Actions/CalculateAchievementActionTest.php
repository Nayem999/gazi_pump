<?php

declare(strict_types=1);

namespace Tests\Unit\Actions;

use App\Actions\CalculateAchievementAction;
use App\Enums\PerformanceGrade;
use App\Models\CollectionEntry;
use App\Models\Dealer;
use App\Models\Product;
use App\Models\Order;
use App\Models\Target;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class CalculateAchievementActionTest extends TestCase
{
    use RefreshDatabase;

    private function action(): CalculateAchievementAction
    {
        return app(CalculateAchievementAction::class);
    }

    private function saleOf(User $user, Carbon $date, float $total, int $quantity): void
    {
        $entry = Order::factory()->create([
            'user_id' => $user->id,
            'order_date' => $date->toDateString(),
            'total_amount' => $total,
        ]);

        $entry->items()->create([
            'product_id' => Product::factory()->create()->id,
            'quantity' => $quantity,
            'unit_price' => $total / max($quantity, 1),
            'discount_amount' => 0,
            'total_amount' => $total,
        ]);
    }

    public function test_it_sums_sales_collections_and_quantity_for_the_targets_period_only(): void
    {
        $user = User::factory()->create();
        $target = Target::factory()->create([
            'user_id' => $user->id,
            'month' => 8,
            'year' => 2026,
            'order_value_target' => 1000,
            'collection_target' => 500,
            'quantity_target' => 20,
        ]);

        $this->saleOf($user, Carbon::create(2026, 8, 10), 600, 10);
        $this->saleOf($user, Carbon::create(2026, 8, 15), 400, 10);
        // Outside the target's period — must not count.
        $this->saleOf($user, Carbon::create(2026, 7, 15), 9999, 99);

        CollectionEntry::factory()->create([
            'user_id' => $user->id,
            'dealer_id' => Dealer::factory()->create()->id,
            'collection_date' => Carbon::create(2026, 8, 20)->toDateString(),
            'amount' => 500,
        ]);

        $achievement = $this->action()($target);

        $this->assertSame('1000.00', (string) $achievement->order_achieved);
        $this->assertSame('500.00', (string) $achievement->collection_achieved);
        $this->assertSame(20, $achievement->quantity_achieved);
    }

    public function test_percentages_are_achieved_over_target_times_100(): void
    {
        $user = User::factory()->create();
        $target = Target::factory()->create([
            'user_id' => $user->id,
            'order_value_target' => 1000,
            'collection_target' => 1000,
            'quantity_target' => 100,
        ]);

        $this->saleOf($user, Carbon::create($target->year, $target->month, 1), 500, 50);

        $achievement = $this->action()($target);

        $this->assertSame('50.00', (string) $achievement->order_pct);
        $this->assertSame('0.00', (string) $achievement->collection_pct);
        $this->assertSame('50.00', (string) $achievement->quantity_pct);
        $this->assertSame('33.33', (string) $achievement->overall_pct);
    }

    public function test_a_zero_target_yields_zero_percent_instead_of_dividing_by_zero(): void
    {
        $user = User::factory()->create();
        // Bypasses form-request validation (which requires min:1) on purpose
        // — the Action must defensively guard div-by-zero regardless of how
        // a zero-valued target reaches it.
        $target = Target::factory()->create([
            'user_id' => $user->id,
            'order_value_target' => 0,
            'collection_target' => 0,
            'quantity_target' => 0,
        ]);

        $achievement = $this->action()($target);

        $this->assertSame('0.00', (string) $achievement->order_pct);
        $this->assertSame('0.00', (string) $achievement->collection_pct);
        $this->assertSame('0.00', (string) $achievement->quantity_pct);
    }

    /**
     * @dataProvider gradeThresholdCases
     */
    public function test_overall_percentage_maps_to_the_correct_grade(float $overallPct, PerformanceGrade $expectedGrade): void
    {
        config(['sfa.targets.grade_thresholds' => ['A' => 90, 'B' => 75, 'C' => 60, 'D' => 40]]);

        $user = User::factory()->create();
        // All three components equal so overall == each component exactly.
        $target = Target::factory()->create([
            'user_id' => $user->id,
            'order_value_target' => 100,
            'collection_target' => 100,
            'quantity_target' => 100,
        ]);

        $this->saleOf($user, Carbon::create($target->year, $target->month, 1), $overallPct, (int) $overallPct);
        CollectionEntry::factory()->create([
            'user_id' => $user->id,
            'dealer_id' => Dealer::factory()->create()->id,
            'collection_date' => Carbon::create($target->year, $target->month, 1)->toDateString(),
            'amount' => $overallPct,
        ]);

        $achievement = $this->action()($target);

        $this->assertSame($expectedGrade, $achievement->grade);
    }

    /**
     * @return array<string, array{0: float, 1: PerformanceGrade}>
     */
    public static function gradeThresholdCases(): array
    {
        return [
            'exactly at A threshold' => [90.0, PerformanceGrade::A],
            'just below A is B' => [89.0, PerformanceGrade::B],
            'exactly at B threshold' => [75.0, PerformanceGrade::B],
            'exactly at C threshold' => [60.0, PerformanceGrade::C],
            'exactly at D threshold' => [40.0, PerformanceGrade::D],
            'below D is F' => [39.0, PerformanceGrade::F],
            'zero is F' => [0.0, PerformanceGrade::F],
        ];
    }

    public function test_recalculating_the_same_target_updates_in_place_rather_than_duplicating(): void
    {
        $user = User::factory()->create();
        $target = Target::factory()->create(['user_id' => $user->id, 'order_value_target' => 100]);

        $this->action()($target);
        $this->saleOf($user, Carbon::create($target->year, $target->month, 1), 100, 5);
        $this->action()($target);

        $this->assertDatabaseCount('achievements', 1);
        $this->assertSame('100.00', (string) $target->achievement()->first()->order_achieved);
    }
}
