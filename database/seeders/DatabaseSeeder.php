<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@inventorymanager.com',
            'password' => bcrypt('password'),
        ]);

        User::factory()->create([
            'name' => 'Staff',
            'email' => 'staff@inventorymanager.com',
            'password' => bcrypt('password'),
        ]);

        $this->call([
            WarehouseSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            StockSeeder::class,
            StockTransferSeeder::class,
            StockTransactionSeeder::class,
        ]);
    }
}
