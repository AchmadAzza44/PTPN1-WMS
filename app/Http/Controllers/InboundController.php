<?php

namespace App\Http\Controllers;

use App\Models\StockLot;
use App\Models\StockDetail;
use App\Models\InboundTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InboundController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'ticket_number' => 'required|string',
            'net_weight' => 'required|numeric|min:0',
            'gross_weight' => 'required|numeric|min:0', // New
            'tare_weight' => 'required|numeric|min:0', // New
            'driver_name' => 'required|string', // New
            'vehicle_plate' => 'required|string', // New
            'quality_type' => 'required|string',
            'photo_path' => 'required|string',
            'lots' => 'nullable|array',
            'lots.*.lot_number' => 'required|string',
            'lots.*.fdf_number' => 'sometimes|string',
            'lots.*.weight' => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $createdLots = 0;

            // Scenario A: Multi-Lot Input (Surat Pengantar)
            if ($request->has('lots') && is_array($request->lots) && count($request->lots) > 0) {

                // Group by Lot Number
                $groupedLots = collect($request->lots)->groupBy('lot_number');

                $transactionCreated = false;

                foreach ($groupedLots as $lotNo => $items) {
                    // Create Stock Lot Pattern: LOT-[TANGGAL]-[KONTRAK]-[NO_LOT_ASLI]
                    // Atau simpel: LOT-[NO_LOT_ASLI]-[TANGGAL]
                    // User req: "Otomatis nyesuaikan" -> Kita maintain Lot Number asli dari dokumen
                    $fullLotNumber = $lotNo . '-' . date('dmY');

                    // Create/Find Stock Lot
                    // Disini kita asumsi Inbound baru selalu create new StockLot
                    // Tapi jika Lot Number sama di hari sama, mungkin append? 
                    // Untuk aman: Create New dengan unique constraint handling manual atau timestamp

                    $originUnit = str_contains($request->quality_type, 'RSS') ? 'RSS' : 'SIR';

                    $stockLot = StockLot::create([
                        'lot_number' => $fullLotNumber,
                        'production_year' => date('Y'),
                        'quality_type' => $request->quality_type,
                        'origin_unit' => $originUnit,
                        'status' => 'blue',
                        'inbound_at' => now(),
                    ]);

                    foreach ($items as $item) {
                        $isRss = str_contains($request->quality_type, 'RSS');
                        $weightInput = $item['weight'] ?? 0;
                        StockDetail::create([
                            'stock_lot_id' => $stockLot->id,
                            'packaging_type' => $isRss ? 'bale' : 'pallet',
                            'fdf_number' => $item['fdf_number'] ?? $request->ticket_number,
                            'bale_range' => '-',
                            'quantity_unit' => $isRss ? $weightInput : floor($weightInput / 35),
                            'net_weight_kg' => $isRss ? ($weightInput * 113) : $weightInput,
                        ]);
                    }

                    // Create Inbound Transaction (Linked to First Lot only to avoid Unique Constraint violation on ticket_number)
                    if (!$transactionCreated) {
                        InboundTransaction::create([
                            'stock_lot_id' => $stockLot->id,
                            'ticket_number' => $request->ticket_number,
                            'vehicle_plate' => $request->vehicle_plate,
                            'driver_name' => $request->driver_name,
                            'gross_weight' => $request->gross_weight,
                            'tare_weight' => $request->tare_weight,
                            'net_weight' => $request->net_weight, // Total net weight from ticket
                            'weigh_in_at' => now()->subMinutes(30),
                            'weigh_out_at' => now(),
                            'photo_path' => $request->photo_path,
                            'ai_ocr_data' => json_encode($request->all()), // Audit trail
                        ]);
                        $transactionCreated = true;
                    }

                    $createdLots++;
                }

            } else {
                // Scenario B: Single Lot (Nota Timbang / Manual fallback)
                // Logic lama
                $originUnit = str_contains($request->quality_type, 'RSS') ? 'RSS' : 'SIR';
                $prefix = $originUnit === 'RSS' ? 'RSS-' : 'LOT-';
                $lotNumber = $prefix . date('ymd') . '-' . $request->ticket_number;

                $stockLot = StockLot::create([
                    'lot_number' => $lotNumber,
                    'production_year' => date('Y'),
                    'quality_type' => $request->quality_type,
                    'origin_unit' => $originUnit,
                    'status' => 'blue',
                    'inbound_at' => now(),
                ]);

                StockDetail::create([
                    'stock_lot_id' => $stockLot->id,
                    'packaging_type' => $originUnit === 'RSS' ? 'bale' : 'pallet',
                    'fdf_number' => $request->ticket_number,
                    'bale_range' => '-',
                    'quantity_unit' => $originUnit === 'RSS' ? $request->net_weight : floor($request->net_weight / 35),
                    'net_weight_kg' => $originUnit === 'RSS' ? ($request->net_weight * 113) : $request->net_weight, // Ini pake total
                ]);

                // Create Inbound Transaction
                InboundTransaction::create([
                    'stock_lot_id' => $stockLot->id,
                    'ticket_number' => $request->ticket_number,
                    'vehicle_plate' => $request->vehicle_plate,
                    'driver_name' => $request->driver_name,
                    'gross_weight' => $request->gross_weight,
                    'tare_weight' => $request->tare_weight,
                    'net_weight' => $request->net_weight,
                    'weigh_in_at' => now()->subMinutes(30),
                    'weigh_out_at' => now(),
                    'photo_path' => $request->photo_path,
                    'ai_ocr_data' => json_encode($request->all()),
                ]);
                $createdLots++;
            }

            DB::commit();

            return redirect()->route('stocks.index')->with('success', "Data Inbound Berhasil. $createdLots Lot Terbuat.");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Inbound Save Error: " . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }
}
