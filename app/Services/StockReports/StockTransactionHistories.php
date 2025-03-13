<?php

namespace App\Services\StockReports;

use App\Models\Product;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StockTransactionHistories
{
    /**
     * Get stock transaction history for a product.
     */
    public static function get(Request $request, Product $product)
    {
        $query = $product->stockTransactions()
            ->with(['stock:id,warehouse_id,product_id,product_variant_id,created_at']);

        // Filter by warehouse
        if ($warehouse = Warehouse::where('name', $request->warehouse)->select(['id', 'name'])->first()) {
            $query->whereHas('stock', fn($q) => $q->where('warehouse_id', $warehouse->id));
        }

        // Filter by product variant
        if ($request->filled('product_variant_id')) {
            $query->whereHas('stock', fn($q) => $q->has('productVariant')
                ->where('product_variant_id', $request->product_variant_id));
        } else {
            $query->whereHas('stock', fn($q) => $q->doesntHave('productVariant'));
        }

        // Filter by date range
        $startDate = Carbon::parse($request->input('start_date', today()->toDateString()))->startOfDay();
        $endDate = Carbon::parse($request->input('end_date', today()->toDateString()))->endOfDay();

        $query->whereBetween('stock_transactions.created_at', [$startDate, $endDate]);

        // Load necessary relationships
        $query->with([
            'stockTransfer:id,transferred_by,created_at',
            'stockTransfer.user:id,name',
            'stock.product:id,name',
            'stock.warehouse:id,name',
        ]);

        // Get paginated results
        $stockTransactions = $query->orderBy('stock_transactions.created_at', 'asc')
            ->paginate($request->input('per_page', 200))
            ->withQueryString()
            ->through(fn($transaction) => [
                'id' => $transaction->id,
                'stock_id' => $transaction->stock_id,
                'product_id' => $transaction->stock->product_id,
                'product_variant_id' => $transaction->stock->productVariant?->id,
                'product_name' => $transaction->stock->productVariant?->name ?? $transaction->stock->product?->name ?? 'Unknown product',
                'stock_in' => $transaction->stock_in ? $transaction->quantity : null,
                'stock_out' => $transaction->stock_out ? $transaction->quantity : null,
                'current_balance' => $transaction->current_balance,
                'previous_balance' => $transaction->previous_balance,
                'stock_transfer' => $transaction->stockTransfer,
                'warehouse' => $transaction->stock->warehouse,
                'date' => $transaction->created_at->format('Y-m-d H:i:sa'),
                'created_at' => $transaction->created_at,
            ]);

        return response()->json([
            'status' => (bool) $stockTransactions,
            'stocks' => $stockTransactions,
            'stock_metrics' => self::calculateStock($stockTransactions->items(), $product, $warehouse ?? null),
        ]);
    }

    /**
     * Calculate opening and closing balance for stock transactions.
     */
    public static function calculateStock(array $transactions, Product $product, ?Warehouse $warehouse)
    {
        $openingBalance = 0;
        $closingBalance = 0;
        $totalStockIn = 0;
        $totalStockOut = 0;
        $balance = 0;
        $stockList = [];

        // Get previous stock balance before the given date range
        $previousStockQuery = $product->stockTransactions()
            ->with('stock:id,warehouse_id,product_id,product_variant_id')
            ->whereHas('stock', function ($query) use ($warehouse) {
                if ($warehouse) {
                    $query->where('warehouse_id', $warehouse->id);
                }
                if (request('product_variant_id')) {
                    $query->where('product_variant_id', request('product_variant_id'));
                } else {
                    $query->whereNull('product_variant_id');
                }
            });

        if (!empty($transactions)) {
            $firstTransactionDate = Carbon::parse($transactions[0]['created_at']);
            $previousStockInfo = $previousStockQuery
                ->where('stock_transactions.created_at', '<', $firstTransactionDate)
                ->orderBy('stock_transactions.created_at', 'desc')
                ->first();
        } else {
            $previousStockInfo = $previousStockQuery
                ->where('stock_transactions.created_at', '<=', Carbon::yesterday()->endOfDay())
                ->orderBy('stock_transactions.created_at', 'desc')
                ->first();
        }

        $openingBalance = $previousStockInfo->current_balance ?? 0;

        // Compute stock balances
        foreach ($transactions as $key => $transaction) {
            $stockIn = (int) ($transaction['stock_in'] ?? 0);
            $stockOut = (int) ($transaction['stock_out'] ?? 0);

            $balance = $key === 0
                ? $openingBalance + $stockIn - $stockOut
                : $balance + $stockIn - $stockOut;

            $totalStockIn += $stockIn;
            $totalStockOut += $stockOut;

            $stockList[] = array_merge($transaction, ['balance' => $balance]);

            if ($key === count($transactions) - 1) {
                $closingBalance = $transaction['current_balance'];
            }
        }

        $closingBalance = count($transactions) === 0 ? $openingBalance : $closingBalance;

        return [
            'stocks' => $stockList,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'total_stock_in' => $totalStockIn,
            'total_stock_out' => $totalStockOut,
        ];
    }
}
