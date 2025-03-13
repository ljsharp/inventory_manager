<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = ['product_id', 'attributes', 'sku', 'price'];

    protected $casts = [
        'attributes' => 'array',
    ];

    protected $appends = ['name'];

    protected static function boot()
    {
        parent::boot();

        // Generate SKU when creating a new variant
        static::creating(function ($variant) {
            $variant->sku = self::generateVariantSku($variant);
        });

        // Regenerate SKU when product name is updated
        static::updating(function ($variant) {
            if ($variant->isDirty('attributes')) {
                $variant->sku = self::generateVariantSku($variant);
            }
        });
    }

    /**
     * Generate a unique SKU based on product name and variant attributes.
     */
    private static function generateVariantSku(ProductVariant $variant)
    {
        $productSku = $variant->product->sku;

        $attributes = is_string($variant->attributes)
            ? json_decode($variant->attributes, true)
            : $variant->attributes;

        info($variant->attributes);
        info($attributes);

        if (!is_array($attributes)) {
            $attributes = [];
        }

        $attributeValues = collect(json_decode($attributes['attributes'], true))
            ->map(fn($value) => is_string($value) ? strtoupper(substr($value, 0, 2)) : '')
            ->filter()
            ->implode('-');

        // Construct the SKU
        $sku = empty($attributeValues) ? $productSku : "{$productSku}-{$attributeValues}";

        // Ensure SKU uniqueness by appending an incremental number
        $counter = 1;
        $originalSku = $sku;
        while (self::where('sku', $sku)->exists()) {
            $sku = "{$originalSku}-" . $counter;
            $counter++;
        }

        return $sku;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getNameAttribute()
    {
        $attributes = $this->getAttribute('attributes');

        if (is_string($attributes)) {
            $attributes = json_decode($attributes, true);
        }

        if (!is_array($attributes)) {
            info("Invalid attributes: " . json_encode($attributes));
            return "Invalid Name";
        }

        ksort($attributes);

        info($attributes);

        $flattenedAttributes = [];
        foreach ($attributes as $key => $value) {
            $flattenedAttributes[] = ucwords($value);
        }

        return implode(' ', $flattenedAttributes);
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function stockTransactions()
    {
        return $this->hasManyThrough(StockTransaction::class, Stock::class);
    }
}
