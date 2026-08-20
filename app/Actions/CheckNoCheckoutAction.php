<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Attendance;
use App\Notifications\NoCheckoutNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Flags attendance records for past days (never today — the employee may
 * still be on shift) where the executive checked in but never checked out.
 */
class CheckNoCheckoutAction
{
    public function __invoke(): int
    {
        $notified = 0;

        Attendance::with('user.manager')
            ->whereDate('date', '<', Carbon::today())
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->each(function (Attendance $attendance) use (&$notified) {
                if ($this->alreadyNotified($attendance)) {
                    return;
                }

                $recipients = collect([$attendance->user, $attendance->user->manager])->filter();
                Notification::send($recipients, new NoCheckoutNotification($attendance));
                $notified++;
            });

        return $notified;
    }

    private function alreadyNotified(Attendance $attendance): bool
    {
        return $attendance->user->notifications()
            ->where('type', NoCheckoutNotification::class)
            ->where('data->attendance_id', $attendance->id)
            ->exists();
    }
}
