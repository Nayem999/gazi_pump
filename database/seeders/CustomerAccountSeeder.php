<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\CustomerAccount;
use Illuminate\Database\Seeder;

class CustomerAccountSeeder extends Seeder
{
    public function run(): void
    {
        $linkedCustomers = Customer::inRandomOrder()->limit(3)->get();

        foreach ($linkedCustomers as $customer) {
            CustomerAccount::factory()->create([
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'email' => $customer->email ?? fake()->unique()->safeEmail(),
                'phone' => $customer->phone,
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
