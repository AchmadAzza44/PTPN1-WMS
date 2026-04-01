<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\PurchaseOrder;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentController extends Controller
{
    public function generateBA($poId)
    {
        $po = PurchaseOrder::with(['contract', 'stockLots'])->findOrFail($poId);

        // Data diambil dari dokumen riil PT. Bitung Gunasejahtera
        $data = [
            'title' => 'BERITA ACARA PENYERAHAN BARANG',
            'contract_no' => $po->contract->contract_number,
            'buyer' => $po->contract->buyer_name,
            'po_no' => $po->po_number,
            'date' => now()->format('d F Y'),
            'items' => $po->stockLots
        ];

        $pdf = Pdf::loadView('pdf.berita_acara', $data);
        return $pdf->download('BA-'.$po->po_number.'.pdf');
    }
}