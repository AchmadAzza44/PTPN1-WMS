<x-layout title="Tambah Stok Manual" header="Input Data Stok Baru">
    <div class="glass p-8 rounded-2xl shadow-sm card-glow" style="max-width: 768px; margin: 0 auto;">
        <form action="{{ route('stocks.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

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
                        <label for="lot_number"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Nomor
                            Lot</label>
                        <input type="text" name="lot_number" id="lot_number"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-family: monospace; text-transform: uppercase; font-size: 13px; outline: none; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#34A853'; this.style.boxShadow='0 0 0 3px rgba(52,168,83,0.12)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                            placeholder="LOT-260120-001" value="{{ old('lot_number') }}" required>
                        @error('lot_number')
                            <p style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="production_year"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Tahun
                            Produksi</label>
                        <input type="number" name="production_year" id="production_year"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; outline: none; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#34A853'; this.style.boxShadow='0 0 0 3px rgba(52,168,83,0.12)'"
                            onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none'"
                            value="{{ date('Y') }}" required>
                    </div>
                    <div>
                        <label for="quality_type"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Jenis
                            Mutu</label>
                        <select name="quality_type" id="quality_type"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; background: white; outline: none; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="SIR 20 SW">SIR 20 SW</option>
                            <option value="RSS 1">RSS 1</option>
                            <option value="Cutting A">Cutting A</option>
                            <option value="Cutting B">Cutting B</option>
                        </select>
                    </div>
                    <div>
                        <label for="origin_unit"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Unit
                            Asal</label>
                        <select name="origin_unit" id="origin_unit"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; background: white; outline: none; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'">
                            <option value="SIR">Pabrik SIR</option>
                            <option value="RSS">Pabrik RSS</option>
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
                    Detail Berat
                </h3>

                <div class="bg-green-10"
                    style="padding: 20px; border-radius: 12px; border: 1px solid rgba(52,168,83,0.1); display: flex; flex-wrap: wrap; gap: 20px;">
                    <div style="flex: 1; min-width: 200px;">
                        <label for="inbound_at"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Tanggal
                            Masuk</label>
                        <input type="datetime-local" name="inbound_at" id="inbound_at"
                            style="width: 100%; padding: 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-size: 13px; outline: none; transition: all 0.2s;"
                            onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'"
                            value="{{ now()->format('Y-m-d\TH:i') }}">
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label for="net_weight"
                            style="display: block; font-size: 11px; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px;">Berat
                            Bersih (Kg)</label>
                        <div style="position: relative;">
                            <input type="number" step="0.01" name="net_weight" id="net_weight"
                                style="width: 100%; padding: 10px 48px 10px 16px; border: 1px solid #e2e8f0; border-radius: 12px; font-family: monospace; font-weight: 700; font-size: 13px; outline: none; transition: all 0.2s;"
                                onfocus="this.style.borderColor='#34A853'" onblur="this.style.borderColor='#e2e8f0'"
                                placeholder="0.00" value="{{ old('net_weight') }}" required>
                            <span
                                style="position: absolute; right: 16px; top: 10px; color: #94a3b8; font-weight: 700; font-size: 13px;">Kg</span>
                        </div>
                        @error('net_weight')
                            <p style="color: #ef4444; font-size: 12px; margin-top: 4px;">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <p style="font-size: 12px; color: #64748b; margin-top: 8px; font-style: italic;">*Total berat akan
                    otomatis dialokasikan ke FDF Number default.</p>
            </div>

            <div
                style="display: flex; justify-content: flex-end; gap: 12px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                <a href="{{ route('stocks.index') }}"
                    style="padding: 10px 20px; border-radius: 12px; color: #475569; font-weight: 600; font-size: 13px; text-decoration: none; transition: background 0.2s;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                    Batal
                </a>
                <button type="submit" class="bg-green-gradient shadow-green"
                    style="padding: 10px 20px; color: white; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; font-size: 13px; display: flex; align-items: center; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.transform='translateY(-2px)'"
                    onmouseout="this.style.transform='translateY(0)'">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    Simpan Data Stok
                </button>
            </div>
        </form>
    </div>
</x-layout>