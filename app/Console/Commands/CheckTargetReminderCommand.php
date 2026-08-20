<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CheckTargetReminderAction;
use Illuminate\Console\Command;

class CheckTargetReminderCommand extends Command
{
    protected $signature = 'notifications:check-target-reminders';

    protected $description = 'Remind executives (and their managers) who are behind pace as the month nears its end';

    public function handle(CheckTargetReminderAction $action): int
    {
        $count = $action();
        $this->info("Target reminder notifications sent: {$count}");

        return self::SUCCESS;
    }
}
