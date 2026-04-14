<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShipmentGroup extends Model
{
    protected $fillable = [
        'ba_number',
        'buyer_name',
        'transporter_name',
        'driver_name',
        'vehicle_plate',
        'weather_condition',
        'dispatched_at',
        'status',
        'verified_at',
        'verified_by',
        'krani_name',
        'manager_name',
        'signed_document_path',
    ];

    protected $casts = [
        'dispatched_at' => 'datetime',
        'verified_at'   => 'datetime',
    ];

    /**
     * All shipments (each linked to one PO/Kontrak/Surat Kuasa) in this Berita Acara.
     */
    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /**
     * Total berat dari semua shipment dalam group ini.
     */
    public function getTotalWeightAttribute(): float
    {
        return $this->shipments->sum(function ($shipment) {
            return $shipment->items->sum('qty_loaded_kg');
        });
    }

    /**
     * Auto-generate nomor Berita Acara berdasarkan ID dan tanggal dispatch.
     */
    public static function generateBaNumber(int $id, ?\DateTime $date = null): string
    {
        $bulanRomawi = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV',
            5 => 'V', 6 => 'VI', 7 => 'VII', 8 => 'VIII',
            9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII'
        ];

        $date = $date ?? now();
        $bulan = (int) $date->format('n');
        $tahun = $date->format('Y');

        return 'IPMG.Bkl/BA/' . str_pad($id, 2, '0', STR_PAD_LEFT) . '/' . $bulanRomawi[$bulan] . '/' . $tahun;
    }
}
