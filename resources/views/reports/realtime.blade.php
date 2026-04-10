@extends('layouts.modern')

@section('title', 'Laporan Real-Time')
@section('header', 'Real-Time Stock Dashboard')
@section('subheader', 'Monitor stok gudang secara live — data diperbarui otomatis setiap 10 detik')

@section('actions')
    <div style="display:flex;align-items:center;gap:8px;">
        <span id="liveIndicator" style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;background:rgba(52,168,83,0.1);border:1px solid rgba(52,168,83,0.2);font-size:11px;font-weight:700;color:#34A853;">
            <span style="width:7px;height:7px;border-radius:50%;background:#34A853;animation:pulse 2s infinite;"></span>
            LIVE
        </span>
        <span id="lastUpdated" style="font-size:11px;color:var(--text-muted);">Loading...</span>
    </div>
@endsection

@section('content')
<style>
    @keyframes pulse { 0%,100%{opacity:1;} 50%{opacity:0.3;} }
    .filter-btn { padding:7px 16px;border-radius:10px;border:1.5px solid #e2e8f0;background:white;font-size:12px;font-weight:600;cursor:pointer;transition:all 0.2s;color:var(--text-secondary); }
    .filter-btn:hover { border-color:var(--blue);color:var(--blue); }
    .filter-btn.active { background:var(--blue);color:white;border-color:var(--blue);box-shadow:0 2px 8px rgba(74,173,228,0.3); }
    .stat-value { font-size:1.8rem;font-weight:900;font-variant-numeric:tabular-nums;transition:opacity 0.3s; }
    .stat-value.updating { opacity:0.4; }
    .rt-card { transition:transform 0.2s; } .rt-card:hover { transform:translateY(-2px); }
    @media(max-width:768px) {
        .filter-bar { flex-wrap:wrap !important; }
        .filter-btn { padding:6px 12px;font-size:11px; }
        .stat-value { font-size:1.4rem; }
    }
</style>

{{-- ═══ FILTER BAR ═══ --}}
<div class="card-premium p-4 mb-5 anim-fade-up">
    <div class="filter-bar" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
        <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-right:4px;">
            <i data-lucide="filter" style="width:12px;height:12px;vertical-align:middle;"></i> Rentang:
        </span>
        @foreach([
            'today' => 'Hari Ini',
            '3d'    => '3 Hari',
            '5d'    => '5 Hari',
            '7d'    => '7 Hari',
            'monthly' => 'Bulanan',
            'yearly'  => 'Tahunan',
        ] as $key => $label)
        <button class="filter-btn {{ $key === '7d' ? 'active' : '' }}" data-range="{{ $key }}" onclick="setRange('{{ $key }}', this)">{{ $label }}</button>
        @endforeach

        <div style="margin-left:auto;display:flex;align-items:center;gap:6px;">
            <input type="date" id="customStart" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:11px;outline:none;">
            <span style="color:var(--text-muted);font-size:11px;">s/d</span>
            <input type="date" id="customEnd" style="padding:6px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:11px;outline:none;">
            <button class="filter-btn" onclick="setCustomRange()" style="padding:6px 12px;">
                <i data-lucide="search" style="width:12px;height:12px;"></i>
            </button>
        </div>
    </div>
</div>

{{-- ═══ LIVE STAT CARDS ═══ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">
    {{-- Total Stok --}}
    <div class="card-premium border-green p-5 rt-card anim-fade-up delay-1">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="bg-green-10 shadow-green" style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="package" class="text-ptpn-green" style="width:18px;height:18px;"></i>
            </div>
            <p style="font-size:12px;font-weight:600;color:var(--text-secondary);margin:0;">Total Stok Aktif</p>
        </div>
        <h3 class="stat-value" id="statTotalStock" style="color:var(--text-primary);margin:0 0 4px 0;">
            {{ number_format(collect($initialStock)->sum('total_kg')) }} <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>
        </h3>
        <div class="badge badge-green" style="font-size:10px;" id="statLotCount">
            {{ collect($initialStock)->sum('lot_count') }} Lot aktif
        </div>
    </div>

    {{-- Inbound Range --}}
    <div class="card-premium border-blue p-5 rt-card anim-fade-up delay-2">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="bg-blue-10 shadow-blue" style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="arrow-down-circle" class="text-ptpn-blue" style="width:18px;height:18px;"></i>
            </div>
            <p style="font-size:12px;font-weight:600;color:var(--text-secondary);margin:0;">Total Masuk</p>
        </div>
        <h3 class="stat-value" id="statInbound" style="color:var(--blue);margin:0 0 4px 0;">
            {{ number_format($initialSummary['total_inbound_kg']) }} <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>
        </h3>
        <div class="badge badge-blue" style="font-size:10px;" id="statInboundCount">
            {{ $initialSummary['inbound_count'] }} transaksi
        </div>
    </div>

    {{-- Outbound Range --}}
    <div class="card-premium border-orange p-5 rt-card anim-fade-up delay-3">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div class="bg-orange-10 shadow-orange" style="width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="arrow-up-circle" class="text-ptpn-orange" style="width:18px;height:18px;"></i>
            </div>
            <p style="font-size:12px;font-weight:600;color:var(--text-secondary);margin:0;">Total Keluar</p>
        </div>
        <h3 class="stat-value" id="statOutbound" style="color:var(--orange);margin:0 0 4px 0;">
            {{ number_format($initialSummary['total_outbound_kg']) }} <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>
        </h3>
        <div class="badge badge-orange" style="font-size:10px;">Periode terpilih</div>
    </div>

    {{-- Net Change --}}
    <div class="card-premium p-5 rt-card anim-fade-up delay-4" style="border-top:3px solid {{ $initialSummary['net_change_kg'] >= 0 ? 'var(--green)' : '#ef4444' }};">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
            <div style="width:38px;height:38px;border-radius:10px;background:{{ $initialSummary['net_change_kg'] >= 0 ? 'rgba(52,168,83,0.1)' : 'rgba(239,68,68,0.1)' }};display:flex;align-items:center;justify-content:center;">
                <i data-lucide="{{ $initialSummary['net_change_kg'] >= 0 ? 'trending-up' : 'trending-down' }}" style="width:18px;height:18px;color:{{ $initialSummary['net_change_kg'] >= 0 ? 'var(--green)' : '#ef4444' }};"></i>
            </div>
            <p style="font-size:12px;font-weight:600;color:var(--text-secondary);margin:0;">Selisih Bersih</p>
        </div>
        <h3 class="stat-value" id="statNetChange" style="color:{{ $initialSummary['net_change_kg'] >= 0 ? 'var(--green)' : '#ef4444' }};margin:0 0 4px 0;">
            {{ $initialSummary['net_change_kg'] >= 0 ? '+' : '' }}{{ number_format($initialSummary['net_change_kg']) }} <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>
        </h3>
        <div class="badge badge-gray" style="font-size:10px;">Masuk − Keluar</div>
    </div>
</div>

{{-- ═══ CHART ═══ --}}
<div class="card-premium border-blue p-6 mb-6 anim-fade-up delay-2">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
        <h3 class="section-title" style="margin:0;">
            <div class="section-icon bg-blue-10">
                <i data-lucide="bar-chart-3" class="text-ptpn-blue" style="width:15px;height:15px;"></i>
            </div>
            Tren Inbound vs Outbound
        </h3>
    </div>
    <div style="height:320px;width:100%;position:relative;">
        <canvas id="realtimeChart"></canvas>
    </div>
</div>

{{-- ═══ STOCK COMPOSITION + RECENT TABLE ═══ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Stock by Quality --}}
    <div class="card-premium p-6 anim-fade-up delay-3">
        <h3 class="section-title">
            <div class="section-icon bg-green-10">
                <i data-lucide="pie-chart" class="text-ptpn-green" style="width:15px;height:15px;"></i>
            </div>
            Komposisi Stok
        </h3>
        <div id="stockComposition" style="display:flex;flex-direction:column;gap:12px;margin-top:16px;">
            @foreach($initialStock as $item)
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="flex:1;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-size:12px;font-weight:700;color:var(--text-primary);">{{ $item['quality_type'] }}</span>
                        <span style="font-size:12px;font-weight:600;color:var(--text-muted);">{{ number_format($item['total_kg']) }} kg</span>
                    </div>
                    <div class="progress-track" style="height:8px;">
                        <div class="{{ str_contains($item['quality_type'], 'SIR') ? 'bg-green-gradient' : 'bg-blue-gradient' }}"
                             style="height:8px;border-radius:999px;width:{{ collect($initialStock)->sum('total_kg') > 0 ? round($item['total_kg'] / collect($initialStock)->sum('total_kg') * 100) : 0 }}%;transition:width 0.8s;"></div>
                    </div>
                </div>
                <span class="badge {{ str_contains($item['quality_type'], 'SIR') ? 'badge-green' : 'badge-blue' }}" style="font-size:10px;">
                    {{ $item['lot_count'] }} lot
                </span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Recent Inbound --}}
    <div class="card-premium p-6 lg:col-span-2 anim-fade-up delay-4">
        <h3 class="section-title">
            <div class="section-icon bg-blue-10">
                <i data-lucide="clock" class="text-ptpn-blue" style="width:15px;height:15px;"></i>
            </div>
            Transaksi Terbaru
        </h3>
        <div style="overflow-x:auto;border-radius:10px;border:1px solid var(--border);margin-top:12px;">
            <table class="table-modern" id="recentTable">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>No. Tiket</th>
                        <th>Kendaraan</th>
                        <th>Mutu</th>
                        <th style="text-align:right;">Berat (kg)</th>
                    </tr>
                </thead>
                <tbody id="recentTbody">
                    @foreach($initialRecent as $r)
                    <tr>
                        <td><span style="font-size:11px;color:var(--text-muted);">{{ $r['time'] }}</span></td>
                        <td><span style="font-family:monospace;font-size:11px;">{{ Str::limit($r['ticket'], 20) }}</span></td>
                        <td>{{ $r['vehicle'] }}</td>
                        <td><span class="badge {{ str_contains($r['quality'], 'SIR') ? 'badge-green' : 'badge-blue' }}" style="font-size:10px;">{{ $r['quality'] }}</span></td>
                        <td style="text-align:right;font-weight:700;">{{ number_format($r['net_weight']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ SCRIPTS ═══ --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── State ────────────────────────────────────────
    let currentRange = '7d';
    let customStart = null, customEnd = null;
    let pollingTimer = null;

    // ── Chart Setup ─────────────────────────────────
    const ctx = document.getElementById('realtimeChart').getContext('2d');
    let gradIn = ctx.createLinearGradient(0,0,0,350);
    gradIn.addColorStop(0,'rgba(52,168,83,0.2)'); gradIn.addColorStop(1,'rgba(52,168,83,0)');
    let gradOut = ctx.createLinearGradient(0,0,0,350);
    gradOut.addColorStop(0,'rgba(245,166,35,0.2)'); gradOut.addColorStop(1,'rgba(245,166,35,0)');

    const initTrend = @json($initialTrend);
    const chart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: initTrend.labels,
            datasets: [
                {
                    label: 'Inbound (Masuk)',
                    data: initTrend.inbound,
                    borderColor: '#34A853', backgroundColor: gradIn,
                    borderWidth: 2.5, pointBackgroundColor: '#fff', pointBorderColor: '#34A853',
                    pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 7, fill: true, tension: 0.4
                },
                {
                    label: 'Outbound (Keluar)',
                    data: initTrend.outbound,
                    borderColor: '#F5A623', backgroundColor: gradOut,
                    borderWidth: 2.5, pointBackgroundColor: '#fff', pointBorderColor: '#F5A623',
                    pointBorderWidth: 2, pointRadius: 4, pointHoverRadius: 7, fill: true, tension: 0.4
                }
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: {
                legend: { position:'top', align:'end', labels: { usePointStyle:true, pointStyle:'circle', boxWidth:7, padding:16, font:{size:12,family:'Inter',weight:'600'} } },
                tooltip: { mode:'index', intersect:false, backgroundColor:'#0D1B2A', titleFont:{size:13,family:'Inter',weight:'700'}, bodyFont:{size:12,family:'Inter'}, padding:14, cornerRadius:12 }
            },
            scales: {
                y: { beginAtZero:true, grid:{color:'rgba(226,232,240,0.5)',borderDash:[4,4]}, border:{display:false}, ticks:{font:{size:11,family:'Inter'},color:'#94a3b8',padding:8, callback:v=>v>=1000?(v/1000)+'k':v} },
                x: { grid:{display:false}, border:{display:false}, ticks:{font:{size:11,family:'Inter'},color:'#94a3b8',padding:8} }
            },
            interaction: { mode:'nearest', axis:'x', intersect:false },
            animation: { duration:600, easing:'easeInOutQuart' }
        }
    });

    // ── Fetch Data ───────────────────────────────────
    function fetchData() {
        let url = '{{ route("reports.api.data") }}?range=' + currentRange;
        if (currentRange === 'custom' && customStart && customEnd) {
            url += '&start=' + customStart + '&end=' + customEnd;
        }
        fetch(url, { headers: { 'Accept':'application/json', 'X-Requested-With':'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            updateStats(data);
            updateChart(data.trend);
            updateRecent(data.recent);
            document.getElementById('lastUpdated').textContent = 'Update: ' + data.updated_at + ' WIB';
        })
        .catch(err => console.warn('Polling error:', err));
    }

    function updateStats(data) {
        const totalKg = data.stock.reduce((s,i) => s + i.total_kg, 0);
        const totalLots = data.stock.reduce((s,i) => s + i.lot_count, 0);
        animateStat('statTotalStock', number_format(totalKg) + ' <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>');
        document.getElementById('statLotCount').textContent = totalLots + ' Lot aktif';
        animateStat('statInbound', number_format(data.summary.total_inbound_kg) + ' <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>');
        document.getElementById('statInboundCount').textContent = data.summary.inbound_count + ' transaksi';
        animateStat('statOutbound', number_format(data.summary.total_outbound_kg) + ' <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>');

        const net = data.summary.net_change_kg;
        const sign = net >= 0 ? '+' : '';
        const el = document.getElementById('statNetChange');
        el.style.color = net >= 0 ? 'var(--green)' : '#ef4444';
        animateStat('statNetChange', sign + number_format(net) + ' <span style="font-size:13px;color:var(--text-muted);font-weight:400;">kg</span>');
    }

    function animateStat(id, html) {
        const el = document.getElementById(id);
        el.classList.add('updating');
        setTimeout(() => { el.innerHTML = html; el.classList.remove('updating'); }, 200);
    }

    function updateChart(trend) {
        chart.data.labels = trend.labels;
        chart.data.datasets[0].data = trend.inbound;
        chart.data.datasets[1].data = trend.outbound;
        chart.update('active');
    }

    function updateRecent(recent) {
        const tbody = document.getElementById('recentTbody');
        tbody.innerHTML = recent.map(r => `
            <tr>
                <td><span style="font-size:11px;color:var(--text-muted);">${r.time}</span></td>
                <td><span style="font-family:monospace;font-size:11px;">${r.ticket.substring(0,20)}</span></td>
                <td>${r.vehicle}</td>
                <td><span class="badge ${r.quality.includes('SIR')?'badge-green':'badge-blue'}" style="font-size:10px;">${r.quality}</span></td>
                <td style="text-align:right;font-weight:700;">${number_format(r.net_weight)}</td>
            </tr>
        `).join('');
    }

    function number_format(n) {
        return Math.round(n).toLocaleString('id-ID');
    }

    // ── Filter Controls ─────────────────────────────
    window.setRange = function(range, btn) {
        currentRange = range;
        customStart = null; customEnd = null;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        fetchData();
    };

    window.setCustomRange = function() {
        const s = document.getElementById('customStart').value;
        const e = document.getElementById('customEnd').value;
        if (!s || !e) return;
        currentRange = 'custom';
        customStart = s; customEnd = e;
        document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
        fetchData();
    };

    // ── Auto-Polling (10 seconds) ───────────────────
    pollingTimer = setInterval(fetchData, 10000);

    // Pause polling when tab hidden
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) { clearInterval(pollingTimer); }
        else { fetchData(); pollingTimer = setInterval(fetchData, 10000); }
    });

    if (window.lucide) lucide.createIcons();
});
</script>
@endsection
