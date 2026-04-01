@extends('layouts.modern')

@section('title', 'Manajemen Stok')
@section('header', 'Manajemen Stok')
@section('subheader', 'Data lot stok gudang PTPN 1 secara real-time')

@section('actions')
    <a href="{{ route('stocks.create') }}" class="btn btn-green">
        <i data-lucide="plus" style="width:15px;height:15px;"></i>
        Tambah Stok
    </a>
@endsection

@push('styles')
<style>
/* ════════════════════════════════
   STOCK PAGE — MOBILE CARD SYSTEM
   Show: table on desktop, cards on mobile
   ════════════════════════════════ */

/* Default: desktop table shown, mobile cards hidden */
.stock-desktop { display: block; }
.stock-mobile  { display: none;  }

@media (max-width: 780px) {
    .stock-desktop { display: none !important; }
    .stock-mobile  { display: block; }
    .stock-area-label {
        padding: 10px 16px 4px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
    }
}

/* ── Premium Stock Card ─────────────────────────── */
.stock-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(203,213,225,0.5);
    margin: 0 16px 14px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06), 0 1px 3px rgba(0,0,0,0.04);
    transition: transform 0.15s, box-shadow 0.15s;
}
.stock-card:active {
    transform: scale(0.985);
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
}

/* Card gradient header */
.stock-card-head {
    background: linear-gradient(135deg, rgba(52,168,83,0.12) 0%, rgba(52,168,83,0.04) 100%);
    border-bottom: 1px solid rgba(52,168,83,0.12);
    padding: 14px 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.stock-card-head .sck-lot {
    font-family: var(--font-mono, 'JetBrains Mono', monospace);
    font-size: 20px;
    font-weight: 900;
    color: #111827;
    letter-spacing: -0.03em;
    line-height: 1;
}
.stock-card-head .sck-lot small {
    display: block;
    font-size: 9px;
    font-weight: 600;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: var(--green, #34a853);
    margin-bottom: 2px;
    font-family: 'Inter', sans-serif;
}

/* Card body rows */
.stock-card-body {
    padding: 0;
}
.sck-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid rgba(203,213,225,0.35);
    gap: 10px;
    min-height: 44px;
}
.sck-row:last-child { border-bottom: none; }
.sck-key {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
    flex-shrink: 0;
    max-width: 40%;
}
.sck-key svg { flex-shrink: 0; opacity: 0.65; }
.sck-val {
    font-size: 13px;
    font-weight: 700;
    color: #111827;
    text-align: right;
    flex: 1;
    line-height: 1.4;
}
.sck-val .muted {
    font-size: 10.5px;
    font-weight: 400;
    color: #94a3b8;
    margin-left: 2px;
}

/* Palet chips */
.sck-pallets {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    padding: 8px 16px 10px;
    border-bottom: 1px solid rgba(203,213,225,0.35);
    background: rgba(248,250,252,0.7);
}
.sck-pallet-header {
    width: 100%;
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: #94a3b8;
    margin-bottom: 4px;
}
.sck-chip {
    background: rgba(52,168,83,0.08);
    border: 1px solid rgba(52,168,83,0.2);
    color: #1a7d38;
    font-family: var(--font-mono, monospace);
    font-size: 9.5px;
    font-weight: 800;
    padding: 3px 7px;
    border-radius: 6px;
    letter-spacing: 0.02em;
}

/* Card footer */
.stock-card-foot {
    padding: 10px 14px;
    background: rgba(249,250,251,0.9);
}
.stock-card-foot a {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 9px 0;
    background: #fff;
    border: 1.5px solid rgba(203,213,225,0.7);
    border-radius: 10px;
    font-size: 13px;
    font-weight: 700;
    color: #374151;
    text-decoration: none;
    transition: all 0.15s;
}
.stock-card-foot a:active {
    background: rgba(52,168,83,0.06);
    border-color: rgba(52,168,83,0.3);
    color: #1a7d38;
}
</style>
@endpush

@section('content')

{{-- ══ Summary mini-cards ══ --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 anim-fade-up">
    @php
        $totalLot  = $stocks->total();
        $available = $stocks->getCollection()->where('status','blue')->count();
        $shipping  = $stocks->getCollection()->whereIn('status',['yellow','orange'])->count();
        $totalKg   = $stocks->getCollection()->sum(fn($s) => $s->details->sum('net_weight_kg'));
    @endphp
    @foreach([
        ['label'=>'Total Data',  'val'=>number_format($totalLot),      'icon'=>'package-2',   'clr'=>'#34a853'],
        ['label'=>'Tersedia',    'val'=>number_format($available),     'icon'=>'check-circle', 'clr'=>'#4AADE4'],
        ['label'=>'Dalam Kirim', 'val'=>number_format($shipping),      'icon'=>'truck',        'clr'=>'#f4a11b'],
        ['label'=>'Total Berat', 'val'=>number_format($totalKg).' kg', 'icon'=>'weight',       'clr'=>'#34a853'],
    ] as $i => $s)
    <div class="card-premium p-4 anim-fade-up delay-{{ $i+1 }}">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:38px;height:38px;border-radius:11px;background:{{ $s['clr'] }}1a;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="{{ $s['icon'] }}" style="width:18px;height:18px;color:{{ $s['clr'] }};"></i>
            </div>
            <div>
                <p style="font-size:11px;color:var(--text-muted);margin:0;font-weight:500;">{{ $s['label'] }}</p>
                <p style="font-size:16px;font-weight:800;color:var(--text-primary);margin:2px 0 0;letter-spacing:-0.02em;">{{ $s['val'] }}</p>
            </div>
        </div>
    </div>
    @endforeach
</div>

{{-- ══ Main Card ══ --}}
<div class="card-premium anim-fade-up delay-2" style="overflow:hidden;">

    {{-- Toolbar --}}
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:8px;">
            <span style="font-size:14px;font-weight:700;color:var(--text-primary);">Daftar Stok</span>
            <div class="filter-tabs" id="statusTabs">
                <button class="filter-tab active"  onclick="filterAll('all',      this)">Semua</button>
                <button class="filter-tab"         onclick="filterAll('blue',     this)">Tersedia</button>
                <button class="filter-tab"         onclick="filterAll('shipping', this)">Dalam Kirim</button>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:8px;padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:rgba(248,250,252,0.9);">
            <i data-lucide="search" style="width:14px;height:14px;color:var(--text-muted);flex-shrink:0;"></i>
            <input type="text" id="tableSearch" placeholder="Cari nomor lot..."
                   oninput="filterAll(null, null, this.value)"
                   style="border:none;background:transparent;font-size:13px;color:var(--text-primary);outline:none;width:155px;font-family:'Inter',sans-serif;">
            <button onclick="document.getElementById('tableSearch').value='';filterAll(null,null,'');"
                    style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0;display:flex;">
                <i data-lucide="x" style="width:13px;height:13px;"></i>
            </button>
        </div>
    </div>

    {{-- ══════════ SIR Stocks ══════════ --}}
    {{-- Desktop table header for SIR --}}
    <div class="stock-desktop">
        <div style="padding:10px 20px 4px;border-bottom:1px solid var(--border);background:rgba(52,168,83,0.04);">
            <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--green);">▶ Data Lot SIR</span>
        </div>
        <table class="table-modern" style="margin:0;" id="dtSirHead">
            <thead>
                <tr>
                    <th style="padding-left:20px;">Nomor Lot</th>
                    <th>Mutu</th>
                    <th>Status</th>
                    <th>Berat / Palet</th>
                    <th>Tgl. Masuk</th>
                    <th style="text-align:right;padding-right:20px;">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Mobile area label for SIR --}}
    <div class="stock-mobile stock-area-label" style="color:var(--green);padding-top:14px;">
        ▶ DATA LOT SIR
    </div>

    @forelse($groupedSirStocks as $prefix => $lotGroup)
    @php
        $lastStock = $lotGroup->last();
        $statusMap = [
            'blue'   => ['label'=>'Tersedia',     'class'=>'badge-blue'],
            'yellow' => ['label'=>'Shipping (G)', 'class'=>'badge-orange'],
            'orange' => ['label'=>'Shipping (Gn)','class'=>'badge-orange'],
            'white'  => ['label'=>'Kosong',       'class'=>'badge-gray'],
        ];
        $quality     = $lastStock->quality_type;
        $qClass      = str_contains($quality, 'SIR 20') ? 'badge-green' : 'badge-gray';
        $st          = $statusMap[$lastStock->status] ?? ['label'=>'Unknown','class'=>'badge-gray'];
        $allDetails  = collect();
        foreach ($lotGroup as $stk) {
            foreach ($stk->details as $dtk) { $allDetails->push($dtk); }
        }
        $totalBerat  = $allDetails->sum('net_weight_kg');
        $statusGroup = in_array($lastStock->status, ['yellow','orange']) ? 'shipping' : $lastStock->status;
    @endphp

    {{-- Desktop TR --}}
    <div class="stock-desktop" data-si data-search="{{ strtolower($prefix) }}" data-status="{{ $statusGroup }}">
        <table class="table-modern" style="margin:0;">
            <tbody>
                <tr>
                    <td style="padding-left:20px;width:15%;">
                        <span style="font-family:var(--font-mono);font-size:14px;font-weight:800;">{{ $prefix }}</span>
                    </td>
                    <td style="width:12%;">
                        <span class="badge {{ $qClass }}">{{ $quality }}</span>
                    </td>
                    <td style="width:10%;">
                        <span class="badge {{ $st['class'] }}">{{ $st['label'] }}</span>
                    </td>
                    <td style="width:32%;">
                        <div>
                            <span style="font-family:var(--font-mono);font-size:14px;font-weight:800;">{{ number_format($totalBerat) }}</span>
                            <span style="font-size:11px;color:var(--text-muted);"> kg</span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:3px;margin-top:4px;">
                            @foreach($allDetails as $det)
                            @php $pNum = trim(str_replace('FDF','',strtoupper($det->fdf_number ?? ''))); @endphp
                            <span class="sck-chip" style="font-size:9px;" title="{{ $det->fdf_number }} | {{ $det->net_weight_kg }} kg">{{ $pNum ?: $loop->iteration }}</span>
                            @endforeach
                            <span style="font-size:10px;color:var(--text-muted);align-self:center;margin-left:2px;">{{ $allDetails->count() }} Palet</span>
                        </div>
                    </td>
                    <td style="width:16%;font-size:12px;color:var(--text-secondary);">
                        <div>{{ $lastStock->created_at->format('d M Y') }}</div>
                        <div style="font-size:10px;color:var(--text-muted);">{{ $lastStock->created_at->format('H:i') }}</div>
                    </td>
                    <td style="width:15%;text-align:right;padding-right:20px;">
                        <a href="{{ route('stocks.edit', $lastStock->id) }}" class="btn btn-ghost" style="font-size:12px;padding:5px 10px;">
                            <i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ══ MOBILE CARD ══ --}}
    <div class="stock-mobile" data-si data-search="{{ strtolower($prefix) }}" data-status="{{ $statusGroup }}">
        <div class="stock-card">
            {{-- Gradient Header --}}
            <div class="stock-card-head">
                <div class="sck-lot">
                    <small>SIR Lot</small>
                    {{ $prefix }}
                </div>
                <span class="badge {{ $st['class'] }}" style="flex-shrink:0;font-size:11px;">{{ $st['label'] }}</span>
            </div>
            {{-- Body --}}
            <div class="stock-card-body">
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        Mutu
                    </span>
                    <span class="sck-val"><span class="badge {{ $qClass }}">{{ $quality }}</span></span>
                </div>
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3v18M3 12h18"/></svg>
                        Berat
                    </span>
                    <span class="sck-val">
                        <span style="font-family:var(--font-mono);font-size:16px;font-weight:900;">{{ number_format($totalBerat) }}</span><span class="muted"> kg</span>
                    </span>
                </div>
                <div class="sck-pallets">
                    <span class="sck-pallet-header">{{ $allDetails->count() }} Palet</span>
                    @foreach($allDetails as $det)
                    @php $pNum = trim(str_replace('FDF','',strtoupper($det->fdf_number ?? ''))); @endphp
                    <span class="sck-chip" title="{{ $det->fdf_number }} | {{ $det->net_weight_kg }} kg">{{ $pNum ?: $loop->iteration }}</span>
                    @endforeach
                </div>
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Tgl. Masuk
                    </span>
                    <span class="sck-val">
                        {{ $lastStock->created_at->format('d M Y') }}
                        <span class="muted">{{ $lastStock->created_at->format('H:i') }}</span>
                    </span>
                </div>
            </div>
            {{-- Footer --}}
            <div class="stock-card-foot">
                <a href="{{ route('stocks.edit', $lastStock->id) }}">
                    <i data-lucide="pencil" style="width:14px;height:14px;color:#34a853;"></i>
                    Detail / Edit
                </a>
            </div>
        </div>
    </div>

    @empty
    <div class="empty-state" style="margin:16px;">
        <div class="empty-state-icon"><i data-lucide="inbox" style="width:28px;height:28px;color:var(--text-muted);"></i></div>
        <h3 style="font-size:14px;font-weight:700;margin:0;">Belum Ada Data Stok SIR</h3>
        <p style="font-size:12px;color:var(--text-muted);margin:0;">Tambahkan melalui scan OCR atau form manual</p>
    </div>
    @endforelse

    {{-- ══════════ RSS Stocks ══════════ --}}
    {{-- Desktop header for RSS --}}
    <div class="stock-desktop" style="border-top:2px solid var(--border);">
        <div style="padding:10px 20px 4px;border-bottom:1px solid var(--border);background:rgba(74,173,228,0.04);">
            <span style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:0.08em;color:var(--blue);">▶ Data Stok RSS</span>
        </div>
        <table class="table-modern" style="margin:0;">
            <thead>
                <tr>
                    <th style="padding-left:20px;">Nomor Referensi</th>
                    <th>Mutu</th>
                    <th>Status</th>
                    <th>Bale / Berat</th>
                    <th>Tgl. Masuk</th>
                    <th style="text-align:right;padding-right:20px;">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    {{-- Mobile area label for RSS --}}
    <div class="stock-mobile stock-area-label" style="color:var(--blue);padding-top:10px;border-top:2px solid var(--border);">
        ▶ DATA STOK RSS
    </div>

    @forelse($rssStocks as $stock)
    @php
        $statusMap2   = [
            'blue'   => ['label'=>'Tersedia',     'class'=>'badge-blue'],
            'yellow' => ['label'=>'Shipping (G)', 'class'=>'badge-orange'],
            'orange' => ['label'=>'Shipping (Gn)','class'=>'badge-orange'],
            'white'  => ['label'=>'Kosong',       'class'=>'badge-gray'],
        ];
        $stRss        = $statusMap2[$stock->status] ?? ['label'=>'Unknown','class'=>'badge-gray'];
        $totalBale    = $stock->details->sum('quantity_unit');
        $totalBeratR  = $stock->details->sum('net_weight_kg');
        $statusGroupR = in_array($stock->status,['yellow','orange']) ? 'shipping' : $stock->status;
    @endphp

    {{-- Desktop TR RSS --}}
    <div class="stock-desktop" data-si data-search="{{ strtolower($stock->lot_number) }}" data-status="{{ $statusGroupR }}">
        <table class="table-modern" style="margin:0;">
            <tbody>
                <tr>
                    <td style="padding-left:20px;width:22%;"><span style="font-family:var(--font-mono);font-size:13px;font-weight:800;">{{ $stock->lot_number }}</span></td>
                    <td style="width:12%;"><span class="badge badge-blue">{{ $stock->quality_type }}</span></td>
                    <td style="width:10%;"><span class="badge {{ $stRss['class'] }}">{{ $stRss['label'] }}</span></td>
                    <td style="width:24%;">
                        <span style="font-family:var(--font-mono);font-weight:800;">{{ number_format($totalBale) }}</span><span style="font-size:11px;color:var(--text-muted);"> Bale</span><br>
                        <span style="font-size:11px;color:var(--text-muted);">{{ number_format($totalBeratR) }} kg</span>
                    </td>
                    <td style="width:16%;font-size:12px;">
                        <div>{{ $stock->created_at->format('d M Y') }}</div>
                        <div style="font-size:10px;color:var(--text-muted);">{{ $stock->created_at->format('H:i') }}</div>
                    </td>
                    <td style="width:16%;text-align:right;padding-right:20px;">
                        <a href="{{ route('stocks.edit', $stock->id) }}" class="btn btn-ghost" style="font-size:12px;padding:5px 10px;">
                            <i data-lucide="pencil" style="width:12px;height:12px;"></i> Edit
                        </a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ══ MOBILE CARD RSS ══ --}}
    <div class="stock-mobile" data-si data-search="{{ strtolower($stock->lot_number) }}" data-status="{{ $statusGroupR }}">
        <div class="stock-card" style="border-top-color:rgba(74,173,228,0.2);">
            {{-- Gradient Header (blue tint for RSS) --}}
            <div class="stock-card-head" style="background:linear-gradient(135deg,rgba(74,173,228,0.1) 0%,rgba(74,173,228,0.03) 100%);border-bottom-color:rgba(74,173,228,0.15);">
                <div class="sck-lot">
                    <small style="color:var(--blue);">RSS Stock</small>
                    {{ Str::limit($stock->lot_number, 20) }}
                </div>
                <span class="badge {{ $stRss['class'] }}" style="flex-shrink:0;font-size:11px;">{{ $stRss['label'] }}</span>
            </div>
            <div class="stock-card-body">
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        Mutu
                    </span>
                    <span class="sck-val"><span class="badge badge-blue">{{ $stock->quality_type }}</span></span>
                </div>
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                        Total Bale
                    </span>
                    <span class="sck-val">
                        <span style="font-family:var(--font-mono);font-size:16px;font-weight:900;">{{ number_format($totalBale) }}</span><span class="muted"> bale</span>
                    </span>
                </div>
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3v18M3 12h18"/></svg>
                        Berat
                    </span>
                    <span class="sck-val">
                        <span style="font-family:var(--font-mono);font-weight:800;">{{ number_format($totalBeratR) }}</span><span class="muted"> kg</span>
                    </span>
                </div>
                <div class="sck-row">
                    <span class="sck-key">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Tgl. Masuk
                    </span>
                    <span class="sck-val">
                        {{ $stock->created_at->format('d M Y') }}
                        <span class="muted">{{ $stock->created_at->format('H:i') }}</span>
                    </span>
                </div>
            </div>
            <div class="stock-card-foot">
                <a href="{{ route('stocks.edit', $stock->id) }}">
                    <i data-lucide="pencil" style="width:14px;height:14px;color:#4AADE4;"></i>
                    Detail / Edit
                </a>
            </div>
        </div>
    </div>

    @empty
    <div class="empty-state" style="margin:16px;">
        <div class="empty-state-icon"><i data-lucide="inbox" style="width:28px;height:28px;color:var(--text-muted);"></i></div>
        <h3 style="font-size:14px;font-weight:700;margin:0;">Belum Ada Data Stok RSS</h3>
        <p style="font-size:12px;color:var(--text-muted);margin:0;">Tambahkan melalui scan OCR atau form manual</p>
    </div>
    @endforelse

    <div style="padding-bottom:16px;"></div>
</div>

<script>
let _status = 'all';
let _search = '';

function filterAll(status, btn, search) {
    if (status  !== null && status  !== undefined) _status = status;
    if (search  !== null && search  !== undefined) _search = search.toLowerCase();

    if (btn) {
        document.querySelectorAll('#statusTabs .filter-tab').forEach(b => b.className = 'filter-tab');
        btn.classList.add(_status === 'shipping' ? 'active-orange' : 'active');
    }

    document.querySelectorAll('[data-si]').forEach(el => {
        const ok = (!_search || (el.dataset.search || '').includes(_search))
                && (_status === 'all' || el.dataset.status === _status);
        el.style.display = ok ? '' : 'none';
    });
}

document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endsection