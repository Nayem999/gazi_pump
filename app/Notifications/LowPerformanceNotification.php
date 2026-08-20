<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\Achievement;
use Illuminate\Notifications\Notification;

class LowPerformanceNotification extends Notification
{
    public function __construct(private readonly Achievement $achievement) {}

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
        $target = $this->achievement->target;

        return [
            'type' => NotificationType::LowPerformance->value,
            'title' => 'Low Performance',
            'message' => "{$target->user->name} scored grade {$this->achievement->grade->value} ({$this->achievement->overall_pct}%) for {$target->periodLabel()}.",
            'target_id' => $target->id,
            'achievement_id' => $this->achievement->id,
        ];
    }
}
