<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CheckLowPerformanceAction;
use Illuminate\Console\Command;

class CheckLowPerformanceCommand extends Command
{
    protected $signature = 'notifications:check-low-performance {--month=} {--year=}';

    protected $description = 'Notify executives (and their managers) whose computed grade is in the low-performance bucket';

    public function handle(CheckLowPerformanceAction $action): int
    {
        $month = $this->option('month') ? (int) $this->option('month') : null;
        $year = $this->option('year') ? (int) $this->option('year') : null;

        $count = $action($month, $year);
        $this->info("Low performance notifications sent: {$count}");

        return self::SUCCESS;
    }
}
