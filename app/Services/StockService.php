<?php

namespace App\Services;

use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Handle stock-in (restocking) and stock-out (selling).
     *
     * @param array $data
     * @return StockTransaction
     * @throws ValidationException
     */
    static public function handleStockAdjustment(array $data): StockTransaction
    {
        return DB::transaction(function () use ($data) {
            // Find stock based on product or variant
            $stock = Stock::where('warehouse_id', $data['warehouse_id'])
                ->where('product_id', $data['product_id'])
                ->where('product_variant_id', $data['product_variant_id'] ?? null)
                ->firstOrFail();

            // Store previous balance
            $previousBalance = $stock->quantity;

            // Handle stock movement based on stock_type
            if ($data['stock_type'] === 'stock_in') {
                $stock->increment('quantity', $data['quantity']);
            } elseif ($data['stock_type'] === 'stock_out') {
                if ($stock->quantity < $data['quantity']) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Not enough stock available.',
                    ]);
                }
                $stock->decrement('quantity', $data['quantity']);
            }

            // Log stock transaction
            return StockTransaction::create([
                'stock_id' => $stock->id,
                'quantity' => $data['quantity'],
                'stock_in' => $data['stock_type'] === 'stock_in',
                'stock_out' => $data['stock_type'] === 'stock_out',
                'stock_transfer_id' => null,
                'previous_balance' => $previousBalance,
                'current_balance' => $stock->quantity,
            ]);
        });
    }

    /**
     * Handle stock transfers between warehouses.
     *
     * @param array $data
     * @throws ValidationException
     */
    static public function transferStocks(array $data): void
    {
        DB::transaction(function () use ($data) {
            foreach ($data as $transfer) {
                // Find stocks in source and destination warehouses
                $sourceStock = Stock::with('product', 'productVariant', 'warehouse')->where('warehouse_id', $transfer['source_warehouse_id'])
                    ->where('product_id', $transfer['product_id'])
                    ->where('product_variant_id', $transfer['product_variant_id'] ?? null)
                    ->firstOrFail();

                $destinationStock = Stock::with('product', 'productVariant', 'warehouse')->firstOrCreate([
                    'warehouse_id' => $transfer['destination_warehouse_id'],
                    'product_id' => $transfer['product_id'],
                    'product_variant_id' => $transfer['product_variant_id'] ?? null,
                ], ['quantity' => 0]);

                // Ensure source warehouse has enough stock
                if ($sourceStock->quantity < (int) $transfer['quantity']) {
                    throw ValidationException::withMessages([
                        'quantity' => "Not enough stock ({$sourceStock->product->name}) available in source warehouse.",
                    ]);
                }

                // Store previous balances
                $sourcePreviousBalance = $sourceStock->quantity;
                $destinationPreviousBalance = $destinationStock->quantity;

                // Perform stock transfer
                $sourceStock->decrement('quantity', $transfer['quantity']);
                $destinationStock->increment('quantity', $transfer['quantity']);

                // Create stock transfer record
                $stockTransfer = StockTransfer::create([
                    'product_id' => $transfer['product_id'],
                    'product_variant_id' => $transfer['product_variant_id'] ?? null,
                    'source_warehouse_id' => $transfer['source_warehouse_id'],
                    'destination_warehouse_id' => $transfer['destination_warehouse_id'],
                    'quantity' => $transfer['quantity'],
                    'transferred_by' => $transfer['transferred_by'],
                ]);

                // Log stock transactions
                StockTransaction::create([
                    'stock_id' => $sourceStock->id,
                    'quantity' => $transfer['quantity'],
                    'stock_in' => false,
                    'stock_out' => true,
                    'stock_transfer_id' => $stockTransfer->id,
                    'previous_balance' => $sourcePreviousBalance,
                    'current_balance' => $sourceStock->quantity,
                ]);

                StockTransaction::create([
                    'stock_id' => $destinationStock->id,
                    'quantity' => $transfer['quantity'],
                    'stock_in' => true,
                    'stock_out' => false,
                    'stock_transfer_id' => $stockTransfer->id,
                    'previous_balance' => $destinationPreviousBalance,
                    'current_balance' => $destinationStock->quantity,
                ]);
            }
        });
    }
}
