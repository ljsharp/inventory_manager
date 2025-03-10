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
        static::creating(function ($product) {
            do {
                $sku = 'PROD-' . strtoupper(Str::random(5));
            } while (self::where('sku', $sku)->exists());

            $product->sku = $sku;
        });
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
}
