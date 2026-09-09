<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SyncDirection;
use App\Enums\TallyEntityType;
use App\Models\SyncLog;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncLog>
 */
class SyncLogFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_type' => TallyEntityType::Dealer,
            'entity_id' => fake()->numberBetween(1, 100000),
            'direction' => SyncDirection::PullFromTally,
            'request_time' => now(),
            'response_time' => now(),
            'status' => 'success',
            'external_reference' => 'SFA-'.fake()->unique()->uuid(),
        ];
    }
}
