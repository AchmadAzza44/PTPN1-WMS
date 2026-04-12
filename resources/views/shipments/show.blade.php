@extends('layouts.modern')

@section('title', 'Detail Pengiriman')
@section('header', 'Detail Pengiriman')
@section('subheader', 'Informasi lengkap dan status dokumen pengiriman')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Status Banner -->
        <div
            class="p-4 rounded-xl flex items-center justify-between {{ $shipment->status == 'completed' ? 'bg-green-100 border border-green-200 text-green-800' : ($shipment->status == 'verified' ? 'bg-blue-100 border border-blue-200 text-blue-800' : 'bg-yellow-100 border border-yellow-200 text-yellow-800') }}">
            <div class="flex items-center">
                @if($shipment->status == 'completed')
                    <i data-lucide="check-circle-2" class="w-6 h-6 mr-3"></i>
                    <div>
                        <h3 class="font-bold">Pengiriman Selesai</h3>
                        <p class="text-sm">Semua dokumen telah lengkap dan diarsipkan.</p>
                    </div>
                @elseif($shipment->status == 'verified')
                    <i data-lucide="file-check" class="w-6 h-6 mr-3"></i>
                    <div>
                        <h3 class="font-bold">Terverifikasi - Siap Jalan</h3>
                        <p class="text-sm">Silakan cetak dokumen jalan dan berita acara.</p>
                    </div>
                @else
                    <i data-lucide="clock" class="w-6 h-6 mr-3"></i>
                    <div>
                        <h3 class="font-bold">Menunggu Verifikasi</h3>
                        <p class="text-sm">Menunggu pengecekan fisik oleh Petugas Gudang.</p>
                    </div>
                @endif
            </div>

            @if($shipment->status == 'draft')
                <!-- Simulate Verify Button for Demo if user wants to self-verify -->
                <form action="{{ route('shipments.verify', $shipment->id) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="text-sm bg-white/50 hover:bg-white px-3 py-1.5 rounded-lg border border-transparent hover:border-yellow-300 transition-colors font-bold">
                        Simulasi Verifikasi
                    </button>
                </form>
            @endif
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Info -->
            <div class="md:col-span-2 space-y-6">
                <!-- Info Card -->
                <div class="glass p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Informasi Kontrak</h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Nama Pembeli</p>
                            <p class="font-bold text-slate-800">{{ $shipment->purchaseOrder->contract->buyer_name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500">Nomor Kontrak</p>
                            <p class="font-mono font-bold text-slate-800">
                                {{ $shipment->purchaseOrder->contract->contract_number ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-slate-500">No. DO / SP</p>
                            <p class="font-mono font-bold text-slate-800">{{ $shipment->do_number_manual ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Volume Dokumen</p>
                            <p class="font-bold text-slate-800">{{ number_format($shipment->documented_qty_kg ?? 0, 2) }} Kg</p>
                        </div>
                    </div>
                </div>

                <!-- Items Table -->
                <div class="glass p-6 rounded-2xl shadow-sm">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Daftar Muatan</h3>
                    <table class="w-full text-sm text-left">
                        <thead class="text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="pb-2">Lot Number</th>
                                <th class="pb-2">Mutu</th>
                                <th class="pb-2 text-right">Berat (Kg)</th>
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

            <!-- Right Actions -->
            <div class="space-y-6">
                <!-- Action Card -->
                <div class="glass p-6 rounded-2xl shadow-sm sticky top-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <i data-lucide="printer" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Dokumen
                    </h3>

                    @if($shipment->status == 'verified' || $shipment->status == 'completed')
                        <div class="space-y-3">
                            <a href="{{ route('shipments.print_sj', $shipment->id) }}" target="_blank"
                                class="block w-full py-3 px-4 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-center transition-colors shadow-lg shadow-slate-500/20">
                                <i data-lucide="file-text" class="w-4 h-4 inline mr-2"></i>
                                Cetak Surat Jalan
                            </a>
                            <a href="{{ route('shipments.print_sjt', $shipment->id) }}" target="_blank"
                                class="block w-full py-3 px-4 bg-slate-700 hover:bg-slate-800 text-white font-bold rounded-xl text-center transition-colors shadow-lg shadow-slate-500/20">
                                <i data-lucide="shield-check" class="w-4 h-4 inline mr-2"></i>
                                Cetak Surat Jaminan
                            </a>
                            <a href="{{ route('shipments.print_ba', $shipment->id) }}" target="_blank"
                                class="block w-full py-3 px-4 bg-white border-2 border-slate-200 hover:border-slate-300 text-slate-700 font-bold rounded-xl text-center transition-colors">
                                <i data-lucide="file-check" class="w-4 h-4 inline mr-2"></i>
                                Cetak Berita Acara
                            </a>
                        </div>
                    @else
                        <div class="text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <i data-lucide="lock" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-400 font-bold">Dokumen terkunci hingga diverifikasi</p>
                        </div>
                    @endif

                    @if($shipment->status == 'verified')
                        <div class="my-6 border-t border-slate-100"></div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                            <i data-lucide="edit-3" class="w-5 h-5 mr-2 text-blue-500"></i>
                            Review & Edit Data (Krani)
                        </h3>
                        <form action="{{ route('shipments.update_details', $shipment->id) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">No. DO / SP</label>
                                <input type="text" name="do_number_manual" value="{{ $shipment->do_number_manual ?? '' }}"
                                    class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Volume Dokumen (Kg)</label>
                                <input type="number" step="0.01" name="documented_qty_kg"
                                    value="{{ $shipment->documented_qty_kg ?? 0 }}"
                                    class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500 font-mono">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Perusahaan Ekspedisi</label>
                                <input type="text" name="transporter_name" value="{{ $shipment->transporter_name !== '-' ? $shipment->transporter_name : '' }}"
                                    class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500 mb-2">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">No. Polisi (Plat)</label>
                                    <input type="text" name="vehicle_plate" value="{{ $shipment->vehicle_plate !== '-' ? $shipment->vehicle_plate : '' }}"
                                        class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500 uppercase">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Nama Supir</label>
                                    <input type="text" name="driver_name" value="{{ $shipment->driver_name !== '-' ? $shipment->driver_name : '' }}"
                                        class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full py-2 bg-green-600 hover:bg-green-700 text-white font-bold rounded-lg text-xs transition-colors shadow-lg shadow-green-500/30">
                                <i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i>
                                Konfirmasi & Selesaikan
                            </button>
                        </form>
                    @endif

                    <div class="my-6 border-t border-slate-100"></div>

                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <i data-lucide="upload-cloud" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Arsip Dokumen Balik
                    </h3>

                    @if($shipment->signed_document_path)
                        <div class="p-4 bg-green-50 border border-green-100 rounded-xl mb-4">
                            <p class="text-xs text-green-600 font-bold mb-2">Dokumen Tersimpan</p>
                            <a href="{{ asset('storage/' . $shipment->signed_document_path) }}" target="_blank"
                                class="text-sm font-bold text-green-700 hover:underline flex items-center">
                                <i data-lucide="file" class="w-4 h-4 mr-1"></i>
                                Lihat Dokumen
                            </a>
                        </div>
                    @endif

                    @if($shipment->status == 'verified' || $shipment->status == 'completed')
                        <form action="{{ route('shipments.upload_signed_doc', $shipment->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-3">
                                <label class="block text-xs font-bold text-slate-500 uppercase">Upload Surat Jalan
                                    (Signed)</label>
                                <input type="file" name="signed_doc" required
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-2">
                                <button type="submit"
                                    class="w-full py-2 bg-blue-100 hover:bg-blue-200 text-blue-800 font-bold rounded-lg text-xs transition-colors">
                                    Upload & Selesai
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection