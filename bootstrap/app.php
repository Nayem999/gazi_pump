<?php

use App\Helpers\ApiResponse;
use App\Http\Middleware\ApplySettingsToConfig;
use App\Http\Middleware\EnsureUserIsActive;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            Route::middleware('web')->group(base_path('routes/portal.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'active' => EnsureUserIsActive::class,
        ]);

        // Applies to every request (web + api) — business rules read via
        // config('sfa.*') are used by both the admin dashboard and the
        // mobile API.
        $middleware->append(ApplySettingsToConfig::class);

        // Two separate "guest areas" (admin 'web' guard and the customer
        // portal's 'customer' guard) means an unauthenticated redirect must
        // pick the right login page — an unauthenticated portal route
        // (named 'portal.*') sends the visitor to the portal login, every
        // other route keeps the default admin login.
        $middleware->redirectGuestsTo(function (Request $request): string {
            return $request->route()?->named('portal.*') ? route('portal.login') : route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $wantsJson = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (ValidationException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('The given data was invalid.', 422, $e->errors());
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('Unauthenticated.', 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error($e->getMessage() ?: 'This action is unauthorized.', 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('Resource not found.', 404);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('Endpoint not found.', 404);
            }
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request)) {
                return ApiResponse::error('Too many requests. Please try again later.', 429);
            }
        });

        $exceptions->render(function (Throwable $e, Request $request) use ($wantsJson) {
            if ($wantsJson($request) && ! config('app.debug')) {
                return ApiResponse::error('Server error. Please try again later.', 500);
            }
        });
    })->create();
