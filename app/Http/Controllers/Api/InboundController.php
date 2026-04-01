<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StockLot;
use App\Models\StockDetail;
use App\Models\InboundTransaction;
use App\Services\AIService; // WAJIB: Agar AIService dikenali
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InboundController extends Controller
{
    /**
     * Menyimpan data barang masuk (Scan AI atau Manual)
     */
    public function store(Request $request)
    {
        // --- LOGIKA AI MULAI DISINI ---
        $netWeight = $request->net_weight;

        // Jika petugas mengirim foto dari HP, biarkan AI yang membaca angkanya
        if ($request->hasFile('photo')) {
            $aiService = app(AIService::class);
            $rawText = $aiService->scanWeightTicket($request->file('photo')->path());
            
            // Mengambil angka berat netto dari hasil scan nota
            $netWeight = $aiService->parseNetWeight($rawText['generated_text'] ?? '');
            
            // Update request data agar validasi di bawah tidak error
            $request->merge(['net_weight' => $netWeight]);
        }
        // --- LOGIKA AI SELESAI ---

        // 1. Validasi Data Masuk
        $validator = Validator::make($request->all(), [
            'lot_number'      => 'required|string',
            'quality_type'    => 'required|in:SIR 20 SW,RSS 1,RSS 2,Cutting A,Cutting B',
            'production_year' => 'required|integer',
            'origin_unit'     => 'required|string',
            'ticket_number'   => 'required|unique:inbound_transactions,ticket_number',
            'vehicle_plate'   => 'required|string',
            'gross_weight'    => 'required|numeric',
            'tare_weight'     => 'required|numeric',
            'net_weight'      => 'required|numeric', // Sekarang diisi otomatis oleh AI
            'weigh_in_at'     => 'required|date_format:Y-m-d H:i:s',
            'weigh_out_at'    => 'required|date_format:Y-m-d H:i:s',
            'packaging_type'  => 'required|in:pallet,bale',
            'details'         => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // A. Simpan ke Tabel stock_lots (Visual Biru di Dashboard)
            $stockLot = StockLot::create([
                'lot_number'      => $request->lot_number,
                'production_year' => $request->production_year,
                'quality_type'    => $request->quality_type,
                'origin_unit'     => $request->origin_unit,
                'status'          => 'blue',
                'inbound_at'      => now(),
            ]);

            // B. Simpan Transaksi Timbangan
            InboundTransaction::create([
                'stock_lot_id'   => $stockLot->id,
                'ticket_number'  => $request->ticket_number,
                'vehicle_plate'  => $request->vehicle_plate,
                'driver_name'    => $request->driver_name ?? 'N/A',
                'gross_weight'   => $request->gross_weight,
                'tare_weight'    => $request->tare_weight,
                'net_weight'     => $netWeight, // Menggunakan data hasil AI
                'weigh_in_at'    => $request->weigh_in_at,
                'weigh_out_at'   => $request->weigh_out_at,
                'ai_ocr_data'    => $request->ai_ocr_raw,
            ]);

            // C. Simpan Detail FDF/Palet
            foreach ($request->details as $item) {
                StockDetail::create([
                    'stock_lot_id'   => $stockLot->id,
                    'packaging_type' => $request->packaging_type,
                    'fdf_number'     => $item['fdf_number'] ?? null,
                    'bale_range'     => $item['bale_range'] ?? null,
                    'quantity_unit'  => $item['qty'] ?? 1,
                    'net_weight_kg'  => $item['weight'] ?? 1260,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'Data Lot ' . $request->lot_number . ' berhasil diproses otomatis oleh AI.',
                'data'    => ['lot_id' => $stockLot->id, 'net_weight_detected' => $netWeight]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }
}