<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\AttendanceStatus;
use App\Services\AttendanceService;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Regression coverage for determineStatus(): Carbon 3 changed diffInMinutes()
 * to return a signed value by default (Carbon 2 was always absolute), which
 * silently produced negative late_minutes for every late check-in until this
 * was caught by manual tinker verification and fixed with absolute: true.
 */
class AttendanceServiceTest extends TestCase
{
    private function service(): AttendanceService
    {
        return app(AttendanceService::class);
    }

    public function test_check_in_exactly_on_time_is_present(): void
    {
        [$status, $lateMinutes] = $this->service()->determineStatus(Carbon::parse('2026-08-03 09:00:00'));

        $this->assertSame(AttendanceStatus::Present, $status);
        $this->assertSame(0, $lateMinutes);
    }

    public function test_check_in_within_grace_period_is_present(): void
    {
        [$status, $lateMinutes] = $this->service()->determineStatus(Carbon::parse('2026-08-03 09:15:00'));

        $this->assertSame(AttendanceStatus::Present, $status);
        $this->assertSame(0, $lateMinutes);
    }

    public function test_check_in_past_grace_period_is_late_with_positive_minutes(): void
    {
        [$status, $lateMinutes] = $this->service()->determineStatus(Carbon::parse('2026-08-03 09:30:00'));

        $this->assertSame(AttendanceStatus::Late, $status);
        $this->assertSame(30, $lateMinutes);
    }

    public function test_check_in_before_office_start_is_present(): void
    {
        [$status, $lateMinutes] = $this->service()->determineStatus(Carbon::parse('2026-08-03 08:45:00'));

        $this->assertSame(AttendanceStatus::Present, $status);
        $this->assertSame(0, $lateMinutes);
    }
}
