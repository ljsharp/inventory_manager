<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StockRequest;
use App\Http\Requests\StockTransferRequest;
use App\Models\Product;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Http\Request;

class StockManagementController extends Controller
{
    public function getProductWarehouses()
    {
        $warehouses = Warehouse::with('products.variants')->get()->map(function ($warehouse) {
            return [
                'id' => $warehouse->id,
                'name' => $warehouse->name,
                'products' => $warehouse->products->unique('id')->values(),
            ];
        })->values();
        return response()->json([
            'products' => Product::select(['id', 'name'])->with('variants')->get(),
            'warehouses' => $warehouses,
        ]);
    }


    public function stockAdjustment(StockRequest $request)
    {
        $validated = $request->validated();
        $stockTransaction = StockService::handleStockAdjustment($validated);

        return back()->with('success', 'Stock adjusted successfully');
    }

    public function stockTransfers(StockTransferRequest $request)
    {
        $validated = $request->validated();
        StockService::transferStocks(
            $validated,
            $request->source_warehouse_id,
            $request->destination_warehouse_id
        );

        return back()->with('success', 'Stocks transferred successfully');
    }
}
