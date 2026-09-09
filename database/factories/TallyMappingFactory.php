<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TallyEntityType;
use App\Enums\TallyMappingSyncStatus;
use App\Models\TallyMapping;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TallyMapping>
 */
class TallyMappingFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_type' => TallyEntityType::Dealer,
            'sfa_id' => fake()->unique()->numberBetween(1, 100000),
            'tally_guid' => fake()->unique()->uuid(),
            'tally_name' => fake()->company(),
            'tally_alter_id' => (string) fake()->unique()->numberBetween(1, 100000),
            'last_synced_at' => now(),
            'sync_status' => TallyMappingSyncStatus::Synced,
        ];
    }
}
