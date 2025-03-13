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
    /**
     * Get a paginated list of products with their variants and attributes.
     * 
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getProducts()
    {
        $query = Product::query();

        // Apply search filter if 'search' is present in request
        $query->when(request('search'), function ($subQuery, $search) {
            $subQuery->where('name', 'like', '%' . $search . '%')
                ->orWhere('description', 'like', '%' . $search . '%');
        });

        return $query->with(['variants', 'attributes'])->paginate()->withQueryString();
    }

    /**
     * Create a new product along with attributes, variants, and stock.
     * 
     * @param array $data Product data including attributes and variants.
     * @return Product The newly created product instance.
     */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Create product
            $product = Product::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'category_id' => $data['category_id'] ?? null,
                'price' => $data['price'],
            ]);

            // Store product attributes
            $attributes = $data['attributes'] ?? [];
            $this->storeAttributes($product, $attributes);

            // Generate product variants based on attributes
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

    /**
     * Store attributes for a given product.
     * 
     * @param Product $product The product to associate attributes with.
     * @param array $attributes An array of attributes (name => values).
     */
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

    /**
     * Initialize stock for a product or product variant in all warehouses.
     * 
     * @param Product $product The product for which to create stock.
     * @param ProductVariant|null $variant (Optional) The product variant (if applicable).
     */
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

    /**
     * Generate all possible product variants from attributes.
     * 
     * @param array $attributes The product attributes.
     * @return array The generated variant combinations.
     */
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

    /**
     * Update an existing product, including its attributes and variants.
     * 
     * @param Product $product The product to update.
     * @param array $data The updated product data.
     * @return Product The updated product instance.
     */
    public function updateProduct(Product $product, array $data)
    {
        return DB::transaction(function () use ($product, $data) {
            // Update product details
            $product->update($data);

            // Update attributes if provided
            if (isset($data['attributes'])) {
                $product->attributes()->delete(); // Remove old attributes
                $this->storeAttributes($product, $data['attributes']);
            }

            // Get existing variants
            $existingVariants = $product->variants->keyBy(fn($variant) => json_encode($variant->attributes));

            // Update or create variants
            if (isset($data['variants'])) {
                foreach ($data['variants'] as $variantData) {
                    if (!empty($variantData['id'])) {
                        // Update existing variant by ID
                        $variant = ProductVariant::find($variantData['id']);
                        if ($variant) {
                            $variant->update($variantData);
                        }
                    } else {
                        // Create a new variant if no ID is provided
                        $variant = $product->variants()->create([
                            'attributes' => $variantData['attributes'],
                            'price' => $variantData['price'] ?? $data['price'] ?? null,
                            'sku' => $variantData['sku'] ?? '',
                        ]);
                        $this->initializeStock($product, $variant);
                    }
                }
            }

            return $product;
        });
    }

    /**
     * Delete a product along with its variants, attributes, and stock.
     * 
     * @param Product $product The product to delete.
     */
    public function deleteProduct(Product $product)
    {
        return DB::transaction(function () use ($product) {
            // Delete stock records
            $product->stocks()->delete();

            // Delete variants and their stocks
            $product->variants()->each(function ($variant) {
                $variant->stocks()->delete();
                $variant->delete();
            });

            // Delete product attributes
            $product->attributes()->delete();

            // Delete the product itself
            $product->delete();
        });
    }
}
