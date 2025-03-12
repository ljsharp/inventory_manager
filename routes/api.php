<?php

use App\Http\Controllers\Admin\StockReportController;
use App\Http\Controllers\Admin\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route::apiResource('warehouses', WarehouseController::class);
// Route::get('/get-stock-availability', [StockReportController::class, 'getStockAvailability']);
// Route::get('/get-warehouse-based-stocks/{warehouse:name}', [StockReportController::class, 'getWarehouseBasedStocks']);
// Route::get('/get-stock-transactions/{product}', [StockReportController::class, 'getStockTransactionHistories']);
