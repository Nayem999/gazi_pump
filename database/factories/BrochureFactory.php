<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Brochure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brochure>
 */
class BrochureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'file' => 'brochures/'.fake()->uuid().'.pdf',
            'cover_image' => null,
            'is_published' => true,
        ];
    }
}
