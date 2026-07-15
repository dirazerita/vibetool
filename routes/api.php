<?php

use App\Http\Controllers\Api\AppAuthController;
use App\Http\Controllers\Api\AppDataController;
use App\Http\Controllers\Api\LicenseValidationController;
use App\Http\Controllers\Api\MemberAuthValidationController;
use App\Http\Controllers\Api\MemberRegisterController;
use App\Http\Controllers\Api\PublicSettingController;
use Illuminate\Support\Facades\Route;

// ===== API Aplikasi Android Native (folder "ANDROID NATIVE") =====
Route::prefix('app')->group(function () {
    // Publik
    Route::post('/login', [AppAuthController::class, 'login'])->middleware('throttle:15,1');
    Route::get('/products', [AppDataController::class, 'products'])->middleware('throttle:60,1');
    Route::get('/products/{slug}', [AppDataController::class, 'product'])->middleware('throttle:60,1');

    // Butuh Bearer token
    Route::middleware('auth.apitoken')->group(function () {
        Route::post('/logout', [AppAuthController::class, 'logout']);
        Route::get('/me', [AppAuthController::class, 'me']);
        Route::get('/dashboard', [AppDataController::class, 'dashboard']);
        Route::get('/licenses', [AppDataController::class, 'licenses']);
        Route::post('/licenses/{license}/reset-devices', [AppDataController::class, 'resetLicenseDevices']);
        Route::get('/commissions', [AppDataController::class, 'commissions']);
        Route::get('/team', [AppDataController::class, 'team']);
        Route::get('/purchases', [AppDataController::class, 'purchases']);
        Route::get('/checkout-link/{slug}', [AppDataController::class, 'checkoutLink']);
        Route::get('/web-link', [AppDataController::class, 'webLink']);
    });
});

Route::post('/license/validate', [LicenseValidationController::class, 'validate']);

// Validasi kredensial member untuk akses Produk Gratis (software).
// Rate-limited 30 req/menit/IP supaya tidak bisa dipakai brute-force.
Route::post('/auth/validate-member', [MemberAuthValidationController::class, 'validate'])
    ->middleware('throttle:30,1');

// Registrasi member via aplikasi software klien. Akun dibuat dengan status `pending`
// dan harus diaktifkan admin via WhatsApp sebelum bisa login.
// Rate-limited 10 req/menit/IP supaya tidak bisa dipakai spam pendaftaran.
Route::post('/auth/register', [MemberRegisterController::class, 'store'])
    ->middleware('throttle:10,1');

// Nomor WhatsApp admin untuk tombol "Hubungi Admin" di aplikasi software klien.
// Rate-limited 60 req/menit/IP — endpoint cuma return 1 nomor, aman dipanggil sering.
Route::get('/setting/whatsapp-admin', [PublicSettingController::class, 'whatsappAdmin'])
    ->middleware('throttle:60,1');
