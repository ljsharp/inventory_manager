<?php

namespace App\Services\StockReports;

use App\Models\Stock;
use App\Models\Warehouse;

/**
 * Class StockAvailability
 *
 * Generates a warehouse stock report, including available stock for products and variants.
 */
class StockAvailability
{
    /**
     * Retrieves warehouse stock data and calculates stock availability.
     *
     * @return \Illuminate\Http\JsonResponse JSON response containing stock data, warehouse names, and totals.
     */
    public static function get()
    {
        // Check if filtering is based on product variants
        $hasVariants = request('is_variants') === "yes";

        // Get all warehouse names
        $warehouseList = Warehouse::pluck('name');

        // Query stock data with product and warehouse relationships
        $stockQuery = Stock::query()->with(['warehouse:id,name,location', 'product:id,name', 'productVariant']);

        // Apply filtering based on whether the product has variants
        $hasVariants ? $stockQuery->has("productVariant") : $stockQuery->doesntHave("productVariant");

        // Group stock data by product name (and variant name if applicable)
        $groupedStockData = $stockQuery->get(['id', 'product_id', 'warehouse_id', 'quantity', 'product_variant_id'])
            ->groupBy(fn($stock) => $stock->product->name . ($hasVariants && $stock->productVariant ? " " . $stock->productVariant->name : ''));

        /**
         * Process and structure physical stock data.
         * Each product's stock is summed across warehouses.
         */
        $physicalStockData = $groupedStockData->map(function ($stockItems, $productName) use ($warehouseList) {
            // Compute stock quantity per warehouse
            $warehouseStockCounts = collect($warehouseList)->mapWithKeys(fn($warehouse) => [
                $warehouse => $stockItems->where('warehouse.name', $warehouse)->sum('quantity')
            ])->toArray();

            return [
                'product' => $productName,
                'product_id' => $stockItems->first()->product_id,
                'product_variant_id' => $stockItems->first()->product_variant_id,
                ...$warehouseStockCounts,
                'current_stock' => $stockItems->sum('quantity'), // Total available stock per product
            ];
        })->values()->toArray();

        // Retrieve existing product IDs that already have stock
        $existingProductIds = array_column($physicalStockData, $hasVariants ? 'product_variant_id' : 'product_id');

        // Query for products that have no stock in any warehouse (missing stock)
        $missingStockQuery = Stock::query()->with(['product:id,name', 'warehouse:id,name,location', 'productVariant']);
        $hasVariants
            ? $missingStockQuery->has('productVariant')->whereNotIn('product_variant_id', $existingProductIds)
            : $missingStockQuery->whereNotIn('product_id', $existingProductIds);

        // Group missing stock data by product name (and variant if applicable)
        $groupedMissingStock = $missingStockQuery->get(['id', 'product_id', 'warehouse_id', 'quantity', 'product_variant_id'])
            ->groupBy(fn($stock) => $stock->product->name . ($hasVariants && $stock->productVariant ? " " . $stock->productVariant->name : ''));

        /**
         * Process missing stock data.
         * These products do not currently exist in stock but are still part of the inventory.
         */
        $missingStockData = $groupedMissingStock->map(function ($stockItems, $productName) use ($warehouseList) {
            // Compute stock quantity per warehouse
            $warehouseStockCounts = collect($warehouseList)->mapWithKeys(fn($warehouse) => [
                $warehouse => $stockItems->where('warehouse.name', $warehouse)->sum('quantity')
            ])->toArray();

            return [
                'product' => $productName,
                'product_id' => $stockItems->first()->product_id,
                'product_variant_id' => $stockItems->first()->product_variant_id,
                ...$warehouseStockCounts,
                'current_stock' => $stockItems->sum('quantity'), // Total available stock per product
            ];
        })->values()->toArray();

        // Merge physical stock and missing stock data
        $mergedStockData = collect([...$missingStockData, ...$physicalStockData]);

        /**
         * Calculate available stock per product.
         * This ensures all stocks are tracked correctly.
         */
        $finalStockReport = collect($mergedStockData)->map(fn($stock) => [
            ...$stock,
            'available_stock' => $stock['current_stock'], // Available stock is the same as current stock
        ])->sortByDesc('available_stock')->values()->toArray();

        // Compute total available stock
        $totalAvailableStock = array_sum(array_column($finalStockReport, 'available_stock'));

        return response()->json([
            'stocks' => $finalStockReport,
            'warehouses' => $warehouseList,
            'total_available_stock' => $totalAvailableStock,
        ]);
    }
}
