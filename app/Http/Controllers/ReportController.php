<?php

namespace App\Http\Controllers;

use App\Models\DailyStockSnapshot;
use App\Models\StockLot;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf; // Pastikan library dompdf sudah terinstall

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
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