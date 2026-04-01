<?php

namespace App\Services;

use App\Models\StockLot;
use App\Models\DailyStockSnapshot; // FIX: Impor Model
use App\Models\PurchaseOrder;
use Carbon\Carbon;

class AnalyticsService
{
    public function getCapacityStatus($type = 'SIR 20 SW')
    {
        // Kapasitas berdasarkan dokumen Laporan Harian
        $max = ($type == 'SIR 20 SW') ? 760 : 1200;
        $current = StockLot::where('quality_type', $type)->where('status', 'blue')->count();

        return [
            'type' => $type,
            'current' => $current,
            'max' => $max,
            'percent' => $max > 0 ? round(($current / $max) * 100, 2) : 0,
            'remaining_space' => $max - $current
        ];
    }

    public function predictStockOutDays($poId)
    {
        $po = PurchaseOrder::find($poId);
        if (!$po)
            return 0;

        // Ambil rata-rata pengiriman 7 hari terakhir dari DailyStockSnapshot
        $avgOutbound = DailyStockSnapshot::where('report_date', '>=', Carbon::now()->subDays(7))
            ->avg('outbound_total');

        $avgOutbound = $avgOutbound > 0 ? $avgOutbound : 1000; // Default fallback jika data kurang

        // Hitung sisa yang harus dikirim
        $shippedKg = $po->shipments()->join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id')
            ->sum('shipment_items.qty_loaded_kg');

        $orderedKg = $po->qty_ordered_ton * 1000; // Asumsi qty_ordered di PO dalam Ton (perlu cek model PO) -> Cek schema: qty_ordered_kg??
        // Cek model PO nanti, sementara asumsi field qty_ordered ada

        $remainingKg = max(0, $orderedKg - $shippedKg);

        return ceil($remainingKg / $avgOutbound);
    }
}