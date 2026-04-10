<?php

namespace App\Http\Controllers;

use App\Models\DailyStockSnapshot;
use App\Models\StockLot;
use App\Services\StockReportService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
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

    public function downloadDailyPDF(Request $request)
    {
        $date = $request->date ?? now()->format('Y-m-d');

        // Mengambil data snapshot harian sesuai format fisik
        $reports = DailyStockSnapshot::whereDate('report_date', $date)->get();

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