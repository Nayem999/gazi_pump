<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\VisitRequestStatus;
use App\Models\CustomerAccount;
use App\Models\VisitRequest;
use Illuminate\Database\Seeder;

class VisitRequestSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = CustomerAccount::all();

        foreach (range(1, 8) as $i) {
            VisitRequest::factory()->create([
                'customer_account_id' => $accounts->random()->id,
                'status' => fake()->randomElement(VisitRequestStatus::cases()),
            ]);
        }
    }
}
