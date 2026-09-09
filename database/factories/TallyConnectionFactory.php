<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TallyApiFormat;
use App\Models\TallyConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TallyConnection>
 */
class TallyConnectionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'connection_name' => 'Connection '.fake()->unique()->numberBetween(1, 999),
            'tally_company_name' => fake()->company(),
            'tally_company_guid' => fake()->uuid(),
            'host' => 'localhost',
            'port' => 9000,
            'protocol' => 'http',
            'api_format' => TallyApiFormat::Xml,
            'sync_agent_id' => fake()->unique()->uuid(),
            'sync_agent_token' => hash('sha256', fake()->uuid()),
            'is_active' => true,
        ];
    }
}
