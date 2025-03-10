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
    public function handleStockMovement(array $data): StockTransaction
    {
        // Validate input data
        $validatedData = validator($data, [
            'product_id' => ['required_without:product_variant_id', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'stock_type' => ['required', 'in:stock_in,stock_out'],
        ])->validate();

        return DB::transaction(function () use ($validatedData) {
            // Find stock based on product or variant
            $stock = Stock::where('warehouse_id', $validatedData['warehouse_id'])
                ->where('product_id', $validatedData['product_id'])
                ->where('product_variant_id', $validatedData['product_variant_id'] ?? null)
                ->firstOrFail();

            // Store previous balance
            $previousBalance = $stock->quantity;

            // Handle stock movement based on stock_type
            if ($validatedData['stock_type'] === 'stock_in') {
                $stock->increment('quantity', $validatedData['quantity']);
            } elseif ($validatedData['stock_type'] === 'stock_out') {
                if ($stock->quantity < $validatedData['quantity']) {
                    throw ValidationException::withMessages([
                        'quantity' => 'Not enough stock available.',
                    ]);
                }
                $stock->decrement('quantity', $validatedData['quantity']);
            }

            // Log stock transaction
            return StockTransaction::create([
                'stock_id' => $stock->id,
                'quantity' => $validatedData['quantity'],
                'stock_in' => $validatedData['stock_type'] === 'stock_in',
                'stock_out' => $validatedData['stock_type'] === 'stock_out',
                'stock_transfer_id' => null,
                'previous_balance' => $previousBalance,
                'current_balance' => $stock->quantity,
            ]);
        });
    }

    /**
     * Handle stock transfer between warehouses.
     *
     * @param array $data
     * @return StockTransfer
     * @throws ValidationException
     */
    public function transferStock(array $data): StockTransfer
    {
        // Validate input data
        $validatedData = validator($data, [
            'product_id' => ['required_without:product_variant_id', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'exists:product_variants,id'],
            'source_warehouse_id' => ['required', 'exists:warehouses,id'],
            'destination_warehouse_id' => ['required', 'exists:warehouses,id', 'different:source_warehouse_id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'transferred_by' => ['required', 'exists:users,id'],
        ])->validate();

        return DB::transaction(function () use ($validatedData) {
            // Find stocks in source and destination warehouses
            $sourceStock = Stock::with('product', 'productVariant', 'warehouse')->where('warehouse_id', $validatedData['source_warehouse_id'])
                ->where('product_id', $validatedData['product_id'])
                ->where('product_variant_id', $validatedData['product_variant_id'] ?? null)
                ->firstOrFail();

            $destinationStock = Stock::with('product', 'productVariant', 'warehouse')->firstOrCreate([
                'warehouse_id' => $validatedData['destination_warehouse_id'],
                'product_id' => $validatedData['product_id'],
                'product_variant_id' => $validatedData['product_variant_id'] ?? null,
            ], ['quantity' => 0]);

            // Ensure source warehouse has enough stock
            if ($sourceStock->quantity < (int) $validatedData['quantity']) {
                throw ValidationException::withMessages([
                    'quantity' => "Not enough stock ({$sourceStock->product->name}) available in source warehouse.",
                ]);
            }

            // Store previous balances
            $sourcePreviousBalance = $sourceStock->quantity;
            $destinationPreviousBalance = $destinationStock->quantity;

            // Perform stock transfer
            $sourceStock->decrement('quantity', $validatedData['quantity']);
            $destinationStock->increment('quantity', $validatedData['quantity']);

            // Create stock transfer record
            $stockTransfer = StockTransfer::create([
                'product_id' => $validatedData['product_id'],
                'product_variant_id' => $validatedData['product_variant_id'] ?? null,
                'source_warehouse_id' => $validatedData['source_warehouse_id'],
                'destination_warehouse_id' => $validatedData['destination_warehouse_id'],
                'quantity' => $validatedData['quantity'],
                'transferred_by' => $validatedData['transferred_by'],
            ]);

            // Log stock transactions
            StockTransaction::create([
                'stock_id' => $sourceStock->id,
                'quantity' => $validatedData['quantity'],
                'stock_in' => false,
                'stock_out' => true,
                'stock_transfer_id' => $stockTransfer->id,
                'previous_balance' => $sourcePreviousBalance,
                'current_balance' => $sourceStock->quantity,
            ]);

            StockTransaction::create([
                'stock_id' => $destinationStock->id,
                'quantity' => $validatedData['quantity'],
                'stock_in' => true,
                'stock_out' => false,
                'stock_transfer_id' => $stockTransfer->id,
                'previous_balance' => $destinationPreviousBalance,
                'current_balance' => $destinationStock->quantity,
            ]);

            return $stockTransfer;
        });
    }
}
