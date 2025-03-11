<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function getProducts()
    {
        $query = Product::query();

        $query->when(request('search'), function ($subQuery, $search) {
            $subQuery->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        });

        return $query->with(['variants', 'attributes'])->paginate()->withQueryString();
    }

    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'price' => $data['price'],
            ]);

            // Store Attributes
            $attributes = $data['attributes'] ?? [];
            $this->storeAttributes($product, $attributes);

            // Generate Variants
            if ($attributes) {
                $variants = $this->generateVariants($attributes);
                if (!empty($variants)) {
                    foreach ($variants as $variantData) {
                        $variant = $product->variants()->create([
                            'attributes' => $variantData,
                            'price' => $data['price'] ?? null,
                        ]);
                        $this->initializeStock($product, $variant);
                    }
                } else {
                    $this->initializeStock($product);
                }
            }

            return $product;
        });
    }

    private function storeAttributes(Product $product, array $attributes)
    {
        foreach ($attributes as $name => $values) {
            ProductAttribute::create([
                'product_id' => $product->id,
                'name' => $name,
                'values' => $values,
            ]);
        }
    }

    private function initializeStock(Product $product, ?ProductVariant $variant = null)
    {
        $warehouses = Warehouse::all();
        foreach ($warehouses as $warehouse) {
            Stock::create([
                'warehouse_id' => $warehouse->id,
                'product_id' => $product->id,
                'product_variant_id' => $variant ? $variant->id : null,
                'quantity' => 0,
            ]);
        }
    }

    private function generateVariants(array $attributes): array
    {
        $result = [[]];

        foreach ($attributes as $name => $values) {
            $temp = [];
            foreach ($result as $variant) {
                foreach ($values as $value) {
                    $temp[] = array_merge($variant, [$name => $value]);
                }
            }
            $result = $temp;
        }

        return $result;
    }

    public function updateProduct(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {
            $product->update($data);

            // Update Attributes
            if (isset($data['attributes'])) {
                $product->attributes()->delete();
                $this->storeAttributes($product, $data['attributes']);
            }

            // Update or Create Variants
            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    ProductVariant::updateOrCreate(
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
            $product->attributes()->delete();
            $product->delete();
        });
    }
}
