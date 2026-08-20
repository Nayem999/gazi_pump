<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\PerformanceGrade;
use App\Models\Achievement;
use App\Models\Target;
use App\Models\Territory;
use App\Models\User;
use App\Services\TerritoryMapService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TerritoryMapServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // territoryPerformance() (wrapped by TerritoryMapService) calls
        // User::role('Sales Executive'), which requires the role to exist.
        $this->seed(RolePermissionSeeder::class);
    }

    private function service(): TerritoryMapService
    {
        return app(TerritoryMapService::class);
    }

    public function test_a_territory_with_no_achievements_has_a_null_grade(): void
    {
        $territory = Territory::factory()->create();

        $row = $this->service()->performanceFor($territory, 8, 2026);

        $this->assertNull($row->grade);
        $this->assertNull($row->avg_achievement_pct);
    }

    public function test_it_computes_the_grade_from_the_average_achievement_of_the_territorys_executives(): void
    {
        $territory = Territory::factory()->create();
        $executiveA = User::factory()->create(['territory_id' => $territory->id]);
        $executiveB = User::factory()->create(['territory_id' => $territory->id]);

        $targetA = Target::factory()->create(['user_id' => $executiveA->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $targetA->id, 'overall_pct' => 100]);

        $targetB = Target::factory()->create(['user_id' => $executiveB->id, 'month' => 8, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $targetB->id, 'overall_pct' => 80]);

        $row = $this->service()->performanceFor($territory, 8, 2026);

        // Average of 100 and 80 is 90 — exactly the A threshold.
        $this->assertSame(90.0, $row->avg_achievement_pct);
        $this->assertSame(PerformanceGrade::A, $row->grade);
    }

    public function test_it_ignores_achievements_from_a_different_period(): void
    {
        $territory = Territory::factory()->create();
        $executive = User::factory()->create(['territory_id' => $territory->id]);
        $target = Target::factory()->create(['user_id' => $executive->id, 'month' => 7, 'year' => 2026]);
        Achievement::factory()->create(['target_id' => $target->id, 'overall_pct' => 100]);

        $row = $this->service()->performanceFor($territory, 8, 2026);

        $this->assertNull($row->grade);
    }

    public function test_it_computes_the_grade_for_the_given_month_and_year(): void
    {
        $territory = Territory::factory()->create();
        $executive = User::factory()->create(['territory_id' => $territory->id]);
        $target = Target::factory()->create([
            'user_id' => $executive->id,
            'month' => 8,
            'year' => 2026,
        ]);
        Achievement::factory()->create(['target_id' => $target->id, 'overall_pct' => 20]);

        $row = $this->service()->performanceFor($territory, 8, 2026);

        $this->assertSame(PerformanceGrade::F, $row->grade);
    }
}
