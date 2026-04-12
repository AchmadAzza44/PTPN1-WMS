<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'purchase_order_id',
        'transporter_name',
        'driver_name',
        'vehicle_plate',
        'vehicle_checklist', // cast to array/json
        'weather_condition',
        'dispatched_at',
        'do_number_manual',
        'documented_qty_kg',
        'signed_document_path',
        'status',
        'verified_at',
        'verified_by',
        'krani_name',
        'manager_name'
    ];

    protected $casts = [
        'vehicle_checklist' => 'array',
        'dispatched_at'     => 'datetime',
        'verified_at'       => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(ShipmentItem::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}
