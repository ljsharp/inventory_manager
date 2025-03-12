<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('dashboard');

    Route::resources([
        'warehouses' => Admin\WarehouseController::class,
        'categories' => Admin\CategoryController::class,
        'products' => Admin\ProductController::class,
    ]);

    Route::get('/stocks', Admin\StockViewController::class)->name('stocks.view');

    // Stock Reports
    Route::get('/get-stock-availability', [Admin\StockReportController::class, 'getStockAvailability'])
        ->name('stock.availability');
    Route::get('/get-warehouse-based-stocks/{warehouse:name}', [
        Admin\StockReportController::class,
        'getWarehouseBasedStocks'
    ])->name('warehouse.based.stocks');
    Route::get('/get-stock-transactions/{product}', [
        Admin\StockReportController::class,
        'getStockTransactionHistories'
    ])->name('stock.transactions');

    // Stock Adjustments and Stock Transfers
    Route::get('/get-product-warehouses', [Admin\StockManagementController::class, 'getProductWarehouses'])
        ->name('get.product.warehouses');
    Route::post('/stock-adjustments', [Admin\StockManagementController::class, 'stockAdjustment'])
        ->name('stock.adjustments');
    Route::post('/stock-transfers', [Admin\StockManagementController::class, 'stockTransfers'])
        ->name('stock.transfers');
});
