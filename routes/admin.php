<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Admin\DashboardController::class)->name('admin.dashboard');

    Route::resources([
        'warehouses' => Admin\WarehouseController::class,
        'products' => Admin\ProductController::class,
    ]);
});
