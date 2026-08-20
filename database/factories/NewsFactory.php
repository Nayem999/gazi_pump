<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $publishedAt = Carbon::instance(fake()->dateTimeBetween('-60 days', 'now'));

        return [
            'title' => fake()->sentence(6),
            'excerpt' => fake()->sentence(15),
            'body' => implode("\n\n", fake()->paragraphs(4)),
            'cover_image' => null,
            'is_published' => true,
            'published_at' => $publishedAt,
        ];
    }
}
