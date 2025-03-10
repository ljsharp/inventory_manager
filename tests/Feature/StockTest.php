<?php

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\User;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Create necessary models
    $this->user = User::factory()->create();
    $this->warehouse = Warehouse::factory()->create();
    $this->product = Product::factory()->create();
    $this->variant = ProductVariant::factory()->create(['product_id' => $this->product->id]);

    // Create stock
    $this->stock = Stock::factory()->create([
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 50,
    ]);

    // Initialize StockService
    $this->stockService = app(StockService::class);
});

it('can successfully add stock (stock_in)', function () {
    $data = [
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 10,
        'stock_type' => 'stock_in',
    ];

    // Perform stock-in operation
    $transaction = $this->stockService->handleStockMovement($data);

    // Assertions
    expect($transaction)->toBeInstanceOf(\App\Models\StockTransaction::class);
    expect($transaction->stock_in)->toBeTrue();
    expect($transaction->stock_out)->toBeFalse();
    expect($transaction->current_balance)->toBe(60);
});

it('can successfully remove stock (stock_out)', function () {
    $data = [
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 20,
        'stock_type' => 'stock_out',
    ];

    // Perform stock-out operation
    $transaction = $this->stockService->handleStockMovement($data);

    // Assertions
    expect($transaction)->toBeInstanceOf(\App\Models\StockTransaction::class);
    expect($transaction->stock_out)->toBeTrue();
    expect($transaction->stock_in)->toBeFalse();
    expect($transaction->current_balance)->toBe(30);
});

it('fails when stock-out quantity exceeds available stock', function () {
    $data = [
        'warehouse_id' => $this->warehouse->id,
        'product_id' => $this->product->id,
        'product_variant_id' => $this->variant->id,
        'quantity' => 100, // Exceeds available stock (50)
        'stock_type' => 'stock_out',
    ];

    // Expect exception
    $this->expectException(ValidationException::class);
    $this->expectExceptionMessage('Not enough stock available.');

    $this->stockService->handleStockMovement($data);
});

it('fails when required fields are missing', function () {
    $data = [
        'warehouse_id' => null,
        'product_id' => null,
        'quantity' => null,
        'stock_type' => 'stock_in',
    ];

    // Expect exception
    $this->expectException(ValidationException::class);

    $this->stockService->handleStockMovement($data);
});
