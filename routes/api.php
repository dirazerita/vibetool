<?php

use App\Http\Controllers\Api\LicenseValidationController;
use App\Http\Controllers\Api\MemberAuthValidationController;
use Illuminate\Support\Facades\Route;

Route::post('/license/validate', [LicenseValidationController::class, 'validate']);

// Validasi kredensial member untuk akses Produk Gratis (software).
// Rate-limited 30 req/menit/IP supaya tidak bisa dipakai brute-force.
Route::post('/auth/validate-member', [MemberAuthValidationController::class, 'validate'])
    ->middleware('throttle:30,1');
