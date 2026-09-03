<?php

use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderDetailController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('users', UserController::class);
Route::apiResource('items', ItemController::class);
Route::apiResource('favorites', FavoriteController::class);
Route::apiResource('carts', CartController::class);
Route::apiResource('orders', OrderController::class);
Route::apiResource('notifications', NotificationController::class);
Route::post('midtrans/notification', [MidtransWebhookController::class, 'handle']);

Route::get('order-details', [OrderDetailController::class, 'index']);
Route::post('order-details', [OrderDetailController::class, 'store']);
Route::patch('orders/{id_order}/items/{id_item}', [OrderDetailController::class, 'update']);
Route::delete('orders/{id_order}/items/{id_item}', [OrderDetailController::class, 'destroy']);
