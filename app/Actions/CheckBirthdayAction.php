<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Notifications\BirthdayNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Wishes today's birthday executives (and notifies their manager) — scanned
 * in PHP via Carbon's isBirthday() since matching month/day in SQL varies
 * by driver and the user count here is small.
 */
class CheckBirthdayAction
{
    public function __invoke(): int
    {
        $notified = 0;

        User::with('manager')
            ->whereNotNull('date_of_birth')
            ->get()
            ->filter(fn (User $user) => $user->isBirthdayToday())
            ->each(function (User $celebrant) use (&$notified) {
                if ($this->alreadyNotified($celebrant)) {
                    return;
                }

                $recipients = collect([$celebrant, $celebrant->manager])->filter();
                Notification::send($recipients, new BirthdayNotification($celebrant));
                $notified++;
            });

        return $notified;
    }

    private function alreadyNotified(User $celebrant): bool
    {
        return $celebrant->notifications()
            ->where('type', BirthdayNotification::class)
            ->where('data->user_id', $celebrant->id)
            ->whereDate('created_at', now()->toDateString())
            ->exists();
    }
}
