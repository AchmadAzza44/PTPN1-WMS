<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockDetail extends Model
{
    protected $fillable = [
        'stock_lot_id',
        'packaging_type',
        'fdf_number',
        'pallet_number',
        'bale_range',
        'quantity_unit',
        'net_weight_kg'
    ];

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }
}