<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/send-register-otp', [AdminController::class, 'sendRegisterOtp']);
Route::post('/verify-register-otp', [AdminController::class, 'verifyRegisterOtp']);

// New pending registration route for Flutter app
Route::post('/register/pending', [AuthController::class, 'registerPending']);