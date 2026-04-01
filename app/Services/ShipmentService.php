<?php

namespace App\Services;

use App\Models\StockLot;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Exception;

class ShipmentService
{
    /**
     * Memproses pengiriman barang (Outbound)
     */
    public function processOutbound($poId, $lotId, $netWeight)
    {
        return DB::transaction(function () use ($poId, $lotId, $netWeight) {
            $po = PurchaseOrder::findOrFail($poId);
            $lot = StockLot::findOrFail($lotId);

            // 1. Validasi Sisa DO
            $remainingDo = $po->qty_ordered_kg - $po->qty_served_kg;
            if ($netWeight > $remainingDo) {
                throw new Exception("Berat muatan ($netWeight Kg) melebihi sisa DO ($remainingDo Kg)!");
            }

            // 2. Update Realisasi PO
            $po->increment('qty_served_kg', $netWeight);

            // 3. Update Status Heatmap (Otomatis ganti warna)
            // Logika: Tanggal ganjil = Yellow, Genap = Orange
            $newStatus = (date('d') % 2 !== 0) ? 'yellow' : 'orange';
            $lot->update([
                'status' => $newStatus,
                'outbound_at' => now()
            ]);

            return [
                'po_number' => $po->po_number,
                'new_remaining_do' => $po->qty_ordered_kg - $po->qty_served_kg,
                'status_color' => $newStatus
            ];
        });
    }
}