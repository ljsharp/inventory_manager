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
            info($data);
            $query = Stock::where('warehouse_id', $data['warehouse_id'])
                ->has('product')
                ->where('product_id', $data['product_id']);

            if ($data['product_variant_id']) {
                info("called");
                $query->has('productVariant')->where('product_variant_id', $data['product_variant_id']);
            } else {
                info("no variant");
            }

            $stock = $query->first();

            if (!$stock) {
                $stock = Stock::with('product', 'productVariant', 'warehouse')->firstOrCreate([
                    'warehouse_id' => $data['warehouse_id'],
                    'product_id' => $data['product_id'],
                    'product_variant_id' => $data['product_variant_id'] ?? null,
                ], ['quantity' => 0]);
            }

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
    static public function transferStocks(array $data, $sourceWarehouseId, $destinationWarehouseId): void
    {
        DB::transaction(function () use ($data, $sourceWarehouseId, $destinationWarehouseId) {
            foreach ($data['transfers'] as $transfer) {
                $productId = $transfer['product_id'];
                $variantId = $transfer['product_variant_id'] ?? null;
                $quantity = (int) $transfer['quantity'];
                $transferredBy = auth()->id();

                // Find source stock
                $sourceStock = Stock::where([
                    'warehouse_id' => $sourceWarehouseId,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                ])->firstOrFail();

                // Ensure source warehouse has enough stock
                if ($sourceStock->quantity < $quantity) {
                    $name = $sourceStock->product->name;
                    if ($sourceStock->productVariant) {
                        $name .= " " . $sourceStock->productVariant->name;
                    }
                    throw ValidationException::withMessages([
                        'message' => "Not enough stock ({$name}) available in the source warehouse.",
                    ]);
                }

                // Find or create destination stock
                $destinationStock = Stock::where([
                    'warehouse_id' => $destinationWarehouseId,
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                ])->first();

                if (!$destinationStock) {
                    $destinationStock = Stock::create([
                        'warehouse_id' => $destinationWarehouseId,
                        'product_id' => $productId,
                        'product_variant_id' => $variantId,
                        'quantity' => 0,
                    ]);
                }

                // Store previous balances
                $sourcePreviousBalance = $sourceStock->quantity;
                $destinationPreviousBalance = $destinationStock->quantity;

                // Perform stock transfer
                $sourceStock->decrement('quantity', $quantity);
                $destinationStock->increment('quantity', $quantity);

                // Create stock transfer record
                $stockTransfer = StockTransfer::create([
                    'product_id' => $productId,
                    'product_variant_id' => $variantId,
                    'source_warehouse_id' => $sourceWarehouseId,
                    'destination_warehouse_id' => $destinationWarehouseId,
                    'quantity' => $quantity,
                    'transferred_by' => $transferredBy,
                ]);

                // Log stock transactions
                $transactions = [
                    [
                        'stock_id' => $sourceStock->id,
                        'quantity' => $quantity,
                        'stock_in' => false,
                        'stock_out' => true,
                        'stock_transfer_id' => $stockTransfer->id,
                        'previous_balance' => $sourcePreviousBalance,
                        'current_balance' => $sourceStock->quantity,
                    ],
                    [
                        'stock_id' => $destinationStock->id,
                        'quantity' => $quantity,
                        'stock_in' => true,
                        'stock_out' => false,
                        'stock_transfer_id' => $stockTransfer->id,
                        'previous_balance' => $destinationPreviousBalance,
                        'current_balance' => $destinationStock->quantity,
                    ],
                ];

                StockTransaction::insert($transactions); // Bulk insert for efficiency
            }
        });
    }
}
