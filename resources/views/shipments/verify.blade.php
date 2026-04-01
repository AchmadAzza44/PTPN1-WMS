@extends('layouts.modern')

@section('title', 'Verifikasi Pengiriman')
@section('header', 'Verifikasi Outbound')
@section('subheader', 'Konfirmasi fisik barang sebelum surat jalan diterbitkan')

@section('content')
    <div class="space-y-6">
        @forelse($shipments as $shipment)
            <div class="glass p-6 rounded-2xl shadow-sm border-l-4 border-blue-500">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <div class="flex items-center space-x-3 mb-2">
                            <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2 py-1 rounded-lg uppercase">
                                {{ $shipment->purchaseOrder->po_number }}
                            </span>
                            <span class="text-slate-400 text-xs">
                                <i data-lucide="clock" class="w-3 h-3 inline mr-1"></i>
                                {{ $shipment->created_at->diffForHumans() }}
                            </span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $shipment->purchaseOrder->contract->buyer_name ?? '-' }}
                        </h3>
                        <p class="text-slate-500 text-sm">Kontrak:
                            {{ $shipment->purchaseOrder->contract->contract_number ?? '-' }}</p>
                    </div>
                    <form action="{{ route('shipments.verify', $shipment->id) }}" method="POST">
                        @csrf

                        <div class="space-y-2 mb-3">
                            <label
                                class="flex items-center space-x-2 p-2 bg-yellow-50 rounded-lg border border-yellow-100 cursor-pointer hover:bg-yellow-100 transition-colors">
                                <input type="checkbox" name="check_physical" value="1" required
                                    class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                                <span class="text-sm font-bold text-slate-700">Kondisi Fisik Barang Baik (Kering/Tidak
                                    Kontam)</span>
                            </label>
                            <label
                                class="flex items-center space-x-2 p-2 bg-yellow-50 rounded-lg border border-yellow-100 cursor-pointer hover:bg-yellow-100 transition-colors">
                                <input type="checkbox" name="check_pallet" value="1" required
                                    class="w-5 h-5 text-blue-600 rounded focus:ring-blue-500 border-gray-300">
                                <span class="text-sm font-bold text-slate-700">Kesesuaian Nomor Palet & Lot (FIFO)</span>
                            </label>
                        </div>

                        <button type="submit"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-xl shadow-lg shadow-green-500/30 flex items-center justify-center transition-transform hover:-translate-y-1">
                            <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                            Verifikasi Fisik
                        </button>
                    </form>
                </div>

                <!-- Items List -->
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                    <table class="w-full text-sm text-left">
                        <thead class="text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="pb-2">Lot Number</th>
                                <th class="pb-2">Mutu</th>
                                <th class="pb-2 text-right">Muatan (Kg)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($shipment->items as $item)
                                <tr>
                                    <td class="py-2 font-mono text-slate-700">{{ $item->stockLot->lot_number }}</td>
                                    <td class="py-2">{{ $item->stockLot->quality_type }}</td>
                                    <td class="py-2 text-right font-bold">{{ number_format($item->qty_loaded_kg, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-slate-200">
                            <tr>
                                <td colspan="2" class="pt-2 text-right font-bold text-slate-500">Total</td>
                                <td class="pt-2 text-right font-bold text-slate-800">
                                    {{ number_format($shipment->items->sum('qty_loaded_kg'), 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-12">
                <div class="bg-slate-100 rounded-full w-16 h-16 flex items-center justify-center mx-auto mb-4">
                    <i data-lucide="check-check" class="w-8 h-8 text-slate-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-800">Semua Beres!</h3>
                <p class="text-slate-500">Tidak ada pengiriman yang perlu diverifikasi saat ini.</p>
            </div>
        @endforelse
    </div>
@endsection