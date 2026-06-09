<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin-protected routes
Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('products', ProductController::class);
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
});

// Cashier routes
Route::middleware(['auth'])->group(function () {
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');
    Route::post('/cashier/add', [CashierController::class, 'addToCart'])->name('cashier.add');
    Route::post('/cashier/remove/{product}', [CashierController::class, 'removeFromCart'])->name('cashier.remove');
    Route::post('/cashier/clear', [CashierController::class, 'clearCart'])->name('cashier.clear');
    Route::post('/cashier/checkout', [CashierController::class, 'checkout'])->name('cashier.checkout');

    Route::get('/orders/pending', [CashierController::class, 'pendingOrders'])->name('orders.pending');
    Route::get('/orders/{order}', [CashierController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/complete', [CashierController::class, 'completeOrder'])->name('orders.complete');
});

Route::view('/product', 'product')->middleware('auth')->name('product');
