<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Product::insert([
            ['name' => 'Laptop', 'description' => 'High-performance laptop', 'sku' => 'LAPTOP-123', 'category_id' => 1, 'price' => 2500.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'T-Shirt', 'description' => 'Cotton t-shirt', 'sku' => 'TSHIRT-456', 'category_id' => 2, 'price' => 15.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);

        ProductVariant::insert([
            ['product_id' => 2, 'attributes' => json_encode(['size' => 'M', 'color' => 'Red']), 'sku' => 'TSHIRT-456-RED-M', 'price' => 18.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['product_id' => 2, 'attributes' => json_encode(['size' => 'L', 'color' => 'Blue']), 'sku' => 'TSHIRT-456-BLUE-L', 'price' => 20.00, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
