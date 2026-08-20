<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Target;
use Illuminate\Notifications\Notification;

class TargetReminderNotification extends Notification
{
    public function __construct(private readonly Target $target, private readonly float $overallPct) {}

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
            'type' => NotificationType::TargetReminder->value,
            'title' => 'Target Reminder',
            'message' => "{$this->target->user->name} is at {$this->overallPct}% overall for {$this->target->periodLabel()} — the month is ending soon.",
            'target_id' => $this->target->id,
        ];
    }
}
