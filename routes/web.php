<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AvatarController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login');
});

Route::post('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', [OrderController::class, 'index'])->name('home');
    Route::post('/api/orders', [OrderController::class, 'store'])->middleware('throttle:30,1')->name('orders.store');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::get('/simulasi-gateway/{order:payment_reference}', [PaymentController::class, 'simulator'])->name('payments.simulator');

    Route::post('/avatar', [AvatarController::class, 'store'])->name('avatar.store');
    Route::get('/avatar/{user}', [AvatarController::class, 'show'])->name('avatar.show');

    Route::middleware('can:admin')->prefix('admin')->name('admin.')->controller(AdminController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/menu', 'storeMenu')->name('menu.store');
        Route::post('/menu/{menu}', 'updateMenu')->name('menu.update');
        Route::post('/orders/{order}/selesai', 'completeOrder')->name('orders.complete');
        Route::post('/users', 'storeUser')->name('users.store');
    });
});
