<?php

namespace Database\Seeders;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Stock::insert([
            ['warehouse_id' => 1, 'product_id' => 1, 'product_variant_id' => null, 'quantity' => 55, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['warehouse_id' => 1, 'product_id' => 2, 'product_variant_id' => 1, 'quantity' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['warehouse_id' => 2, 'product_id' => 2, 'product_variant_id' => 2, 'quantity' => 80, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
