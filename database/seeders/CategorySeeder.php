<?php

namespace Database\Seeders;

use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            ['name' => 'Electronics', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Clothing', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Footwear', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Mobile Devices', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Accessories', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
