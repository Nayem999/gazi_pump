<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CheckNoCheckoutAction;
use Illuminate\Console\Command;

class CheckNoCheckoutCommand extends Command
{
    protected $signature = 'notifications:check-no-checkout';

    protected $description = 'Notify executives (and their managers) who checked in on a past day but never checked out';

    public function handle(CheckNoCheckoutAction $action): int
    {
        $count = $action();
        $this->info("No-checkout notifications sent: {$count}");

        return self::SUCCESS;
    }
}
