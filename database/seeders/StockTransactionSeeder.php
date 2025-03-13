<?php

namespace Database\Seeders;

use App\Models\StockTransaction;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockTransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StockTransaction::insert([
            ['stock_id' => 1, 'quantity' => 55, 'stock_in' => 1, 'stock_out' => 0, 'stock_transfer_id' => null, 'previous_balance' => 0, 'current_balance' => 55, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['stock_id' => 2, 'quantity' => 10, 'stock_in' => 0, 'stock_out' => 1, 'stock_transfer_id' => null, 'previous_balance' => 20, 'current_balance' => 10, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
