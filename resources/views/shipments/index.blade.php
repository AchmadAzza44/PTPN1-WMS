@extends('layouts.modern')

@section('title', 'Data Pengiriman')
@section('header', 'Data Pengiriman (Outbound)')
@section('subheader', 'Kelola pengiriman barang dan surat jalan')

@section('actions')
    <a href="{{ route('ocr.index', ['type' => 'outbound']) }}" class="btn btn-orange">
        <i data-lucide="scan-text" style="width:15px;height:15px;"></i>
        Buat Pengiriman Baru
    </a>
@endsection

@push('styles')
<style>
.ship-desktop { display: block; }
.ship-mobile  { display: none;  }

@media (max-width: 780px) {
    .ship-desktop { display: none !important; }
    .ship-mobile  { display: block; }
}

.ship-card {
    background: #fff;
    border-radius: 18px;
    border: 1px solid rgba(203,213,225,0.5);
    margin: 0 16px 14px;
    overflow: hidden;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    transition: transform .15s, box-shadow .15s;
}
.ship-card:active { transform: scale(0.985); box-shadow: 0 1px 4px rgba(0,0,0,0.06); }

.ship-card-head {
    background: linear-gradient(135deg, rgba(244,161,27,0.10) 0%, rgba(244,161,27,0.03) 100%);
    border-bottom: 1px solid rgba(244,161,27,0.15);
    padding: 14px 16px;
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}
.ship-id {
    font-family: var(--font-mono, monospace);
    font-size: 19px;
    font-weight: 900;
    color: #111827;
    letter-spacing: -0.02em;
    line-height: 1.1;
}
.ship-id small {
    display: block;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: #f4a11b;
    margin-bottom: 2px;
    font-family: 'Inter', sans-serif;
}
.ship-card-body {}
.ship-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    padding: 10px 16px;
    border-bottom: 1px solid rgba(203,213,225,0.35);
    gap: 10px;
    min-height: 42px;
}
.ship-row:last-child { border-bottom: none; }
.ship-key {
    display: flex;
    align-items: center;
    gap: 7px;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    flex-shrink: 0;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    padding-top: 1px;
    max-width: 42%;
}
.ship-val {
    font-size: 13px;
    font-weight: 600;
    color: #111827;
    text-align: right;
    flex: 1;
    line-height: 1.4;
}
.ship-val .muted {
    font-size: 11px;
    font-weight: 400;
    color: #94a3b8;
    display: block;
    margin-top: 1px;
}
.ship-card-foot {
    padding: 10px 14px;
    background: rgba(249,250,251,0.9);
}
.ship-card-foot a {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    width: 100%; padding: 9px 0;
    background: #fff; border: 1.5px solid rgba(203,213,225,0.7);
    border-radius: 10px; font-size: 13px; font-weight: 700;
    color: #374151; text-decoration: none; transition: all .15s;
}
.ship-card-foot a:active {
    background: rgba(244,161,27,0.07); border-color: rgba(244,161,27,0.4); color: #b07a10;
}

/* Empty state fix on mobile */
@media (max-width: 780px) {
    .mobile-empty-state {
        margin: 16px;
        padding: 32px 20px;
        background: rgba(248,250,252,0.7);
        border: 1.5px dashed rgba(203,213,225,0.7);
        border-radius: 16px;
        text-align: center;
    }
}
</style>
@endpush

@section('content')

{{-- Summary mini-cards --}}
@php
    $total    = $shipments->total();
    $draft    = $shipments->getCollection()->where('status','draft')->count();
    $done     = $shipments->getCollection()->where('status','completed')->count();
    $verified = $shipments->getCollection()->where('status','verified')->count();
@endphp
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6 anim-fade-up">
    @foreach([
        ['label'=>'Total Pengiriman', 'val'=>number_format($total),    'icon'=>'truck',          'clr'=>'#4AADE4'],
        ['label'=>'Draft',            'val'=>number_format($draft),    'icon'=>'clock',          'clr'=>'#f4a11b'],
        ['label'=>'Terverifikasi',    'val'=>number_format($verified), 'icon'=>'shield-check',   'clr'=>'#4AADE4'],
        ['label'=>'Selesai',          'val'=>number_format($done),     'icon'=>'check-circle-2', 'clr'=>'#34a853'],
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

{{-- Main Card --}}
<div class="card-premium anim-fade-up delay-2" style="overflow:hidden;">

    {{-- Toolbar --}}
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;border-radius:9px;background:rgba(244,161,27,0.1);display:flex;align-items:center;justify-content:center;">
                <i data-lucide="list" style="width:15px;height:15px;color:#f4a11b;"></i>
            </div>
            <span style="font-size:14px;font-weight:700;color:var(--text-primary);">Daftar Pengiriman</span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;padding:7px 12px;border-radius:10px;border:1px solid var(--border);background:rgba(248,250,252,0.9);">
            <i data-lucide="search" style="width:14px;height:14px;color:var(--text-muted);flex-shrink:0;"></i>
            <input type="text" id="shipSearch" placeholder="Cari ekspedisi / tujuan..."
                   oninput="filterShipments(this.value)"
                   style="border:none;background:transparent;font-size:13px;color:var(--text-primary);outline:none;width:180px;font-family:'Inter',sans-serif;">
            <button onclick="document.getElementById('shipSearch').value='';filterShipments('');"
                    style="background:none;border:none;cursor:pointer;color:var(--text-muted);padding:0;display:flex;">
                <i data-lucide="x" style="width:13px;height:13px;"></i>
            </button>
        </div>
    </div>

    {{-- Desktop Table --}}
    <div class="ship-desktop">
        <table class="table-modern" id="shipmentsTable">
            <thead>
                <tr>
                    <th>ID / Tanggal</th>
                    <th>Ekspedisi &amp; Supir</th>
                    <th>Tujuan (Pembeli)</th>
                    <th style="text-align:right;">Total Muatan</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody id="shipmentsTbody">
                @forelse($shipments as $shipment)
                @php
                    $searchVal = strtolower($shipment->transporter_name . ' ' . ($shipment->purchaseOrder->contract->buyer_name ?? ''));
                @endphp
                <tr data-search="{{ $searchVal }}">
                    <td>
                        <span class="mono" style="font-weight:700;color:var(--text-primary);">#{{ $shipment->id }}</span>
                        <p style="font-size:12px;color:var(--text-muted);margin:2px 0 0;">{{ $shipment->created_at->format('d M Y H:i') }}</p>
                    </td>
                    <td>
                        <p style="font-weight:600;color:var(--text-primary);margin:0;">{{ $shipment->transporter_name }}</p>
                        <p style="font-size:12px;color:var(--text-secondary);margin:2px 0 0;">{{ $shipment->driver_name }} &bull; {{ $shipment->vehicle_plate }}</p>
                    </td>
                    <td>
                        <p style="font-weight:600;margin:0;">{{ $shipment->purchaseOrder->contract->buyer_name ?? '-' }}</p>
                        <p style="font-size:12px;color:var(--text-muted);font-family:var(--font-mono);margin:2px 0 0;">{{ $shipment->purchaseOrder->contract->contract_number ?? '-' }}</p>
                    </td>
                    <td style="text-align:right;">
                        <span class="mono" style="font-weight:700;">{{ number_format($shipment->items->sum('qty_loaded_kg'), 0) }}</span>
                        <span style="font-size:12px;color:var(--text-muted);margin-left:2px;">KG</span>
                    </td>
                    <td style="text-align:center;">
                        @if($shipment->status == 'completed')
                            <span class="badge badge-green"><i data-lucide="check-circle-2" style="width:10px;height:10px;"></i>Selesai</span>
                        @elseif($shipment->status == 'verified')
                            <span class="badge badge-blue"><i data-lucide="shield-check" style="width:10px;height:10px;"></i>Terverifikasi</span>
                        @else
                            <span class="badge badge-orange"><i data-lucide="clock" style="width:10px;height:10px;"></i>Draft</span>
                        @endif
                    </td>
                    <td style="text-align:center;">
                        <a href="{{ route('shipments.show', $shipment->id) }}" class="btn btn-ghost" style="padding:6px 12px;font-size:12px;">
                            Detail <i data-lucide="arrow-right" style="width:12px;height:12px;"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6">
                    <div class="empty-state">
                        <div class="empty-state-icon"><i data-lucide="truck" style="width:30px;height:30px;color:var(--text-muted);"></i></div>
                        <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);margin:0;">Belum Ada Pengiriman</h3>
                        <p style="font-size:13px;color:var(--text-muted);margin:0;">Mulai dengan membuat pengiriman baru via OCR</p>
                        <a href="{{ route('ocr.index', ['type' => 'outbound']) }}" class="btn btn-orange">
                            <i data-lucide="scan-text" style="width:14px;height:14px;"></i>Buat Pengiriman
                        </a>
                    </div>
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Mobile Cards --}}
    <div class="ship-mobile" style="padding-top:10px;padding-bottom:4px;">
        @forelse($shipments as $shipment)
        @php
            $searchVal  = strtolower($shipment->transporter_name . ' ' . ($shipment->purchaseOrder->contract->buyer_name ?? ''));
            $totalKg    = $shipment->items->sum('qty_loaded_kg');
        @endphp
        <div class="ship-card" data-ship data-search="{{ $searchVal }}">
            <div class="ship-card-head">
                <div class="ship-id">
                    <small>Pengiriman</small>
                    #{{ $shipment->id }}
                    <span style="font-size:11px;font-weight:500;color:#64748b;display:block;margin-top:2px;font-family:'Inter',sans-serif;">{{ $shipment->created_at->format('d M Y, H:i') }}</span>
                </div>
                @if($shipment->status == 'completed')
                    <span class="badge badge-green" style="flex-shrink:0;font-size:11px;">Selesai</span>
                @elseif($shipment->status == 'verified')
                    <span class="badge badge-blue" style="flex-shrink:0;font-size:11px;">Terverifikasi</span>
                @else
                    <span class="badge badge-orange" style="flex-shrink:0;font-size:11px;">Draft</span>
                @endif
            </div>
            <div class="ship-card-body">
                <div class="ship-row">
                    <span class="ship-key">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="1" y="3" width="15" height="13"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                        Ekspedisi
                    </span>
                    <span class="ship-val">
                        {{ $shipment->transporter_name }}
                        <span class="muted">{{ $shipment->driver_name }} &bull; {{ $shipment->vehicle_plate }}</span>
                    </span>
                </div>
                <div class="ship-row">
                    <span class="ship-key">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Tujuan
                    </span>
                    <span class="ship-val">
                        {{ $shipment->purchaseOrder->contract->buyer_name ?? '-' }}
                        <span class="muted" style="font-family:var(--font-mono);">{{ $shipment->purchaseOrder->contract->contract_number ?? '' }}</span>
                    </span>
                </div>
                <div class="ship-row">
                    <span class="ship-key">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 3v18M3 12h18"/></svg>
                        Muatan
                    </span>
                    <span class="ship-val">
                        <span style="font-family:var(--font-mono);font-size:16px;font-weight:900;">{{ number_format($totalKg, 0) }}</span>
                        <span style="font-size:11px;color:#94a3b8;font-weight:400;"> KG</span>
                    </span>
                </div>
            </div>
            <div class="ship-card-foot">
                <a href="{{ route('shipments.show', $shipment->id) }}">
                    <i data-lucide="eye" style="width:14px;height:14px;color:#f4a11b;"></i>
                    Lihat Detail
                </a>
            </div>
        </div>
        @empty
        <div class="mobile-empty-state">
            <i data-lucide="truck" style="width:40px;height:40px;color:#cbd5e1;margin-bottom:12px;"></i>
            <h3 style="font-size:15px;font-weight:700;color:#374151;margin:0 0 6px;">Belum Ada Pengiriman</h3>
            <p style="font-size:13px;color:#94a3b8;margin:0 0 14px;">Mulai dengan membuat pengiriman baru via OCR</p>
            <a href="{{ route('ocr.index', ['type' => 'outbound']) }}" class="btn btn-orange" style="display:inline-flex;">
                <i data-lucide="scan-text" style="width:14px;height:14px;"></i>Buat Pengiriman
            </a>
        </div>
        @endforelse
    </div>

    @if($shipments->hasPages())
    <div style="padding:16px 24px;border-top:1px solid var(--border);">
        {{ $shipments->links() }}
    </div>
    @endif
</div>

<script>
function filterShipments(query) {
    const q = query.toLowerCase();
    // Desktop
    document.querySelectorAll('#shipmentsTbody tr[data-search]').forEach(row => {
        row.style.display = row.dataset.search.includes(q) ? '' : 'none';
    });
    // Mobile
    document.querySelectorAll('.ship-card[data-ship]').forEach(card => {
        card.style.display = (card.dataset.search || '').includes(q) ? '' : 'none';
    });
}
document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
</script>
@endsection