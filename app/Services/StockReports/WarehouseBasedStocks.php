<?php

namespace App\Services\StockReports;

use App\Models\StockTransaction;
use App\Models\Warehouse;
use Carbon\Carbon;

/**
 * Class WarehouseBasedStocks
 *
 * This service retrieves stock transactions for a specific warehouse within a date range.
 */
class WarehouseBasedStocks
{
    /**
     * Retrieve warehouse stock data, including stock-in, stock-out, and balances.
     *
     * @param Warehouse $warehouse The warehouse for which stock data is retrieved.
     * @return \Illuminate\Http\JsonResponse JSON response containing stock data and totals.
     */
    public static function get(Warehouse $warehouse)
    {
        // Initialize stock transaction query with stock relationship
        $stockTransactionQuery = StockTransaction::query()->with('stock');

        // Filter transactions based on warehouse and whether the product has variants
        $stockTransactionQuery->whereHas('stock', function ($stockQuery) use ($warehouse) {
            $stockQuery->where('warehouse_id', $warehouse->id);

            if (request('is_variants') === "yes") {
                $stockQuery->has("productVariant");
            } else {
                $stockQuery->doesntHave("productVariant");
            }
        });

        // Apply date filters based on request parameters
        if (request('start_date') && request('end_date')) {
            $startDate = Carbon::parse(request('start_date'))->startOfDay();
            $endDate = Carbon::parse(request('end_date'))->endOfDay();
            $stockTransactionQuery->whereBetween('created_at', [$startDate, $endDate]);
        } elseif (request('current_date')) {
            $selectedDate = Carbon::parse(request('current_date'));
            $stockTransactionQuery->whereDate('created_at', $selectedDate);
        } else {
            $stockTransactionQuery->whereDate('created_at', Carbon::today());
        }

        // Retrieve and group stock transactions by product name
        $stockTransactions = $stockTransactionQuery
            ->orderBy('created_at', 'asc')
            ->get()
            ->groupBy('product_name');

        // Process stock transaction data
        $processedStockData = [];

        foreach ($stockTransactions as $productName => $transactions) {
            $processedStockData[] = [
                'product_name' => $productName,
                'product_id' => $transactions->first()->stock->product_id,
                'product_variant_id' => $transactions->first()->stock->productVariant?->id,
                'total_stock_in' => $transactions->where('stock_in', true)->sum('quantity'),
                'total_stock_out' => $transactions->where('stock_out', true)->sum('quantity'),
                'current_balance' => $transactions->last()->current_balance,
                'previous_balance' => $transactions->last()->previous_balance,
            ];
        }

        // Calculate total stock-in, stock-out, and balance
        $totalStockIn = array_sum(array_column($processedStockData, 'total_stock_in'));
        $totalStockOut = array_sum(array_column($processedStockData, 'total_stock_out'));
        $totalStockBalance = array_sum(array_column($processedStockData, 'current_balance'));

        // Determine sorting column and order (default: balance in ascending order)
        $sortColumn = 'current_balance';
        $sortOrder = 'asc';

        if (request('sort_by')) {
            [$sortColumn, $sortOrder] = explode('__', request('sort_by'));
        }

        // Sort stock data based on user preference
        $sortedStockData = collect($processedStockData)
            ->sortBy($sortColumn, $sortOrder === "asc" ? SORT_ASC : SORT_DESC, $sortOrder === "desc")
            ->values()
            ->toArray();

        // Return response
        return response()->json([
            'stocks' => $sortedStockData,
            'total_stock_in' => $totalStockIn,
            'total_stock_out' => $totalStockOut,
            'total_stock_balance' => $totalStockBalance,
        ]);
    }
}
