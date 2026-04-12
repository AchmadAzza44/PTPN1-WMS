@extends('layouts.modern')

@section('title', 'Buat Pengiriman')
@section('header', 'Pengiriman Baru (Outbound)')
@section('subheader', 'Buat Surat Jalan dan alokasikan stok untuk pengiriman')

@section('content')
    <div class="{{ request('foto_path') ? 'max-w-7xl' : 'max-w-4xl' }} mx-auto">
        <div class="grid grid-cols-1 {{ request('foto_path') ? 'lg:grid-cols-12 gap-8' : '' }}">
            @if(request('foto_path'))
            <!-- Kolom Kiri: Foto Dokumen -->
            <div class="lg:col-span-5 relative">
                <div class="sticky top-6 glass p-4 rounded-2xl shadow-sm border-t-4 border-blue-500">
                    <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider mb-4 flex items-center">
                        <i data-lucide="image" class="w-4 h-4 mr-2 text-slate-400"></i>
                        Foto Dokumen OCR
                    </h3>
                    <div class="rounded-xl overflow-y-auto border border-slate-200 bg-slate-50 p-2 max-h-[80vh] custom-scrollbar">
                        <img src="{{ url('/cloud-storage/' . request('foto_path')) }}" alt="Dokumen" class="w-full h-auto object-top rounded-lg block">
                    </div>
                </div>
            </div>
            <div class="lg:col-span-7">
            @else
            <div>
            @endif
        <form action="{{ route('shipments.store') }}" method="POST" id="shipmentForm">
            @csrf
            <input type="hidden" name="foto_path" value="{{ request('foto_path') ?? ($preFill['foto_path'] ?? '') }}">

            <!-- Cek Stok Alert -->
            <div id="stockAlert" class="hidden mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-xl shadow-sm items-start transition-all">
                <i data-lucide="alert-triangle" class="w-6 h-6 text-red-500 mr-3 mt-0.5 flex-shrink-0"></i>
                <div>
                    <h4 class="text-sm font-bold text-red-800">Stok Gudang Tidak Mencukupi!</h4>
                    <p class="text-sm text-red-700 mt-1">Stok yang tersedia saat ini (<strong id="availableStockDisplay" class="font-mono">{{ number_format($totalAvailableStock ?? 0, 2) }}</strong> KG) tidak cukup untuk memenuhi target pengiriman yang diminta (<strong id="requestedStockDisplay" class="font-mono">0</strong> KG).</p>
                    <p class="text-xs text-red-600 mt-1 uppercase font-bold tracking-wide">Tindakan: Silakan turunkan [Volume Pengiriman Saat Ini] untuk melakukan Pengiriman Parsial.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Data Dokumen (OCR Auto-fill) -->
                <div class="lg:col-span-1 space-y-6">

                    <div class="glass p-6 rounded-2xl shadow-sm">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center">
                            <i data-lucide="scan-line" class="w-4 h-4 mr-2"></i>
                            Data Dokumen
                        </h3>

                        @if(isset($preFill['contract_number_ref']) && $preFill['contract_number_ref'])
                            <div class="p-2.5 bg-green-50 border border-green-200 rounded-lg flex items-center mb-4 text-xs">
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-green-600 mr-2 flex-shrink-0"></i>
                                <span class="text-green-700 font-bold">Terisi otomatis dari hasil scan OCR</span>
                            </div>
                        @endif

                        <div class="space-y-4">
                            <!-- No Kontrak -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nomor Kontrak</label>
                                <input type="text" name="contract_number" required
                                    value="{{ $preFill['contract_number_ref'] ?? '' }}"
                                    placeholder="Contoh: 1794/HO-SUPCO/SIR-L/N-1/IX/2025"
                                    class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm font-mono">
                            </div>

                            <!-- No DO / SP -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. DO / Surat
                                    Perintah</label>
                                <input type="text" name="do_number_manual" value="{{ $preFill['do_number_manual'] ?? '' }}"
                                    placeholder="Contoh: 014/KARET SC/2025"
                                    class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm font-mono">
                            </div>

                            <!-- Nama Pembeli -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Pembeli</label>
                                <input type="text" name="buyer_name" value="{{ $preFill['buyer_name'] ?? '' }}"
                                    placeholder="Nama perusahaan pembeli"
                                    class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm">
                            </div>

                            @if(isset($preFill['total_pesanan_kg']) && $preFill['total_pesanan_kg'] > 0)
                            <!-- Informasi Parsial -->
                            <div class="p-3 bg-yellow-50 border border-yellow-200 rounded-lg space-y-1">
                                <div class="flex justify-between text-xs">
                                    <span class="text-yellow-700 font-bold">Total Pesanan DO:</span>
                                    <span class="text-yellow-800 font-mono">{{ number_format($preFill['total_pesanan_kg'], 2) }} KG</span>
                                </div>
                                <div class="flex justify-between text-xs">
                                    <span class="text-yellow-700 font-bold">Sisa Belum Dikirim:</span>
                                    <span class="text-yellow-800 font-mono">{{ number_format($preFill['sisa_pesanan_kg'], 2) }} KG</span>
                                </div>
                            </div>
                            @endif

                            <!-- Volume Pengiriman Saat Ini -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Volume Pengiriman Saat Ini
                                    (Kg)</label>
                                <div class="relative">
                                    <input type="number" step="0.01" name="documented_qty_kg" id="documentedQtyInput"
                                        value="{{ $preFill['documented_qty_kg'] ?? '' }}" placeholder="0.00" required oninput="checkStockWarning(); recalculateFIFO();"
                                        class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm font-mono font-bold text-blue-600 pr-12">
                                    <span
                                        class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">KG</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-5 border-t border-slate-100 space-y-4">
                            <h4 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 flex items-center">
                                <i data-lucide="truck" class="w-4 h-4 mr-2"></i>
                                Data Angkutan (Bisa diisi oleh Petugas Gudang)
                            </h4>
                            
                            <!-- Ekspedisi -->
                            <div>
                                <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Ekspedisi</label>
                                <input type="text" name="transporter_name" value="{{ $preFill['transporter_name'] ?? '' }}"
                                    placeholder="Contoh: PT. Sumber Makmur"
                                    class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- No Polisi -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">No. Polisi</label>
                                    <input type="text" name="vehicle_plate" value="{{ $preFill['vehicle_plate'] ?? '' }}"
                                        placeholder="Contoh: BE 1234 XY"
                                        class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm uppercase">
                                </div>

                                <!-- Nama Supir -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Nama Supir</label>
                                    <input type="text" name="driver_name" value="{{ $preFill['driver_name'] ?? '' }}"
                                        placeholder="Nama Lengkap"
                                        class="block w-full border-slate-200 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm bg-white text-sm">
                                </div>
                            </div>
                        </div>

                        <p class="text-[10px] text-slate-400 mt-4 italic">* Anda dapat mengirim secara parsial (sebagian). Sisa pesanan akan dicatat untuk pengiriman selanjutnya.</p>
                    </div>
                </div>

                <!-- Right Column: Cargo Selection -->
                <div class="lg:col-span-2">
                    <div class="glass p-6 rounded-2xl shadow-sm">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center">
                                <i data-lucide="package" class="w-5 h-5 mr-2 text-blue-500"></i>
                                Daftar Muatan (Lot)
                            </h3>
                            <button type="button" onclick="addRow()"
                                class="text-sm font-bold text-blue-600 hover:text-blue-700 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-colors flex items-center">
                                <i data-lucide="plus-circle" class="w-4 h-4 mr-1.5"></i>
                                Tambah Baris
                            </button>
                        </div>

                        <div id="items-container" class="space-y-3">
                            <!-- Template Row will be injected here -->
                        </div>

                        <!-- Total Weight Summary -->
                        <div
                            class="mt-6 p-4 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center">
                            <span class="text-sm font-bold text-slate-500 uppercase">Total Estimasi Muatan</span>
                            <span class="text-2xl font-mono font-bold text-slate-800" id="totalWeightDisplay">0 <span
                                    class="text-sm text-slate-400">KG</span></span>
                        </div>

                        <div class="mt-8 pt-6 border-t border-slate-100 flex justify-end">
                            <button type="submit"
                                class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-500/30 transition-all flex items-center">
                                <i data-lucide="send" class="w-5 h-5 mr-2"></i>
                                Buat Surat Jalan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        @if(request('foto_path'))
            </div>
        @endif
        </div>
    </div>
    </div>

    <!-- Scripts -->
    <script>
        let stocks = @json($stocks);
        const totalAvailableStock = {{ $totalAvailableStock ?? 0 }};

        function checkStockWarning() {
            const input = document.getElementById('documentedQtyInput');
            const targetQty = parseFloat(input.value) || 0;
            const alertEl = document.getElementById('stockAlert');
            const displayEl = document.getElementById('requestedStockDisplay');
            
            if (targetQty > totalAvailableStock && targetQty > 0) {
                alertEl.classList.remove('hidden');
                alertEl.classList.add('flex');
                displayEl.textContent = targetQty.toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 2});
            } else {
                alertEl.classList.add('hidden');
                alertEl.classList.remove('flex');
            }
        }

        function addRow() {
            const container = document.getElementById('items-container');
            const index = container.children.length;

            const row = document.createElement('div');
            row.className = 'item-row col-span-12 p-4 rounded-xl border border-slate-200 bg-white hover:border-blue-300 transition-colors relative group';
            row.innerHTML = `
                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-12 md:col-span-7">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Pilih Lot Stok</label>
                        <select name="items[${index}][stock_lot_id]" required onchange="updateRowMax(this, ${index})" 
                                class="stock-select block w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 font-mono">
                            <option value="">-- Pilih Lot --</option>
                            ${stocks.map((s, idx) => {
                                const fifoTag = idx < 3 ? ' ⭐ FIFO' : '';
                                return `<option value="${s.id}" data-max="${s.remaining_weight.toFixed(2)}">Lot ${s.lot_number} (${s.quality_type}) - Sisa: ${s.remaining_weight.toLocaleString()} kg${fifoTag}</option>`;
                            }).join('')}
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1 fdf-hint"></p>
                    </div>

                    <div class="col-span-10 md:col-span-4 max-w-[200px]">
                        <label class="block text-xs font-bold text-slate-400 uppercase mb-1">Berat Muat (Kg)</label>
                        <div class="relative">
                            <input type="number" step="0.01" name="items[${index}][qty_loaded_kg]" required
                                   class="qty-input block w-full border-slate-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500 font-mono pr-8"
                                   oninput="calculateTotal()">
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs font-bold">KG</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1 text-right max-hint">Maks: -</p>
                    </div>

                    <div class="col-span-2 md:col-span-1 flex items-center justify-end md:mt-5">
                        <button type="button" onclick="this.closest('.item-row').remove(); calculateTotal();" 
                                class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Hapus Baris">
                            <i data-lucide="trash-2" class="w-5 h-5"></i>
                        </button>
                    </div>
                </div>
                <div class="pallet-container mt-3 hidden p-3 bg-slate-50 border border-slate-100 rounded-lg">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-2"><i data-lucide="layers" class="w-3 h-3 inline mr-1"></i> Pilih Spesifik Palet (Opsional)</label>
                    <div class="pallet-list grid grid-cols-2 md:grid-cols-4 gap-2"></div>
                </div>
            `;

            container.appendChild(row);
            if(window.lucide) window.lucide.createIcons();
        }

        function updateRowMax(select, rowIndex) {
            const row = select.closest('.item-row');
            const input = row.querySelector('.qty-input');
            const hint = row.querySelector('.max-hint');
            const fdfHint = row.querySelector('.fdf-hint');
            const palletContainer = row.querySelector('.pallet-container');
            const palletList = row.querySelector('.pallet-list');
            
            const selectedStockId = select.value;
            const stock = stocks.find(s => s.id == selectedStockId);

            if (stock) {
                const max = stock.remaining_weight.toFixed(2);
                input.max = max;
                input.value = max; // Auto-fill max for convenience
                input.readOnly = false;
                hint.textContent = `Maks: ${parseFloat(max).toLocaleString()} kg`;
                hint.classList.add('text-blue-500');

                // Render checkboxes for details
                if (stock.details && stock.details.length > 0) {
                    palletContainer.classList.remove('hidden');
                    palletList.innerHTML = stock.details.map(d => `
                        <label class="flex items-center p-2 rounded-lg border border-slate-200 bg-white hover:bg-blue-50 cursor-pointer transition-colors text-xs font-mono">
                            <input type="checkbox" name="items[${rowIndex}][selected_details][]" value="${d.id}" class="pallet-checkbox mr-2 rounded text-blue-600 focus:ring-blue-500" data-weight="${d.net_weight_kg}" onchange="recalcPalletWeight(this)">
                            <div>
                                <span class="block font-bold text-slate-700">P-${d.fdf_number || d.pallet_number || d.id}</span>
                                <span class="text-[9px] text-slate-400">${d.net_weight_kg} kg</span>
                            </div>
                        </label>
                    `).join('');
                    
                    if (window.lucide) window.lucide.createIcons();
                } else {
                    palletContainer.classList.add('hidden');
                    palletList.innerHTML = '';
                }
                
                if (stock.fdf_numbers && stock.fdf_numbers.length > 0) {
                    fdfHint.innerHTML = `<span class="text-blue-600 font-bold">Total Palet: ${stock.fdf_numbers.length}</span>`;
                }
                
            } else {
                input.removeAttribute('max');
                input.value = '';
                input.readOnly = false;
                hint.textContent = 'Maks: -';
                fdfHint.textContent = '';
                palletContainer.classList.add('hidden');
                palletList.innerHTML = '';
            }

            calculateTotal();
        }

        function recalcPalletWeight(checkbox) {
            const row = checkbox.closest('.item-row');
            const input = row.querySelector('.qty-input');
            const checkboxes = row.querySelectorAll('.pallet-checkbox');
            
            let checkedWeight = 0;
            let hasChecked = false;
            
            checkboxes.forEach(cb => {
                if(cb.checked) {
                    checkedWeight += parseFloat(cb.dataset.weight);
                    hasChecked = true;
                }
            });

            if (hasChecked) {
                input.value = checkedWeight.toFixed(2);
                input.readOnly = true; // Lock manual input if they are using pallets exactly
                input.classList.add('bg-slate-100');
            } else {
                // Revert to max fallback
                const select = row.querySelector('.stock-select');
                const selectedStockId = select.value;
                const stock = stocks.find(s => s.id == selectedStockId);
                
                if (stock) input.value = stock.remaining_weight.toFixed(2);
                input.readOnly = false;
                input.classList.remove('bg-slate-100');
            }
            
            calculateTotal();
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.qty-input').forEach(input => {
                total += parseFloat(input.value) || 0;
            });
            document.getElementById('totalWeightDisplay').textContent = total.toLocaleString('id-ID');
        }

        let isFifoGenerated = false;
        
        function recalculateFIFO(force = false) {
            if (isFifoGenerated && !force) return;

            const input = document.getElementById('documentedQtyInput');
            const targetQty = parseFloat(input.value) || 0;
            
            // Only auto-recommend if target is smaller or eq to available limit to not spam errors
            const allocTarget = Math.min(targetQty, totalAvailableStock);

            if (allocTarget > 0 && stocks.length > 0 && !isFifoGenerated) {
                isFifoGenerated = true;
                const container = document.getElementById('items-container');
                container.innerHTML = ''; // safe clear

                let remaining = allocTarget;

                // Show recommendation banner if not exists
                if (!document.getElementById('fifoBanner')) {
                    const banner = document.createElement('div');
                    banner.id = 'fifoBanner';
                    banner.className = 'p-3 bg-green-50 border border-green-200 rounded-xl mb-3 flex items-center text-sm';
                    banner.innerHTML = `
                            <i data-lucide="sparkles" class="w-4 h-4 text-green-600 mr-2 flex-shrink-0"></i>
                            <span class="text-green-700 font-bold">Rekomendasi FIFO</span>
                            <span class="text-green-600 ml-2 shadow-text">— Sistem mengalokasikan stok secara otomatis dari lot terlama.</span>
                        `;
                    container.parentElement.insertBefore(banner, container);
                }

                for (let i = 0; i < stocks.length && remaining > 0; i++) {
                    const stock = stocks[i];
                    const allocate = Math.min(stock.remaining_weight, remaining);

                    if (allocate <= 0) continue;

                    // Create row
                    addRow();
                    
                    // Get the latest row
                    const rows = container.querySelectorAll('.item-row');
                    const rowIndex = rows.length - 1;
                    const row = rows[rowIndex];
                    const select = row.querySelector('select');
                    const qtyInput = row.querySelector('.qty-input');
                    const hint = row.querySelector('.max-hint');

                    // Auto-select this stock
                    for (let j = 0; j < select.options.length; j++) {
                        if (select.options[j].value == stock.id) {
                            select.selectedIndex = j;
                            break;
                        }
                    }

                    // Trigger updateRowMax to render pallets
                    updateRowMax(select, rowIndex);

                    // Set qty manually (since it might be partial if total order < lot max)
                    qtyInput.value = allocate.toFixed(2);
                    
                    // Mark checkboxes if it happens to be exactly some pallets
                    const checkboxes = row.querySelectorAll('.pallet-checkbox');
                    let tempWeight = 0;
                    checkboxes.forEach(cb => {
                        if (tempWeight + parseFloat(cb.dataset.weight) <= allocate) {
                            cb.checked = true;
                            tempWeight += parseFloat(cb.dataset.weight);
                        }
                    });
                    
                    if (tempWeight > 0) {
                        recalcPalletWeight(checkboxes[0]);
                    }

                    // Mark row as FIFO recommended
                    row.classList.add('border-green-300', 'bg-green-50/30');

                    remaining -= allocate;
                }

                calculateTotal();
                if(window.lucide) window.lucide.createIcons();
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkStockWarning();
            const targetQty = parseFloat(document.getElementById('documentedQtyInput').value) || 0;
            if (targetQty > 0) {
                recalculateFIFO();
            } else {
                addRow();
            }
        });
    </script>
@endsection