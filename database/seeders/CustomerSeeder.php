<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Territory;
use Illuminate\Database\Seeder;

/**
 * Seeds a representative slice of customers (dealers, retailers,
 * distributors) spread across the 12 territories that OrgStructureSeeder
 * actually staffed with executives — the other ~5,148 imported real
 * territories intentionally have no demo activity, same as a real company
 * would only have sales presence in a subset of all unions nationwide.
 */
class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $territories = Territory::orderBy('id')->limit(12)->get();

        if ($territories->isEmpty()) {
            return;
        }

        Customer::factory()
            ->count(60)
            ->create()
            ->each(function (Customer $customer, int $i) use ($territories): void {
                $customer->update(['territory_id' => $territories->get($i % $territories->count())->id]);
            });
    }
}
