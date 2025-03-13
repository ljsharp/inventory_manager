<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductAttribute;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = Category::all()->keyBy('name');

        // Insert Products
        $products = [
            [
                'name' => 'Laptop',
                'description' => 'High-performance laptop',
                'sku' => 'laptop-123',
                'category_id' => $categories['Electronics']->id,
                'price' => 2500.00,
            ],
            [
                'name' => 'T-Shirt',
                'description' => 'Cotton t-shirt',
                'sku' => 'tshirt-456',
                'category_id' => $categories['Clothing']->id,
                'price' => 15.00,
            ],
            [
                'name' => 'Sneakers',
                'description' => 'Comfortable running sneakers',
                'sku' => 'sneakers-789',
                'category_id' => $categories['Footwear']->id,
                'price' => 60.00,
            ],
            [
                'name' => 'Smartphone',
                'description' => 'Latest model smartphone',
                'sku' => 'smartphone-101',
                'category_id' => $categories['Mobile Devices']->id,
                'price' => 999.00,
            ],
            [
                'name' => 'Backpack',
                'description' => 'Durable travel backpack',
                'sku' => 'backpack-202',
                'category_id' => $categories['Accessories']->id,
                'price' => 45.00,
            ],
        ];

        foreach ($products as &$product) {
            $product['created_at'] = Carbon::now();
            $product['updated_at'] = Carbon::now();
        }

        Product::insert($products);

        // Fetch inserted products
        $tShirt = Product::where('sku', 'tshirt-456')->first();
        $sneakers = Product::where('sku', 'sneakers-789')->first();
        $smartphone = Product::where('sku', 'smartphone-101')->first();

        // Insert Product Attributes
        ProductAttribute::insert([
            // T-Shirt Attributes
            [
                'product_id' => $tShirt->id,
                'name' => 'size',
                'values' => json_encode(['s', 'm', 'l', 'xl']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_id' => $tShirt->id,
                'name' => 'color',
                'values' => json_encode(['red', 'blue', 'green', 'black']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Sneakers Attributes
            [
                'product_id' => $sneakers->id,
                'name' => 'size',
                'values' => json_encode(['6', '7', '8', '9', '10']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_id' => $sneakers->id,
                'name' => 'color',
                'values' => json_encode(['white', 'black', 'blue']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Smartphone Attributes
            [
                'product_id' => $smartphone->id,
                'name' => 'storage',
                'values' => json_encode(['64gb', '128gb', '256gb']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_id' => $smartphone->id,
                'name' => 'color',
                'values' => json_encode(['black', 'white', 'gold']),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);

        // Insert Product Variants
        ProductVariant::insert([
            // T-Shirt Variants
            [
                'product_id' => $tShirt->id,
                'attributes' => json_encode(['size' => 'm', 'color' => 'red']),
                'sku' => 'tshirt-456-red-m',
                'price' => 18.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_id' => $tShirt->id,
                'attributes' => json_encode(['size' => 'l', 'color' => 'blue']),
                'sku' => 'tshirt-456-blue-l',
                'price' => 20.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Sneakers Variants
            [
                'product_id' => $sneakers->id,
                'attributes' => json_encode(['size' => '8', 'color' => 'white']),
                'sku' => 'sneakers-789-white-8',
                'price' => 65.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_id' => $sneakers->id,
                'attributes' => json_encode(['size' => '9', 'color' => 'black']),
                'sku' => 'sneakers-789-black-9',
                'price' => 70.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

            // Smartphone Variants
            [
                'product_id' => $smartphone->id,
                'attributes' => json_encode(['storage' => '128gb', 'color' => 'black']),
                'sku' => 'smartphone-101-black-128gb',
                'price' => 1050.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'product_id' => $smartphone->id,
                'attributes' => json_encode(['storage' => '256gb', 'color' => 'gold']),
                'sku' => 'smartphone-101-gold-256gb',
                'price' => 1200.00,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
