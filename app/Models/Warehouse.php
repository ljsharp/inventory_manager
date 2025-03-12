<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'location', 'contact_info', 'capacity'];

    public function stocks()
    {
        return $this->hasMany(Stock::class);
    }

    public function products()
    {
        return $this->hasManyThrough(
            Product::class,
            Stock::class,
            'warehouse_id',
            'id',
            'id',
            'product_id'
        );
    }

    public function productVariants()
    {
        return $this->hasManyThrough(
            ProductVariant::class,
            Stock::class,
            'warehouse_id',
            'id',
            'id',
            'product_sku_id'
        );
    }
}
