<?php

namespace App\Http\Controllers;

use App\Models\DailyStockSnapshot;
use App\Models\StockLot;
use App\Models\StockDetail;
use App\Services\StockReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan library dompdf sudah terinstall

class ReportController extends Controller
{
    private StockReportService $reportService;

    public function __construct(StockReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    public function index()
    {
        return view('reports.index');
    }

    /**
     * Real-Time Dashboard page (server-rendered shell, data via AJAX)
     */
    public function realtimeDashboard()
    {
        // Initial data for first paint (no AJAX needed on load)
        [$start, $end] = $this->reportService->resolveRange('7d');

        return view('reports.realtime', [
            'initialStock'   => $this->reportService->currentStockByQuality(),
            'initialTrend'   => $this->reportService->trendData($start, $end),
            'initialSummary' => $this->reportService->rangeSummary($start, $end),
            'initialRecent'  => $this->reportService->recentInbound(10),
        ]);
    }

    /**
     * API endpoint for real-time data (AJAX polling)
     */
    public function apiRealtimeData(Request $request): JsonResponse
    {
        $preset = $request->input('range', '7d');
        $startDate = $request->input('start');
        $endDate   = $request->input('end');

        [$start, $end] = $this->reportService->resolveRange($preset, $startDate, $endDate);

        return response()->json([
            'stock'   => $this->reportService->currentStockByQuality(),
            'trend'   => $this->reportService->trendData($start, $end),
            'summary' => $this->reportService->rangeSummary($start, $end),
            'recent'  => $this->reportService->recentInbound(10),
            'updated_at' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Download PDF Laporan Harian Mutasi Produksi.
     * Sekarang query REAL-TIME dari stock_lots + stock_details + shipments,
     * sehingga bisa diambil kapan saja tanpa bergantung pada DailyStockSnapshot.
     */
    public function downloadDailyPDF(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');

        // Ambil semua jenis mutu yang ada di database
        $qualityTypes = StockLot::distinct()->pluck('quality_type')->filter()->sort()->values();

        $reports = collect();

        foreach ($qualityTypes as $quality) {
            // === INBOUND hari itu (palet masuk) ===
            $inboundPalet = StockLot::where('quality_type', $quality)
                ->whereDate('inbound_at', $date)
                ->count();

            $inboundKg = StockLot::where('quality_type', $quality)
                ->whereDate('inbound_at', $date)
                ->join('stock_details', 'stock_lots.id', '=', 'stock_details.stock_lot_id')
                ->sum('stock_details.net_weight_kg');

            // === OUTBOUND hari itu (dari shipments yang dispatched) ===
            $outboundData = DB::table('shipment_items')
                ->join('shipments', 'shipment_items.shipment_id', '=', 'shipments.id')
                ->join('stock_lots', 'shipment_items.stock_lot_id', '=', 'stock_lots.id')
                ->where('stock_lots.quality_type', $quality)
                ->whereDate('shipments.dispatched_at', $date)
                ->selectRaw('COUNT(DISTINCT stock_lots.id) as palet_count, COALESCE(SUM(shipment_items.qty_loaded_kg), 0) as total_kg')
                ->first();

            $outboundPalet = $outboundData->palet_count ?? 0;
            $outboundKg = $outboundData->total_kg ?? 0;

            // === STOK SAAT INI (semua lot aktif / tidak berstatus 'orange') ===
            $currentStockPalet = StockLot::where('quality_type', $quality)
                ->where('status', '!=', 'orange')
                ->count();

            $currentStockKg = StockLot::where('quality_type', $quality)
                ->where('status', '!=', 'orange')
                ->join('stock_details', 'stock_lots.id', '=', 'stock_details.stock_lot_id')
                ->sum('stock_details.net_weight_kg');

            // === SISA / OPENING STOCK = Stok Sekarang - Inbound + Outbound ===
            $openingPalet = $currentStockPalet - $inboundPalet + $outboundPalet;
            $openingKg = $currentStockKg - $inboundKg + $outboundKg;

            $reports->push((object) [
                'quality_type'     => $quality,
                'opening_palet'    => max(0, $openingPalet),
                'inbound_palet'    => $inboundPalet,
                'outbound_palet'   => $outboundPalet,
                'closing_palet'    => $currentStockPalet,
                'opening_kg'       => max(0, round($openingKg, 2)),
                'inbound_kg'       => round($inboundKg, 2),
                'outbound_kg'      => round($outboundKg, 2),
                'closing_kg'       => round($currentStockKg, 2),
            ]);
        }

        $data = [
            'tanggal' => \Carbon\Carbon::parse($date)->translatedFormat('d F Y'),
            'reports' => $reports,
            'kepada' => 'Kepala Bagian Teknik dan Pengolahan (TNP)',
            'dari' => 'Kepala Bagian Manajemen Aset dan Pemasaran',
            'koordinator' => 'Baktiar Yusuf, SE',
            'krani' => 'Friska Rajagukguk'
        ];

        $pdf = Pdf::loadView('reports.daily_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->stream("Laporan_Harian_Mutasi_$date.pdf");
    }
}