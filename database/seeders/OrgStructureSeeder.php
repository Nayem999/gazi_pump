<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\ImportTerritoryBoundariesAction;
use App\Models\SalesTeam;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Creates 3 sales teams and imports the real Bangladesh Union Council
 * boundaries as territories (see ImportTerritoryBoundariesAction), then
 * places the demo users seeded in Module 1 into a representative sample of
 * 12 of those real territories: one Territory Manager per territory, one
 * Sales Manager and one Area Manager per sales team, and Sales Executives
 * distributed round-robin across the same 12 territories.
 */
class OrgStructureSeeder extends Seeder
{
    public function run(): void
    {
        $teams = collect(['Team 1', 'Team 2', 'Team 3'])->map(
            fn (string $name, int $i) => SalesTeam::factory()->create([
                'name' => $name,
                'code' => 'TEAM-'.($i + 1),
                'description' => "Sales {$name} covering its assigned executives nationwide.",
            ])
        );

        app(ImportTerritoryBoundariesAction::class)();

        $territories = Territory::orderBy('id')->limit(12)->get();

        $territoryManagers = User::role('Territory Manager')->orderBy('id')->get();
        $territories->each(function (Territory $territory, int $i) use ($territoryManagers) {
            $manager = $territoryManagers->get($i % max($territoryManagers->count(), 1));

            if (! $manager) {
                return;
            }

            $territory->update(['manager_id' => $manager->id]);
            $manager->territories()->sync([$territory->id]);
        });

        $areaManagers = User::role('Area Manager')->orderBy('id')->get();
        $areaManagers->each(function (User $manager, int $i) use ($teams) {
            $manager->update(['sales_team_id' => $teams->get($i % $teams->count())->id]);
        });

        $salesManagers = User::role('Sales Manager')->orderBy('id')->get();
        $salesManagers->each(function (User $manager, int $i) use ($teams) {
            $manager->update(['sales_team_id' => $teams->get($i % $teams->count())->id]);
        });

        $executives = User::role('Sales Executive')->orderBy('id')->get();
        $executives->each(function (User $executive, int $i) use ($territories, $teams) {
            $territory = $territories->get($i % $territories->count());

            $executive->update([
                'sales_team_id' => $teams->get($i % $teams->count())->id,
            ]);
            $executive->territories()->sync([$territory->id]);
        });
    }
}
