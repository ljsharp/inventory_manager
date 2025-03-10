<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Stock>
 */
class StockFactory extends Factory
{
    protected $model = Stock::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'product_id' => Product::factory(),
            'product_variant_id' => null, // Some stocks may not have variants
            'quantity' => $this->faker->numberBetween(10, 100),
        ];
    }

    public function withVariant()
    {
        return $this->state(function (array $attributes) {
            return [
                'product_variant_id' => ProductVariant::factory(),
            ];
        });
    }
}
