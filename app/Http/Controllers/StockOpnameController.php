<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\StockLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOpnameController extends Controller
{
    public function index()
    {
        $stocks = StockLot::with('details')->orderBy('id', 'desc')->get();
        $adjustments = StockAdjustment::with(['stockLot', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('stock_opname.index', compact('stocks', 'adjustments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'stock_lot_id' => 'required|exists:stock_lots,id',
            'actual_weight' => 'required|numeric|min:0',
            'reason' => 'required|string|max:255',
            'type' => 'sometimes|in:correction,loss,gain',
        ]);

        $stockLot = StockLot::with('details')->findOrFail($request->stock_lot_id);
        $currentWeight = $stockLot->details->sum('net_weight_kg');
        $actualWeight = (float) $request->actual_weight;
        $diff = $actualWeight - $currentWeight;

        // Determine type automatically if not provided
        $type = $request->input('type', $diff >= 0 ? 'gain' : 'loss');

        DB::beginTransaction();
        try {
            // Record adjustment history FIRST
            StockAdjustment::create([
                'stock_lot_id' => $stockLot->id,
                'user_id' => Auth::id(),
                'type' => $type,
                'weight_adjusted_kg' => $diff,
                'weight_before_kg' => $currentWeight,
                'weight_after_kg' => $actualWeight,
                'reason' => $request->reason,
            ]);

            // Apply adjustment to the stock lot (use existing adjustStock method if available)
            if (method_exists($stockLot, 'adjustStock')) {
                $stockLot->adjustStock($actualWeight, $request->reason);
            }

            DB::commit();
            return redirect()->route('stock-opname.index')
                ->with('success', 'Penyesuaian stok berhasil dicatat. Selisih: ' . ($diff >= 0 ? '+' : '') . number_format($diff, 2) . ' Kg');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan koreksi stok: ' . $e->getMessage());
        }
    }
}
