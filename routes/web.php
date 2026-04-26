<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Dashboard\CommissionController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\ProductController as DashboardProductController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\TeamController;
use App\Http\Controllers\Dashboard\WithdrawalController as DashboardWithdrawalController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/p/{slug}', [HomeController::class, 'show'])->name('product.show');

// Halaman pending aktivasi member (publik & untuk user yang sudah login tapi belum aktif).
Route::get('/pending', [PendingController::class, 'show'])->name('pending');

// Webhook (no CSRF)
Route::post('/webhook/xendit', [WebhookController::class, 'xendit'])->name('webhook.xendit');

// Download
Route::get('/download/{token}', [DownloadController::class, 'download'])->name('download');

// Auth required
Route::middleware('auth')->group(function () {
    // Member-only (akun harus sudah diaktifkan admin)
    Route::middleware('active')->group(function () {
        // Checkout
        Route::get('/checkout/{slug}', [CheckoutController::class, 'show'])->name('checkout');
        Route::post('/checkout/{slug}', [CheckoutController::class, 'process'])->name('checkout.process');
        Route::post('/checkout/{slug}/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.apply-coupon');
        Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    });

    // Dashboard (member only, butuh akun aktif)
    Route::middleware('active')->prefix('dashboard')->name('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/products', [DashboardProductController::class, 'index'])->name('.products');
        Route::get('/commissions', [CommissionController::class, 'index'])->name('.commissions');
        Route::get('/team', [TeamController::class, 'index'])->name('.team');
        Route::get('/withdrawals', [DashboardWithdrawalController::class, 'index'])->name('.withdrawals');
        Route::post('/withdrawals', [DashboardWithdrawalController::class, 'store'])->name('.withdrawals.store');
        Route::get('/settings', [SettingController::class, 'index'])->name('.settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('.settings.update');
    });
    // (penutup grup `active` di atas)

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('/products/{product}/landing-page', [LandingPageController::class, 'edit'])->name('products.landing-page');
        Route::put('/products/{product}/landing-page', [LandingPageController::class, 'update'])->name('products.landing-page.update');
        Route::post('/products/{product}/landing-page/images', [LandingPageController::class, 'uploadImage'])->name('products.landing-page.images.upload');
        Route::delete('/products/{product}/landing-page/images/{image}', [LandingPageController::class, 'deleteImage'])->name('products.landing-page.images.delete');
        Route::post('/products/{product}/landing-page/images/reorder', [LandingPageController::class, 'reorderImages'])->name('products.landing-page.images.reorder');
        Route::post('/products/{product}/landing-page/testimonials', [LandingPageController::class, 'storeTestimonial'])->name('products.landing-page.testimonials.store');
        Route::put('/products/{product}/landing-page/testimonials/{testimonial}', [LandingPageController::class, 'updateTestimonial'])->name('products.landing-page.testimonials.update');
        Route::delete('/products/{product}/landing-page/testimonials/{testimonial}', [LandingPageController::class, 'deleteTestimonial'])->name('products.landing-page.testimonials.delete');
        Route::post('/products/{product}/landing-page/testimonials/{testimonial}/toggle', [LandingPageController::class, 'toggleTestimonial'])->name('products.landing-page.testimonials.toggle');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/members', [MemberController::class, 'index'])->name('members');
        Route::get('/members/{user}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{user}', [MemberController::class, 'update'])->name('members.update');
        Route::patch('/members/{user}/activate', [MemberController::class, 'activate'])->name('members.activate');
        Route::patch('/members/{user}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
        Route::delete('/members/{user}', [MemberController::class, 'destroy'])->name('members.destroy');
        Route::resource('coupons', CouponController::class);
        Route::post('/coupons/generate-code', [CouponController::class, 'generateCode'])->name('coupons.generate-code');
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals');
        Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
    });
});

require __DIR__ . '/auth.php';
