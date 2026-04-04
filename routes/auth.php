<?php

use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::post('/password/forgot', [AuthController::class, 'sendResetLink']);
Route::get('password/reset/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/password/change', [AuthController::class, 'changePassword']);
    Route::get('auth/user',[AuthController::class, 'getAuthUser']);
});

// google register/login
// Route::post('auth/google', [AuthController::class, 'googleLogin']);

// Route::group(['middleware' => ['web']], function () {
    Route::get('auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
// });