<?php

declare(strict_types=1);

namespace App\Enums;

enum NotificationType: string
{
    case LateAttendance = 'late_attendance';
    case NoCheckout = 'no_checkout';
    case LowPerformance = 'low_performance';
    case TargetReminder = 'target_reminder';
    case Birthday = 'birthday';
    case Announcement = 'announcement';

    public function label(): string
    {
        return match ($this) {
            self::LateAttendance => 'Late Attendance',
            self::NoCheckout => 'Missed Checkout',
            self::LowPerformance => 'Low Performance',
            self::TargetReminder => 'Target Reminder',
            self::Birthday => 'Birthday',
            self::Announcement => 'Announcement',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::LateAttendance => 'ti-clock-exclamation',
            self::NoCheckout => 'ti-door-exit',
            self::LowPerformance => 'ti-trending-down',
            self::TargetReminder => 'ti-target-arrow',
            self::Birthday => 'ti-cake',
            self::Announcement => 'ti-speakerphone',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::LateAttendance => 'warning',
            self::NoCheckout => 'danger',
            self::LowPerformance => 'danger',
            self::TargetReminder => 'info',
            self::Birthday => 'success',
            self::Announcement => 'primary',
        };
    }
}
