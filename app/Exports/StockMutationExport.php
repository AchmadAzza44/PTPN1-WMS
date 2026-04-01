<?php

namespace App\Exports;

use App\Models\DailyStockSnapshot;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockMutationExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    /**
     * Mengambil koleksi data snapshot harian
     */
    public function collection()
    {
        return DailyStockSnapshot::orderBy('report_date', 'desc')
            ->orderBy('quality_type', 'asc')
            ->get();
    }

    /**
     * Menentukan judul kolom di file Excel
     */
    public function headings(): array
    {
        return [
            'Tanggal Laporan',
            'Jenis Mutu Produk',
            'Saldo Awal (Unit)',
            'Masuk Hari Ini (Unit)',
            'Keluar Hari Ini (Unit)',
            'Stok Akhir (Unit)',
            'Keterangan'
        ];
    }

    /**
     * Memetakan data agar formatnya rapi (Mapping)
     * @param mixed $snapshot
     */
    public function map($snapshot): array
    {
        return [
            \Carbon\Carbon::parse($snapshot->report_date)->format('d/m/Y'),
            $snapshot->quality_type,
            $snapshot->opening_stock,
            $snapshot->inbound_total,
            $snapshot->outbound_total,
            $snapshot->closing_stock,
            $snapshot->closing_stock <= 10 ? 'Stok Tipis' : 'Tersedia'
        ];
    }
}