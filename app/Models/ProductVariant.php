<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'attributes', 'sku', 'price'];

    protected $casts = ['attributes' => 'array'];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($variant) {
            $product = $variant->product;
            $count = $product->variants()->count() + 1;
            $variant->sku = $product->sku . '-VAR-' . str_pad($count, 2, '0', STR_PAD_LEFT);
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
