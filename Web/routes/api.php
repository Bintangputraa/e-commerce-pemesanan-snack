<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use Illuminate\Support\Facades\Route;

Route::post('auth/register', [AuthController::class, 'register'])->name('api.auth.register');
Route::post('auth/login', [AuthController::class, 'login'])->middleware('throttle:5,1')->name('api.auth.login');
Route::post('midtrans/notification', [MidtransWebhookController::class, 'handle'])->name('api.midtrans.notification');
Route::apiResource('items', ItemController::class)->only(['index', 'show']);

Route::middleware('api.token')->group(function (): void {
    Route::get('auth/me', [AuthController::class, 'me'])->name('api.auth.me');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');

    Route::apiResource('carts', CartController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('favorites', FavoriteController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('notifications', NotificationController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::apiResource('order-details', OrderDetailController::class)->only(['index', 'store']);
    Route::patch('orders/{id_order}/items/{id_item}', [OrderDetailController::class, 'update']);
    Route::delete('orders/{id_order}/items/{id_item}', [OrderDetailController::class, 'destroy']);

    Route::post('checkout', [CheckoutController::class, 'store'])->name('api.checkout.store');
    Route::post('orders/{order}/pay', [CustomerOrderController::class, 'pay'])->name('api.orders.pay');
    Route::get('orders/{order}/payment-status', [CustomerOrderController::class, 'paymentStatus'])
        ->name('api.orders.payment-status');

    Route::middleware('admin')->group(function (): void {
        Route::post('items', [ItemController::class, 'store'])->name('api.items.store');
        Route::put('items/{item}', [ItemController::class, 'update'])->name('api.items.update');
        Route::patch('items/{item}', [ItemController::class, 'update']);
        Route::delete('items/{item}', [ItemController::class, 'destroy'])->name('api.items.destroy');
    });
    Route::apiResource('users', UserController::class)->middleware('admin');
});
