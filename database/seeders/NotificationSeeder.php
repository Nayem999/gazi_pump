<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Actions\CheckBirthdayAction;
use App\Actions\CheckLateAttendanceAction;
use App\Actions\CheckLowPerformanceAction;
use App\Actions\CheckNoCheckoutAction;
use App\Actions\CheckTargetReminderAction;
use App\Enums\AnnouncementAudience;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\User;
use App\Services\AnnouncementService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Runs every notification check against the data the earlier seeders already
 * created, so the bell dropdown / inbox have realistic notifications right
 * after a fresh seed — plus a couple of hand-authored sample announcements.
 */
class NotificationSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedLateAttendanceNotifications();
        $this->seedNoCheckoutNotifications();

        app(CheckLowPerformanceAction::class)();
        app(CheckTargetReminderAction::class)();

        $this->seedBirthdayNotification();
        $this->seedAnnouncements();
    }

    private function seedLateAttendanceNotifications(): void
    {
        $action = app(CheckLateAttendanceAction::class);

        $day = Carbon::today();
        $checked = 0;

        while ($checked < 14) {
            $day = $day->copy()->subDay();

            if ($day->isWeekend()) {
                continue;
            }

            $action($day);
            $checked++;
        }
    }

    /**
     * AttendanceSeeder never leaves a checkout blank, so a couple of
     * synthetic incomplete records are created here (on dates it didn't
     * touch) purely to give the "missed checkout" notification something
     * real to find.
     */
    private function seedNoCheckoutNotifications(): void
    {
        $executives = User::role('Sales Executive')->take(2)->get();

        foreach ($executives as $index => $executive) {
            // Well outside AttendanceSeeder's last-14-weekdays window, so this
            // never collides with the (user_id, date) unique constraint.
            $date = Carbon::today()->subDays(45 + $index);

            Attendance::factory()->notCheckedOut()->create([
                'user_id' => $executive->id,
                'date' => $date->toDateString(),
                'check_in_at' => $date->copy()->setTime(9, 0),
                'status' => AttendanceStatus::Present,
            ]);
        }

        app(CheckNoCheckoutAction::class)();
    }

    /**
     * Seeded birthdates are random across decades, so a demo can't rely on
     * one naturally landing on today — one executive's is overwritten to
     * guarantee the notification fires.
     */
    private function seedBirthdayNotification(): void
    {
        $celebrant = User::role('Sales Executive')->inRandomOrder()->first();

        if (! $celebrant) {
            return;
        }

        $celebrant->forceFill(['date_of_birth' => Carbon::now()->subYears(28)->format('Y-m-d')])->save();

        app(CheckBirthdayAction::class)();
    }

    private function seedAnnouncements(): void
    {
        $sender = User::role('General Manager')->first() ?? User::role('Super Admin')->first();

        if (! $sender) {
            return;
        }

        $announcements = app(AnnouncementService::class);

        $announcements->send($sender, [
            'title' => 'Welcome to Gazi Pump SFA',
            'message' => 'This system is now live for all sales operations. Please reach out to your manager with any questions.',
            'audience' => AnnouncementAudience::All->value,
            'audience_role' => null,
            'audience_territory_id' => null,
            'audience_user_id' => null,
        ]);

        $announcements->send($sender, [
            'title' => 'Month-End Reminder',
            'message' => 'Please make sure all sales entries and collections are recorded before month end.',
            'audience' => AnnouncementAudience::Role->value,
            'audience_role' => 'Sales Executive',
            'audience_territory_id' => null,
            'audience_user_id' => null,
        ]);
    }
}
