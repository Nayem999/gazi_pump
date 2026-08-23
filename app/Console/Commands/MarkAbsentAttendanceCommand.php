<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\MarkAbsentAttendanceAction;
use Illuminate\Console\Command;

class MarkAbsentAttendanceCommand extends Command
{
    protected $signature = 'attendance:mark-absent';

    protected $description = 'Backfill an Absent attendance row for every Sales Executive with no check-in on a past working day';

    public function handle(MarkAbsentAttendanceAction $action): int
    {
        $count = $action();

        $this->info("Marked absent: {$count}");

        return self::SUCCESS;
    }
}
