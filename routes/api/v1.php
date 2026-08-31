<?php

declare(strict_types=1);

use App\Helpers\ApiResponse;
use App\Http\Controllers\Api\V1\AttendanceController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CollectionEntryController;
use App\Http\Controllers\Api\V1\DealerController;
use App\Http\Controllers\Api\V1\GpsLogController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\OrgStructureController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RetailerController;
use App\Http\Controllers\Api\V1\TargetController;
use App\Http\Controllers\Api\V1\VisitController;
use App\Http\Controllers\Api\V1\VisitPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
| Each module appends its own route group here as it is built
| (Users, Dealers, Products, Attendance, GPS, Visits, Orders,
| Collections, Targets, Reports, ...). All routes are versioned under
| /api/v1 and, from Module 1 onward, protected by the `auth:sanctum`
| middleware plus Spatie API permissions.
*/

Route::get('/ping', fn () => ApiResponse::success(['pong' => true], 'API v1 is reachable.'))->middleware('throttle:api');

Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:6,1');

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function (): void {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    Route::get('/sales-teams', [OrgStructureController::class, 'salesTeams']);
    Route::get('/divisions', [OrgStructureController::class, 'divisions']);
    Route::get('/districts', [OrgStructureController::class, 'districts']);
    Route::get('/thanas', [OrgStructureController::class, 'thanas']);
    Route::get('/territories', [OrgStructureController::class, 'territories']);

    Route::get('/dealers', [DealerController::class, 'index']);
    Route::post('/dealers', [DealerController::class, 'store']);
    Route::get('/dealers/{dealer}', [DealerController::class, 'show']);
    Route::get('/dealers/{dealer}/outstanding-balance', [DealerController::class, 'outstandingBalance']);
    Route::get('/dealers/{dealer}/ledger', [DealerController::class, 'ledger']);

    Route::get('/retailers', [RetailerController::class, 'index']);
    Route::get('/retailers/{retailer}', [RetailerController::class, 'show']);

    Route::get('/product-categories', [ProductCategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    Route::post('/attendance/check-in', [AttendanceController::class, 'checkIn']);
    Route::post('/attendance/check-out', [AttendanceController::class, 'checkOut']);
    Route::get('/attendance/today', [AttendanceController::class, 'today']);
    Route::get('/attendance/history', [AttendanceController::class, 'history']);

    Route::post('/gps-logs', [GpsLogController::class, 'store']);
    Route::get('/gps-logs/history', [GpsLogController::class, 'history']);

    Route::post('/visit-plans', [VisitPlanController::class, 'store']);
    Route::get('/visit-plans', [VisitPlanController::class, 'index']);

    Route::post('/visits/check-in', [VisitController::class, 'checkIn']);
    Route::post('/visits/check-out', [VisitController::class, 'checkOut']);
    Route::get('/visits/current', [VisitController::class, 'current']);
    Route::get('/visits/history', [VisitController::class, 'history']);

    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);

    Route::post('/collection-entries/send-otp', [CollectionEntryController::class, 'sendOtp']);
    Route::post('/collection-entries', [CollectionEntryController::class, 'store']);
    Route::get('/collection-entries', [CollectionEntryController::class, 'index']);

    Route::get('/targets/current', [TargetController::class, 'current']);
    Route::get('/targets', [TargetController::class, 'index']);

    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead']);
});
