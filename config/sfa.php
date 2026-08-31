<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| SFA Business Rules
|--------------------------------------------------------------------------
| Tunable constants for sales-force-automation business logic that don't
| belong in a Settings-table module (Phase 16) yet but need one place to
| live so they aren't scattered across services as magic numbers.
*/
return [
    'attendance' => [
        'office_start_time' => env('SFA_OFFICE_START_TIME', '09:00'),
        'office_end_time' => env('SFA_OFFICE_END_TIME', '18:00'),
        'late_grace_minutes' => (int) env('SFA_LATE_GRACE_MINUTES', 15),

        // Bangladesh's standard government/private-sector weekend since the
        // 2025 shift to a two-day week. Not yet consumed by any absence or
        // reporting logic — see the Settings screen doc-comment.
        'weekend_days' => ['Friday', 'Saturday'],
    ],

    'visits' => [
        // A check-in more than this far from the dealer's registered GPS
        // pin is flagged as unverified rather than rejected outright — field
        // GPS accuracy is imperfect and shops move within a building/lot.
        'gps_verification_radius_meters' => (int) env('SFA_VISIT_GPS_RADIUS_METERS', 300),
    ],

    'orders' => [
        // A discount larger than this percentage of the line subtotal needs
        // manager approval outside this system for now — the entry itself is
        // rejected rather than silently capped, so the rep re-enters the
        // correct figure or escalates.
        'max_discount_percent' => (float) env('SFA_ORDERS_MAX_DISCOUNT_PERCENT', 20),
    ],

    'collections' => [
        // A collection is allowed to exceed the dealer's outstanding
        // balance by up to this percentage — field collections sometimes
        // round up or advance-pay slightly — but a collection wildly beyond
        // what's owed almost always means the wrong dealer or amount was
        // entered, so it's rejected rather than silently accepted.
        'overpayment_tolerance_percent' => (float) env('SFA_COLLECTION_OVERPAYMENT_PERCENT', 10),
    ],

    'targets' => [
        // The minimum overall achievement percentage required for each
        // letter grade, checked from A down to D — anything below the D
        // threshold is an F. Structured rather than env-driven since it's a
        // small ordered map, not a single tunable number.
        'grade_thresholds' => [
            'A' => 90,
            'B' => 75,
            'C' => 60,
            'D' => 40,
        ],
    ],

    'notifications' => [
        // Achievement grades severe enough to alert the executive and their
        // manager mid-cycle, rather than waiting for the monthly report.
        'low_performance_grades' => ['D', 'F'],

        // How many days before a target's month ends to start reminding an
        // executive who is still behind pace.
        'target_reminder_days_before_month_end' => (int) env('SFA_TARGET_REMINDER_DAYS_BEFORE_END', 5),

        // An achievement below this overall percentage counts as "behind
        // pace" once the reminder window (above) opens.
        'target_reminder_min_pct' => (float) env('SFA_TARGET_REMINDER_MIN_PCT', 70),
    ],

    'live_gps' => [
        // A ping older than this is shown as "last known position" rather
        // than an actively live marker — the executive's phone may be off,
        // out of signal, or simply hasn't pinged again yet.
        'stale_after_minutes' => (int) env('SFA_LIVE_GPS_STALE_MINUTES', 30),
    ],

    'movement' => [
        // The Movement Summary report splits a day's GPS trail into
        // "active" vs "idle" time by walking consecutive pings — a gap
        // where the later ping's own reported speed is at or below this
        // (km/h) counts as idle (stationary/GPS drift), otherwise active.
        'idle_speed_threshold_kmh' => (float) env('SFA_MOVEMENT_IDLE_SPEED_KMH', 1.0),
    ],
];
