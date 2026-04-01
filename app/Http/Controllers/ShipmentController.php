<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\StockLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shipments = Shipment::with(['purchaseOrder.contract', 'items'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('shipments.index', compact('shipments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Ambil data pre-fill dari OCR (jika ada)
        $preFill = [
            'do_number_manual' => $request->query('do_number_manual'),
            'contract_number_ref' => $request->query('contract_number_ref'),
            'documented_qty_kg' => $request->query('documented_qty_kg'),
        ];

        // Ambil stok yang tersedia (Status Blue atau Yellow), exclude Orange (Kosong)
        $stocks = StockLot::whereIn('status', ['blue', 'yellow'])
            ->with([
                'details' => function ($query) {
                    $query->where('net_weight_kg', '>', 0);
                }
            ])

            ->orderBy('inbound_at', 'asc') // FIFO Rules
            ->get()
            ->map(function ($lot) {
                $lot->remaining_weight = $lot->details->sum('net_weight_kg');
                // Kumpulkan nomor FDF/palet dari detail
                $lot->fdf_numbers = $lot->details
                    ->pluck('fdf_number')
                    ->filter()
                    ->unique()
                    ->values()
                    ->toArray();
                return $lot;
            });

        return view('shipments.create', compact('stocks', 'preFill'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'contract_number' => 'required|string',
            'buyer_name' => 'nullable|string',
            'do_number_manual' => 'nullable|string',
            'documented_qty_kg' => 'nullable|numeric',
            'items' => 'required|array',
            'items.*.stock_lot_id' => 'required|exists:stock_lots,id',
            'items.*.qty_loaded_kg' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            // 1. Auto-create or find Contract
            $contract = \App\Models\Contract::firstOrCreate(
                ['contract_number' => $request->contract_number],
                [
                    'buyer_name' => $request->buyer_name ?? '-',
                    'contract_date' => now(),
                    'total_tonnage' => 1000
                ]
            );

            // 2. Auto-create PO from DO number
            $po = \App\Models\PurchaseOrder::firstOrCreate(
                [
                    'contract_id' => $contract->id,
                    'po_number' => $request->do_number_manual ?? ('PO-' . now()->format('ymd-His')),
                ],
                [
                    'po_date' => now(),
                    'qty_ordered_kg' => $request->documented_qty_kg ?? 0,
                    'qty_served_kg' => 0,
                    'status' => 'open'
                ]
            );

            // 3. Create Shipment (tanpa data supir/nopol — bukan bagian alur outbound)
            $shipment = Shipment::create([
                'purchase_order_id' => $po->id,
                'transporter_name' => '-',
                'driver_name' => '-',
                'vehicle_plate' => '-',
                'vehicle_checklist' => json_encode(['default' => true]),
                'weather_condition' => 'Cerah',
                'dispatched_at' => now(),
                'status' => 'draft',
                'do_number_manual' => $request->do_number_manual ?? null,
                'documented_qty_kg' => $request->documented_qty_kg ?? 0,
            ]);

            // 2. Process Items
            foreach ($request->items as $item) {
                $stockLot = StockLot::find($item['stock_lot_id']);

                // Validasi lagi stok cukup
                $currentStock = $stockLot->details()->sum('net_weight_kg');
                if ($currentStock < $item['qty_loaded_kg']) {
                    throw new \Exception("Stok tidak cukup untuk Lot " . $stockLot->lot_number);
                }

                // Kurangi Stok (Panggil method di Model)
                $stockLot->reduceStock($item['qty_loaded_kg']);

                // Catat di Shipment Items
                ShipmentItem::create([
                    'shipment_id' => $shipment->id,
                    'stock_lot_id' => $stockLot->id,
                    'qty_loaded_kg' => $item['qty_loaded_kg'],
                ]);
            }

            DB::commit();

            return redirect()->route('shipments.show', $shipment->id)->with('success', 'Pengiriman berhasil dibuat! Silakan lanjut ke verifikasi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi Kesalahan: ' . $e->getMessage());
        }
    }
    public function printSuratJalan($id)
    {
        $shipment = Shipment::with(['items.stockLot', 'purchaseOrder.contract'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.surat_jalan', compact('shipment'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('Surat_Jalan_' . $shipment->id . '.pdf');
    }

    public function printBeritaAcara($id)
    {
        $shipment = Shipment::with(['items.stockLot.details', 'purchaseOrder.contract'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.berita_acara', compact('shipment'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('Berita_Acara_' . $shipment->id . '.pdf');
    }

    public function show($id)
    {
        $shipment = Shipment::with(['items.stockLot', 'purchaseOrder.contract'])->findOrFail($id);
        return view('shipments.show', compact('shipment'));
    }

    public function uploadSignedDoc(Request $request, $id)
    {
        $request->validate([
            'signed_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // Max 5MB
        ]);

        $shipment = Shipment::findOrFail($id);

        if ($request->hasFile('signed_doc')) {
            $path = $request->file('signed_doc')->store('shipment_docs', 'public');
            $shipment->update([
                'signed_document_path' => $path,
                'status' => 'completed' // Finalize status on upload? Or separate step? User said "Krani Finalize". Let's keep it flexible or auto-complete here.
            ]);
            return back()->with('success', 'Dokumen berhasil diunggah! Status pengiriman selesai.');
        }

        return back()->with('error', 'Gagal mengunggah dokumen.');
    }

    public function indexVerification()
    {
        // Show only 'draft' shipments
        $shipments = Shipment::with(['purchaseOrder.contract', 'items.stockLot'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('shipments.verify', compact('shipments'));
    }

    public function verify(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);

        if ($shipment->status !== 'draft') {
            return back()->with('error', 'Pengiriman sudah diverifikasi.');
        }

        // Update vehicle checklist
        if ($request->has('check_physical') && $request->has('check_pallet')) {
            $checklist = $shipment->vehicle_checklist ?? [];
            if (is_string($checklist))
                $checklist = json_decode($checklist, true);

            $checklist['physical_condition_ok'] = true;
            $checklist['pallet_lot_match'] = true;

            $shipment->vehicle_checklist = $checklist;
        }

        $shipment->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id() ?? 1
        ]);

        return back()->with('success', 'Pengiriman berhasil diverifikasi! Dokumen siap dicetak.');
    }

    public function updateDetails(Request $request, $id)
    {
        $request->validate([
            'do_number_manual' => 'nullable|string',
            'documented_qty_kg' => 'nullable|numeric',
        ]);

        $shipment = Shipment::findOrFail($id);
        $shipment->update([
            'do_number_manual' => $request->do_number_manual,
            'documented_qty_kg' => $request->documented_qty_kg ?? 0,
            'status' => 'completed', // Krani confirms → completed
        ]);

        return back()->with('success', 'Data dikonfirmasi! Pengiriman selesai. Dokumen siap diarsipkan.');
    }

    public function printSuratJaminan($id)
    {
        $shipment = Shipment::with(['items.stockLot.details', 'purchaseOrder.contract'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.surat_jaminan_transportasi', compact('shipment'));
        $pdf->setPaper('a4', 'landscape');
        return $pdf->stream('SJT_' . $shipment->id . '.pdf');
    }
}
