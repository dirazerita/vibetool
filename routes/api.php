<?php

use App\Http\Controllers\Api\LicenseValidationController;
use App\Http\Controllers\Api\MemberAuthValidationController;
use App\Http\Controllers\Api\MemberRegisterController;
use App\Http\Controllers\Api\PublicSettingController;
use Illuminate\Support\Facades\Route;

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
