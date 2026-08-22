<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Cache;

class Setting extends BaseModel
{
    /** @use HasFactory<SettingFactory> */
    use HasFactory;

    private const CACHE_KEY = 'settings.current';

    protected $fillable = [
        'company_name',
        'company_logo',
        'company_address',
        'company_phone',
        'company_email',
        'attendance_office_start_time',
        'attendance_late_grace_minutes',
        'visit_gps_radius_meters',
        'order_max_discount_percent',
        'collection_overpayment_tolerance_percent',
        'target_grade_a_min',
        'target_grade_b_min',
        'target_grade_c_min',
        'target_grade_d_min',
        'low_performance_grades',
        'target_reminder_days_before_month_end',
        'target_reminder_min_pct',
        'live_gps_stale_after_minutes',
    ];

    protected function casts(): array
    {
        return [
            'attendance_late_grace_minutes' => 'integer',
            'visit_gps_radius_meters' => 'integer',
            'order_max_discount_percent' => 'decimal:2',
            'collection_overpayment_tolerance_percent' => 'decimal:2',
            'target_grade_a_min' => 'integer',
            'target_grade_b_min' => 'integer',
            'target_grade_c_min' => 'integer',
            'target_grade_d_min' => 'integer',
            'low_performance_grades' => 'array',
            'target_reminder_days_before_month_end' => 'integer',
            'target_reminder_min_pct' => 'decimal:2',
            'live_gps_stale_after_minutes' => 'integer',
        ];
    }

    /**
     * The one settings row, created on first access with values matching
     * config/sfa.php's own defaults so a fresh install behaves identically
     * whether or not this row has been touched yet. Read on nearly every
     * request (ApplySettingsToConfig middleware + the admin/portal logo view
     * composer), so it's cached forever and invalidated by `booted()` below
     * whenever the row is written to — there's only ever one row, so a flat
     * "forget on any save" is exact, not an approximation.
     */
    public static function current(): self
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::query()->firstOrCreate([], self::defaults()));
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'company_name' => config('app.name'),
            'company_logo' => null,
            'company_address' => null,
            'company_phone' => null,
            'company_email' => null,
            'attendance_office_start_time' => config('sfa.attendance.office_start_time'),
            'attendance_late_grace_minutes' => config('sfa.attendance.late_grace_minutes'),
            'visit_gps_radius_meters' => config('sfa.visits.gps_verification_radius_meters'),
            'order_max_discount_percent' => config('sfa.orders.max_discount_percent'),
            'collection_overpayment_tolerance_percent' => config('sfa.collections.overpayment_tolerance_percent'),
            'target_grade_a_min' => config('sfa.targets.grade_thresholds.A'),
            'target_grade_b_min' => config('sfa.targets.grade_thresholds.B'),
            'target_grade_c_min' => config('sfa.targets.grade_thresholds.C'),
            'target_grade_d_min' => config('sfa.targets.grade_thresholds.D'),
            'low_performance_grades' => config('sfa.notifications.low_performance_grades'),
            'target_reminder_days_before_month_end' => config('sfa.notifications.target_reminder_days_before_month_end'),
            'target_reminder_min_pct' => config('sfa.notifications.target_reminder_min_pct'),
            'live_gps_stale_after_minutes' => config('sfa.live_gps.stale_after_minutes'),
        ];
    }

    public function logoUrl(): ?string
    {
        return $this->company_logo ? asset('storage/'.$this->company_logo) : null;
    }
}
