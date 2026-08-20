<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Achievement;
use App\Notifications\LowPerformanceNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Alerts an executive and their manager when a month's computed grade lands
 * in the configured "low performance" bucket (D/F by default).
 */
class CheckLowPerformanceAction
{
    public function __invoke(?int $month = null, ?int $year = null): int
    {
        $month ??= Carbon::now()->month;
        $year ??= Carbon::now()->year;
        $notified = 0;

        Achievement::with('target.user.manager')
            ->whereIn('grade', config('sfa.notifications.low_performance_grades'))
            ->whereHas('target', fn ($query) => $query->where('month', $month)->where('year', $year))
            ->each(function (Achievement $achievement) use (&$notified) {
                $user = $achievement->target->user;

                if ($this->alreadyNotified($achievement)) {
                    return;
                }

                $recipients = collect([$user, $user->manager])->filter();
                Notification::send($recipients, new LowPerformanceNotification($achievement));
                $notified++;
            });

        return $notified;
    }

    private function alreadyNotified(Achievement $achievement): bool
    {
        return $achievement->target->user->notifications()
            ->where('type', LowPerformanceNotification::class)
            ->where('data->achievement_id', $achievement->id)
            ->exists();
    }
}
