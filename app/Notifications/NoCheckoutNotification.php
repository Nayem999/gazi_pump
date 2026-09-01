<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Attendance;
use Illuminate\Notifications\Notification;

class NoCheckoutNotification extends Notification
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
            'type' => NotificationType::NoCheckout->value,
            'title' => 'Missed Checkout',
            'message' => "{$this->attendance->user->name} checked in on {$this->attendance->date->format('d M Y')} but never checked out.",
            'attendance_id' => $this->attendance->id,
        ];
    }
}
