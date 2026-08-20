<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Attendance;
use Illuminate\Notifications\Notification;

class LateAttendanceNotification extends Notification
{
    public function __construct(private readonly Attendance $attendance) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => NotificationType::LateAttendance->value,
            'title' => 'Late Attendance',
            'message' => "{$this->attendance->user->name} was marked late by {$this->attendance->late_minutes} minute(s) on {$this->attendance->date->format('M d, Y')}.",
            'attendance_id' => $this->attendance->id,
        ];
    }
}
