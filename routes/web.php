<?php

declare(strict_types=1);

use App\Http\Controllers\Web\Admin\ActivityLogController;
use App\Http\Controllers\Web\Admin\AnnouncementController;
use App\Http\Controllers\Web\Admin\AttendanceController;
use App\Http\Controllers\Web\Admin\BrochureController;
use App\Http\Controllers\Web\Admin\CollectionEntryController;
use App\Http\Controllers\Web\Admin\DashboardController;
use App\Http\Controllers\Web\Admin\DealerController;
use App\Http\Controllers\Web\Admin\DistrictController;
use App\Http\Controllers\Web\Admin\DivisionController;
use App\Http\Controllers\Web\Admin\FaqController;
use App\Http\Controllers\Web\Admin\GpsLogController;
use App\Http\Controllers\Web\Admin\InquiryController;
use App\Http\Controllers\Web\Admin\LiveGpsController;
use App\Http\Controllers\Web\Admin\NewsController;
use App\Http\Controllers\Web\Admin\NotificationController;
use App\Http\Controllers\Web\Admin\OrderController;
use App\Http\Controllers\Web\Admin\PermissionController;
use App\Http\Controllers\Web\Admin\ProductCategoryController;
use App\Http\Controllers\Web\Admin\ProductController;
use App\Http\Controllers\Web\Admin\PromotionController;
use App\Http\Controllers\Web\Admin\ReportController;
use App\Http\Controllers\Web\Admin\RoleController;
use App\Http\Controllers\Web\Admin\SalesTeamController;
use App\Http\Controllers\Web\Admin\ServiceCenterController;
use App\Http\Controllers\Web\Admin\SettingsController;
use App\Http\Controllers\Web\Admin\TargetController;
use App\Http\Controllers\Web\Admin\TerritoryController;
use App\Http\Controllers\Web\Admin\TerritoryMapController;
use App\Http\Controllers\Web\Admin\ThanaController;
use App\Http\Controllers\Web\Admin\UserController;
use App\Http\Controllers\Web\Admin\VisitController;
use App\Http\Controllers\Web\Admin\VisitPlanController;
use App\Http\Controllers\Web\Admin\VisitRequestController;
use App\Http\Controllers\Web\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

/**
 * The full CRUD route set every simple management module needs (list, create,
 * export/import/print, bulk actions, soft-delete/restore/force-delete, status
 * toggle, edit/update/destroy). The URI param is snake_cased so it matches
 * the controller's camelCase type-hinted variable via Laravel's implicit
 * route-model-binding fallback (Str::snake($paramName)).
 */
$registerManagementRoutes = function (string $prefix, string $controller, bool $withShow = false): void {
    $param = Str::singular(str_replace('-', '_', $prefix));

    Route::prefix($prefix)->name("{$prefix}.")->group(function () use ($controller, $param, $withShow): void {
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/create', [$controller, 'create'])->name('create');
        Route::post('/', [$controller, 'store'])->name('store');
        Route::get('/export', [$controller, 'export'])->name('export');
        Route::post('/import', [$controller, 'import'])->name('import');
        Route::get('/print', [$controller, 'print'])->name('print');
        Route::post('/bulk-destroy', [$controller, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [$controller, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [$controller, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [$controller, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::patch("/{{$param}}/toggle-status", [$controller, 'toggleStatus'])->name('toggle-status');
        if ($withShow) {
            Route::get("/{{$param}}", [$controller, 'show'])->name('show');
        }
        Route::get("/{{$param}}/edit", [$controller, 'edit'])->name('edit');
        Route::put("/{{$param}}", [$controller, 'update'])->name('update');
        Route::delete("/{{$param}}", [$controller, 'destroy'])->name('destroy');
    });
};

/**
 * Customer Web Portal content management (Module 22): the same shape as
 * $registerManagementRoutes minus export/import/print/show — this is
 * marketing content edited occasionally by hand, not business data anyone
 * needs to bulk-export, report on, or print.
 *
 * URI lives under "/cms/{name}" rather than a bare "/{name}" — the portal's
 * own News/Promotions/Service-Centers/Brochures pages (Module 17) already
 * own those bare paths (e.g. GET /news), and Laravel silently drops
 * whichever named route is registered first when two routes share the
 * exact same method+URI. Since the portal pages shipped first and already
 * work, this new admin module is the one that adapts, not them — route
 * *names* stay exactly "news.index" etc. (via $namePrefix) so every
 * controller/redirect/view already written against those names is
 * unaffected; only the URI segment changes.
 */
$registerContentRoutes = function (string $uriPrefix, string $controller, string $namePrefix): void {
    $param = Str::singular(str_replace('-', '_', $namePrefix));

    Route::prefix($uriPrefix)->name("{$namePrefix}.")->group(function () use ($controller, $param): void {
        Route::get('/', [$controller, 'index'])->name('index');
        Route::get('/create', [$controller, 'create'])->name('create');
        Route::post('/', [$controller, 'store'])->name('store');
        Route::post('/bulk-destroy', [$controller, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [$controller, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [$controller, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [$controller, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::patch("/{{$param}}/toggle-status", [$controller, 'toggleStatus'])->name('toggle-status');
        Route::get("/{{$param}}/edit", [$controller, 'edit'])->name('edit');
        Route::put("/{{$param}}", [$controller, 'update'])->name('update');
        Route::delete("/{{$param}}", [$controller, 'destroy'])->name('destroy');
    });
};

Route::middleware(['auth', 'active'])->group(function () use ($registerManagementRoutes, $registerContentRoutes): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('users')->name('users.')->group(function (): void {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/export', [UserController::class, 'export'])->name('export');
        Route::post('/import', [UserController::class, 'import'])->name('import');
        Route::get('/print', [UserController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [UserController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [UserController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [UserController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::patch('/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/pdf', [UserController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    Route::resource('roles', RoleController::class)->except(['show']);

    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');

    $registerManagementRoutes('sales-teams', SalesTeamController::class);
    $registerManagementRoutes('territories', TerritoryController::class);
    $registerManagementRoutes('divisions', DivisionController::class);
    $registerManagementRoutes('districts', DistrictController::class);
    $registerManagementRoutes('thanas', ThanaController::class);
    Route::get('/districts-options', [DistrictController::class, 'options'])->name('districts.options');
    Route::get('/thanas-options', [ThanaController::class, 'options'])->name('thanas.options');
    Route::get('/territories-options', [TerritoryController::class, 'options'])->name('territories.options');
    $registerManagementRoutes('dealers', DealerController::class, withShow: true);
    Route::get('/dealers-options', [DealerController::class, 'options'])->name('dealers.options');
    Route::get('/dealers/{dealer}/pdf', [DealerController::class, 'downloadPdf'])->name('dealers.download-pdf');
    $registerManagementRoutes('product-categories', ProductCategoryController::class);
    $registerManagementRoutes('products', ProductController::class);

    /**
     * Attendance has no boolean status to toggle and its records are
     * created either by the mobile check-in API or by an admin manual
     * correction, so it gets its own route block instead of reusing
     * $registerManagementRoutes (which always wires a toggle-status route).
     */
    Route::prefix('attendance')->name('attendance.')->group(function (): void {
        Route::get('/', [AttendanceController::class, 'index'])->name('index');
        Route::get('/create', [AttendanceController::class, 'create'])->name('create');
        Route::post('/', [AttendanceController::class, 'store'])->name('store');
        Route::get('/export', [AttendanceController::class, 'export'])->name('export');
        Route::post('/import', [AttendanceController::class, 'import'])->name('import');
        Route::get('/print', [AttendanceController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [AttendanceController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [AttendanceController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [AttendanceController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [AttendanceController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::get('/{attendance}', [AttendanceController::class, 'show'])->name('show');
        Route::get('/{attendance}/edit', [AttendanceController::class, 'edit'])->name('edit');
        Route::put('/{attendance}', [AttendanceController::class, 'update'])->name('update');
        Route::delete('/{attendance}', [AttendanceController::class, 'destroy'])->name('destroy');
    });

    /**
     * GPS Tracking has no create/edit at all — pings only ever arrive via the
     * mobile ingestion API. Admins can only view the Location History report
     * and clean up bad readings (delete/restore/permanent-delete).
     */
    Route::prefix('gps-logs')->name('gps-logs.')->group(function (): void {
        Route::get('/', [GpsLogController::class, 'index'])->name('index');
        Route::get('/export', [GpsLogController::class, 'export'])->name('export');
        Route::get('/print', [GpsLogController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [GpsLogController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [GpsLogController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [GpsLogController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [GpsLogController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::delete('/{gpsLog}', [GpsLogController::class, 'destroy'])->name('destroy');
    });

    /**
     * Real-time-ish (poll-refreshed, not websocket-pushed) map of every
     * executive's latest known position — read-only, no backing owned
     * entity of its own, so this mirrors Reports/Territory Map's shape.
     */
    Route::prefix('live-gps')->name('live-gps.')->group(function (): void {
        Route::get('/', [LiveGpsController::class, 'index'])->name('index');
        Route::get('/positions', [LiveGpsController::class, 'positions'])->name('positions');
    });

    /**
     * Visit Plan status is a 3-state enum (planned/completed/cancelled), not
     * a boolean, so this gets its own route block instead of
     * $registerManagementRoutes (which always wires a toggle-status route).
     */
    Route::prefix('visit-plans')->name('visit-plans.')->group(function (): void {
        Route::get('/', [VisitPlanController::class, 'index'])->name('index');
        Route::get('/create', [VisitPlanController::class, 'create'])->name('create');
        Route::post('/', [VisitPlanController::class, 'store'])->name('store');
        Route::get('/export', [VisitPlanController::class, 'export'])->name('export');
        Route::post('/import', [VisitPlanController::class, 'import'])->name('import');
        Route::get('/print', [VisitPlanController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [VisitPlanController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [VisitPlanController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [VisitPlanController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [VisitPlanController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::get('/{visit_plan}/edit', [VisitPlanController::class, 'edit'])->name('edit');
        Route::put('/{visit_plan}', [VisitPlanController::class, 'update'])->name('update');
        Route::delete('/{visit_plan}', [VisitPlanController::class, 'destroy'])->name('destroy');
    });

    /**
     * Dealer Visits: same reasoning as Attendance — records mostly arrive
     * via the mobile check-in/out API, admins can also backfill/correct, but
     * there's no boolean status to toggle.
     */
    Route::prefix('visits')->name('visits.')->group(function (): void {
        Route::get('/', [VisitController::class, 'index'])->name('index');
        Route::get('/create', [VisitController::class, 'create'])->name('create');
        Route::post('/', [VisitController::class, 'store'])->name('store');
        Route::get('/export', [VisitController::class, 'export'])->name('export');
        Route::post('/import', [VisitController::class, 'import'])->name('import');
        Route::get('/print', [VisitController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [VisitController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [VisitController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [VisitController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [VisitController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::get('/{visit}', [VisitController::class, 'show'])->name('show');
        Route::get('/{visit}/edit', [VisitController::class, 'edit'])->name('edit');
        Route::put('/{visit}', [VisitController::class, 'update'])->name('update');
        Route::delete('/{visit}', [VisitController::class, 'destroy'])->name('destroy');
    });

    /**
     * Order: a flat one-row-per-product order record, entered mostly
     * via the mobile API but backfillable by an admin. No boolean status to
     * toggle, so this gets its own route block instead of
     * $registerManagementRoutes.
     */
    Route::prefix('orders')->name('orders.')->group(function (): void {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/export', [OrderController::class, 'export'])->name('export');
        Route::post('/import', [OrderController::class, 'import'])->name('import');
        Route::get('/print', [OrderController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [OrderController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [OrderController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [OrderController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [OrderController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::get('/{order}/pdf', [OrderController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{order}/edit', [OrderController::class, 'edit'])->name('edit');
        Route::put('/{order}', [OrderController::class, 'update'])->name('update');
        Route::delete('/{order}', [OrderController::class, 'destroy'])->name('destroy');
    });

    /**
     * Collection Entry: a flat one-row-per-payment record, entered mostly
     * via the mobile API but backfillable by an admin. No boolean status to
     * toggle, so this gets its own route block instead of
     * $registerManagementRoutes.
     */
    Route::prefix('collection-entries')->name('collection-entries.')->group(function (): void {
        Route::get('/', [CollectionEntryController::class, 'index'])->name('index');
        Route::get('/create', [CollectionEntryController::class, 'create'])->name('create');
        Route::post('/', [CollectionEntryController::class, 'store'])->name('store');
        Route::get('/export', [CollectionEntryController::class, 'export'])->name('export');
        Route::post('/import', [CollectionEntryController::class, 'import'])->name('import');
        Route::get('/print', [CollectionEntryController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [CollectionEntryController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [CollectionEntryController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [CollectionEntryController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [CollectionEntryController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::get('/{collection_entry}', [CollectionEntryController::class, 'show'])->name('show');
        Route::get('/{collection_entry}/pdf', [CollectionEntryController::class, 'downloadPdf'])->name('download-pdf');
        Route::get('/{collection_entry}/edit', [CollectionEntryController::class, 'edit'])->name('edit');
        Route::put('/{collection_entry}', [CollectionEntryController::class, 'update'])->name('update');
        Route::delete('/{collection_entry}', [CollectionEntryController::class, 'destroy'])->name('destroy');
    });

    /**
     * Targets have no boolean status to toggle — their "state" is the
     * computed achievement recalculated alongside every create/update, plus
     * an explicit recalculate action — so this gets its own route block
     * instead of $registerManagementRoutes.
     */
    Route::prefix('targets')->name('targets.')->group(function (): void {
        Route::get('/', [TargetController::class, 'index'])->name('index');
        Route::get('/create', [TargetController::class, 'create'])->name('create');
        Route::post('/', [TargetController::class, 'store'])->name('store');
        Route::get('/export', [TargetController::class, 'export'])->name('export');
        Route::post('/import', [TargetController::class, 'import'])->name('import');
        Route::get('/print', [TargetController::class, 'print'])->name('print');
        Route::post('/bulk-destroy', [TargetController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [TargetController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [TargetController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [TargetController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::get('/{target}', [TargetController::class, 'show'])->name('show');
        Route::get('/{target}/edit', [TargetController::class, 'edit'])->name('edit');
        Route::put('/{target}', [TargetController::class, 'update'])->name('update');
        Route::delete('/{target}', [TargetController::class, 'destroy'])->name('destroy');
        Route::post('/{target}/recalculate', [TargetController::class, 'recalculate'])->name('recalculate');
    });

    /**
     * Reports are read-only aggregations with no backing Eloquent entity —
     * no create/edit/delete, just a view + Excel export + PDF print per
     * report type, each gated by its own `report.{key}` permission.
     */
    Route::prefix('reports')->name('reports.')->group(function (): void {
        Route::get('/', [ReportController::class, 'index'])->name('index');

        Route::get('/attendance-summary', [ReportController::class, 'attendanceSummary'])->name('attendance-summary');
        Route::get('/attendance-summary/export', [ReportController::class, 'attendanceSummaryExport'])->name('attendance-summary.export');
        Route::get('/attendance-summary/print', [ReportController::class, 'attendanceSummaryPrint'])->name('attendance-summary.print');

        Route::get('/visit-compliance', [ReportController::class, 'visitCompliance'])->name('visit-compliance');
        Route::get('/visit-compliance/export', [ReportController::class, 'visitComplianceExport'])->name('visit-compliance.export');
        Route::get('/visit-compliance/print', [ReportController::class, 'visitCompliancePrint'])->name('visit-compliance.print');

        Route::get('/order-performance', [ReportController::class, 'orderPerformance'])->name('order-performance');
        Route::get('/order-performance/export', [ReportController::class, 'orderPerformanceExport'])->name('order-performance.export');
        Route::get('/order-performance/print', [ReportController::class, 'orderPerformancePrint'])->name('order-performance.print');

        Route::get('/collection-summary', [ReportController::class, 'collectionSummary'])->name('collection-summary');
        Route::get('/collection-summary/export', [ReportController::class, 'collectionSummaryExport'])->name('collection-summary.export');
        Route::get('/collection-summary/print', [ReportController::class, 'collectionSummaryPrint'])->name('collection-summary.print');

        Route::get('/territory-performance', [ReportController::class, 'territoryPerformance'])->name('territory-performance');
        Route::get('/territory-performance/export', [ReportController::class, 'territoryPerformanceExport'])->name('territory-performance.export');
        Route::get('/territory-performance/print', [ReportController::class, 'territoryPerformancePrint'])->name('territory-performance.print');

        Route::get('/target-achievement', [ReportController::class, 'targetAchievement'])->name('target-achievement');
        Route::get('/target-achievement/export', [ReportController::class, 'targetAchievementExport'])->name('target-achievement.export');
        Route::get('/target-achievement/print', [ReportController::class, 'targetAchievementPrint'])->name('target-achievement.print');

        Route::get('/executive-performance', [ReportController::class, 'executivePerformance'])->name('executive-performance');
        Route::get('/executive-performance/export', [ReportController::class, 'executivePerformanceExport'])->name('executive-performance.export');
        Route::get('/executive-performance/print', [ReportController::class, 'executivePerformancePrint'])->name('executive-performance.print');

        Route::get('/dealer-coverage', [ReportController::class, 'dealerCoverage'])->name('dealer-coverage');
        Route::get('/dealer-coverage/export', [ReportController::class, 'dealerCoverageExport'])->name('dealer-coverage.export');
        Route::get('/dealer-coverage/print', [ReportController::class, 'dealerCoveragePrint'])->name('dealer-coverage.print');

        Route::get('/gps-report', [ReportController::class, 'gpsReport'])->name('gps-report');
        Route::get('/gps-report/export', [ReportController::class, 'gpsReportExport'])->name('gps-report.export');
        Route::get('/gps-report/print', [ReportController::class, 'gpsReportPrint'])->name('gps-report.print');
    });

    /**
     * Every authenticated user's personal notification inbox — no Policy,
     * no CRUD; the query itself (always scoped to the current user) is the
     * authorization boundary.
     */
    Route::prefix('notifications')->name('notifications.')->group(function (): void {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
        Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });

    /**
     * Announcements are one-way broadcasts — create + list + trash only, no
     * edit/export/import/print, so this gets its own trimmed route block
     * instead of $registerManagementRoutes.
     */
    Route::prefix('announcements')->name('announcements.')->group(function (): void {
        Route::get('/', [AnnouncementController::class, 'index'])->name('index');
        Route::get('/create', [AnnouncementController::class, 'create'])->name('create');
        Route::post('/', [AnnouncementController::class, 'store'])->name('store');
        Route::post('/bulk-destroy', [AnnouncementController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('/bulk-restore', [AnnouncementController::class, 'bulkRestore'])->name('bulk-restore');
        Route::post('/{id}/restore', [AnnouncementController::class, 'restore'])->whereNumber('id')->name('restore');
        Route::delete('/{id}/force', [AnnouncementController::class, 'forceDestroy'])->whereNumber('id')->name('force-destroy');
        Route::delete('/{announcement}', [AnnouncementController::class, 'destroy'])->name('destroy');
    });

    /**
     * Audit trail — read-only, no backing owned entity beyond Spatie's
     * package Activity model, so this mirrors the Reports module's shape
     * (index + export + print, no create/edit/delete).
     */
    Route::prefix('activity-log')->name('activity-log.')->group(function (): void {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/export', [ActivityLogController::class, 'export'])->name('export');
        Route::get('/print', [ActivityLogController::class, 'print'])->name('print');
        Route::get('/{activity}', [ActivityLogController::class, 'show'])->name('show');
    });

    /**
     * A single read-only GIS dashboard page — no CRUD of its own, so just
     * one route, gated the same way Reports/Activity Log are.
     */
    Route::prefix('territory-map')->name('territory-map.')->group(function (): void {
        Route::get('/', [TerritoryMapController::class, 'index'])->name('index');
        Route::get('/{territory}', [TerritoryMapController::class, 'show'])->name('show');
    });

    /**
     * A singleton record (one row, no index/create/delete) — edit-only.
     */
    Route::prefix('settings')->name('settings.')->group(function (): void {
        Route::get('/', [SettingsController::class, 'edit'])->name('edit');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
    });

    /**
     * Inquiries and Visit Requests arrive from the Customer Web Portal
     * (guests, self-registered customers, or the mobile API) — admins only
     * view them and update their status here, never create/delete/export.
     */
    Route::prefix('inquiries')->name('inquiries.')->group(function (): void {
        Route::get('/', [InquiryController::class, 'index'])->name('index');
        Route::get('/{inquiry}', [InquiryController::class, 'show'])->name('show');
        Route::put('/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('update-status');
    });

    Route::prefix('visit-requests')->name('visit-requests.')->group(function (): void {
        Route::get('/', [VisitRequestController::class, 'index'])->name('index');
        Route::get('/{visit_request}', [VisitRequestController::class, 'show'])->name('show');
        Route::put('/{visit_request}/status', [VisitRequestController::class, 'updateStatus'])->name('update-status');
    });

    $registerContentRoutes('cms/news', NewsController::class, 'news');
    $registerContentRoutes('cms/promotions', PromotionController::class, 'promotions');
    $registerContentRoutes('cms/faqs', FaqController::class, 'faqs');
    $registerContentRoutes('cms/service-centers', ServiceCenterController::class, 'service-centers');
    $registerContentRoutes('cms/brochures', BrochureController::class, 'brochures');
});


Route::get('/clear', function () {
    Artisan::call('cache:clear');
    Artisan::call('route:clear');
    // Artisan::call('optimize');
    // Artisan::call('route:cache');
    Artisan::call('view:clear');
    // Artisan::call('config:cache');
    // Artisan::call('storage:link');
    // Artisan::call('migrate --force');
    return '<h1>Routes and Cache Cleared Successfully! </h1>';
});
