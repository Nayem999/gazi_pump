<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Merges the single Setting row into config() at the start of every
 * request, so every service that already reads config('sfa.*') (or
 * config('app.name')) becomes admin-editable via the Settings screen
 * without those call sites changing at all. Guarded against a missing
 * `settings` table so this can't break `php artisan migrate` on a brand
 * new install, before the table exists yet.
 */
class ApplySettingsToConfig
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            if (Schema::hasTable('settings')) {
                $this->merge(Setting::current());
            }
        } catch (\Throwable) {
            // Database not reachable yet (e.g. mid-migration) — fall back
            // to whatever config/sfa.php and .env already provide.
        }

        return $next($request);
    }

    private function merge(Setting $settings): void
    {
        config([
            'app.name' => $settings->company_name,
            'sfa.attendance.office_start_time' => $settings->attendance_office_start_time,
            'sfa.attendance.late_grace_minutes' => $settings->attendance_late_grace_minutes,
            'sfa.visits.gps_verification_radius_meters' => $settings->visit_gps_radius_meters,
            'sfa.sales.max_discount_percent' => (float) $settings->sales_max_discount_percent,
            'sfa.collections.overpayment_tolerance_percent' => (float) $settings->collection_overpayment_tolerance_percent,
            'sfa.targets.grade_thresholds' => [
                'A' => $settings->target_grade_a_min,
                'B' => $settings->target_grade_b_min,
                'C' => $settings->target_grade_c_min,
                'D' => $settings->target_grade_d_min,
            ],
            'sfa.notifications.low_performance_grades' => $settings->low_performance_grades,
            'sfa.notifications.target_reminder_days_before_month_end' => $settings->target_reminder_days_before_month_end,
            'sfa.notifications.target_reminder_min_pct' => (float) $settings->target_reminder_min_pct,
            'sfa.live_gps.stale_after_minutes' => $settings->live_gps_stale_after_minutes,
        ]);
    }
}
