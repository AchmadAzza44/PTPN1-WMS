<x-layout title="Laporan" header="Pusat Laporan & Arsip">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">

        <!-- Daily Report Card -->
        <div
            class="glass p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden card-glow">
            <div class="bg-green-gradient" style="position: absolute; top: 0; left: 0; right: 0; height: 3px;"></div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div class="bg-green-10"
                    style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="file-text" class="text-ptpn-green" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Laporan Harian</h3>
            </div>
            <p style="font-size: 13px; color: #64748b; margin: 0 0 24px 0;">Rekapitulasi stok masuk, keluar, dan sisa
                per hari ini.</p>

            <form action="{{ route('report.daily.pdf') }}" method="GET" target="_blank" style="display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <label for="date" class="block text-xs font-semibold text-slate-500 mb-1">Pilih Tanggal</label>
                    <input type="date" id="date" name="date" value="{{ date('Y-m-d') }}" class="w-full px-4 py-2 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-ptpn-green/50" required>
                </div>
                <button type="submit" class="bg-green-gradient shadow-green"
                    style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px 16px; color: white; font-weight: 700; border-radius: 12px; border: none; cursor: pointer; text-decoration: none; font-size: 13px; gap: 8px; transition: all 0.2s;"
                    onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                    Download PDF
                </button>
            </form>
        </div>

        <!-- Real-Time Dashboard Card -->
        <div
            class="glass p-6 rounded-2xl shadow-sm hover:shadow-lg transition-all duration-300 relative overflow-hidden card-glow">
            <div class="bg-blue-gradient" style="position: absolute; top: 0; left: 0; right: 0; height: 3px;"></div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div class="bg-blue-10"
                    style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="activity" class="text-ptpn-blue" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Dashboard Real-Time</h3>
            </div>
            <p style="font-size: 13px; color: #64748b; margin: 0 0 24px 0;">Monitor stok live dengan chart, filter waktu dinamis, dan tabel transaksi terbaru.</p>

            <a href="{{ route('reports.realtime') }}" class="bg-blue-gradient shadow-blue"
                style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px 16px; color: white; font-weight: 700; border-radius: 12px; text-decoration: none; font-size: 13px; gap: 8px; transition: all 0.2s;"
                onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i data-lucide="bar-chart-3" style="width: 16px; height: 16px;"></i>
                Buka Dashboard Live
            </a>
        </div>

        <!-- Monthly Excel Report (Coming Soon) -->
        <div class="glass p-6 rounded-2xl shadow-sm relative overflow-hidden" style="opacity: 0.75;">
            <div class="bg-orange-gradient"
                style="position: absolute; top: 0; left: 0; right: 0; height: 3px; opacity: 0.5;"></div>
            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                <div class="bg-orange-10"
                    style="width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="table" class="text-ptpn-orange" style="width: 20px; height: 20px;"></i>
                </div>
                <h3 style="font-size: 15px; font-weight: 700; color: #1e293b; margin: 0;">Laporan Bulanan (Excel)</h3>
            </div>
            <p style="font-size: 13px; color: #64748b; margin: 0 0 24px 0;">Coming Soon: Export data stok bulanan ke
                format Excel.</p>

            <button disabled
                style="display: flex; align-items: center; justify-content: center; width: 100%; padding: 10px 16px; background: #f1f5f9; color: #94a3b8; font-weight: 700; border-radius: 12px; border: 1px solid #e2e8f0; cursor: not-allowed; font-size: 13px; gap: 8px;">
                <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                Download Excel
            </button>
        </div>

    </div>
</x-layout>