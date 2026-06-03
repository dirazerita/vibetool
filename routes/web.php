<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BroadcastController as AdminBroadcastController;
use App\Http\Controllers\Admin\CommissionController as AdminCommissionController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CreatorShareController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LicenseController as AdminLicenseController;
use App\Http\Controllers\Admin\MemberCommissionController;
use App\Http\Controllers\Admin\MemberController;
use App\Http\Controllers\Admin\MessageController as AdminMessageController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SoftwareRequestController as AdminSoftwareRequestController;
use App\Http\Controllers\Admin\VideoTutorialController;
use App\Http\Controllers\Admin\WebhookDeliveryController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Dashboard\CommissionController;
use App\Http\Controllers\Dashboard\CouponController as DashboardCouponController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\LicenseController as DashboardLicenseController;
use App\Http\Controllers\Dashboard\MemberProductController;
use App\Http\Controllers\Dashboard\MessageController as DashboardMessageController;
use App\Http\Controllers\Dashboard\ProductController as DashboardProductController;
use App\Http\Controllers\Dashboard\PurchaseController as DashboardPurchaseController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\SoftwareRequestController as DashboardSoftwareRequestController;
use App\Http\Controllers\Dashboard\TeamController;
use App\Http\Controllers\Dashboard\VideoTutorialController as DashboardVideoTutorialController;
use App\Http\Controllers\Dashboard\WithdrawalController as DashboardWithdrawalController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\FreeProductController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PendingController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/p/{slug}', [HomeController::class, 'show'])->name('product.show');

// Halaman pending aktivasi member (publik & untuk user yang sudah login tapi belum aktif).
Route::get('/pending', [PendingController::class, 'show'])->name('pending');

// Webhook (no CSRF)
Route::post('/webhook/xendit', [WebhookController::class, 'xendit'])->name('webhook.xendit');
Route::post('/webhook/telegram/{secret}', [TelegramWebhookController::class, 'handle'])->name('webhook.telegram');

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
        Route::get('/checkout/manual/{order}', [CheckoutController::class, 'manual'])->name('checkout.manual');
        Route::post('/checkout/manual/{order}/proof', [CheckoutController::class, 'uploadProof'])->name('checkout.manual.proof');
        Route::post('/checkout/manual/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.manual.cancel');

        // Klaim produk gratis (tanpa pembayaran)
        Route::post('/free/{slug}/claim', [FreeProductController::class, 'claim'])->name('free.claim');
    });

    // Dashboard (member only, butuh akun aktif)
    Route::middleware('active')->prefix('dashboard')->name('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/products', [DashboardProductController::class, 'index'])->name('.products');
        Route::get('/purchases', [DashboardPurchaseController::class, 'index'])->name('.purchases');
        Route::get('/licenses', [DashboardLicenseController::class, 'index'])->name('.licenses');
        Route::get('/commissions', [CommissionController::class, 'index'])->name('.commissions');
        Route::get('/coupons', [DashboardCouponController::class, 'index'])->name('.coupons');
        Route::get('/team', [TeamController::class, 'index'])->name('.team');
        Route::get('/video-tutorials', [DashboardVideoTutorialController::class, 'index'])->name('.video-tutorials');
        Route::get('/video-tutorials/{product}', [DashboardVideoTutorialController::class, 'show'])->name('.video-tutorials.show');
        Route::get('/member-products', [MemberProductController::class, 'index'])->name('.member-products');
        Route::get('/member-products/create', [MemberProductController::class, 'create'])->name('.member-products.create');
        Route::post('/member-products', [MemberProductController::class, 'store'])->name('.member-products.store');
        Route::get('/member-products/{product}/edit', [MemberProductController::class, 'edit'])->name('.member-products.edit');
        Route::put('/member-products/{product}', [MemberProductController::class, 'update'])->name('.member-products.update');
        Route::delete('/member-products/{product}', [MemberProductController::class, 'destroy'])->name('.member-products.destroy');
        Route::get('/withdrawals', [DashboardWithdrawalController::class, 'index'])->name('.withdrawals');
        Route::post('/withdrawals', [DashboardWithdrawalController::class, 'store'])->name('.withdrawals.store');
        Route::get('/settings', [SettingController::class, 'index'])->name('.settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('.settings.update');
        Route::get('/messages', [DashboardMessageController::class, 'index'])->name('.messages');
        Route::post('/messages', [DashboardMessageController::class, 'store'])->name('.messages.store');
        Route::get('/messages/{message}/attachment', [DashboardMessageController::class, 'attachment'])->name('.messages.attachment');

        Route::get('/software-requests', [DashboardSoftwareRequestController::class, 'index'])->name('.software-requests.index');
        Route::get('/software-requests/create', [DashboardSoftwareRequestController::class, 'create'])->name('.software-requests.create');
        Route::post('/software-requests', [DashboardSoftwareRequestController::class, 'store'])->name('.software-requests.store');
        Route::get('/software-requests/{softwareRequest}', [DashboardSoftwareRequestController::class, 'show'])->name('.software-requests.show');
        Route::get('/software-requests/{softwareRequest}/attachment', [DashboardSoftwareRequestController::class, 'attachment'])->name('.software-requests.attachment');
    });
    // (penutup grup `active` di atas)

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('/products-pending', [AdminProductController::class, 'pendingProducts'])->name('products.pending');
        Route::post('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
        Route::post('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
        Route::get('/products/{product}/landing-page', [LandingPageController::class, 'edit'])->name('products.landing-page');
        Route::put('/products/{product}/landing-page', [LandingPageController::class, 'update'])->name('products.landing-page.update');
        Route::post('/products/{product}/landing-page/images', [LandingPageController::class, 'uploadImage'])->name('products.landing-page.images.upload');
        Route::delete('/products/{product}/landing-page/images/{image}', [LandingPageController::class, 'deleteImage'])->name('products.landing-page.images.delete');
        Route::post('/products/{product}/landing-page/images/reorder', [LandingPageController::class, 'reorderImages'])->name('products.landing-page.images.reorder');
        Route::post('/products/{product}/landing-page/testimonials', [LandingPageController::class, 'storeTestimonial'])->name('products.landing-page.testimonials.store');
        Route::put('/products/{product}/landing-page/testimonials/{testimonial}', [LandingPageController::class, 'updateTestimonial'])->name('products.landing-page.testimonials.update');
        Route::delete('/products/{product}/landing-page/testimonials/{testimonial}', [LandingPageController::class, 'deleteTestimonial'])->name('products.landing-page.testimonials.delete');
        Route::post('/products/{product}/landing-page/testimonials/{testimonial}/toggle', [LandingPageController::class, 'toggleTestimonial'])->name('products.landing-page.testimonials.toggle');
        Route::get('/products/{product}/video-tutorials', [VideoTutorialController::class, 'index'])->name('products.video-tutorials');
        Route::post('/products/{product}/video-tutorials', [VideoTutorialController::class, 'store'])->name('products.video-tutorials.store');
        Route::put('/products/{product}/video-tutorials/{tutorial}', [VideoTutorialController::class, 'update'])->name('products.video-tutorials.update');
        Route::delete('/products/{product}/video-tutorials/{tutorial}', [VideoTutorialController::class, 'destroy'])->name('products.video-tutorials.destroy');
        Route::post('/products/{product}/video-tutorials/{tutorial}/toggle', [VideoTutorialController::class, 'toggle'])->name('products.video-tutorials.toggle');
        Route::post('/products/{product}/video-tutorials/reorder', [VideoTutorialController::class, 'reorder'])->name('products.video-tutorials.reorder');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::post('/orders/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('orders.mark-paid');
        Route::get('/members', [MemberController::class, 'index'])->name('members');
        Route::get('/members/{user}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{user}', [MemberController::class, 'update'])->name('members.update');
        Route::patch('/members/{user}/activate', [MemberController::class, 'activate'])->name('members.activate');
        Route::patch('/members/{user}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
        Route::delete('/members/{user}', [MemberController::class, 'destroy'])->name('members.destroy');
        Route::get('/commissions', [AdminCommissionController::class, 'index'])->name('commissions');
        Route::get('/commissions/{user}', [AdminCommissionController::class, 'show'])->name('commissions.show');
        Route::resource('member-commissions', MemberCommissionController::class)->except(['show']);
        Route::get('/creator-shares', [CreatorShareController::class, 'index'])->name('creator-shares.index');
        Route::put('/creator-shares/{product}', [CreatorShareController::class, 'update'])->name('creator-shares.update');
        Route::get('/licenses', [AdminLicenseController::class, 'index'])->name('licenses');
        Route::get('/licenses/{product}', [AdminLicenseController::class, 'show'])->name('licenses.show');
        Route::post('/licenses/{product}', [AdminLicenseController::class, 'store'])->name('licenses.store');
        Route::delete('/licenses/{license}', [AdminLicenseController::class, 'destroy'])->name('licenses.destroy');
        Route::put('/licenses/{license}', [AdminLicenseController::class, 'update'])->name('licenses.update');
        Route::post('/licenses/assign-order/{order}', [AdminLicenseController::class, 'assignOrder'])->name('licenses.assign-order');
        Route::post('/licenses/{license}/reset-devices', [AdminLicenseController::class, 'resetDevices'])->name('licenses.reset-devices');
        Route::delete('/licenses/{license}/devices/{device}', [AdminLicenseController::class, 'deleteDevice'])->name('licenses.devices.destroy');
        Route::get('/products/{product}/webhook-deliveries', [WebhookDeliveryController::class, 'index'])->name('products.webhook-deliveries');
        Route::get('/products/{product}/webhook-deliveries/{delivery}', [WebhookDeliveryController::class, 'show'])->name('products.webhook-deliveries.show');
        Route::post('/products/{product}/webhook-deliveries/{delivery}/retry', [WebhookDeliveryController::class, 'retry'])->name('products.webhook-deliveries.retry');
        Route::resource('coupons', CouponController::class);
        Route::post('/coupons/generate-code', [CouponController::class, 'generateCode'])->name('coupons.generate-code');
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index'])->name('withdrawals');
        Route::post('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve'])->name('withdrawals.approve');
        Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/telegram/test', [AdminSettingController::class, 'testTelegram'])->name('settings.telegram.test');
        Route::post('/settings/telegram/setup-webhook', [AdminSettingController::class, 'setupTelegramWebhook'])->name('settings.telegram.setup-webhook');

        Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/attachment/{message}', [AdminMessageController::class, 'attachment'])->name('messages.attachment');
        Route::get('/messages/{user}', [AdminMessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{user}', [AdminMessageController::class, 'store'])->name('messages.store');

        Route::get('/software-requests', [AdminSoftwareRequestController::class, 'index'])->name('software-requests.index');
        Route::get('/software-requests/{softwareRequest}', [AdminSoftwareRequestController::class, 'show'])->name('software-requests.show');
        Route::put('/software-requests/{softwareRequest}', [AdminSoftwareRequestController::class, 'update'])->name('software-requests.update');
        Route::delete('/software-requests/{softwareRequest}', [AdminSoftwareRequestController::class, 'destroy'])->name('software-requests.destroy');
        Route::get('/software-requests/{softwareRequest}/attachment', [AdminSoftwareRequestController::class, 'attachment'])->name('software-requests.attachment');

        Route::get('/broadcasts', [AdminBroadcastController::class, 'index'])->name('broadcasts.index');
        Route::get('/broadcasts/create', [AdminBroadcastController::class, 'create'])->name('broadcasts.create');
        Route::post('/broadcasts', [AdminBroadcastController::class, 'store'])->name('broadcasts.store');
        Route::get('/broadcasts/{broadcast}/attachment', [AdminBroadcastController::class, 'attachment'])->name('broadcasts.attachment');
        Route::get('/broadcasts/{broadcast}', [AdminBroadcastController::class, 'show'])->name('broadcasts.show');

        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [AdminProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});

require __DIR__.'/auth.php';
