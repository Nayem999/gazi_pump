<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Notification Checks (Module 12)
|--------------------------------------------------------------------------
| Each command is idempotent (skips records it already notified about), so
| re-running the scheduler after a missed tick never double-sends — but
| ->withoutOverlapping() (Module 19 hardening) still avoids two copies of
| the same slow run doing redundant work concurrently if one runs long.
*/
Schedule::command('notifications:check-late-attendance')->dailyAt('20:00')->withoutOverlapping();
Schedule::command('notifications:check-no-checkout')->dailyAt('22:00')->withoutOverlapping();
Schedule::command('notifications:check-low-performance')->monthlyOn(1, '06:00')->withoutOverlapping();
Schedule::command('notifications:check-target-reminders')->dailyAt('07:00')->withoutOverlapping();
Schedule::command('notifications:check-birthdays')->dailyAt('08:00')->withoutOverlapping();

/*
|--------------------------------------------------------------------------
| Attendance Backfill
|--------------------------------------------------------------------------
| Runs just after midnight so yesterday has fully elapsed before anyone
| with no attendance entry is marked Absent — never touches a day that
| already has a row (manual, auto, or otherwise), so reruns are safe.
*/
Schedule::command('attendance:mark-absent')->dailyAt('00:30')->withoutOverlapping();
