<x-layout title="Edit Stok" header="Edit Data Stok">
    <div class="glass p-8 rounded-2xl shadow-sm card-glow" style="max-width: 768px; margin: 0 auto;">
        <form action="{{ route('stocks.update', $stock->id) }}" method="POST">
            @csrf
            @method('PUT')

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

            <div style="margin-bottom: 32px;">
                <h3
                    style="font-size: 15px; font-weight: 700; color: #1e293b; display: flex; align-items: center; padding-bottom: 12px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
                    <div class="bg-blue-10"
                        style="width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 10px;">
                        <i data-lucide="weight" class="text-ptpn-blue" style="width: 14px; height: 14px;"></i>
                    </div>
                    Total Berat (kg)
                </h3>

                <div class="bg-blue-10"
                    style="padding: 20px; border-radius: 12px; border: 1px solid rgba(74,173,228,0.1);">
                    <label for="net_weight"
                        style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Koreksi
                        Berat Stok Saat Ini</label>
                    <div style="position: relative;">
                        <input type="number" step="0.01" name="net_weight" id="net_weight"
                            style="width: 100%; padding: 12px 48px 12px 16px; font-size: 18px; border: 1px solid #e2e8f0; border-radius: 12px; font-family: monospace; font-weight: 700; color: #1e293b; outline: none; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#4AADE4'; this.style.boxShadow='0 0 0 3px rgba(74,173,228,0.12)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                            value="{{ old('net_weight', $stock->details->sum('net_weight_kg')) }}" required>
                        <span
                            style="position: absolute; right: 16px; top: 12px; color: #94a3b8; font-weight: 700;">Kg</span>
                    </div>
                    <p class="text-ptpn-orange"
                        style="font-size: 12px; margin-top: 8px; font-weight: 500; display: flex; align-items: center;">
                        <i data-lucide="alert-triangle" style="width: 12px; height: 12px; margin-right: 4px;"></i>
                        Perhatian: Mengubah berat total akan mereset detail FDF menjadi 1 item adjustment global.
                    </p>
                </div>
            </div>

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
</x-layout>