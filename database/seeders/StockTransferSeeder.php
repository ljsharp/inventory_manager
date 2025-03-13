<?php

namespace Database\Seeders;

use App\Models\StockTransfer;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockTransferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockTransfer::insert([
            ['source_warehouse_id' => 1, 'destination_warehouse_id' => 2, 'product_id' => 1, 'product_variant_id' => null, 'quantity' => 5, 'transferred_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['source_warehouse_id' => 2, 'destination_warehouse_id' => 1, 'product_id' => 2, 'product_variant_id' => 1, 'quantity' => 10, 'transferred_by' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
