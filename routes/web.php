<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

// Halaman pertama yang muncul saat website dibuka.
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Bagian login dan logout.
// GET /login menampilkan halaman login, POST /login memproses email dan password.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

// Logout harus memakai POST supaya lebih aman, bukan sekadar link biasa.
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Bagian khusus admin.
// Semua route di dalam grup ini hanya bisa dibuka oleh akun admin.
Route::middleware([AdminMiddleware::class])->group(function () {
    // Halaman ringkasan data penjualan, pesanan, dan menu.
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route otomatis untuk kelola produk: lihat, tambah, simpan, edit, update, hapus.
    Route::resource('products', ProductController::class);

    // Halaman laporan riwayat pembelian dan pendapatan.
    Route::get('/reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('/reports/revenue', [ReportController::class, 'revenue'])->name('reports.revenue');
});

// Bagian kasir.
// Semua route di dalam grup ini hanya bisa dibuka setelah user login.
Route::middleware(['auth'])->group(function () {
    // Halaman kasir untuk memilih menu dan melihat keranjang.
    Route::get('/cashier', [CashierController::class, 'index'])->name('cashier.index');

    // Tombol tambah, hapus, kosongkan keranjang, dan buat pesanan.
    Route::post('/cashier/add', [CashierController::class, 'addToCart'])->name('cashier.add');
    Route::post('/cashier/remove/{product}', [CashierController::class, 'removeFromCart'])->name('cashier.remove');
    Route::post('/cashier/clear', [CashierController::class, 'clearCart'])->name('cashier.clear');
    Route::post('/cashier/checkout', [CashierController::class, 'checkout'])->name('cashier.checkout');

    // Halaman antrean pesanan, detail pesanan, dan proses pembayaran sampai selesai.
    Route::get('/orders/pending', [CashierController::class, 'pendingOrders'])->name('orders.pending');
    Route::get('/orders/{order}', [CashierController::class, 'showOrder'])->name('orders.show');
    Route::post('/orders/{order}/complete', [CashierController::class, 'completeOrder'])->name('orders.complete');
});

// Halaman produk lama/sederhana. Tetap wajib login untuk membukanya.
Route::view('/product', 'product')->middleware('auth')->name('product');
