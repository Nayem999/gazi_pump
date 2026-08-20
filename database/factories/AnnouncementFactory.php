<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AnnouncementAudience;
use App\Models\Announcement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Announcement>
 */
class AnnouncementFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'message' => fake()->paragraph(),
            'audience' => AnnouncementAudience::All,
            'audience_role' => null,
            'audience_territory_id' => null,
            'audience_user_id' => null,
            'sent_by' => User::factory(),
            'recipient_count' => 0,
        ];
    }
}
