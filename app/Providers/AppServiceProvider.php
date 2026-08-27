<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Setting;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Services\NotificationService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Role/Permission are package models (not under App\Models), so they
        // need explicit policy registration instead of auto-discovery.
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(Permission::class, PermissionPolicy::class);

        Paginator::defaultView('pagination::bootstrap-5');
        Paginator::defaultSimpleView('pagination::simple-bootstrap-5');

        // Laravel 11+'s slim bootstrap/app.php no longer registers a default
        // 'api' limiter the way the old RouteServiceProvider did — without
        // this, every api/v1/* route except /auth/login (which has its own
        // flat throttle:6,1) had no rate limiting at all, including a
        // compromised/leaked Sanctum token being replayed without limit.
        // Keyed by user ID once authenticated so one user's polling can't
        // exhaust another's budget; falls back to IP for the public /ping.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Feeds the notification bell dropdown on every admin page — cheap
        // at this app's scale; Phase 19 (Hardening) can cache it if needed.
        View::composer('layouts.admin', function ($view): void {
            if ($user = Auth::user()) {
                $notifications = app(NotificationService::class);
                $view->with('unreadNotificationsCount', $notifications->unreadCount($user));
                $view->with('recentNotifications', $notifications->recent($user));
            }
        });

        // Same idea for the customer portal's account sidebar — the badge
        // next to "Notifications" there, keyed off the 'customer' guard
        // instead of the default one.
        View::composer('layouts.portal-account', function ($view): void {
            if ($account = Auth::guard('customer')->user()) {
                $notifications = app(NotificationService::class);
                $view->with('unreadNotificationsCount', $notifications->unreadCount($account));
            }
        });

        // The uploaded company logo (Settings module) replaces the default
        // truck icon on the admin sidebar brand, the login page, and the
        // customer portal's nav brand.
        View::composer(['layouts.admin', 'layouts.auth', 'layouts.portal'], function ($view): void {
            $view->with('companyLogoUrl', Setting::current()->logoUrl());
        });

        // The uploaded favicon (Settings module) is rendered as a <link
        // rel="icon"> on every top-level layout's <head>, replacing the
        // browser's default /favicon.ico lookup site-wide.
        View::composer(['layouts.admin', 'layouts.auth', 'layouts.portal', 'layouts.portal-account'], function ($view): void {
            $view->with('companyFaviconUrl', Setting::current()->faviconUrl());
        });
    }
}
