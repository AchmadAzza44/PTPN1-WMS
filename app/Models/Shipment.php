<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_group_id',
        'purchase_order_id',
        'transporter_name',
        'driver_name',
        'vehicle_plate',
        'vehicle_checklist', // cast to array/json
        'weather_condition',
        'dispatched_at',
        'do_number_manual',
        'surat_kuasa_number',
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

    /**
     * Relasi ke ShipmentGroup (Berita Acara parent).
     * Nullable untuk backward compatibility.
     */
    public function group()
    {
        return $this->belongsTo(ShipmentGroup::class, 'shipment_group_id');
    }
}
