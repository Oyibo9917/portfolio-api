<?php

use App\Http\Controllers\PaymentController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payment', [PaymentController::class, 'handlePayment']);
    Route::post('/payment/callback', [PaymentController::class, 'handleCallback'])->name('payment.callback');
    Route::get('/payment/history', [PaymentController::class, 'index']);
});
