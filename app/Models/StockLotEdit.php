<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLotEdit extends Model
{
    protected $fillable = [
        'stock_lot_id',
        'user_id',
        'field_changed',
        'old_value',
        'new_value',
        'reason',
    ];

    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
