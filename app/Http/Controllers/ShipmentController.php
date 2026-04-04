<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentItem;
use App\Models\StockLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ShipmentVerificationNotification;

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
        $doNumber = $request->query('do_number_manual');
        // Ambil data pre-fill dari OCR (jika ada)
        $preFill = [
            'do_number_manual' => $doNumber,
            'contract_number_ref' => $request->query('contract_number_ref'),
            'documented_qty_kg' => $request->query('documented_qty_kg'),
            'sisa_pesanan_kg' => null,
            'total_pesanan_kg' => null,
        ];

        // Jika DO number ada, cari apakah sudah pernah ada PO ini (untuk parsial)
        if ($doNumber) {
            $po = \App\Models\PurchaseOrder::where('po_number', $doNumber)->first();
            if ($po) {
                if (empty($preFill['contract_number_ref']) && $po->contract) {
                    $preFill['contract_number_ref'] = $po->contract->contract_number;
                }
                $preFill['total_pesanan_kg'] = $po->qty_ordered_kg;
                $rem = max(0, $po->qty_ordered_kg - $po->qty_served_kg);
                $preFill['sisa_pesanan_kg'] = $rem;
                // Saran pengiriman saat ini adalah sisa pesanannya
                if ($rem > 0) {
                    $preFill['documented_qty_kg'] = $rem;
                }
            }
        }

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

        $totalAvailableStock = $stocks->sum('remaining_weight');

        return view('shipments.create', compact('stocks', 'preFill', 'totalAvailableStock'));
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

            // Cek exist PO
            $do_number = $request->do_number_manual ?? ('PO-' . now()->format('ymd-His'));
            $po = \App\Models\PurchaseOrder::where('po_number', $do_number)->first();
            
            // Variabel ini diperlukan untuk total beban pada PO 
            $requested_do_qty = $request->documented_qty_kg ?? 0;

            if (!$po) {
                // 2. Auto-create PO jika tidak ada
                $po = \App\Models\PurchaseOrder::create([
                    'contract_id' => $contract->id,
                    'po_number' => $do_number,
                    'po_date' => now(),
                    'qty_ordered_kg' => $requested_do_qty,
                    'qty_served_kg' => 0,
                    'status' => 'open'
                ]);
            }

            // Hitung total diangkut untuk Shipment ini
            $totalLoaded = collect($request->items)->sum('qty_loaded_kg');

            // 3. Create Shipment 
            $shipment = Shipment::create([
                'purchase_order_id' => $po->id,
                'transporter_name' => '-',
                'driver_name' => '-',
                'vehicle_plate' => '-',
                'vehicle_checklist' => json_encode(['default' => true]),
                'weather_condition' => 'Cerah',
                'dispatched_at' => now(),
                'status' => 'draft',
                'do_number_manual' => $do_number,
                'documented_qty_kg' => $totalLoaded, // Set ke jumlah yang benar-benar dikirim pada pengiriman ini
            ]);

            // 4. Process Items
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

            // Update qty_served_kg pada PO
            $po->qty_served_kg += $totalLoaded;
            if ($po->qty_served_kg >= $po->qty_ordered_kg) {
                $po->status = 'completed';
            }
            $po->save();

            DB::commit();

            // Kirim Push Notification ke seluruh Petugas Gudang (operator)
            $petugasGudang = User::where('role', 'operator')->get();
            if ($petugasGudang->count() > 0) {
                Notification::send($petugasGudang, new ShipmentVerificationNotification($shipment));
            }

            return redirect()->route('shipments.show', $shipment->id)->with('success', 'Pengiriman parsial/penuh berhasil dibuat! Silakan lanjut ke verifikasi.');

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
