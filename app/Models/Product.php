<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'sku', 'category_id', 'price'];

    protected static function boot()
    {
        parent::boot();

        // Generate SKU when creating a new product
        static::creating(function ($product) {
            $product->sku = self::generateProductSku($product->name);
        });

        // Regenerate SKU when updating the product name
        static::updating(function ($product) {
            if ($product->isDirty('name')) {
                $product->sku = self::generateProductSku($product->name);
            }
        });
    }

    /**
     * Generate a unique SKU based on the product name.
     */
    private static function generateProductSku($productName)
    {
        $baseSku = strtoupper(Str::slug($productName, '-'));
        $sku = "{$baseSku}-" . strtoupper(Str::random(4));

        while (self::where('sku', $sku)->exists()) {
            $sku = "{$baseSku}-" . strtoupper(Str::random(4));
        }

        return $sku;
    }

    public function stockTransactions()
    {
        return $this->hasManyThrough(StockTransaction::class, Stock::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttribute::class);
    }
}
