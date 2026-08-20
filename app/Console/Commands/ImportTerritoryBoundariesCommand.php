<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\ImportTerritoryBoundariesAction;
use Illuminate\Console\Command;

class ImportTerritoryBoundariesCommand extends Command
{
    protected $signature = 'territories:import-boundaries';

    protected $description = 'Replace all territories with the real Bangladesh Union Council (ADM4) boundaries from geoBoundaries.org';

    public function handle(ImportTerritoryBoundariesAction $action): int
    {
        $count = $action();
        $this->info("Imported {$count} territory boundaries.");

        return self::SUCCESS;
    }
}
