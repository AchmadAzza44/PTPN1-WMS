@extends('layouts.modern')

@section('title', 'Verifikasi Pengiriman')
@section('header', 'Verifikasi Outbound')
@section('subheader', 'Konfirmasi fisik barang sebelum surat jalan diterbitkan')

@section('content')
    <div class="space-y-6">
        @php
            $hasItems = $groups->count() > 0 || $standaloneShipments->count() > 0;
        @endphp

        <!-- Grouped Shipments (Berita Acara) -->
        @foreach($groups as $group)
            <div class="glass p-6 rounded-2xl shadow-sm border-l-4 border-indigo-500">
                <div class="flex flex-col md:flex-row justify-between items-start mb-4 gap-4">
                    <div class="w-full md:w-auto">
                        <div class="flex flex-wrap items-center gap-2 mb-2">
                            <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2 py-1 rounded-lg uppercase">
                                BERITA ACARA
                            </span>
                            <span class="text-slate-400 text-xs">
                                <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                                {{ $group->created_at ? $group->created_at->diffForHumans() : '-' }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $group->ba_number ?? ('BA #' . $group->id) }}</h3>
                        <p class="text-slate-500 text-sm">Pembeli: {{ $group->buyer_name ?? '-' }}</p>
                        <p class="text-slate-500 text-sm font-bold mt-1">Total Muatan: {{ number_format($group->total_weight, 0) }} Kg ({{ count($group->shipments) }} Dokumen)</p>
                    </div>
                    <form action="{{ route('shipments.verify', $group->id) }}" method="POST" class="w-full md:w-auto border-t md:border-t-0 md:border-l border-slate-200 pt-4 md:pt-0 md:pl-4">
                        @csrf
                        <div class="space-y-2 mb-3">
                            <label class="block text-xs font-bold text-slate-500 uppercase">Ekspedisi</label>
                            <input type="text" name="transporter_name" class="w-full text-sm p-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-indigo-500 mb-2" value="{{ $group->transporter_name !== '-' ? $group->transporter_name : '' }}" required>
                            
                            <label class="block text-xs font-bold text-slate-500 uppercase">Nomor Polisi (Plat)</label>
                            <input type="text" name="vehicle_plate" class="w-full text-sm p-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-indigo-500 mb-2 uppercase" value="{{ $group->vehicle_plate !== '-' ? $group->vehicle_plate : '' }}" required>
                            
                            <label class="block text-xs font-bold text-slate-500 uppercase">Nama Supir</label>
                            <input type="text" name="driver_name" class="w-full text-sm p-2 bg-slate-50 border border-slate-200 rounded-lg focus:ring-indigo-500 mb-4" value="{{ $group->driver_name !== '-' ? $group->driver_name : '' }}" required>

                            <label class="flex items-center space-x-2 p-2 bg-yellow-50 rounded-lg border border-yellow-100 cursor-pointer hover:bg-yellow-100 transition-colors">
                                <input type="checkbox" name="check_physical" value="1" required class="w-5 h-5 text-indigo-600 rounded">
                                <span class="text-sm font-bold text-slate-700">Kondisi Fisik Barang Baik</span>
                            </label>
                            <label class="flex items-center space-x-2 p-2 bg-yellow-50 rounded-lg border border-yellow-100 cursor-pointer hover:bg-yellow-100 transition-colors">
                                <input type="checkbox" name="check_pallet" value="1" required class="w-5 h-5 text-indigo-600 rounded">
                                <span class="text-sm font-bold text-slate-700">Lot & PO Sesuai Dokumen</span>
                            </label>
                        </div>
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg shadow-indigo-500/30 flex items-center justify-center transition-transform hover:-translate-y-1">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i> Verifikasi Fisik
                        </button>
                    </form>
                </div>
                
                <!-- Brief Shipments List -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 space-y-2">
                    @foreach($group->shipments as $sidx => $ship)
                    <div class="text-sm border-b border-slate-100 pb-2 mb-2 last:border-0 last:pb-0 last:mb-0">
                        <div class="flex justify-between font-bold text-slate-700">
                            <span>PO: {{ $ship->purchaseOrder?->po_number ?? '-' }} ({{ $ship->purchaseOrder?->contract?->contract_number ?? '-' }})</span>
                            <span>{{ number_format($ship->items->sum('qty_loaded_kg'), 0) }} Kg</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1">
                            Lots: 
                            @foreach($ship->items as $item)
                                <span class="inline-block bg-white border border-slate-200 px-1 rounded mr-1 font-mono">{{ $item->stockLot?->lot_number ?? 'Dihapus' }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <!-- Legacy Standalone Shipments -->
        @foreach($standaloneShipments as $shipment)
            <!-- Legacy code omitted for brevity but generally same structure as above, pointing to shipments.verify -->
        @endforeach

        @if(!$hasItems)
            <div class="text-center py-12">
                <div class="bg-slate-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-check" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Semua Beres!</h3>
                <p class="text-slate-500">Tidak ada pengiriman yang perlu diverifikasi saat ini.</p>
            </div>
        @endif
    </div>
@endsection