<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Target;
use App\Notifications\TargetReminderNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Once the reminder window before month-end opens, nudges any executive
 * whose current-month achievement is still behind the configured pace
 * threshold — or who has no achievement snapshot yet at all.
 */
class CheckTargetReminderAction
{
    public function __invoke(?Carbon $today = null): int
    {
        $today ??= Carbon::today();
        $daysBeforeEnd = (int) config('sfa.notifications.target_reminder_days_before_month_end');
        $daysRemaining = $today->daysInMonth - $today->day;

        if ($daysRemaining > $daysBeforeEnd) {
            return 0;
        }

        $minPct = (float) config('sfa.notifications.target_reminder_min_pct');
        $notified = 0;

        Target::with('achievement', 'user.manager')
            ->where('month', $today->month)
            ->where('year', $today->year)
            ->each(function (Target $target) use (&$notified, $minPct) {
                $overallPct = $target->achievement ? (float) $target->achievement->overall_pct : 0.0;

                if ($overallPct >= $minPct) {
                    return;
                }

                if ($this->alreadyNotified($target)) {
                    return;
                }

                $recipients = collect([$target->user, $target->user->manager])->filter();
                Notification::send($recipients, new TargetReminderNotification($target, $overallPct));
                $notified++;
            });

        return $notified;
    }

    private function alreadyNotified(Target $target): bool
    {
        return $target->user->notifications()
            ->where('type', TargetReminderNotification::class)
            ->where('data->target_id', $target->id)
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->exists();
    }
}
