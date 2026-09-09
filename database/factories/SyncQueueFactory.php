<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SyncDirection;
use App\Enums\SyncStatus;
use App\Enums\TallyEntityType;
use App\Models\SyncQueue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SyncQueue>
 */
class SyncQueueFactory extends Factory
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
            'external_reference' => 'SFA-'.fake()->unique()->uuid(),
            'payload' => ['name' => fake()->company()],
            'status' => SyncStatus::Pending,
            'attempt_count' => 0,
        ];
    }
}
