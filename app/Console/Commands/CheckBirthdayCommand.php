<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\CheckBirthdayAction;
use Illuminate\Console\Command;

class CheckBirthdayCommand extends Command
{
    protected $signature = 'notifications:check-birthdays';

    protected $description = "Wish today's birthday executives and notify their managers";

    public function handle(CheckBirthdayAction $action): int
    {
        $count = $action();
        $this->info("Birthday notifications sent: {$count}");

        return self::SUCCESS;
    }
}
