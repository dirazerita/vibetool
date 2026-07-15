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
use App\Http\Controllers\Admin\PageBuilderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\PromoTemplateController as AdminPromoTemplateController;
use App\Http\Controllers\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\Admin\SoftwareRequestController as AdminSoftwareRequestController;
use App\Http\Controllers\Admin\VideoTutorialController;
use App\Http\Controllers\Admin\WebhookDeliveryController;
use App\Http\Controllers\Admin\WithdrawalController as AdminWithdrawalController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\Dashboard\BalanceController as DashboardBalanceController;
use App\Http\Controllers\Dashboard\CommissionController;
use App\Http\Controllers\Dashboard\CouponController as DashboardCouponController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Dashboard\EmailVerificationController;
use App\Http\Controllers\Dashboard\LicenseController as DashboardLicenseController;
use App\Http\Controllers\Dashboard\LandingPageController as DashboardLandingPageController;
use App\Http\Controllers\Dashboard\MemberProductController;
use App\Http\Controllers\Dashboard\MemberVideoTutorialController;
use App\Http\Controllers\Dashboard\MessageController as DashboardMessageController;
use App\Http\Controllers\Dashboard\PageBuilderController as DashboardPageBuilderController;
use App\Http\Controllers\Dashboard\ProductController as DashboardProductController;
use App\Http\Controllers\Dashboard\PromoController as DashboardPromoController;
use App\Http\Controllers\Dashboard\PromoTemplateController as DashboardPromoTemplateController;
use App\Http\Controllers\Dashboard\PurchaseController as DashboardPurchaseController;
use App\Http\Controllers\Dashboard\SalesController as DashboardSalesController;
use App\Http\Controllers\Dashboard\SettingController;
use App\Http\Controllers\Dashboard\SoftwareRequestController as DashboardSoftwareRequestController;
use App\Http\Controllers\Dashboard\TeamController;
use App\Http\Controllers\Dashboard\TeamPurchaseController;
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
Route::post('/webhook/pakasir', [WebhookController::class, 'pakasir'])->name('webhook.pakasir');
Route::post('/webhook/telegram/{secret}', [TelegramWebhookController::class, 'handle'])->name('webhook.telegram');

// Download
Route::get('/download/{token}', [DownloadController::class, 'download'])->name('download');

// Autologin dari aplikasi Android native: signed URL 15 menit yang melogin
// member lalu redirect ke checkout. Pembayaran gateway memang berbasis web.
Route::get('/app/autologin', function (\Illuminate\Http\Request $request) {
    $user = \App\Models\User::find($request->query('user'));
    $slug = $request->query('slug');

    if (! $user || ($user->status ?? 'active') !== 'active') {
        abort(403, 'Akun tidak valid atau belum aktif.');
    }

    \Illuminate\Support\Facades\Auth::login($user);
    $request->session()->regenerate();

    return redirect()->route('checkout', $slug);
})->middleware('signed')->name('app.autologin');

// Auth required
Route::middleware('auth')->group(function () {
    // Checkout — dibuka untuk SEMUA user login (termasuk pending/baru register)
    // agar user baru bisa checkout setelah register tanpa harus diaktivasi dulu.
    Route::get('/checkout/{slug}', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('/checkout/{slug}', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/{slug}/apply-coupon', [CheckoutController::class, 'applyCoupon'])->name('checkout.apply-coupon');
    Route::get('/checkout/success/{order}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/manual/{order}', [CheckoutController::class, 'manual'])->name('checkout.manual');
    Route::post('/checkout/manual/{order}/proof', [CheckoutController::class, 'uploadProof'])->name('checkout.manual.proof');
    Route::post('/checkout/manual/{order}/cancel', [CheckoutController::class, 'cancel'])->name('checkout.manual.cancel');

    // Klaim produk gratis (tanpa pembayaran)
    Route::post('/free/{slug}/claim', [FreeProductController::class, 'claim'])->name('free.claim');

    // Member-only (akun harus sudah diaktifkan admin)
    // Dashboard, admin, dan semua menu member
    Route::middleware('active')->prefix('dashboard')->name('dashboard')->group(function () {
        Route::get('/', [DashboardController::class, 'index']);
        Route::get('/balance', [DashboardBalanceController::class, 'index'])->name('.balance');
        Route::get('/sales', [DashboardSalesController::class, 'index'])->name('.sales');
        Route::get('/products', [DashboardProductController::class, 'index'])->name('.products');
        Route::get('/purchases', [DashboardPurchaseController::class, 'index'])->name('.purchases');
        Route::get('/licenses', [DashboardLicenseController::class, 'index'])->name('.licenses');
        Route::post('/licenses/{license}/reset-devices', [DashboardLicenseController::class, 'resetDevices'])->name('.licenses.reset-devices');
        Route::get('/commissions', [CommissionController::class, 'index'])->name('.commissions');
        Route::get('/coupons', [DashboardCouponController::class, 'index'])->name('.coupons');
        Route::get('/team', [TeamController::class, 'index'])->name('.team');
        Route::get('/team-purchases', [TeamPurchaseController::class, 'index'])->name('.team-purchases');
        Route::get('/team/{member}', [TeamController::class, 'show'])->name('.team.show');
        Route::get('/video-tutorials', [DashboardVideoTutorialController::class, 'index'])->name('.video-tutorials');
        Route::get('/video-tutorials/{product}', [DashboardVideoTutorialController::class, 'show'])->name('.video-tutorials.show');
        Route::get('/member-products', [MemberProductController::class, 'index'])->name('.member-products');
        Route::get('/member-products/create', [MemberProductController::class, 'create'])->name('.member-products.create');
        Route::post('/member-products', [MemberProductController::class, 'store'])->name('.member-products.store');
        Route::get('/member-products/{product}/edit', [MemberProductController::class, 'edit'])->name('.member-products.edit');
        Route::put('/member-products/{product}', [MemberProductController::class, 'update'])->name('.member-products.update');
        Route::delete('/member-products/{product}', [MemberProductController::class, 'destroy'])->name('.member-products.destroy');

        // Page Builder per produk — mirror admin, dengan ownership check.
        Route::get('/page-builder', [DashboardPageBuilderController::class, 'index'])->name('.page-builder.index');
        Route::get('/page-builder/{product}', [DashboardPageBuilderController::class, 'edit'])->name('.page-builder.edit');
        Route::put('/page-builder/{product}', [DashboardPageBuilderController::class, 'update'])->name('.page-builder.update');
        Route::post('/page-builder/{product}/upload-image', [DashboardPageBuilderController::class, 'uploadImage'])->name('.page-builder.upload-image');

        // Landing page per produk — mirror admin, dengan ownership check.
        Route::get('/products/{product}/landing-page', [DashboardLandingPageController::class, 'edit'])->name('.products.landing-page');
        Route::put('/products/{product}/landing-page', [DashboardLandingPageController::class, 'update'])->name('.products.landing-page.update');
        Route::post('/products/{product}/landing-page/images', [DashboardLandingPageController::class, 'uploadImage'])->name('.products.landing-page.images.upload');
        Route::delete('/products/{product}/landing-page/images/{image}', [DashboardLandingPageController::class, 'deleteImage'])->name('.products.landing-page.images.delete');
        Route::post('/products/{product}/landing-page/images/reorder', [DashboardLandingPageController::class, 'reorderImages'])->name('.products.landing-page.images.reorder');
        Route::post('/products/{product}/landing-page/testimonials', [DashboardLandingPageController::class, 'storeTestimonial'])->name('.products.landing-page.testimonials.store');
        Route::put('/products/{product}/landing-page/testimonials/{testimonial}', [DashboardLandingPageController::class, 'updateTestimonial'])->name('.products.landing-page.testimonials.update');
        Route::delete('/products/{product}/landing-page/testimonials/{testimonial}', [DashboardLandingPageController::class, 'deleteTestimonial'])->name('.products.landing-page.testimonials.delete');
        Route::post('/products/{product}/landing-page/testimonials/{testimonial}/toggle', [DashboardLandingPageController::class, 'toggleTestimonial'])->name('.products.landing-page.testimonials.toggle');

        // Video tutorial per produk — mirror admin, dengan ownership check.
        Route::get('/products/{product}/video-tutorials', [MemberVideoTutorialController::class, 'index'])->name('.products.video-tutorials');
        Route::post('/products/{product}/video-tutorials', [MemberVideoTutorialController::class, 'store'])->name('.products.video-tutorials.store');
        Route::put('/products/{product}/video-tutorials/{tutorial}', [MemberVideoTutorialController::class, 'update'])->name('.products.video-tutorials.update');
        Route::delete('/products/{product}/video-tutorials/{tutorial}', [MemberVideoTutorialController::class, 'destroy'])->name('.products.video-tutorials.destroy');
        Route::post('/products/{product}/video-tutorials/{tutorial}/toggle', [MemberVideoTutorialController::class, 'toggle'])->name('.products.video-tutorials.toggle');
        Route::post('/products/{product}/video-tutorials/reorder', [MemberVideoTutorialController::class, 'reorder'])->name('.products.video-tutorials.reorder');
        Route::get('/withdrawals', [DashboardWithdrawalController::class, 'index'])->name('.withdrawals');
        Route::post('/withdrawals', [DashboardWithdrawalController::class, 'store'])->name('.withdrawals.store');
        Route::get('/email-verification', [EmailVerificationController::class, 'show'])->name('.email-verification');
        Route::post('/email-verification/send', [EmailVerificationController::class, 'sendCode'])->name('.email-verification.send');
        Route::post('/email-verification/verify', [EmailVerificationController::class, 'verify'])->name('.email-verification.verify');
        Route::get('/settings', [SettingController::class, 'index'])->name('.settings');
        Route::put('/settings', [SettingController::class, 'update'])->name('.settings.update');
        Route::get('/messages', [DashboardMessageController::class, 'index'])->name('.messages');
        Route::post('/messages', [DashboardMessageController::class, 'store'])->name('.messages.store');
        Route::get('/messages/{message}/attachment', [DashboardMessageController::class, 'attachment'])->name('.messages.attachment');

        Route::get('/promo', [DashboardPromoController::class, 'index'])->name('.promo.index');

        // CRUD template promo untuk member yang bisa upload produk
        Route::get('/promo-templates', [DashboardPromoTemplateController::class, 'index'])->name('.promo-templates.index');
        Route::get('/promo-templates/create', [DashboardPromoTemplateController::class, 'create'])->name('.promo-templates.create');
        Route::post('/promo-templates', [DashboardPromoTemplateController::class, 'store'])->name('.promo-templates.store');
        Route::get('/promo-templates/{promoTemplate}/edit', [DashboardPromoTemplateController::class, 'edit'])->name('.promo-templates.edit');
        Route::put('/promo-templates/{promoTemplate}', [DashboardPromoTemplateController::class, 'update'])->name('.promo-templates.update');
        Route::delete('/promo-templates/{promoTemplate}', [DashboardPromoTemplateController::class, 'destroy'])->name('.promo-templates.destroy');
        Route::delete('/promo-templates/{promoTemplate}/media/{media}', [DashboardPromoTemplateController::class, 'destroyMedia'])->name('.promo-templates.media.destroy');

        Route::get('/software-requests', [DashboardSoftwareRequestController::class, 'index'])->name('.software-requests.index');
        Route::get('/software-requests/create', [DashboardSoftwareRequestController::class, 'create'])->name('.software-requests.create');
        Route::post('/software-requests', [DashboardSoftwareRequestController::class, 'store'])->name('.software-requests.store');
        Route::get('/software-requests/{softwareRequest}', [DashboardSoftwareRequestController::class, 'show'])->name('.software-requests.show');
        Route::get('/software-requests/{softwareRequest}/attachment', [DashboardSoftwareRequestController::class, 'attachment'])->name('.software-requests.attachment');
    });

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('index');
        Route::resource('products', AdminProductController::class)->except(['show']);
        Route::get('/products-pending', [AdminProductController::class, 'pendingProducts'])->name('products.pending');
        Route::post('/products/{product}/approve', [AdminProductController::class, 'approve'])->name('products.approve');
        Route::post('/products/{product}/reject', [AdminProductController::class, 'reject'])->name('products.reject');
        Route::get('/page-builder', [PageBuilderController::class, 'index'])->name('page-builder.index');
        Route::get('/page-builder/{product}', [PageBuilderController::class, 'edit'])->name('page-builder.edit');
        Route::put('/page-builder/{product}', [PageBuilderController::class, 'update'])->name('page-builder.update');
        Route::post('/page-builder/{product}/upload-image', [PageBuilderController::class, 'uploadImage'])->name('page-builder.upload-image');
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
        Route::get('/orders/members/search', [OrderController::class, 'searchMembers'])->name('orders.members.search');
        Route::post('/orders/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('orders.mark-paid');
        Route::put('/orders/{order}/affiliate', [OrderController::class, 'updateAffiliate'])->name('orders.update-affiliate');
        Route::post('/orders/{order}/assign-coupon-owner', [OrderController::class, 'assignCouponOwner'])->name('orders.assign-coupon-owner');
        Route::get('/members', [MemberController::class, 'index'])->name('members');
        Route::get('/members/{user}', [MemberController::class, 'show'])->name('members.show');
        Route::get('/members/{user}/edit', [MemberController::class, 'edit'])->name('members.edit');
        Route::put('/members/{user}', [MemberController::class, 'update'])->name('members.update');
        Route::patch('/members/{user}/activate', [MemberController::class, 'activate'])->name('members.activate');
        Route::patch('/members/{user}/deactivate', [MemberController::class, 'deactivate'])->name('members.deactivate');
        Route::delete('/members/{user}', [MemberController::class, 'destroy'])->name('members.destroy');
        Route::post('/orders/{order}/reverse-payment', [MemberController::class, 'reversePayment'])->name('orders.reverse-payment');
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
        Route::post('/withdrawals/{withdrawal}/upload-proof', [AdminWithdrawalController::class, 'uploadProof'])->name('withdrawals.upload-proof');
        Route::post('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject'])->name('withdrawals.reject');
        Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings');
        Route::put('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/telegram/test', [AdminSettingController::class, 'testTelegram'])->name('settings.telegram.test');
        Route::post('/settings/telegram/setup-webhook', [AdminSettingController::class, 'setupTelegramWebhook'])->name('settings.telegram.setup-webhook');

        Route::get('/messages', [AdminMessageController::class, 'index'])->name('messages.index');
        Route::get('/messages/attachment/{message}', [AdminMessageController::class, 'attachment'])->name('messages.attachment');
        Route::get('/messages/{user}', [AdminMessageController::class, 'show'])->name('messages.show');
        Route::post('/messages/{user}', [AdminMessageController::class, 'store'])->name('messages.store');

        Route::get('/promo-templates', [AdminPromoTemplateController::class, 'index'])->name('promo-templates.index');
        Route::get('/promo-templates/create', [AdminPromoTemplateController::class, 'create'])->name('promo-templates.create');
        Route::post('/promo-templates', [AdminPromoTemplateController::class, 'store'])->name('promo-templates.store');
        Route::get('/promo-templates/{promoTemplate}/edit', [AdminPromoTemplateController::class, 'edit'])->name('promo-templates.edit');
        Route::put('/promo-templates/{promoTemplate}', [AdminPromoTemplateController::class, 'update'])->name('promo-templates.update');
        Route::delete('/promo-templates/{promoTemplate}', [AdminPromoTemplateController::class, 'destroy'])->name('promo-templates.destroy');
        Route::delete('/promo-templates/{promoTemplate}/media/{media}', [AdminPromoTemplateController::class, 'destroyMedia'])->name('promo-templates.media.destroy');
        Route::post('/promo-templates/{promoTemplate}/approve', [AdminPromoTemplateController::class, 'approve'])->name('promo-templates.approve');
        Route::post('/promo-templates/{promoTemplate}/reject', [AdminPromoTemplateController::class, 'reject'])->name('promo-templates.reject');

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
