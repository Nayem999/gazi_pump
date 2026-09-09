<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\TallyRetryService;
use Illuminate\Console\Command;

class ProcessTallyRetriesCommand extends Command
{
    protected $signature = 'tally:process-retries';

    protected $description = 'Move due Retry sync-queue rows back to Pending so the Sync Agent picks them up';

    public function handle(TallyRetryService $retry): int
    {
        $count = $retry->processDue();
        $this->info("Sync queue rows released for retry: {$count}");

        return self::SUCCESS;
    }
}
