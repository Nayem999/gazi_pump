<?php

declare(strict_types=1);

namespace App\Actions;

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Notifications\LateAttendanceNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

/**
 * Notifies a late-marked executive and their manager, once per attendance
 * record — re-running for the same date is safe, already-notified records
 * are skipped rather than re-sent.
 */
class CheckLateAttendanceAction
{
    public function __invoke(?Carbon $date = null): int
    {
        $date ??= Carbon::yesterday();
        $notified = 0;

        Attendance::with('user.manager')
            ->where('date', $date->toDateString())
            ->where('status', AttendanceStatus::Late)
            ->each(function (Attendance $attendance) use (&$notified) {
                if ($this->alreadyNotified($attendance)) {
                    return;
                }

                $recipients = collect([$attendance->user, $attendance->user->manager])->filter();
                Notification::send($recipients, new LateAttendanceNotification($attendance));
                $notified++;
            });

        return $notified;
    }

    private function alreadyNotified(Attendance $attendance): bool
    {
        return $attendance->user->notifications()
            ->where('type', LateAttendanceNotification::class)
            ->where('data->attendance_id', $attendance->id)
            ->exists();
    }
}
