<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\CalculateAchievementAction;
use App\Models\Target;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Queueable wrapper around CalculateAchievementAction. Dispatched with
 * ::dispatchSync() from admin-triggered recalculation (immediate feedback,
 * no worker required) and available for genuine ::dispatch() from batch/
 * scheduled recalculation of many targets at once.
 */
class RecalculateAchievementsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public readonly Target $target) {}

    public function handle(CalculateAchievementAction $action): void
    {
        $action($this->target);
    }
}
