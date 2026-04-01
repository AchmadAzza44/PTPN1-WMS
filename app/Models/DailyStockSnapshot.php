<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyStockSnapshot extends Model
{
    protected $fillable = [
        'report_date', 
        'quality_type', 
        'opening_stock', 
        'inbound_total', 
        'outbound_total', 
        'closing_stock'
    ];
}