<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Territory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Adds 2 Sales Executives on top of the small curated demo dataset built
 * after the Module 20 data purge (3 Dhaka territories, 5 dealers, 40
 * products) — AttendanceSeeder/VisitSeeder/OrderSeeder/CollectionEntrySeeder/
 * TargetSeeder all query `User::role('Sales Executive')`, so these two are
 * what those seeders act on.
 */
class SalesExecutiveSampleSeeder extends Seeder
{
    private const EXECUTIVES = [
        ['employee_id' => 'EMP-10001', 'name' => 'Rafiqul Islam', 'email' => 'rafiqul.islam@gazipump.com', 'phone' => '01711000001'],
        ['employee_id' => 'EMP-10002', 'name' => 'Shirin Akter', 'email' => 'shirin.akter@gazipump.com', 'phone' => '01711000002'],
    ];

    public function run(): void
    {
        $territories = Territory::orderBy('id')->get();

        collect(self::EXECUTIVES)->each(function (array $data, int $i) use ($territories) {
            $user = User::factory()->create([
                'employee_id' => $data['employee_id'],
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'designation' => 'Sales Executive',
                'manager_id' => null,
            ]);

            $user->assignRole('Sales Executive');

            if ($territories->isNotEmpty()) {
                $user->territories()->sync([$territories->get($i % $territories->count())->id]);
            }
        });
    }
}
