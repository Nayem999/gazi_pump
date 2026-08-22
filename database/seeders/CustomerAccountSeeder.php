<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerAccount;
use App\Models\Dealer;
use Illuminate\Database\Seeder;

class CustomerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $linkedDealers = Dealer::inRandomOrder()->limit(3)->get();

        foreach ($linkedDealers as $dealer) {
            CustomerAccount::factory()->create([
                'dealer_id' => $dealer->id,
                'name' => $dealer->name,
                'email' => $dealer->email ?? fake()->unique()->safeEmail(),
                'phone' => $dealer->phone,
            ]);
        }

        CustomerAccount::factory()->count(7)->create();

        // A known-password demo account for the browser walkthrough / manual QA.
        CustomerAccount::factory()->create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
        ]);
    }
}
