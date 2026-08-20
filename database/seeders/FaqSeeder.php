<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        foreach (range(0, 9) as $index) {
            Faq::factory()->create(['sort_order' => $index]);
        }
    }
}
