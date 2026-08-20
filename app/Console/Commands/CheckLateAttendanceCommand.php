<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CheckLateAttendanceAction;
use Illuminate\Console\Command;

class CheckLateAttendanceCommand extends Command
{
    protected $signature = 'notifications:check-late-attendance';

    protected $description = "Notify yesterday's late-marked executives and their managers";

    public function handle(CheckLateAttendanceAction $action): int
    {
        $count = $action();
        $this->info("Late attendance notifications sent: {$count}");

        return self::SUCCESS;
    }
}
