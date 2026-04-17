<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentGroup;
use App\Models\ShipmentItem;
use App\Models\StockLot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ShipmentVerificationNotification;
use App\Notifications\ShipmentVerifiedNotification;

class ShipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Group-aware: prioritas tampilkan ShipmentGroup, fallback ke standalone Shipment
        $groups = ShipmentGroup::with(['shipments.purchaseOrder.contract', 'shipments.items'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        // Legacy standalone shipments (tanpa group)
        $standaloneShipments = Shipment::whereNull('shipment_group_id')
            ->with(['purchaseOrder.contract', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('shipments.index', compact('groups', 'standaloneShipments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $doNumber = $request->query('do_number_manual');
        $fotoPath = $request->query('foto_path') ?? $request->foto_path;
        
        // Ambil data pre-fill dari OCR (jika ada)
        $preFill = [
            'do_number_manual' => $doNumber,
            'foto_path' => $fotoPath,
            'contract_number_ref' => $request->query('contract_number_ref'),
            'documented_qty_kg' => $request->query('documented_qty_kg'),
            'transporter_name' => $request->query('transporter_name'),
            'driver_name' => $request->query('driver_name'),
            'vehicle_plate' => $request->query('vehicle_plate'),
            'surat_kuasa_number' => $request->query('surat_kuasa_number'),
            'buyer_name' => $request->query('buyer_name'),
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

                // Coba ambil foto_path dari PO jika tidak dikirim dari request
                if (empty($preFill['foto_path']) && $po->foto_path) {
                    $preFill['foto_path'] = $po->foto_path;
                    $request->merge(['foto_path' => $po->foto_path]);
                }

                // Coba ambil info transporter dari shipment terakhir parsial
                $latestShipment = $po->shipments()->latest()->first();
                if ($latestShipment) {
                    if (empty($preFill['transporter_name'])) $preFill['transporter_name'] = $latestShipment->transporter_name !== '-' ? $latestShipment->transporter_name : '';
                    if (empty($preFill['driver_name'])) $preFill['driver_name'] = $latestShipment->driver_name !== '-' ? $latestShipment->driver_name : '';
                    if (empty($preFill['vehicle_plate'])) $preFill['vehicle_plate'] = $latestShipment->vehicle_plate !== '-' ? $latestShipment->vehicle_plate : '';
                }

                $preFill['total_pesanan_kg'] = $po->qty_ordered_kg;
                $rem = max(0, $po->qty_ordered_kg - $po->qty_served_kg);
                $preFill['sisa_pesanan_kg'] = $rem;
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
     * Now supports multi-PO: creates a ShipmentGroup (Berita Acara)
     * with N Shipments inside.
     */
    public function store(Request $request)
    {
        $request->validate([
            'buyer_name' => 'nullable|string',
            'transporter_name' => 'nullable|string',
            'driver_name' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
            // Multi-PO entries
            'entries' => 'required|array|min:1',
            'entries.*.contract_number' => 'required|string',
            'entries.*.do_number_manual' => 'nullable|string',
            'entries.*.surat_kuasa_number' => 'nullable|string',
            'entries.*.documented_qty_kg' => 'nullable|numeric',
            'entries.*.items' => 'required|array|min:1',
            'entries.*.items.*.stock_lot_id' => 'required|exists:stock_lots,id',
            'entries.*.items.*.qty_loaded_kg' => 'required|numeric|min:1',
        ]);

        DB::beginTransaction();
        try {
            // 1. Create ShipmentGroup (= 1 Berita Acara)
            $group = ShipmentGroup::create([
                'buyer_name' => $request->buyer_name ?? '-',
                'transporter_name' => $request->transporter_name ?? '-',
                'driver_name' => $request->driver_name ?? '-',
                'vehicle_plate' => $request->vehicle_plate ?? '-',
                'weather_condition' => 'Cerah',
                'dispatched_at' => now(),
                'status' => 'draft',
            ]);

            // Auto-generate BA number
            $group->ba_number = ShipmentGroup::generateBaNumber($group->id, now());
            $group->save();

            // 2. Loop each PO entry
            foreach ($request->entries as $entry) {
                // Auto-create or find Contract
                $contract = \App\Models\Contract::firstOrCreate(
                    ['contract_number' => $entry['contract_number']],
                    [
                        'buyer_name' => $request->buyer_name ?? '-',
                        'contract_date' => now(),
                        'total_tonnage' => 1000
                    ]
                );

                // Find or create PO
                $do_number = $entry['do_number_manual'] ?? ('PO-' . now()->format('ymd-His') . '-' . rand(100, 999));
                $po = \App\Models\PurchaseOrder::where('po_number', $do_number)->first();

                $requested_do_qty = $entry['documented_qty_kg'] ?? 0;

                if (!$po) {
                    $po = \App\Models\PurchaseOrder::create([
                        'contract_id' => $contract->id,
                        'po_number' => $do_number,
                        'po_date' => now(),
                        'qty_ordered_kg' => $requested_do_qty,
                        'qty_served_kg' => 0,
                        'status' => 'open',
                        'foto_path' => $request->foto_path ?? null,
                    ]);
                } else {
                    if ($request->foto_path && empty($po->foto_path)) {
                        $po->foto_path = $request->foto_path;
                        $po->save();
                    }
                }

                // Calculate total loaded for this entry
                $totalLoaded = collect($entry['items'])->sum('qty_loaded_kg');

                // Create Shipment linked to group
                $shipment = Shipment::create([
                    'shipment_group_id' => $group->id,
                    'purchase_order_id' => $po->id,
                    'transporter_name' => $request->transporter_name ?? '-',
                    'driver_name' => $request->driver_name ?? '-',
                    'vehicle_plate' => $request->vehicle_plate ?? '-',
                    'vehicle_checklist' => json_encode(['default' => true]),
                    'weather_condition' => 'Cerah',
                    'dispatched_at' => now(),
                    'status' => 'draft',
                    'do_number_manual' => $do_number,
                    'surat_kuasa_number' => $entry['surat_kuasa_number'] ?? null,
                    'documented_qty_kg' => $totalLoaded,
                ]);

                // Process Items
                foreach ($entry['items'] as $item) {
                    $stockLot = StockLot::find($item['stock_lot_id']);

                    if (!empty($item['selected_details']) && is_array($item['selected_details'])) {
                        $availableDetailsWeight = $stockLot->details()
                            ->whereIn('id', $item['selected_details'])
                            ->sum('net_weight_kg');

                        if ($availableDetailsWeight <= 0 || $availableDetailsWeight < $item['qty_loaded_kg']) {
                            throw new \Exception("Stok Palet terpilih tidak cukup / sudah kosong untuk Lot " . $stockLot->lot_number);
                        }

                        $stockLot->reduceStockByDetails($item['selected_details']);
                        $selectedDetailIds = $item['selected_details'];
                    } else {
                        $currentStock = $stockLot->details()->sum('net_weight_kg');
                        if ($currentStock < $item['qty_loaded_kg']) {
                            throw new \Exception("Stok tidak cukup untuk Lot " . $stockLot->lot_number);
                        }
                        $stockLot->reduceStock($item['qty_loaded_kg']);
                        $selectedDetailIds = null;
                    }

                    ShipmentItem::create([
                        'shipment_id' => $shipment->id,
                        'stock_lot_id' => $stockLot->id,
                        'qty_loaded_kg' => $item['qty_loaded_kg'],
                        'selected_detail_ids' => $selectedDetailIds,
                    ]);
                }

                // Update qty_served_kg pada PO
                $po->qty_served_kg += $totalLoaded;
                if ($po->qty_served_kg >= $po->qty_ordered_kg) {
                    $po->status = 'completed';
                }
                $po->save();
            }

            DB::commit();

            try {
                $petugasGudang = User::where('role', 'operator')->get();
                if ($petugasGudang->count() > 0) {
                    // Notify about the first shipment (representative)
                    $firstShipment = $group->shipments()->first();
                    if ($firstShipment) {
                        Notification::send($petugasGudang, new ShipmentVerificationNotification($firstShipment));
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Push Notification Error: ' . $e->getMessage());
            }

            return redirect()->route('shipments.show_group', $group->id)->with('success', 'Berita Acara berhasil dibuat dengan ' . count($request->entries) . ' dokumen. Menunggu verifikasi petugas.');

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

    /**
     * Print Berita Acara — now group-aware.
     * If shipment has a group, print the full group BA with all N PO/Kontrak.
     */
    public function printBeritaAcara($id)
    {
        $group = ShipmentGroup::with(['shipments.items.stockLot.details', 'shipments.purchaseOrder.contract'])->findOrFail($id);
        $pdf = Pdf::loadView('pdf.berita_acara', compact('group'));
        $pdf->setPaper('a4', 'portrait');
        return $pdf->stream('Berita_Acara_' . $group->id . '.pdf');
    }

    /**
     * Show individual shipment (legacy).
     */
    public function show($id)
    {
        $shipment = Shipment::with(['items.stockLot', 'purchaseOrder.contract', 'group'])->findOrFail($id);
        
        // If this shipment belongs to a group, redirect to the group view
        if ($shipment->shipment_group_id) {
            return redirect()->route('shipments.show_group', $shipment->shipment_group_id);
        }

        return view('shipments.show', compact('shipment'));
    }

    /**
     * Show ShipmentGroup (Berita Acara) detail page.
     */
    public function showGroup($id)
    {
        $group = ShipmentGroup::with([
            'shipments.items.stockLot',
            'shipments.purchaseOrder.contract'
        ])->findOrFail($id);

        return view('shipments.show_group', compact('group'));
    }

    public function uploadSignedDoc(Request $request, $id)
    {
        $request->validate([
            'signed_doc' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $group = ShipmentGroup::findOrFail($id);

        if ($request->hasFile('signed_doc')) {
            $path = $request->file('signed_doc')->store('shipment_docs', 'public');
            $group->update([
                'signed_document_path' => $path,
                'status' => 'completed'
            ]);
            return back()->with('success', 'Dokumen berhasil diunggah! Status pengiriman selesai.');
        }

        return back()->with('error', 'Gagal mengunggah dokumen.');
    }

    public function indexVerification()
    {
        // Show 'draft' groups
        $groups = ShipmentGroup::with(['shipments.purchaseOrder.contract', 'shipments.items.stockLot'])
            ->where('status', 'draft')
            ->orderBy('created_at', 'desc')
            ->get();

        // Legacy standalone draft shipments
        $standaloneShipments = Shipment::whereNull('shipment_group_id')
            ->where('status', 'draft')
            ->with(['purchaseOrder.contract', 'items.stockLot'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        return view('shipments.verify', compact('groups', 'standaloneShipments'));
    }

    public function verify(Request $request, $id)
    {
        $group = ShipmentGroup::with('shipments')->findOrFail($id);

        if ($group->status !== 'draft') {
            return back()->with('error', 'Pengiriman sudah diverifikasi.');
        }

        // Update vehicle checklist for all shipments in the group
        foreach ($group->shipments as $shipment) {
            if ($request->has('check_physical') && $request->has('check_pallet')) {
                $checklist = $shipment->vehicle_checklist ?? [];
                if (is_string($checklist)) $checklist = json_decode($checklist, true);
                $checklist['physical_condition_ok'] = true;
                $checklist['pallet_lot_match'] = true;
                $shipment->vehicle_checklist = $checklist;
            }

            $shipment->update([
                'status' => 'verified',
                'verified_at' => now(),
                'verified_by' => auth()->id() ?? 1,
                'transporter_name' => collect([$request->transporter_name, $shipment->transporter_name])->filter()->first(),
                'driver_name' => collect([$request->driver_name, $shipment->driver_name])->filter()->first(),
                'vehicle_plate' => collect([$request->vehicle_plate, $shipment->vehicle_plate])->filter()->first(),
            ]);
        }

        $group->update([
            'status' => 'verified',
            'verified_at' => now(),
            'verified_by' => auth()->id() ?? 1,
            'transporter_name' => collect([$request->transporter_name, $group->transporter_name])->filter()->first(),
            'driver_name' => collect([$request->driver_name, $group->driver_name])->filter()->first(),
            'vehicle_plate' => collect([$request->vehicle_plate, $group->vehicle_plate])->filter()->first(),
        ]);

        try {
            $krani = User::where('role', 'admin')->get();
            if ($krani->count() > 0) {
                $firstShipment = $group->shipments->first();
                if ($firstShipment) {
                    Notification::send($krani, new ShipmentVerifiedNotification($firstShipment));
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Push Notification Error: ' . $e->getMessage());
        }

        return redirect()->route('shipments.show_group', $group->id)->with('success', 'Pengiriman berhasil diverifikasi! Dokumen siap dicetak.');
    }

    public function updateDetails(Request $request, $id)
    {
        $request->validate([
            'krani_name' => 'nullable|string',
            'manager_name' => 'nullable|string',
            'transporter_name' => 'nullable|string',
            'driver_name' => 'nullable|string',
            'vehicle_plate' => 'nullable|string',
        ]);

        $group = ShipmentGroup::findOrFail($id);
        $group->update([
            'transporter_name' => collect([$request->transporter_name, $group->transporter_name])->filter()->first(),
            'driver_name' => collect([$request->driver_name, $group->driver_name])->filter()->first(),
            'vehicle_plate' => collect([$request->vehicle_plate, $group->vehicle_plate])->filter()->first(),
            'krani_name' => collect([$request->krani_name, $group->krani_name])->filter()->first() ?? auth()->user()->name,
            'manager_name' => collect([$request->manager_name, $group->manager_name])->filter()->first(),
            'status' => 'completed',
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
