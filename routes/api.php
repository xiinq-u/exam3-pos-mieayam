<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CashierController;
use App\Http\Controllers\Api\CashierDashboardController;
use App\Http\Controllers\Api\CashierShiftController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\FinancialCategoryController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\KitchenDashboardController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OwnerDashboardController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::put('/account/password', [AccountController::class, 'updatePassword']);
    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware('role:owner');
    Route::get('/owner/dashboard', [OwnerDashboardController::class, 'index'])->middleware('role:owner');
    Route::get('/cashier/dashboard', [CashierDashboardController::class, 'index'])->middleware('role:cashier');
    Route::get('/kitchen/dashboard', [KitchenDashboardController::class, 'index'])->middleware('role:kitchen');
    Route::get('/kitchen/orders', [OrderController::class, 'kitchenOrders'])->middleware('role:owner,kitchen');

    Route::get('/products', [ProductController::class, 'index'])->middleware('role:owner');
    Route::get('/products/{product}', [ProductController::class, 'show'])->middleware('role:owner');
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('role:owner');

    Route::get('/cashier/products', [CashierController::class, 'products'])->middleware('role:cashier');
    Route::get('/cashier/cart', [CashierController::class, 'cart'])->middleware('role:cashier');
    Route::post('/cashier/add', [CashierController::class, 'addToCart'])->middleware('role:cashier');
    Route::post('/cashier/checkout', [CashierController::class, 'checkout'])->middleware('role:cashier');
    Route::get('/cashier/orders/pending', [OrderController::class, 'pendingOrders'])->middleware('role:cashier');
    Route::get('/cashier/orders/{order}', [CashierController::class, 'showOrder'])->middleware('role:cashier');
    Route::post('/orders/{order}/pay', [CashierController::class, 'pay'])->middleware('role:cashier');
    Route::patch('/orders/{order}', [CashierController::class, 'updateOrder'])->middleware('role:cashier');

    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->middleware('role:owner,kitchen');
    Route::get('/materials', [MaterialController::class, 'index'])->middleware('role:owner,kitchen');
    Route::get('/materials/low-stock', [MaterialController::class, 'lowStock'])->middleware('role:owner,kitchen');
    Route::get('/materials/{material}', [MaterialController::class, 'show'])->middleware('role:owner,kitchen');
    Route::post('/materials/{material}/adjust', [MaterialController::class, 'adjust'])->middleware('role:owner,kitchen');
    Route::get('/shifts', [CashierShiftController::class, 'index'])->middleware('role:owner,cashier');
    Route::post('/shifts/open', [CashierShiftController::class, 'open'])->middleware('role:owner,cashier');
    Route::post('/shifts/{cashierShift}/close', [CashierShiftController::class, 'close'])->middleware('role:owner,cashier');

    Route::middleware('role:owner')->group(function () {
        Route::get('/reports/inventory', [MaterialController::class, 'inventoryReport']);
        Route::post('/materials', [MaterialController::class, 'store']);
        Route::patch('/materials/{material}', [MaterialController::class, 'update']);
        Route::delete('/materials/{material}', [MaterialController::class, 'destroy']);

        Route::get('/expenses', [ExpenseController::class, 'index']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::post('/expenses/{expense}/cancel', [ExpenseController::class, 'cancel']);
        Route::get('/incomes', [IncomeController::class, 'index']);
        Route::post('/incomes', [IncomeController::class, 'store']);
        Route::post('/incomes/{income}/cancel', [IncomeController::class, 'cancel']);
        Route::get('/financial-categories', [FinancialCategoryController::class, 'index']);
        Route::post('/financial-categories', [FinancialCategoryController::class, 'store']);
        Route::delete('/financial-categories/{financialCategory}', [FinancialCategoryController::class, 'destroy']);

        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancelOrder']);
        Route::post('/orders/{order}/refund', [OrderController::class, 'refundOrder']);
        Route::get('/orders', [OrderController::class, 'index']);
        Route::get('/orders/refunded', [OrderController::class, 'getRefundedOrders']);
        Route::get('/orders/cancelled', [OrderController::class, 'getCancelledOrders']);

        Route::get('/users', [UserController::class, 'index']);
        Route::post('/users', [UserController::class, 'store']);
        Route::patch('/users/{user}', [UserController::class, 'update']);
        Route::post('/users/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::put('/products/{product}/recipe', [ProductController::class, 'updateRecipe']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::patch('/products/{product}', [ProductController::class, 'update']);
        Route::delete('/products/{product}', [ProductController::class, 'destroy']);
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::patch('/categories/{category}', [CategoryController::class, 'update']);
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

        Route::get('/reports/sales-summary', [ReportController::class, 'salesSummary']);
        Route::get('/reports/product-sales', [ReportController::class, 'productSales']);
        Route::get('/reports/order-type-sales', [ReportController::class, 'orderTypeSales']);
        Route::get('/reports/order-details', [ReportController::class, 'orderDetails']);
        Route::get('/reports/profit-loss', [ReportController::class, 'profitLoss']);
        Route::get('/reports/profit-loss/export/pdf', [ReportController::class, 'exportProfitLossPdf']);
        Route::get('/reports/profit-loss/export/excel', [ReportController::class, 'exportProfitLossExcel']);
    });
});
