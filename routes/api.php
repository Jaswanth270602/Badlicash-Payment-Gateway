<?php

use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PaymentLinkController;
use App\Http\Controllers\Api\RefundController;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public webhook receiver (no auth required)
Route::post('/webhooks/receive', [WebhookController::class, 'receive'])
    ->name('api.webhooks.receive');

// API v1 routes with API key authentication
Route::prefix('v1')->middleware(['App\Http\Middleware\AuthenticateApiKey'])->group(function () {
    
    // Payment endpoints
    Route::post('/payment', [PaymentController::class, 'createPayment'])
        ->name('api.payment.create');
    
    Route::get('/payment/{transactionId}/verify', [PaymentController::class, 'verifyPayment'])
        ->name('api.payment.verify');

    // Order endpoints
    Route::get('/orders', [OrderController::class, 'index'])
        ->name('api.orders.index');
    
    Route::get('/orders/{orderId}', [OrderController::class, 'show'])
        ->name('api.orders.show');

    // Transaction endpoints
    Route::get('/transactions', [TransactionController::class, 'index'])
        ->name('api.transactions.index');
    
    Route::get('/transactions/{transactionId}', [TransactionController::class, 'show'])
        ->name('api.transactions.show');

    // Refund endpoints
    Route::post('/refunds', [RefundController::class, 'create'])
        ->name('api.refunds.create');
    
    Route::get('/refunds', [RefundController::class, 'index'])
        ->name('api.refunds.index');

    // Payment Link endpoints
    Route::post('/payment_links', [PaymentLinkController::class, 'create'])
        ->name('api.payment_links.create');
    
    Route::get('/payment_links', [PaymentLinkController::class, 'index'])
        ->name('api.payment_links.index');

    // Webhook test endpoint
    Route::post('/webhooks/test', [WebhookController::class, 'test'])
        ->name('api.webhooks.test');
});

