@extends('layouts.modern')

@section('title', 'Detail Berita Acara')
@section('header', 'Detail Berita Acara')
@section('subheader', 'Informasi lengkap dokumen pengiriman (' . count($group->shipments) . ' kontrak/PO)')

@section('content')
    <div class="max-w-5xl mx-auto space-y-6">

        <!-- Status Banner -->
        <div
            class="p-4 rounded-xl flex items-center justify-between {{ $group->status == 'completed' ? 'bg-green-100 border border-green-200 text-green-800' : ($group->status == 'verified' ? 'bg-blue-100 border border-blue-200 text-blue-800' : 'bg-yellow-100 border border-yellow-200 text-yellow-800') }}">
            <div class="flex items-center">
                @if($group->status == 'completed')
                    <i data-lucide="check-circle-2" class="w-6 h-6 mr-3"></i>
                    <div>
                        <h3 class="font-bold">Pengiriman Selesai</h3>
                        <p class="text-sm">Semua dokumen telah lengkap dan diarsipkan.</p>
                    </div>
                @elseif($group->status == 'verified')
                    <i data-lucide="file-check" class="w-6 h-6 mr-3"></i>
                    <div>
                        <h3 class="font-bold">Terverifikasi - Siap Jalan</h3>
                        <p class="text-sm">Silakan cetak dokumen jalan dan Berita Acara gabungan.</p>
                    </div>
                @else
                    <i data-lucide="clock" class="w-6 h-6 mr-3"></i>
                    <div>
                        <h3 class="font-bold">Menunggu Verifikasi</h3>
                        <p class="text-sm">Menunggu pengecekan fisik oleh Petugas Gudang.</p>
                    </div>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Left Info (Main) -->
            <div class="md:col-span-2 space-y-6">
                
                <!-- BA General Info -->
                <div class="glass p-6 rounded-2xl shadow-sm border-t-4 border-indigo-500">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-indigo-500"></i>
                        Informasi Umum Berita Acara
                    </h3>
                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <p class="text-slate-500">Nomor BA</p>
                            <p class="font-mono font-bold text-slate-800">{{ $group->ba_number ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Total Muatan</p>
                            <p class="font-bold text-slate-800">{{ number_format($group->totalWeight, 0, ',', '.') }} Kg</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Nama Pembeli</p>
                            <p class="font-bold text-slate-800">{{ $group->buyer_name ?? '-' }}</p>
                        </div>
                        <div>
                            <p class="text-slate-500">Ekspedisi & Angkutan</p>
                            <p class="font-bold text-slate-800">{{ $group->transporter_name }}</p>
                            <p class="text-xs text-slate-500">{{ $group->vehicle_plate }} — {{ $group->driver_name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Loop Shipments -->
                <h3 class="text-md font-bold text-slate-700 mt-6 pt-2">Daftar Dokumen ({{ count($group->shipments) }})</h3>
                
                @foreach($group->shipments as $idx => $shipment)
                <div class="glass p-5 rounded-xl shadow-sm border border-slate-100">
                    <div class="flex justify-between items-start mb-3">
                        <h4 class="font-bold text-slate-800 flex items-center">
                            <span class="w-6 h-6 rounded-full bg-blue-100 text-blue-700 font-bold text-xs flex items-center justify-center mr-2">#{{ $idx + 1 }}</span>
                            PO: {{ $shipment->purchaseOrder->po_number ?? '-' }}
                        </h4>
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-md text-xs font-bold font-mono">
                            {{ number_format($shipment->documented_qty_kg, 2) }} Kg
                        </span>
                    </div>

                    <div class="text-sm grid grid-cols-2 gap-2 mb-4 bg-slate-50 p-3 rounded-lg">
                        <div><span class="text-slate-500 block text-xs">Kontrak</span> <span class="font-mono font-bold">{{ $shipment->purchaseOrder->contract->contract_number ?? '-' }}</span></div>
                        <div><span class="text-slate-500 block text-xs">Surat Kuasa</span> <span class="font-mono font-bold">{{ $shipment->surat_kuasa_number ?? '-' }}</span></div>
                    </div>

                    <table class="w-full text-xs text-left mt-2">
                        <thead class="text-slate-400 font-bold border-b border-slate-200">
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
                    </table>
                </div>
                @endforeach
            </div>

            <!-- Right Actions -->
            <div class="space-y-6">
                <!-- Action Card -->
                <div class="glass p-6 rounded-2xl shadow-sm sticky top-6">
                    <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                        <i data-lucide="printer" class="w-5 h-5 mr-2 text-blue-500"></i>
                        Dokumen
                    </h3>

                    @if($group->status == 'verified' || $group->status == 'completed')
                        <div class="space-y-3">
                            <a href="{{ route('shipments.print_ba', $group->id) }}" target="_blank"
                                class="block w-full py-3 px-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl text-center transition-colors shadow-xl shadow-blue-500/30 border border-blue-500 relative overflow-hidden group">
                                <div class="absolute inset-0 bg-white/20 -translate-x-full group-hover:animate-[shimmer_1.5s_infinite]"></div>
                                <i data-lucide="file-check" class="w-5 h-5 inline mr-2 align-text-bottom"></i>
                                Cetak Berita Acara
                            </a>
                            
                            <hr class="border-slate-100 my-4">

                            <!-- Cetak SJ/SJT per shipment -->
                            <p class="text-xs font-bold text-slate-400 uppercase mb-2">Surat Jalan Per Kontrak:</p>
                            @foreach($group->shipments as $idx => $shipment)
                            <div class="p-2 border border-slate-200 rounded-lg bg-slate-50 mb-2">
                                <p class="text-xs font-bold text-slate-700 mb-2">#{{ $idx+1 }} - PO {{ $shipment->purchaseOrder->po_number }}</p>
                                <div class="flex gap-2">
                                    <a href="{{ route('shipments.print_sj', $shipment->id) }}" target="_blank"
                                        class="flex-1 py-1.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded text-center transition-colors text-[10px]">
                                        SJ
                                    </a>
                                    <a href="{{ route('shipments.print_sjt', $shipment->id) }}" target="_blank"
                                        class="flex-1 py-1.5 bg-slate-700 hover:bg-slate-800 text-white font-bold rounded text-center transition-colors text-[10px]">
                                        Jaminan
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6 bg-slate-50 rounded-xl border border-dashed border-slate-300">
                            <i data-lucide="lock" class="w-8 h-8 text-slate-300 mx-auto mb-2"></i>
                            <p class="text-xs text-slate-400 font-bold">Dokumen terkunci hingga diverifikasi</p>
                        </div>
                    @endif

                    @if($group->status == 'verified' && auth()->user()->role == 'admin')
                        <div class="my-6 border-t border-slate-100"></div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                            <i data-lucide="edit-3" class="w-5 h-5 mr-2 text-blue-500"></i>
                            Konfirmasi Akhir (Krani)
                        </h3>
                        <form action="{{ route('shipments.update_details', $group->id) }}" method="POST" class="space-y-3">
                            @csrf
                            @method('PUT')
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase">Perusahaan Ekspedisi</label>
                                <input type="text" name="transporter_name" value="{{ $group->transporter_name !== '-' ? $group->transporter_name : '' }}"
                                    class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500 mb-2">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">No. Polisi</label>
                                    <input type="text" name="vehicle_plate" value="{{ $group->vehicle_plate !== '-' ? $group->vehicle_plate : '' }}"
                                        class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500 uppercase">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Nama Supir</label>
                                    <input type="text" name="driver_name" value="{{ $group->driver_name !== '-' ? $group->driver_name : '' }}"
                                        class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Nama Krani</label>
                                    <input type="text" name="krani_name" value="{{ $group->krani_name ?? auth()->user()->name }}"
                                        class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase">Manajer</label>
                                    <input type="text" name="manager_name" value="{{ $group->manager_name ?? 'Erwanda Erianto' }}"
                                        class="block w-full border-slate-200 rounded-lg text-sm bg-blue-50 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>
                            <button type="submit"
                                class="w-full mt-2 py-2.5 bg-green-600 hover:bg-green-700 text-white font-bold rounded-xl text-sm transition-colors shadow-lg shadow-green-500/30">
                                <i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i>
                                Selesaikan Berita Acara
                            </button>
                        </form>
                    @endif

                    <div class="my-6 border-t border-slate-100"></div>

                    <h3 class="text-sm font-bold text-slate-800 mb-4 flex items-center mt-2">
                        <i data-lucide="upload-cloud" class="w-4 h-4 mr-2 text-blue-500"></i>
                        Arsip BA Balik (Signed)
                    </h3>

                    @if($group->signed_document_path)
                        <div class="p-4 bg-green-50 border border-green-100 rounded-xl mb-4">
                            <p class="text-xs text-green-600 font-bold mb-2">Dokumen Tersimpan</p>
                            <a href="{{ asset('storage/' . $group->signed_document_path) }}" target="_blank"
                                class="text-sm font-bold text-green-700 hover:underline flex items-center">
                                <i data-lucide="file" class="w-4 h-4 mr-1"></i>
                                Lihat Dokumen
                            </a>
                        </div>
                    @endif

                    @if(($group->status == 'verified' || $group->status == 'completed') && auth()->user()->role == 'admin')
                        <form action="{{ route('shipments.upload_signed_doc', $group->id) }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-3">
                                <input type="file" name="signed_doc" required
                                    class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 mb-2 cursor-pointer">
                                <button type="submit"
                                    class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition-colors">
                                    Upload BA
                                </button>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>
@endsection
