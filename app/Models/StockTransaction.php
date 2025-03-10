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

    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    public function stockTransfer()
    {
        return $this->belongsTo(StockTransfer::class);
    }
}
