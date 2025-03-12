<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Warehouse::insert([
            ['name' => 'Accra W1', 'location' => 'Accra, Ghana', 'contact_info' => '0240000001', 'capacity' => 1000, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
            ['name' => 'Kumasi W2', 'location' => 'Kumasi, Ghana', 'contact_info' => '0240000002', 'capacity' => 1500, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now()],
        ]);
    }
}
