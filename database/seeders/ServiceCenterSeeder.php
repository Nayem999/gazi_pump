<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\ServiceCenter;
use Illuminate\Database\Seeder;

class ServiceCenterSeeder extends Seeder
{
    public function run(): void
    {
        ServiceCenter::factory()->count(5)->create();
    }
}
