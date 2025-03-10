<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create($data);

            if (!empty($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variant = $product->variants()->create($variantData);
                    $this->initializeStock($product, $variant);
                }
            } else {
                $this->initializeStock($product);
            }

            return $product;
        });
    }

    private function initializeStock(Product $product, ?ProductVariant $variant = null)
    {
        $warehouses = \App\Models\Warehouse::all();
        foreach ($warehouses as $warehouse) {
            Stock::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $variant ? null : $product->id,
                'product_variant_id' => $variant ? $variant->id : null,
                'quantity' => 0,
            ]);
        }
    }

    public function updateProduct(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    $variant = ProductVariant::updateOrCreate(
                        ['sku' => $variantData['sku']],
                        array_merge($variantData, ['product_id' => $product->id])
                    );
                }
            }

            return $product;
        });
    }

    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {
            $product->stocks()->delete();
            $product->variants()->each(function ($variant) {
                $variant->stocks()->delete();
                $variant->delete();
            });
            $product->delete();
        });
    }
}
