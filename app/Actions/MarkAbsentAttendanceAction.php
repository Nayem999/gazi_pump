<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\User;
use App\Services\AttendanceService;
use App\Services\HolidayService;
use Illuminate\Support\Carbon;

/**
 * Backfills an Absent attendance row for every active Sales Executive who
 * has no attendance entry at all for $date. Skipped entirely on a
 * configured weekend/off day or a recorded government holiday, since a
 * missing entry there is expected, not a no-show. Defaults to yesterday so
 * the full day has already elapsed — an executive who checks in late on the
 * day this runs is never retroactively marked absent.
 */
class MarkAbsentAttendanceAction
{
    public function __construct(
        private readonly AttendanceService $attendances,
        private readonly HolidayService $holidays,
    ) {}

    public function __invoke(?Carbon $date = null): int
    {
        $date ??= Carbon::yesterday();

        if ($this->attendances->isWeekendDay($date) || $this->holidays->isHoliday($date)) {
            return 0;
        }

        $marked = 0;

        User::role('Sales Executive')
            ->where('status', true)
            ->each(function (User $user) use ($date, &$marked) {
                if ($this->attendances->markAbsentIfMissing($user, $date)) {
                    $marked++;
                }
            });

        return $marked;
    }
}
