<?php

use App\Http\Controllers\Api\LicenseValidationController;
use Illuminate\Support\Facades\Route;

Route::post('/license/validate', [LicenseValidationController::class, 'validate']);
