<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    protected $fillable = [
        'stock_lot_id',
        'user_id',
        'type',
        'weight_adjusted_kg',
        'weight_before_kg',
        'weight_after_kg',
        'reason',
    ];

    protected $casts = [
        'weight_adjusted_kg' => 'float',
        'weight_before_kg' => 'float',
        'weight_after_kg' => 'float',
    ];

    // Relationships
    public function stockLot()
    {
        return $this->belongsTo(StockLot::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
