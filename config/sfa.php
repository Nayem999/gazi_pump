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

    'tally' => [
        // A Sync Agent is expected to heartbeat every minute or so; a
        // connection whose last heartbeat is older than this is shown as
        // offline on the Integration Dashboard rather than connected.
        'heartbeat_stale_after_minutes' => (int) env('SFA_TALLY_HEARTBEAT_STALE_MINUTES', 5),

        // The month a Tally financial year opens on (1 = January).
        // Tally only serves data inside the company's *active* period, so a
        // ledger pull asking for dates before this year's opening returns
        // nothing useful — see TallyLedgerSyncService::financialYearStart().
        // This customer's "GDN Tally" company runs 1-Sep to 31-Aug (verified
        // from its own Company Info screen), hence September rather than the
        // Indian-default April or a calendar year.
        'financial_year_start_month' => (int) env('SFA_TALLY_FY_START_MONTH', 9),

        // Single-depot stock mode: the `depots.code` that receives the whole
        // company's Tally stock.
        //
        // The live customer's TallyPrime 7.1 cannot serve item x godown
        // quantities — godown is absent from voucher/collection exports, and
        // its Godown Summary / Stock Summary reports give per-godown and
        // per-item totals separately, which don't determine the per-cell
        // values product_stocks is keyed on (see
        // docs/tally-sfa-integration.md). Until a Tally consultant exposes
        // an item x godown report, stock is attributed here.
        //
        // Leave blank to disable: godown-less rows are then skipped and
        // counted rather than landing against an arbitrary depot. Setting
        // this does NOT affect rows that do carry a godown — those still
        // match their depot by tally_guid.
        'default_depot_code' => env('SFA_TALLY_DEFAULT_DEPOT_CODE'),

        // SFA -> Tally master push: creating Ledgers, Stock Items and
        // Godowns *inside* the customer's accounting system for records
        // that exist only here.
        //
        // Off by default, deliberately. Every other sync in this system
        // either reads from Tally or writes a voucher an operator already
        // approved; this one creates permanent chart-of-accounts and
        // inventory masters, which an accountant may consider theirs to
        // own. A deployment must opt in knowingly.
        'master_push_enabled' => (bool) env('SFA_TALLY_MASTER_PUSH_ENABLED', false),

        // The Tally groups a pushed master is filed under. These must match
        // the customer's own chart of accounts, and they mirror the agent's
        // TALLY_DEALER_LEDGER_GROUP / TALLY_RETAILER_LEDGER_GROUP — a
        // dealer pushed into a group the pull doesn't read would sync out
        // and never come back.
        'dealer_ledger_group' => env('SFA_TALLY_DEALER_LEDGER_GROUP', 'Sundry Debtors'),
        'retailer_ledger_group' => env('SFA_TALLY_RETAILER_LEDGER_GROUP'),

        // The Tally stock group new Stock Items are filed under. Blank by
        // default on purpose: Tally stores its reserved roots with a
        // leading space (" Primary"), which XML will not carry, and it
        // rejects the space-less spelling — the first live godown push
        // failed with "Godown 'Primary' does not exist!". Left blank, the
        // element is omitted and Tally files the item under its own root.
        // Set it to a real group the customer already uses (this company
        // has "Pump" and "TV") to file pushed products there instead.
        'stock_item_group' => env('SFA_TALLY_STOCK_ITEM_GROUP'),

        // Tally requires a unit on a stock item that will hold quantities,
        // and rejects one it does not already know — so this must name a
        // unit that exists in the company. "PCS" is what this customer's
        // existing stock items use (read live).
        'stock_item_unit' => env('SFA_TALLY_STOCK_ITEM_UNIT', 'PCS'),
    ],
];
