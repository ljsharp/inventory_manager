<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\StockTransaction;
use App\Models\StockTransfer;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create user, warehouses, and product
    $this->user = User::factory()->create();
    $this->sourceWarehouse = Warehouse::factory()->create();
    $this->destinationWarehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);

    // Create stock in source warehouse
    $this->sourceStock = Stock::factory()->create([
        'warehouse_id' => $this->sourceWarehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 50, // Initial stock
    ]);

    // Initialize StockService
    $this->stockService = app(StockService::class);
});

it('can successfully transfer stock between warehouses', function () {
    $data = [
        'source_warehouse_id' => $this->sourceWarehouse->id,
        'destination_warehouse_id' => $this->destinationWarehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 20,
        'transferred_by' => $this->user->id,
    ];

    // Perform stock transfer
    $transfer = $this->stockService->handleStockTransfer($data);

    // Assertions
    expect($transfer)->toBeInstanceOf(StockTransfer::class);
    expect($transfer->source_warehouse_id)->toBe($this->sourceWarehouse->id);
    expect($transfer->destination_warehouse_id)->toBe($this->destinationWarehouse->id);
    expect($transfer->quantity)->toBe(20);

    // Verify source stock reduction
    $this->sourceStock->refresh();
    expect($this->sourceStock->quantity)->toBe(30);

    // Verify destination stock increment
    $destinationStock = Stock::where([
        'warehouse_id' => $this->destinationWarehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
    ])->first();

    expect($destinationStock)->not->toBeNull();
    expect($destinationStock->quantity)->toBe(20);

    // Verify stock transactions
    $transaction = StockTransaction::where('stock_transfer_id', $transfer->id)->get();
    expect($transaction)->toHaveCount(2); // One for source (stock_out), one for destination (stock_in)
});

it('fails to transfer stock if not enough stock is available in source warehouse', function () {
    $data = [
        'source_warehouse_id' => $this->sourceWarehouse->id,
        'destination_warehouse_id' => $this->destinationWarehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 100, // More than available
        'transferred_by' => $this->user->id,
    ];

    // Expect validation error
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Not enough stock available in source warehouse.');

    $this->stockService->handleStockTransfer($data);
});

it('fails to transfer stock if quantity is not positive', function () {
    $data = [
        'source_warehouse_id' => $this->sourceWarehouse->id,
        'destination_warehouse_id' => $this->destinationWarehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 0, // Invalid quantity
        'transferred_by' => $this->user->id,
    ];

    // Expect validation error
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Quantity must be greater than zero.');

    $this->stockService->handleStockTransfer($data);
});
