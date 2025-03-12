<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::get('/get-product-warehouses', [StockManagementController::class, 'getProductWarehouses'])
//     ->name('get.product.warehouses');
// Route::apiResource('warehouses', WarehouseController::class);
// Route::get('/get-stock-availability', [StockReportController::class, 'getStockAvailability']);
// Route::get('/get-warehouse-based-stocks/{warehouse:name}', [StockReportController::class, 'getWarehouseBasedStocks']);
// Route::get('/get-stock-transactions/{product}', [StockReportController::class, 'getStockTransactionHistories']);
