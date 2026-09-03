<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerOrderController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::get('/', StorefrontController::class)->name('home');
Route::post('/register', RegistrationController::class)->name('register.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('orders/history', [CustomerOrderController::class, 'history'])->name('orders.history');
    Route::get('orders/on-process', [CustomerOrderController::class, 'process'])->name('orders.process');
    Route::get('orders/favorites', [CustomerOrderController::class, 'favorites'])->name('orders.favorites');
    Route::post('favorites/{itemId}/toggle', [CustomerOrderController::class, 'toggleFavorite'])->name('favorites.toggle');
    Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::post('orders/{order}/pay', [CustomerOrderController::class, 'pay'])->name('orders.pay');
    Route::get('orders/{order}/payment-status', [CustomerOrderController::class, 'paymentStatus'])->name('orders.payment-status');
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('orders', [AdminController::class, 'orders'])->name('orders');
        Route::get('items', [AdminController::class, 'items'])->name('items');
        Route::post('items', [AdminController::class, 'storeItem'])->name('items.store');
        Route::put('items/{item}', [AdminController::class, 'updateItem'])->name('items.update');
        Route::delete('items/{item}', [AdminController::class, 'destroyItem'])->name('items.destroy');
        Route::get('users', [AdminController::class, 'users'])->name('users');
        Route::post('users', [AdminController::class, 'storeUser'])->name('users.store');
        Route::put('users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
        Route::delete('users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    });
});

require __DIR__.'/settings.php';
