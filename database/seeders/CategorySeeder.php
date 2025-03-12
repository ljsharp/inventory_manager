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
            ['name' => 'Electronics', 'description' => 'Electronic devices and gadgets', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Clothing', 'description' => 'Men and women clothing', 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
