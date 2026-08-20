<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

/**
 * Seeds a representative slice of the six-tier hierarchy (Super Admin down
 * to Sales Executive) with manager_id chained correctly. Territory/Sales
 * Team assignment (and scaling this up toward the full 600+ executives)
 * happens in Module 2 once those org-structure tables exist.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::factory()->create([
            'employee_id' => 'EMP-00001',
            'name' => 'System Administrator',
            'email' => 'admin@gazipump.com',
            'designation' => 'Super Admin',
            'manager_id' => null,
        ]);
        $superAdmin->assignRole('Super Admin');

        $gm = User::factory()->create([
            'employee_id' => 'EMP-00002',
            'name' => 'General Manager',
            'email' => 'gm@gazipump.com',
            'designation' => 'General Manager',
            'manager_id' => $superAdmin->id,
        ]);
        $gm->assignRole('General Manager');

        $salesManagers = User::factory()
            ->count(3)
            ->designation('Sales Manager')
            ->manager($gm->id)
            ->create()
            ->each(fn (User $user) => $user->assignRole('Sales Manager'));

        $areaManagers = User::factory()
            ->count(6)
            ->designation('Area Manager')
            ->create()
            ->each(function (User $user, int $index) use ($salesManagers): void {
                $user->update(['manager_id' => Arr::get($salesManagers, $index % $salesManagers->count())->id]);
                $user->assignRole('Area Manager');
            });

        $territoryManagers = User::factory()
            ->count(12)
            ->designation('Territory Manager')
            ->create()
            ->each(function (User $user, int $index) use ($areaManagers): void {
                $user->update(['manager_id' => Arr::get($areaManagers, $index % $areaManagers->count())->id]);
                $user->assignRole('Territory Manager');
            });

        User::factory()
            ->count(50)
            ->designation('Sales Executive')
            ->create()
            ->each(function (User $user, int $index) use ($territoryManagers): void {
                $user->update(['manager_id' => Arr::get($territoryManagers, $index % $territoryManagers->count())->id]);
                $user->assignRole('Sales Executive');
            });
    }
}
