<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\StockLot;
use App\Models\StockDetail;
use App\Models\StockLotEdit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockController extends Controller
{
    public function index()
    {
        $stocks = StockLot::with('details')->latest()->paginate(10);

        // Data for Warehouse Visualization
        $allStocks = StockLot::with('details')->get();
        $groupedSirStocks = $allStocks->where('origin_unit', 'SIR')->groupBy(function ($stock) {
            $parts = explode('-', $stock->lot_number);
            return $parts[0] ?? $stock->lot_number;
        });

        $rssStocks = $allStocks->where('origin_unit', 'RSS')->values();

        return view('stocks.index', compact('stocks', 'allStocks', 'groupedSirStocks', 'rssStocks'));
    }

    public function create()
    {
        return view('stocks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'lot_number' => 'required|string|unique:stock_lots,lot_number',
            'production_year' => 'required|integer|min:2000',
            'quality_type' => 'required|string',
            'origin_unit' => 'required|string',
            'net_weight' => 'required|numeric|min:0',
            'inbound_at' => 'required|date',
        ]);

        $stockLot = StockLot::create([
            'lot_number' => strtoupper($request->lot_number),
            'production_year' => $request->production_year,
            'quality_type' => $request->quality_type,
            'origin_unit' => $request->origin_unit,
            'status' => 'blue',
            'inbound_at' => $request->inbound_at,
        ]);

        $isRss = str_contains($request->quality_type, 'RSS');
        // Auto Create Detail
        \App\Models\StockDetail::create([
            'stock_lot_id' => $stockLot->id,
            'packaging_type' => $isRss ? 'bale' : 'pallet',
            'fdf_number' => 'MANUAL-INPUT',
            'bale_range' => 'ALL',
            'quantity_unit' => $isRss ? $request->net_weight : floor($request->net_weight / 35),
            'net_weight_kg' => $isRss ? ($request->net_weight * 113) : $request->net_weight,
        ]);

        return redirect()->route('stocks.index')->with('success', 'Stok berhasil ditambahkan secara manual.');
    }

    public function edit($id)
    {
        $stock = StockLot::with('details')->findOrFail($id);
        return view('stocks.edit', compact('stock'));
    }

    public function update(Request $request, $id)
    {
        $stock = StockLot::with('details')->findOrFail($id);

        $request->validate([
            'quality_type' => 'required|string',
            'origin_unit' => 'required|string',
            'status' => 'required|string',
            // Detail rows
            'details' => 'nullable|array',
            'details.*.id' => 'required|integer|exists:stock_details,id',
            'details.*.fdf_number' => 'nullable|string|max:100',
            'details.*.pallet_number' => 'nullable|string|max:100',
            'details.*.quantity_unit' => 'required|integer|min:0',
            'details.*.net_weight_kg' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $stock->update([
                'quality_type' => $request->quality_type,
                'origin_unit' => $request->origin_unit,
                'status' => $request->status,
            ]);

            // Update each detail row individually with audit trail
            if ($request->has('details')) {
                foreach ($request->details as $detailData) {
                    $detail = StockDetail::where('id', $detailData['id'])
                        ->where('stock_lot_id', $stock->id)
                        ->first();

                    if (!$detail) continue;

                    $changes = [];
                    $editableFields = [
                        'fdf_number' => 'No. Peti/FDF',
                        'pallet_number' => 'No. Palet',
                        'quantity_unit' => 'Jumlah Bale',
                        'net_weight_kg' => 'Berat (kg)',
                    ];

                    foreach ($editableFields as $field => $label) {
                        $oldVal = (string)($detail->$field ?? '');
                        $newVal = (string)($detailData[$field] ?? '');
                        if ($oldVal !== $newVal) {
                            $changes[] = ['field' => $field, 'label' => $label, 'old' => $oldVal, 'new' => $newVal];
                        }
                    }

                    if (!empty($changes)) {
                        // Update the detail
                        $detail->update([
                            'fdf_number' => $detailData['fdf_number'] ?? $detail->fdf_number,
                            'pallet_number' => $detailData['pallet_number'] ?? null,
                            'quantity_unit' => $detailData['quantity_unit'],
                            'net_weight_kg' => $detailData['net_weight_kg'],
                        ]);

                        // Audit trail for each changed field
                        foreach ($changes as $c) {
                            StockLotEdit::create([
                                'stock_lot_id' => $stock->id,
                                'user_id' => auth()->id(),
                                'field_changed' => 'detail.' . $detail->id . '.' . $c['field'],
                                'old_value' => $c['old'],
                                'new_value' => $c['new'],
                                'reason' => 'Edit detail dari halaman edit stok',
                            ]);
                        }
                    }
                }
            }

            DB::commit();
            return redirect()->route('stocks.index')->with('success', 'Data stok berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Stock update error: ' . $e->getMessage());
            return back()->with('error', 'Gagal update: ' . $e->getMessage());
        }
    }

    /**
     * Update Lot Number — Operator-only, audit trail mandatory.
     * Hanya boleh untuk lot yang masih di gudang (status=blue).
     */
    public function updateLotNumber(Request $request, $id)
    {
        $stock = StockLot::findOrFail($id);

        $request->validate([
            'lot_number' => 'required|string|max:100',
            'reason'     => 'required|string|min:5|max:255',
        ]);

        // Hanya boleh edit jika status masih 'blue' (di gudang)
        if ($stock->status !== 'blue') {
            return back()->with('error', 'Lot yang sudah keluar gudang (status: ' . $stock->status . ') tidak bisa diubah nomor lot-nya.');
        }

        $oldLotNumber = $stock->lot_number;
        $newLotNumber = strtoupper(trim($request->lot_number));

        // Skip jika tidak berubah
        if ($oldLotNumber === $newLotNumber) {
            return back()->with('info', 'Nomor lot tidak berubah.');
        }

        // Simpan audit trail
        StockLotEdit::create([
            'stock_lot_id'  => $stock->id,
            'user_id'       => auth()->id(),
            'field_changed' => 'lot_number',
            'old_value'     => $oldLotNumber,
            'new_value'     => $newLotNumber,
            'reason'        => $request->reason,
        ]);

        // Update lot number
        $stock->update(['lot_number' => $newLotNumber]);

        return back()->with('success', "Nomor Lot berhasil diubah: {$oldLotNumber} → {$newLotNumber}");
    }

    public function destroy($id)
    {
        $stock = StockLot::findOrFail($id);
        $stock->details()->delete();
        $stock->delete();

        return redirect()->route('stocks.index')->with('success', 'Data stok berhasil dihapus.');
    }
}
