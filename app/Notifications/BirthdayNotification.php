<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationType;
use App\Models\User;
use Illuminate\Notifications\Notification;

class BirthdayNotification extends Notification
{
    public function __construct(private readonly User $celebrant) {}

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
        $isSelf = $notifiable->is($this->celebrant);

        return [
            'type' => NotificationType::Birthday->value,
            'title' => 'Birthday',
            'message' => $isSelf
                ? 'Happy Birthday! The whole team wishes you a great day.'
                : "Today is {$this->celebrant->name}'s birthday.",
            'user_id' => $this->celebrant->id,
        ];
    }
}
