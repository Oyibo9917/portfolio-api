<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EnumsController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\weatherApiController;
use Illuminate\Support\Facades\Route;

@include("auth.php");
@include("payment.php");

// Publicly accessible GET routes
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show']);

// Protected routes for create, update, delete
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::patch('/products/{product}', [ProductController::class, 'update']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
    Route::post('/product/rating', [ProductController::class, 'rating']);
});

// external api
Route::get('/weather/{city}', [weatherApiController::class, 'show']);

Route::post('products/{id}/image', [ProductController::class, 'uploadImage']);
Route::post('delete_products', [ProductController::class, 'deleteMultiple']);

Route::get('/enums', [EnumsController::class, 'getAllEnums']);

Route::post('/chat', [ChatController::class, 'chat']);
Route::post('/contact', [ContactController::class, 'send']);
Route::post('/sms/send', [SmsController::class, 'send']);
Route::get('/sms/history', [SmsController::class, 'history']);

