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
});
