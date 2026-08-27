<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Customer Web Portal Routes
|--------------------------------------------------------------------------
| Built out in Module 17 (Home, About, Product Catalog, Dealer Locator,
| Customer Registration/Login/Dashboard, Inquiries, Visit Requests, News,
| Promotions, FAQ, Service Center, Contact Us). Registered via the `then`
| callback in bootstrap/app.php under the `web` middleware group.
*/

use App\Http\Controllers\Web\Portal\Auth\ForgotPasswordController;
use App\Http\Controllers\Web\Portal\Auth\LoginController;
use App\Http\Controllers\Web\Portal\Auth\RegisterController;
use App\Http\Controllers\Web\Portal\Auth\ResetPasswordController;
use App\Http\Controllers\Web\Portal\BrochureController;
use App\Http\Controllers\Web\Portal\ContactController;
use App\Http\Controllers\Web\Portal\DashboardController;
use App\Http\Controllers\Web\Portal\DealerLocatorController;
use App\Http\Controllers\Web\Portal\FaqController;
use App\Http\Controllers\Web\Portal\HomeController;
use App\Http\Controllers\Web\Portal\InquiryController;
use App\Http\Controllers\Web\Portal\NewsController;
use App\Http\Controllers\Web\Portal\NotificationController;
use App\Http\Controllers\Web\Portal\PaymentController;
use App\Http\Controllers\Web\Portal\ProductController;
use App\Http\Controllers\Web\Portal\ProfileController;
use App\Http\Controllers\Web\Portal\PromotionController;
use App\Http\Controllers\Web\Portal\PurchaseController;
use App\Http\Controllers\Web\Portal\ServiceCenterController;
use App\Http\Controllers\Web\Portal\VisitRequestController;
use Illuminate\Support\Facades\Route;

Route::name('portal.')->group(function (): void {
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/warranty', [HomeController::class, 'warranty'])->name('warranty');

    /**
     * URI is "/catalog", not "/products" — the admin backend already owns
     * the bare "/products" path for its CRUD module, and Laravel's route
     * collection silently drops whichever named route is registered first
     * when two routes share the exact same method+URI, so this avoids
     * clobbering the admin's "products.index"/"products.show" routes.
     */
    Route::get('/catalog', [ProductController::class, 'index'])->name('products.index');
    Route::get('/catalog/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/dealer-locator', [DealerLocatorController::class, 'index'])->name('dealer-locator');

    Route::get('/news', [NewsController::class, 'index'])->name('news.index');
    Route::get('/news/{article}', [NewsController::class, 'show'])->name('news.show');

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');

    Route::get('/faq', [FaqController::class, 'index'])->name('faq.index');

    Route::get('/service-centers', [ServiceCenterController::class, 'index'])->name('service-centers.index');

    Route::get('/brochures', [BrochureController::class, 'index'])->name('brochures.index');
    Route::get('/brochures/{brochure}/download', [BrochureController::class, 'download'])->name('brochures.download');

    Route::get('/contact', [ContactController::class, 'create'])->name('contact');
    Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

    Route::middleware('guest:customer')->prefix('customer')->group(function (): void {
        Route::get('/login', [LoginController::class, 'create'])->name('login');
        Route::post('/login', [LoginController::class, 'store'])->name('login.store');
        Route::get('/register', [RegisterController::class, 'create'])->name('register');
        Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

        Route::get('/forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
        /**
         * Module 23 hardening: the 'customers' password broker only throttles
         * repeat requests for the *same* email (config/auth.php, 60s window)
         * — nothing stopped one client from POSTing thousands of distinct
         * fake emails per minute, each triggering a real mail-send attempt
         * for any that matched. `throttle:6,1` (per IP) mirrors the same
         * limiter already used on the API's own login route.
         */
        Route::post('/forgot-password', [ForgotPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.email');
        Route::get('/reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [ResetPasswordController::class, 'store'])->middleware('throttle:6,1')->name('password.update');
    });

    Route::middleware('auth:customer')->prefix('customer')->group(function (): void {
        Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('/purchases/{salesEntry}', [PurchaseController::class, 'show'])->name('purchases.show');

        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');

        Route::prefix('notifications')->name('notifications.')->group(function (): void {
            Route::get('/', [NotificationController::class, 'index'])->name('index');
            Route::post('/read-all', [NotificationController::class, 'markAllRead'])->name('read-all');
            Route::post('/{id}/read', [NotificationController::class, 'markRead'])->name('read');
            Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        });

        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');

        Route::get('/visit-requests', [VisitRequestController::class, 'index'])->name('visit-requests.index');
        Route::get('/visit-requests/create', [VisitRequestController::class, 'create'])->name('visit-requests.create');
        Route::post('/visit-requests', [VisitRequestController::class, 'store'])->name('visit-requests.store');
    });
});
