<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Creates the single settings row (App\Models\Setting::current() would do
 * this lazily anyway, but seeding it explicitly means the Settings edit
 * page has real values to show immediately after a fresh seed).
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        Setting::current();
    }
}
