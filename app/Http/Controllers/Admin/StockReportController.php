<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockReports\StockAvailability;
use App\Services\StockReports\StockTransactionHistories;
use App\Services\StockReports\WarehouseBasedStocks;
use Illuminate\Http\Request;

class StockReportController extends Controller
{
    public function getStockAvailability()
    {
        return StockAvailability::get();
    }

    public function getWarehouseBasedStocks(Warehouse $warehouse)
    {
        return WarehouseBasedStocks::get($warehouse);
    }

    public function getStockTransactionHistories(Request $request, Product $product)
    {
        return StockTransactionHistories::get($request, $product);
    }
}
