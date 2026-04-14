@extends('layouts.modern')

@section('title', 'Buat Berita Acara')
@section('header', 'Buat Berita Acara Pengiriman')
@section('subheader', 'Buat Berita Acara dengan satu atau lebih PO/Surat Kuasa')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            <!-- Kolom Kiri: Foto Dokumen -->
            <div class="lg:col-span-5 relative">
                <div class="sticky top-6 glass p-4 rounded-2xl shadow-sm border-t-4 border-blue-500">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center">
                        <i data-lucide="image" class="w-4 h-4 mr-2 text-slate-400"></i>
                        Foto Dokumen OCR
                    </h3>
                    <div class="rounded-xl overflow-y-auto border border-slate-200 bg-slate-50 flex items-center justify-center p-2 custom-scrollbar min-h-[300px] max-h-[80vh]">
                        @if(request('foto_path') || (!empty($preFill) && !empty($preFill['foto_path'])))
                            @php 
                                $foto = request('foto_path') ?? $preFill['foto_path'];
                            @endphp
                            <img src="{{ url('/cloud-storage/' . $foto) }}" alt="Dokumen" class="w-full h-auto object-top rounded-lg block">
                        @else
                            <div class="text-center p-6">
                                <div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center mx-auto mb-3">
                                    <i data-lucide="image-off" class="w-8 h-8 text-slate-400"></i>
                                </div>
                                <p class="text-sm font-bold text-slate-500">Preview Tidak Tersedia</p>
                                <p class="text-xs text-slate-400 mt-1">Pengiriman ini dibuat secara manual atau <br>tidak memiliki lampiran foto OCR.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            
            <div class="lg:col-span-7">
        <form action="{{ route('shipments.store') }}" method="POST" id="shipmentForm">
            @csrf
            <input type="hidden" name="foto_path" value="{{ request('foto_path') ?? ($preFill['foto_path'] ?? '') }}">

            <!-- Cek Stok Alert -->
            <div id="stockAlert" class="hidden mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm items-start transition-all">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500 mr-3 mt-0.5 flex-shrink-0"></i>
                <div>
                    <h4 class="text-sm font-bold text-red-800">Stok Gudang Tidak Mencukupi!</h4>
                    <p class="text-sm text-red-700 mt-1">Stok yang tersedia saat ini (<strong id="availableStockDisplay" class="font-mono">{{ number_format($totalAvailableStock ?? 0, 2) }}</strong> KG) tidak cukup untuk memenuhi target pengiriman yang diminta (<strong id="requestedStockDisplay" class="font-mono">0</strong> KG).</p>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════ -->
            <!-- DATA BERITA ACARA (Shared across all entries) -->
            <!-- ══════════════════════════════════════════════ -->
            <div class="glass p-6 rounded-2xl shadow-sm mb-6 border-t-4 border-indigo-500">
                <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                    <i data-lucide="file-text" class="w-4 h-4 mr-2"></i>
                    Data Berita Acara (Umum)
                </h3>
                <div class="space-y-4">
                    <!-- Nama Pembeli -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Pembeli</label>
                        <input type="text" name="buyer_name" value="{{ $preFill['buyer_name'] ?? '' }}"
                            placeholder="Contoh: PT. Bitung Gunasejahtera"
                            class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm">
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-4">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center">
                            <i data-lucide="truck" class="w-4 h-4 mr-2"></i>
                            Data Angkutan
                        </h4>
                        
                        <!-- Ekspedisi -->
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Ekspedisi</label>
                            <input type="text" name="transporter_name" value="{{ $preFill['transporter_name'] ?? '' }}"
                                placeholder="Contoh: PT. Samudera Raflesia Logistik"
                                class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. Polisi</label>
                                <input type="text" name="vehicle_plate" value="{{ $preFill['vehicle_plate'] ?? '' }}"
                                    placeholder="BE 1234 XY"
                                    class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm uppercase">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Supir</label>
                                <input type="text" name="driver_name" value="{{ $preFill['driver_name'] ?? '' }}"
                                    placeholder="Nama Lengkap"
                                    class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════ -->
            <!-- DAFTAR PO / SURAT KUASA (DYNAMIC N ENTRIES)   -->
            <!-- ══════════════════════════════════════════════ -->
            <div id="entries-container">
                <!-- Entry #0 will be injected by JS on load -->
            </div>

            <!-- Tombol Tambah PO -->
            <div class="mt-3 mb-6">
                <button type="button" onclick="addEntry()"
                    class="w-full p-3 border-2 border-dashed border-blue-300 rounded-xl text-blue-600 font-bold text-sm hover:bg-blue-50 hover:border-blue-400 transition-all flex items-center justify-center gap-2">
                    <i data-lucide="plus-circle" class="w-5 h-5"></i>
                    Tambah PO / Surat Kuasa Lagi
                </button>
                <p class="text-[10px] text-slate-400 mt-2 text-center italic">
                    * Untuk Berita Acara dengan beberapa kontrak/PO, klik tombol di atas untuk menambah entry baru.
                </p>
            </div>

            <!-- ══════════════════════════════════════════════ -->
            <!-- TOTAL & SUBMIT                                -->
            <!-- ══════════════════════════════════════════════ -->
            <div class="glass p-4 rounded-xl shadow-sm mb-4">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-bold text-slate-500 uppercase">Total Muatan Seluruh Entry</span>
                    <span class="text-2xl font-mono font-bold text-slate-800" id="grandTotalDisplay">0 <span class="text-sm text-slate-400">KG</span></span>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center">
                    <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                    Buat Berita Acara
                </button>
            </div>
        </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        let stocks = @json($stocks);
        const totalAvailableStock = {{ $totalAvailableStock ?? 0 }};
        let entryCount = 0;

        // Pre-fill data from OCR
        const preFill = @json($preFill);

        function checkStockWarning() {
            let totalAllEntries = 0;
            document.querySelectorAll('.entry-qty-total').forEach(el => {
                totalAllEntries += parseFloat(el.textContent) || 0;
            });

            const alertEl = document.getElementById('stockAlert');
            const displayEl = document.getElementById('requestedStockDisplay');
            
            if (totalAllEntries > totalAvailableStock && totalAllEntries > 0) {
                alertEl.classList.remove('hidden');
                alertEl.classList.add('flex');
                displayEl.textContent = totalAllEntries.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
            } else {
                alertEl.classList.add('hidden');
                alertEl.classList.remove('flex');
            }
        }

        /**
         * Adds a new PO/Surat Kuasa entry block
         */
        function addEntry(prefillData = null) {
            const idx = entryCount++;
            const container = document.getElementById('entries-container');

            const entryDiv = document.createElement('div');
            entryDiv.className = 'entry-block glass p-6 rounded-2xl shadow-sm mb-4 border-l-4 border-amber-400 relative';
            entryDiv.dataset.entryIndex = idx;

            const contractVal = prefillData?.contract_number_ref || '';
            const doVal = prefillData?.do_number_manual || '';
            const skVal = prefillData?.surat_kuasa_number || '';
            const qtyVal = prefillData?.documented_qty_kg || '';

            entryDiv.innerHTML = `
                <div class="flex justify-between items-start mb-4">
                    <h3 class="text-sm font-bold text-amber-700 uppercase tracking-wider flex items-center">
                        <i data-lucide="package" class="w-4 h-4 mr-2"></i>
                        <span class="entry-label">PO / Surat Kuasa #${idx + 1}</span>
                    </h3>
                    ${idx > 0 ? `
                    <button type="button" onclick="removeEntry(this)" class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus entry ini">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>` : ''}
                </div>

                <!-- Data Dokumen -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nomor Kontrak *</label>
                        <input type="text" name="entries[${idx}][contract_number]" required value="${contractVal}"
                            placeholder="1794/HO-SUPCO/SIR-L/N-1/IX/2025"
                            class="block w-full border-slate-200 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm bg-white text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. PO / DO</label>
                        <input type="text" name="entries[${idx}][do_number_manual]" value="${doVal}"
                            placeholder="0076/KARET SC/2026"
                            class="block w-full border-slate-200 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm bg-white text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. Surat Kuasa</label>
                        <input type="text" name="entries[${idx}][surat_kuasa_number]" value="${skVal}"
                            placeholder="LOCBGS-2601-0043"
                            class="block w-full border-slate-200 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm bg-white text-sm font-mono">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Volume Pengiriman (Kg)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="entries[${idx}][documented_qty_kg]" value="${qtyVal}"
                                placeholder="0.00" onchange="recalcEntryFIFO(${idx})" oninput="checkStockWarning()"
                                class="entry-qty-input block w-full border-slate-200 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm bg-white text-sm font-mono font-bold text-amber-600 pr-12">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">KG</span>
                        </div>
                    </div>
                </div>

                <!-- Lot Items for this entry -->
                <div class="border-t border-slate-100 pt-4">
                    <div class="flex justify-between items-center mb-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase flex items-center">
                            <i data-lucide="layers" class="w-3 h-3 mr-1"></i> Alokasi Lot Stok
                        </h4>
                        <button type="button" onclick="addItemRow(${idx})"
                            class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-2 py-1 rounded-lg transition-colors flex items-center">
                            <i data-lucide="plus" class="w-3 h-3 mr-1"></i> Tambah Lot
                        </button>
                    </div>
                    <div class="entry-items-container space-y-2" id="entry-items-${idx}">
                        <!-- Items injected here -->
                    </div>
                    <div class="mt-3 p-3 bg-slate-50 rounded-lg flex justify-between items-center">
                        <span class="text-xs font-bold text-slate-500 uppercase">Subtotal Entry</span>
                        <span class="text-lg font-mono font-bold text-slate-700"><span class="entry-qty-total" id="entry-total-${idx}">0</span> <span class="text-xs text-slate-400">KG</span></span>
                    </div>
                </div>
            `;

            container.appendChild(entryDiv);
            if (window.lucide) window.lucide.createIcons();

            // Auto-add first lot row
            addItemRow(idx);

            // If volume is specified, auto-FIFO
            if (qtyVal && parseFloat(qtyVal) > 0) {
                setTimeout(() => recalcEntryFIFO(idx), 100);
            }

            renumberEntries();
        }

        function removeEntry(btn) {
            btn.closest('.entry-block').remove();
            renumberEntries();
            calculateGrandTotal();
            checkStockWarning();
        }

        function renumberEntries() {
            const entries = document.querySelectorAll('.entry-block');
            entries.forEach((entry, i) => {
                const label = entry.querySelector('.entry-label');
                if (label) label.textContent = `PO / Surat Kuasa #${i + 1}`;
            });
        }

        /**
         * Add a lot item row inside a specific entry
         */
        let itemCounters = {};
        function addItemRow(entryIdx) {
            if (!itemCounters[entryIdx]) itemCounters[entryIdx] = 0;
            const itemIdx = itemCounters[entryIdx]++;
            const container = document.getElementById(`entry-items-${entryIdx}`);

            const row = document.createElement('div');
            row.className = 'item-row p-3 rounded-lg border border-slate-200 bg-white hover:border-blue-300 transition-colors';
            row.innerHTML = `
                <div class="grid grid-cols-12 gap-3 items-end">
                    <div class="col-span-12 md:col-span-6">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Pilih Lot</label>
                        <select name="entries[${entryIdx}][items][${itemIdx}][stock_lot_id]" required
                                onchange="updateItemMax(this, ${entryIdx})"
                                class="stock-select block w-full border-slate-200 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 font-mono">
                            <option value="">-- Pilih Lot --</option>
                            ${stocks.map((s, i) => {
                                const fifo = i < 3 ? ' ⭐ FIFO' : '';
                                return `<option value="${s.id}" data-max="${s.remaining_weight.toFixed(2)}">Lot ${s.lot_number} (${s.quality_type}) - Sisa: ${s.remaining_weight.toLocaleString()} kg${fifo}</option>`;
                            }).join('')}
                        </select>
                    </div>
                    <div class="col-span-8 md:col-span-4">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase mb-1">Berat Muat (Kg)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="entries[${entryIdx}][items][${itemIdx}][qty_loaded_kg]" required
                                class="qty-input block w-full border-slate-200 rounded-lg text-xs focus:ring-blue-500 focus:border-blue-500 font-mono pr-8"
                                oninput="calculateEntryTotal(${entryIdx})">
                            <span class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-400 text-[10px] font-bold">KG</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-0.5 text-right max-hint">Maks: -</p>
                    </div>
                    <div class="col-span-4 md:col-span-2 flex items-center justify-end">
                        <button type="button" onclick="this.closest('.item-row').remove(); calculateEntryTotal(${entryIdx});"
                                class="p-1.5 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>
                <div class="pallet-container mt-2 hidden p-2 bg-slate-50 border border-slate-100 rounded-lg">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase mb-1"><i data-lucide="layers" class="w-3 h-3 inline mr-1"></i> Pilih Palet (Opsional)</label>
                    <div class="pallet-list grid grid-cols-2 md:grid-cols-3 gap-1.5"></div>
                </div>
            `;

            container.appendChild(row);
            if (window.lucide) window.lucide.createIcons();
        }

        function updateItemMax(select, entryIdx) {
            const row = select.closest('.item-row');
            const input = row.querySelector('.qty-input');
            const hint = row.querySelector('.max-hint');
            const palletContainer = row.querySelector('.pallet-container');
            const palletList = row.querySelector('.pallet-list');
            
            const selectedStockId = select.value;
            const stock = stocks.find(s => s.id == selectedStockId);

            // Determine the item index from the select name
            const nameMatch = select.name.match(/entries\[\d+\]\[items\]\[(\d+)\]/);
            const itemIdx = nameMatch ? nameMatch[1] : 0;

            if (stock) {
                const max = stock.remaining_weight.toFixed(2);
                input.max = max;
                input.value = max;
                input.readOnly = false;
                hint.textContent = `Maks: ${parseFloat(max).toLocaleString()} kg`;
                hint.classList.add('text-blue-500');

                if (stock.details && stock.details.length > 0) {
                    palletContainer.classList.remove('hidden');
                    palletList.innerHTML = stock.details.map(d => `
                        <label class="flex items-center p-1.5 rounded-md border border-slate-200 bg-white hover:bg-blue-50 cursor-pointer transition-colors text-[10px] font-mono">
                            <input type="checkbox" name="entries[${entryIdx}][items][${itemIdx}][selected_details][]" value="${d.id}" class="pallet-checkbox mr-1.5 rounded text-blue-600 focus:ring-blue-500" data-weight="${d.net_weight_kg}" onchange="recalcPalletWeight(this, ${entryIdx})">
                            <div>
                                <span class="block font-bold text-slate-700">P-${d.fdf_number || d.pallet_number || d.id}</span>
                                <span class="text-[9px] text-slate-400">${d.net_weight_kg} kg</span>
                            </div>
                        </label>
                    `).join('');
                } else {
                    palletContainer.classList.add('hidden');
                    palletList.innerHTML = '';
                }
            } else {
                input.removeAttribute('max');
                input.value = '';
                hint.textContent = 'Maks: -';
                palletContainer.classList.add('hidden');
                palletList.innerHTML = '';
            }

            calculateEntryTotal(entryIdx);
        }

        function recalcPalletWeight(checkbox, entryIdx) {
            const row = checkbox.closest('.item-row');
            const input = row.querySelector('.qty-input');
            const checkboxes = row.querySelectorAll('.pallet-checkbox');
            
            let checkedWeight = 0;
            let hasChecked = false;
            
            checkboxes.forEach(cb => {
                if (cb.checked) {
                    checkedWeight += parseFloat(cb.dataset.weight);
                    hasChecked = true;
                }
            });

            if (hasChecked) {
                input.value = checkedWeight.toFixed(2);
                input.readOnly = true;
                input.classList.add('bg-slate-100');
            } else {
                const select = row.querySelector('.stock-select');
                const stock = stocks.find(s => s.id == select.value);
                if (stock) input.value = stock.remaining_weight.toFixed(2);
                input.readOnly = false;
                input.classList.remove('bg-slate-100');
            }
            
            calculateEntryTotal(entryIdx);
        }

        function calculateEntryTotal(entryIdx) {
            const container = document.getElementById(`entry-items-${entryIdx}`);
            if (!container) return;
            let total = 0;
            container.querySelectorAll('.qty-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            const display = document.getElementById(`entry-total-${entryIdx}`);
            if (display) display.textContent = total.toLocaleString('id-ID');
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let grand = 0;
            document.querySelectorAll('.entry-qty-total').forEach(el => {
                grand += parseFloat(el.textContent?.replace(/\./g, '').replace(',', '.')) || 0;
            });
            document.getElementById('grandTotalDisplay').innerHTML = `${grand.toLocaleString('id-ID')} <span class="text-sm text-slate-400">KG</span>`;
            checkStockWarning();
        }

        /**
         * Auto-FIFO allocation for a specific entry based on its volume
         */
        function recalcEntryFIFO(entryIdx) {
            const entryBlock = document.querySelector(`.entry-block[data-entry-index="${entryIdx}"]`);
            if (!entryBlock) return;

            const qtyInput = entryBlock.querySelector('.entry-qty-input');
            const targetQty = parseFloat(qtyInput?.value) || 0;
            if (targetQty <= 0) return;

            const itemsContainer = document.getElementById(`entry-items-${entryIdx}`);
            itemsContainer.innerHTML = '';
            itemCounters[entryIdx] = 0;

            const allocTarget = Math.min(targetQty, totalAvailableStock);
            let remaining = allocTarget;

            for (let i = 0; i < stocks.length && remaining > 0; i++) {
                const stock = stocks[i];
                const allocate = Math.min(stock.remaining_weight, remaining);
                if (allocate <= 0) continue;

                addItemRow(entryIdx);

                const rows = itemsContainer.querySelectorAll('.item-row');
                const row = rows[rows.length - 1];
                const select = row.querySelector('select');
                const qtyInp = row.querySelector('.qty-input');

                for (let j = 0; j < select.options.length; j++) {
                    if (select.options[j].value == stock.id) {
                        select.selectedIndex = j;
                        break;
                    }
                }

                updateItemMax(select, entryIdx);
                qtyInp.value = allocate.toFixed(2);

                // Mark checkboxes
                const checkboxes = row.querySelectorAll('.pallet-checkbox');
                let tempWeight = 0;
                checkboxes.forEach(cb => {
                    if (tempWeight + parseFloat(cb.dataset.weight) <= allocate) {
                        cb.checked = true;
                        tempWeight += parseFloat(cb.dataset.weight);
                    }
                });
                if (tempWeight > 0) recalcPalletWeight(checkboxes[0], entryIdx);

                row.classList.add('border-green-300', 'bg-green-50/30');
                remaining -= allocate;
            }

            calculateEntryTotal(entryIdx);
            if (window.lucide) window.lucide.createIcons();
        }

        // ── Initialize ──
        document.addEventListener('DOMContentLoaded', () => {
            // Add first entry with pre-fill from OCR
            if (preFill && preFill.contract_number_ref) {
                addEntry(preFill);
            } else {
                addEntry();
            }
        });
    </script>
@endsection