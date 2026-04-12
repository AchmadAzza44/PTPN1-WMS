<x-layout title="Edit Stok" header="Edit Data Stok">
    <div class="glass p-8 rounded-2xl shadow-sm card-glow" style="max-width: 900px; margin: 0 auto;">
        <form action="{{ route('stocks.update', $stock->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ═══ INFORMASI LOT ═══ --}}
            <div style="margin-bottom: 24px;">
                <h3
                    style="font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; padding-bottom: 12px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                    <div class="bg-green-10"
                        style="width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                        <i data-lucide="info" class="text-ptpn-green" style="width: 14px; height: 14px;"></i>
                    </div>
                    Informasi Lot
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="lot_number_display"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Nomor
                            Lot</label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="text" id="lot_number_display"
                                style="flex:1; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f8fafc; color: #94a3b8; cursor: not-allowed; font-family: monospace; text-transform: uppercase; font-size: 13px;"
                                value="{{ $stock->lot_number }}" readonly>
                            @if($stock->status === 'blue')
                            <button type="button" onclick="document.getElementById('lotEditSection').style.display=document.getElementById('lotEditSection').style.display==='none'?'block':'none'"
                                style="padding:8px 14px;border-radius:10px;border:1px solid rgba(245,166,35,0.3);background:rgba(245,166,35,0.08);color:#F5A623;font-size:11px;font-weight:700;cursor:pointer;white-space:nowrap;display:flex;align-items:center;gap:5px;">
                                <i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit
                            </button>
                            @endif
                        </div>
                        @if($stock->status !== 'blue')
                        <p style="font-size: 12px; color: #94a3b8; margin-top: 4px;">Lot sudah keluar gudang — tidak bisa diubah.</p>
                        @endif
                    </div>
                    <div>
                        <label for="status"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Status
                            Stok</label>
                        <select name="status" id="status"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; background: white; outline: none;"
                            onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="blue" {{ $stock->status == 'blue' ? 'selected' : '' }}>Tersedia (Blue)</option>
                            <option value="yellow" {{ $stock->status == 'yellow' ? 'selected' : '' }}>Partial (Yellow)
                            </option>
                        </select>
                    </div>
                    <div>
                        <label for="quality_type"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Jenis
                            Mutu</label>
                        <select name="quality_type" id="quality_type"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; background: white; outline: none;"
                            onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="SIR 20 SW" {{ $stock->quality_type == 'SIR 20 SW' ? 'selected' : '' }}>SIR 20
                                SW</option>
                            <option value="RSS 1" {{ $stock->quality_type == 'RSS 1' ? 'selected' : '' }}>RSS 1</option>
                            <option value="Cutting A" {{ $stock->quality_type == 'Cutting A' ? 'selected' : '' }}>Cutting
                                A</option>
                        </select>
                    </div>
                    <div>
                        <label for="origin_unit"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Unit
                            Asal</label>
                        <select name="origin_unit" id="origin_unit"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; background: white; outline: none;"
                            onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="SIR" {{ $stock->origin_unit == 'SIR' ? 'selected' : '' }}>Pabrik SIR</option>
                            <option value="RSS" {{ $stock->origin_unit == 'RSS' ? 'selected' : '' }}>Pabrik RSS</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ═══ DETAIL STOK (Per-baris Peti/Palet/Bale/Berat) ═══ --}}
            <div style="margin-bottom: 32px;">
                <h3
                    style="font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; padding-bottom: 12px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                    <div class="bg-blue-10"
                        style="width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                        <i data-lucide="package" class="text-ptpn-blue" style="width: 14px; height: 14px;"></i>
                    </div>
                    Detail Stok
                    <span style="margin-left:auto;display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;background:rgba(74,173,228,0.1);color:#4AADE4;font-size:10px;font-weight:700;">
                        {{ $stock->details->count() }} item &bull; Total {{ number_format($stock->details->sum('net_weight_kg'), 2) }} kg
                    </span>
                </h3>

                @if($stock->details->count() > 0)
                <div style="overflow-x:auto;-webkit-overflow-scrolling:touch;border-radius:12px;border:1px solid var(--border, #e2e8f0);">
                    <table style="width:100%;min-width:600px;border-collapse:collapse;font-size:13px;" id="detailTable">
                        <thead>
                            <tr style="background:rgba(248,250,252,0.9);">
                                <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e2e8f0;min-width:100px;">No. Peti/FDF</th>
                                <th style="padding:10px 12px;text-align:left;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e2e8f0;min-width:100px;">No. Palet</th>
                                <th style="padding:10px 12px;text-align:center;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e2e8f0;min-width:70px;">Bale</th>
                                <th style="padding:10px 12px;text-align:right;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e2e8f0;min-width:120px;">Berat (kg)</th>
                                <th style="padding:10px 12px;text-align:center;font-size:10px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.06em;border-bottom:1px solid #e2e8f0;width:60px;">Tipe</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($stock->details as $i => $detail)
                            <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.15s;" onmouseenter="this.style.background='rgba(248,250,252,0.6)'" onmouseleave="this.style.background='transparent'">
                                <input type="hidden" name="details[{{ $i }}][id]" value="{{ $detail->id }}">
                                <td style="padding:8px 10px;" data-label="No. Peti/FDF">
                                    <input type="text" name="details[{{ $i }}][fdf_number]"
                                        value="{{ $detail->fdf_number }}"
                                        placeholder="No. Peti"
                                        style="width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:monospace;font-weight:600;text-transform:uppercase;outline:none;transition:border-color 0.2s;box-sizing:border-box;background:white;"
                                        onfocus="this.style.borderColor='#4AADE4';this.style.boxShadow='0 0 0 3px rgba(74,173,228,0.12)'"
                                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                                </td>
                                <td style="padding:8px 10px;" data-label="No. Palet">
                                    <input type="text" name="details[{{ $i }}][pallet_number]"
                                        value="{{ $detail->pallet_number }}"
                                        placeholder="No. Palet"
                                        style="width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:monospace;font-weight:600;outline:none;transition:border-color 0.2s;box-sizing:border-box;background:white;"
                                        onfocus="this.style.borderColor='#4AADE4';this.style.boxShadow='0 0 0 3px rgba(74,173,228,0.12)'"
                                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                                </td>
                                <td style="padding:8px 10px;" data-label="Bale">
                                    <input type="number" name="details[{{ $i }}][quantity_unit]"
                                        value="{{ $detail->quantity_unit }}"
                                        placeholder="0" min="0"
                                        style="width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:monospace;font-weight:600;text-align:center;outline:none;transition:border-color 0.2s;box-sizing:border-box;background:white;"
                                        onfocus="this.style.borderColor='#4AADE4';this.style.boxShadow='0 0 0 3px rgba(74,173,228,0.12)'"
                                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';updateTotal()">
                                </td>
                                <td style="padding:8px 10px;" data-label="Berat (kg)">
                                    <input type="number" step="0.01" name="details[{{ $i }}][net_weight_kg]"
                                        value="{{ $detail->net_weight_kg }}"
                                        placeholder="0" min="0"
                                        class="weight-input"
                                        style="width:100%;padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13px;font-family:monospace;font-weight:700;text-align:right;outline:none;transition:border-color 0.2s;box-sizing:border-box;background:white;"
                                        onfocus="this.style.borderColor='#4AADE4';this.style.boxShadow='0 0 0 3px rgba(74,173,228,0.12)'"
                                        onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none';updateTotal()">
                                </td>
                                <td style="padding:8px 10px;text-align:center;" data-label="Tipe">
                                    <span style="display:inline-flex;align-items:center;padding:3px 9px;border-radius:8px;font-size:10px;font-weight:700;text-transform:uppercase;
                                        {{ $detail->packaging_type === 'pallet' ? 'background:rgba(74,173,228,0.1);color:#4AADE4;' : 'background:rgba(52,168,83,0.1);color:#34A853;' }}">
                                        {{ $detail->packaging_type }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr style="background:rgba(248,250,252,0.9);">
                                <td colspan="3" style="padding:10px 12px;font-size:12px;font-weight:700;color:#64748b;text-align:right;">TOTAL BERAT</td>
                                <td style="padding:10px 12px;text-align:right;">
                                    <span id="totalWeight" style="font-size:14px;font-family:monospace;font-weight:800;color:#1e293b;">
                                        {{ number_format($stock->details->sum('net_weight_kg'), 2) }}
                                    </span>
                                    <span style="font-size:11px;color:#94a3b8;font-weight:600;margin-left:2px;">kg</span>
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div style="margin-top:8px;display:flex;align-items:center;gap:6px;padding:8px 12px;border-radius:8px;background:rgba(74,173,228,0.06);border:1px solid rgba(74,173,228,0.15);">
                    <i data-lucide="info" style="width:12px;height:12px;color:#4AADE4;flex-shrink:0;"></i>
                    <p style="font-size:11px;color:#4AADE4;margin:0;font-weight:500;">
                        Semua kolom di atas bisa diedit langsung. Perubahan akan tercatat di audit trail.
                    </p>
                </div>
                @else
                <div style="padding:32px;text-align:center;border-radius:12px;background:rgba(248,250,252,0.8);border:1px dashed #e2e8f0;">
                    <i data-lucide="inbox" style="width:32px;height:32px;color:#94a3b8;margin:0 auto 8px;display:block;"></i>
                    <p style="font-size:13px;color:#94a3b8;margin:0;">Tidak ada detail stok untuk lot ini.</p>
                </div>
                @endif
            </div>

            {{-- ═══ TOMBOL AKSI ═══ --}}
            <div
                style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('stocks.index') }}"
                    style="padding: 10px 20px; border-radius: 12px; color: #475569; font-weight: 600; font-size: 13px; text-decoration: none; transition: background 0.2s;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                    Batal
                </a>
                <button type="submit" class="bg-blue-gradient shadow-blue"
                    style="padding: 10px 20px; color: white; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    Update Data Stok
                </button>
            </div>
        </form>

        {{-- ═══ Lot Number Edit Section (separate form, audit trail) ═══ --}}
        @if($stock->status === 'blue')
        <div id="lotEditSection" style="display:none;margin-top:20px;">
            <form action="{{ route('stocks.update_lot', $stock->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="glass p-6 rounded-2xl" style="border:2px solid rgba(245,166,35,0.3);background:rgba(245,166,35,0.03);">
                    <h4 style="font-size:14px;font-weight:700;color:#1e293b;margin:0 0 14px 0;display:flex;align-items:center;gap:8px;">
                        <div style="width:28px;height:28px;border-radius:8px;background:rgba(245,166,35,0.1);display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="edit-3" style="width:14px;height:14px;color:#F5A623;"></i>
                        </div>
                        Koreksi Nomor Lot
                    </h4>

                    <div style="display:grid;gap:14px;">
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;">Nomor Lot Baru</label>
                            <input type="text" name="lot_number" required
                                style="width:100%;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:12px;font-family:monospace;font-size:14px;text-transform:uppercase;font-weight:700;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#F5A623'" onblur="this.style.borderColor='#e2e8f0'"
                                value="{{ old('lot_number', $stock->lot_number) }}"
                                placeholder="Masukkan nomor lot yang benar">
                        </div>
                        <div>
                            <label style="display:block;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:6px;">
                                Alasan Koreksi <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea name="reason" required minlength="5" rows="2"
                                style="width:100%;padding:10px 16px;border:1.5px solid #e2e8f0;border-radius:12px;font-size:13px;resize:vertical;outline:none;transition:border-color 0.2s;box-sizing:border-box;"
                                onfocus="this.style.borderColor='#F5A623'" onblur="this.style.borderColor='#e2e8f0'"
                                placeholder="Contoh: Salah input dari OCR, seharusnya 136 bukan 130">{{ old('reason') }}</textarea>
                            <p style="font-size:10px;color:#94a3b8;margin:4px 0 0 0;">Minimal 5 karakter. Alasan akan tercatat di audit trail.</p>
                        </div>
                    </div>

                    <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;padding-top:12px;border-top:1px solid rgba(245,166,35,0.15);">
                        <button type="button" onclick="document.getElementById('lotEditSection').style.display='none'"
                            style="padding:8px 16px;border-radius:10px;border:1px solid #e2e8f0;background:white;color:#64748b;font-size:12px;font-weight:600;cursor:pointer;">
                            Batal
                        </button>
                        <button type="submit"
                            style="padding:8px 18px;border-radius:10px;border:none;background:linear-gradient(135deg,#F5A623,#e8951e);color:white;font-size:12px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 2px 8px rgba(245,166,35,0.3);">
                            <i data-lucide="check" style="width:14px;height:14px;"></i>
                            Simpan Perubahan Lot
                        </button>
                    </div>
                </div>
            </form>
        </div>
        @endif
    </div>

    <script>
        function updateTotal() {
            const inputs = document.querySelectorAll('.weight-input');
            let total = 0;
            inputs.forEach(inp => { total += parseFloat(inp.value) || 0; });
            const el = document.getElementById('totalWeight');
            if (el) el.textContent = total.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    </script>

    <style>
        @media (max-width: 768px) {
            .glass.p-8 { padding: 16px !important; }
        }

        /* Mobile card mode for detail table */
        @media (max-width: 640px) {
            #detailTable {
                display: block !important;
                min-width: 0 !important;
                width: 100% !important;
            }
            #detailTable thead { display: none; }
            #detailTable tbody {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            #detailTable tbody tr {
                display: flex;
                flex-direction: column;
                gap: 8px;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 14px;
                padding: 14px;
                box-shadow: 0 1px 4px rgba(0,0,0,0.04);
                border-bottom: none !important;
            }
            #detailTable tbody tr td {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 0 !important;
            }
            #detailTable tbody tr td::before {
                content: attr(data-label);
                font-size: 10px;
                font-weight: 700;
                color: #94a3b8;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                flex-shrink: 0;
                width: 80px;
                min-width: 80px;
            }
            #detailTable tbody tr td input {
                flex: 1;
                min-width: 0;
                font-size: 14px !important;
                padding: 9px 12px !important;
                border-radius: 10px !important;
            }
            #detailTable tbody tr td:last-child {
                justify-content: flex-end;
            }
            #detailTable tbody tr td:last-child::before {
                display: none;
            }
            /* Stacked tfoot */
            #detailTable tfoot {
                display: block;
                margin-top: 10px;
            }
            #detailTable tfoot tr {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 12px 14px;
                background: rgba(248,250,252,0.9);
                border-radius: 12px;
                border: 1px solid #e2e8f0;
            }
            #detailTable tfoot tr td { border: none; }
            #detailTable tfoot tr td:last-child { display: none; }

            /* Action buttons stack */
            div[style*="display: flex; justify-content: flex-end; gap: 12px"] {
                flex-direction: column-reverse !important;
            }
            div[style*="display: flex; justify-content: flex-end; gap: 12px"] a,
            div[style*="display: flex; justify-content: flex-end; gap: 12px"] button {
                width: 100% !important;
                justify-content: center !important;
                text-align: center;
            }

            /* Lot edit section buttons */
            div[style*="display:flex;gap:10px;justify-content:flex-end"] {
                flex-direction: column-reverse !important;
            }
            div[style*="display:flex;gap:10px;justify-content:flex-end"] button {
                width: 100%;
                justify-content: center;
            }

            /* Overflow wrapper */
            div[style*="overflow-x:auto"] {
                overflow-x: visible !important;
                border: none !important;
            }
        }

        /* Tablet: keep table but make it smaller */
        @media (min-width: 641px) and (max-width: 768px) {
            #detailTable { min-width: 550px; }
            #detailTable th { font-size: 9px; padding: 8px 6px; }
            #detailTable td { padding: 6px 4px; }
            #detailTable input { font-size: 12px !important; padding: 6px 8px !important; }
        }
    </style>
</x-layout>