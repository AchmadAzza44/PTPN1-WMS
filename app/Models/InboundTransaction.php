<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InboundTransaction extends Model
{
    // Daftarkan kolom agar bisa diisi oleh Controller
    protected $fillable = [
        'stock_lot_id',
        'ticket_number',
        'vehicle_plate',
        'driver_name',
        'gross_weight',
        'tare_weight',
        'net_weight',
        'weigh_in_at',
        'weigh_out_at',
        'ai_ocr_data',
        'photo_path'
    ];

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }
}