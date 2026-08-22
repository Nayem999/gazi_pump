<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\District;
use App\Models\Thana;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Thana>
 */
class ThanaFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'district_id' => District::factory(),
            'name' => fake()->unique()->citySuffix().' '.fake()->city(),
            'name_bn' => null,
            'status' => true,
        ];
    }
}
