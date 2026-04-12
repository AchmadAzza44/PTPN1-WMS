<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShipmentItem extends Model
{
    protected $fillable = ['shipment_id', 'stock_lot_id', 'qty_loaded_kg', 'selected_detail_ids'];

    protected $casts = [
        'selected_detail_ids' => 'array',
    ];

    public function shipment()
    {
        return $this->belongsTo(Shipment::class);
    }

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }
}
