<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ShipmentService;
use App\Services\AIService;
use Illuminate\Http\Request;

class OutboundController extends Controller
{
    protected $shipmentService;
    protected $aiService;

    public function __construct(ShipmentService $ss, AIService $as)
    {
        $this->shipmentService = $ss;
        $this->aiService = $as;
    }

    public function store(Request $request)
    {
        // 1. Proses AI OCR jika ada foto Nota Timbang
        $netWeight = $request->net_weight;
        if ($request->hasFile('ticket_photo')) {
            $aiResult = $this->aiService->scanWeightTicket($request->file('ticket_photo')->path());
            // Logika tambahan untuk parsing teks AI ke angka berat
        }

        // 2. Eksekusi Logika Bisnis (Potong DO & Ganti Warna)
        try {
            $result = $this->shipmentService->processOutbound(
                $request->purchase_order_id,
                $request->stock_lot_id,
                $netWeight
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Pemuatan berhasil dicatat!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }
    }
}