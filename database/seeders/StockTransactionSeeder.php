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
            ['stock_id' => 1, 'quantity' => 5, 'stock_in' => 5, 'stock_out' => 0, 'stock_transfer_id' => null, 'previous_balance' => 50, 'current_balance' => 55, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['stock_id' => 2, 'quantity' => 10, 'stock_in' => 0, 'stock_out' => 10, 'stock_transfer_id' => null, 'previous_balance' => 100, 'current_balance' => 90, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
