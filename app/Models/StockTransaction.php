<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_id',
        'quantity',
        'stock_in',
        'stock_out',
        'stock_transfer_id',
        'previous_balance',
        'current_balance',
    ];

    protected $casts = [
        "quantity" => "integer",
        "previous_balance" => "integer",
        "current_balance" => "integer",
        "stock_out" => "boolean",
        "stock_out" => "boolean",
    ];

    protected $appends = ['product_name'];

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function getProductNameAttribute()
    {
        $name = $this->stock->product->name;
        if ($this->stock->productVariant) {
            $name .= ' ' . $this->stock->productVariant->name;
        }
        return $name;
    }

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }
}
