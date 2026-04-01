<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Data Stok Real-time
        $stocks = \App\Models\StockLot::with('details')->get();

        // 1b. Group SIR Stocks by Lot Prefix (e.g. "140" from "140-08032026")
        $groupedSirStocks = $stocks->where('origin_unit', 'SIR')->groupBy(function ($stock) {
            $parts = explode('-', $stock->lot_number);
            return $parts[0] ?? $stock->lot_number;
        });

        // 2. Data Kontrak & PO
        $contracts = \App\Models\Contract::with('purchaseOrders')->get();

        // 3. Aggregation for Pie Chart (Stock by Quality)
        $stockByQuality = \App\Models\StockLot::join('stock_details', 'stock_lots.id', '=', 'stock_details.stock_lot_id')
            ->selectRaw('stock_lots.quality_type, SUM(stock_details.net_weight_kg) as total_weight')
            ->groupBy('stock_lots.quality_type')
            ->pluck('total_weight', 'quality_type');

        // 4. Aggregation for Line Chart (Inbound vs Outbound - Real Data)
        $dates = collect(range(6, 0))->map(function ($days) {
            return now()->subDays($days)->format('Y-m-d');
        });

        // Get actual Inbound net_weight per day
        $inboundData = \App\Models\InboundTransaction::selectRaw('DATE(weigh_in_at) as date, SUM(net_weight) as total')
            ->where('weigh_in_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('total', 'date');

        // Get actual Outbound loaded kg per day
        $outboundData = \App\Models\Shipment::join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id')
            ->selectRaw('DATE(shipments.dispatched_at) as date, SUM(shipment_items.qty_loaded_kg) as total')
            ->where('shipments.dispatched_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->pluck('total', 'date');

        $chartData = [
            'labels' => $dates->map(function ($d) {
                return \Carbon\Carbon::parse($d)->format('d M');
            }),
            'inbound' => $dates->map(function ($d) use ($inboundData) {
                return $inboundData[$d] ?? 0;
            }),
            'outbound' => $dates->map(function ($d) use ($outboundData) {
                return $outboundData[$d] ?? 0;
            }),
        ];

        // 5. Capacity Monitoring (SIR & RSS) - Added for Workflow Alignment
        $capacityData = [
            'SIR' => [
                'current' => \App\Models\StockLot::where('origin_unit', 'SIR')->count(), // Count lots
                'max' => 760,
            ],
            'RSS' => [
                'current' => \App\Models\StockDetail::whereHas('stockLot', function ($q) {
                    $q->where('origin_unit', 'RSS');
                })->sum('quantity_unit'), // Count bales
                'max' => 1200,
            ],
        ];

        // 6. Contract Quota Tracking - Added for Workflow Alignment
        $contracts->transform(function ($contract) {
            // Calculate actual shipped tonnage from related Shipments
            // Contract -> PurchaseOrders -> Shipments -> ShipmentItems -> sum(qty_loaded_kg)
            $shippedKg = 0;
            foreach ($contract->purchaseOrders as $po) {
                $shippedKg += $po->shipments()->join('shipment_items', 'shipments.id', '=', 'shipment_items.shipment_id')
                    ->sum('shipment_items.qty_loaded_kg');
            }

            $contract->shipped_tonnage = $shippedKg / 1000; // Convert Kg to Ton
            $contract->remaining_tonnage = max(0, $contract->total_tonnage - $contract->shipped_tonnage);
            $contract->progress_percent = $contract->total_tonnage > 0 ? ($contract->shipped_tonnage / $contract->total_tonnage) * 100 : 0;

            return $contract;
        });

        return view('dashboard', compact('stocks', 'groupedSirStocks', 'contracts', 'stockByQuality', 'chartData', 'capacityData'));
    }
    // Tambahkan di App\Http\Controllers\DashboardController.php

    public function getStocksApi()
    {
        $stocks = \App\Models\StockLot::all();

        // Mengembalikan data lot, status warna, dan asal unit
        return response()->json([
            'status' => 'success',
            'data' => $stocks
        ]);
    }



}
