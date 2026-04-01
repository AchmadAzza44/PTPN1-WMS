@extends('layouts.modern')

@section('title', 'Review Surat Jalan / DO')
@section('header', 'Verifikasi Data Dokumen')
@section('subheader', 'Pastikan data dokumen sesuai sebelum membuat Surat Jalan')

@section('content')
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Kolom Kiri: Gambar Asli -->
        <div class="glass p-6 rounded-2xl shadow-sm h-fit">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i data-lucide="image" class="w-5 h-5 mr-2 text-blue-500"></i>
                Dokumen Fisik (Surat Kuasa/DO)
            </h3>
            <div class="rounded-xl overflow-hidden border border-slate-200 shadow-inner bg-slate-900/5">
                <img src="{{ asset($image_path) }}" alt="Dokumen Fisik" class="w-full h-auto object-contain max-h-[600px]">
            </div>
        </div>

        <!-- Kolom Kanan: Form Hasil OCR -->
        <div class="glass p-8 rounded-2xl shadow-sm relative overflow-hidden">
            <div class="absolute top-0 right-0 p-6 opacity-5">
                <i data-lucide="file-check" class="w-32 h-32 text-blue-600"></i>
            </div>

            <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center">
                <span class="bg-blue-100 text-blue-600 p-2 rounded-lg mr-3">
                    <i data-lucide="scan-text" class="w-5 h-5"></i>
                </span>
                Hasil Ekstraksi AI
            </h3>

            <!-- Form ini akan mengirim data ke Shipment Create untuk Pre-fill -->
            <form action="{{ route('shipments.create') }}" method="GET">
                <div class="space-y-5">

                    <!-- Data Kontrak / DO -->
                    <div class="grid grid-cols-1 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nomor DO / SO (Manual)</label>
                            <input type="text" name="do_number_manual" value="{{ $result['do_number'] ?? '' }}"
                                class="w-full rounded-xl border-slate-200 font-mono text-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-1">Nomor Kontrak</label>
                            <input type="text" name="contract_number_ref" value="{{ $result['contract_number'] ?? '' }}"
                                class="w-full rounded-xl border-slate-200 font-mono text-sm focus:border-blue-500 focus:ring-blue-500 bg-slate-50">
                            <p class="text-[10px] text-slate-400 mt-1">Digunakan untuk referensi pencarian PO</p>
                        </div>
                    </div>

                    <!-- Jumlah Pesanan -->
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1">Jumlah Pesanan (Sesuai Dokumen)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="documented_qty_kg"
                                value="{{ $result['documented_qty'] ?? 0 }}"
                                class="w-full rounded-xl border-slate-200 font-bold text-lg text-blue-600 focus:border-blue-500 focus:ring-blue-500">
                            <span class="absolute right-4 top-3 text-slate-400 text-sm font-bold">KG</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Pastikan angka ini sesuai dengan yang tertulis di Surat
                            Kuasa/DO.</p>
                    </div>

                </div>

                <div class="flex items-center justify-end mt-8 pt-6 border-t border-slate-100">
                    <a href="{{ route('ocr.index') }}"
                        class="text-slate-500 hover:text-slate-700 font-medium mr-6 text-sm">Scan Ulang</a>

                    <button type="submit"
                        class="relative overflow-hidden group py-3 px-6 rounded-xl text-white font-bold bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-500/30 transition-all transform hover:-translate-y-1">
                        <span class="relative z-10 flex items-center">
                            Lanjut ke Pemilihan Lot
                            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection