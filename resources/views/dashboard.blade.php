@extends('layouts.modern')

@section('title', 'Dashboard')
@section('header', 'Dashboard Overview')
@section('subheader', 'Monitor operasi gudang secara real-time')

@section('content')

{{-- ═══════════════ STAT CARDS ═══════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-7">

    {{-- Card 1: SIR 20 SW --}}
    <div class="card-premium border-green p-5 anim-fade-up delay-1">
        <div class="card-icon-bg">
            <i data-lucide="package-2" style="width:72px;height:72px;color:var(--green);"></i>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div class="bg-green-10 shadow-green" style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="package-2" class="text-ptpn-green" style="width:20px;height:20px;"></i>
            </div>
            <p style="font-size:13px;font-weight:600;color:var(--text-secondary);margin:0;">Stok SIR 20 SW</p>
        </div>
        <h3 class="counter-num" style="font-size:2rem;font-weight:900;color:var(--text-primary);margin:0 0 6px 0;letter-spacing:-0.03em;"
            data-target="{{ $stocks->where('quality_type','SIR 20 SW')->where('status','!=','orange')->sum(fn($s) => $s->details->sum('net_weight_kg')) }}">
            0 <span style="font-size:14px;color:var(--text-muted);font-weight:400;">kg</span>
        </h3>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div class="badge badge-green">
                <i data-lucide="trending-up" style="width:10px;height:10px;"></i>
                Lot Aktif: {{ $capacityData['SIR']['current'] }}
            </div>
            <span style="font-size:11px;color:var(--text-muted);">/ {{ $capacityData['SIR']['max'] }} max</span>
        </div>
    </div>

    {{-- Card 2: RSS 1 --}}
    <div class="card-premium border-blue p-5 anim-fade-up delay-2">
        <div class="card-icon-bg">
            <i data-lucide="layers" style="width:72px;height:72px;color:var(--blue);"></i>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div class="bg-blue-10 shadow-blue" style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="layers" class="text-ptpn-blue" style="width:20px;height:20px;"></i>
            </div>
            <p style="font-size:13px;font-weight:600;color:var(--text-secondary);margin:0;">Stok RSS 1</p>
        </div>
        <h3 class="counter-num" style="font-size:2rem;font-weight:900;color:var(--text-primary);margin:0 0 6px 0;letter-spacing:-0.03em;"
            data-target="{{ $stocks->where('quality_type','RSS 1')->where('status','!=','orange')->sum(fn($s) => $s->details->sum('net_weight_kg')) }}">
            0 <span style="font-size:14px;color:var(--text-muted);font-weight:400;">kg</span>
        </h3>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div class="badge badge-blue">
                <i data-lucide="check-circle" style="width:10px;height:10px;"></i>
                Bale Aktif: {{ $capacityData['RSS']['current'] }}
            </div>
            <span style="font-size:11px;color:var(--text-muted);">/ {{ $capacityData['RSS']['max'] }} max</span>
        </div>
    </div>

    {{-- Card 3: Inbound Hari Ini --}}
    <div class="card-premium border-green p-5 anim-fade-up delay-3">
        <div class="card-icon-bg">
            <i data-lucide="arrow-down-circle" style="width:72px;height:72px;color:var(--green);"></i>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div class="bg-green-10" style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="arrow-down-circle" class="text-ptpn-green" style="width:20px;height:20px;"></i>
            </div>
            <p style="font-size:13px;font-weight:600;color:var(--text-secondary);margin:0;">Inbound Hari Ini</p>
        </div>
        <h3 class="counter-num" style="font-size:2rem;font-weight:900;color:var(--text-primary);margin:0 0 6px 0;letter-spacing:-0.03em;"
            data-target="{{ \App\Models\StockLot::whereDate('inbound_at', today())->count() }}">
            0 <span style="font-size:14px;color:var(--text-muted);font-weight:400;">Trans</span>
        </h3>
        <div style="display:flex;align-items:center;gap:8px;">
            <div class="badge badge-gray">
                <i data-lucide="clock" style="width:10px;height:10px;"></i>
                Update: {{ now()->format('H:i') }}
            </div>
        </div>
    </div>

    {{-- Card 4: Kontrak Aktif --}}
    <div class="card-premium border-orange p-5 anim-fade-up delay-4">
        <div class="card-icon-bg">
            <i data-lucide="file-text" style="width:72px;height:72px;color:var(--orange);"></i>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
            <div class="bg-orange-10 shadow-orange" style="width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="file-text" class="text-ptpn-orange" style="width:20px;height:20px;"></i>
            </div>
            <p style="font-size:13px;font-weight:600;color:var(--text-secondary);margin:0;">Kontrak Aktif</p>
        </div>
        <h3 class="counter-num" style="font-size:2rem;font-weight:900;color:var(--text-primary);margin:0 0 6px 0;letter-spacing:-0.03em;"
            data-target="{{ $contracts->count() }}">
            0
        </h3>
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <div class="badge badge-orange">
                <i data-lucide="scale" style="width:10px;height:10px;"></i>
                Sisa: {{ number_format($contracts->sum('remaining_tonnage')) }} Ton
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ CHART + CAPACITY ═══════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-7">

    {{-- Main Chart --}}
    <div class="card-premium border-blue p-6 lg:col-span-2 anim-fade-up delay-2">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <h3 class="section-title" style="margin:0;">
                <div class="section-icon bg-blue-10">
                    <i data-lucide="bar-chart-3" class="text-ptpn-blue" style="width:15px;height:15px;"></i>
                </div>
                Tren Inbound vs Outbound
            </h3>
            {{-- Range Toggles --}}
            <div id="chart-tabs" style="display:flex;gap:4px;background:rgba(241,245,249,0.8);padding:4px;border-radius:10px;">
                <button onclick="switchRange('7d')" id="tab-7d"
                    class="chart-tab active-tab"
                    style="padding:5px 13px;border-radius:7px;border:none;font-size:11px;font-weight:600;cursor:pointer;transition:all 0.2s;">
                    7 Hari
                </button>
                <button onclick="switchRange('30d')" id="tab-30d"
                    class="chart-tab"
                    style="padding:5px 13px;border-radius:7px;border:none;font-size:11px;font-weight:600;cursor:pointer;transition:all 0.2s;background:transparent;color:#94a3b8;">
                    30 Hari
                </button>
            </div>
        </div>
        <div style="height:290px;width:100%;position:relative;">
            <canvas id="mainChart"></canvas>
        </div>
    </div>

    {{-- Capacity Panel --}}
    <div class="card-premium p-6 anim-fade-up delay-3" style="display:flex;flex-direction:column;">
        <h3 class="section-title">
            <div class="section-icon bg-green-10">
                <i data-lucide="warehouse" class="text-ptpn-green" style="width:15px;height:15px;"></i>
            </div>
            Kapasitas Gudang
        </h3>

        {{-- SIR --}}
        @php
            $sirPct = ($capacityData['SIR']['max'] > 0) ? ($capacityData['SIR']['current'] / $capacityData['SIR']['max']) * 100 : 0;
            $rssPct = ($capacityData['RSS']['max'] > 0) ? ($capacityData['RSS']['current'] / $capacityData['RSS']['max']) * 100 : 0;
        @endphp
        <div style="margin-bottom:28px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div>
                    <span style="font-size:13px;font-weight:700;color:var(--text-primary);">Gudang SIR</span>
                    <span class="badge badge-green" style="margin-left:8px;font-size:10px;">{{ round($sirPct) }}%</span>
                </div>
                <span class="text-ptpn-green" style="font-size:13px;font-weight:700;">
                    {{ $capacityData['SIR']['current'] }} <span style="color:var(--text-muted);font-weight:400;">/ {{ $capacityData['SIR']['max'] }}</span>
                </span>
            </div>
            <div class="progress-track" style="height:10px;border-radius:999px;">
                <div class="progress-fill bg-green-gradient cap-sir-bar shadow-green"
                     style="height:10px;" data-target="{{ $sirPct }}"></div>
            </div>
            <p style="font-size:10px;color:var(--text-muted);margin:5px 0 0 0;text-align:right;">Max: {{ $capacityData['SIR']['max'] }} Unit</p>
        </div>

        {{-- RSS --}}
        <div style="margin-bottom:28px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div>
                    <span style="font-size:13px;font-weight:700;color:var(--text-primary);">Gudang RSS</span>
                    <span class="badge badge-blue" style="margin-left:8px;font-size:10px;">{{ round($rssPct) }}%</span>
                </div>
                <span class="text-ptpn-blue" style="font-size:13px;font-weight:700;">
                    {{ $capacityData['RSS']['current'] }} <span style="color:var(--text-muted);font-weight:400;">/ {{ $capacityData['RSS']['max'] }}</span>
                </span>
            </div>
            <div class="progress-track" style="height:10px;border-radius:999px;">
                <div class="progress-fill bg-blue-gradient cap-rss-bar shadow-blue"
                     style="height:10px;" data-target="{{ $rssPct }}"></div>
            </div>
            <p style="font-size:10px;color:var(--text-muted);margin:5px 0 0 0;text-align:right;">Max: {{ $capacityData['RSS']['max'] }} Unit</p>
        </div>

        {{-- Quality Mix mini donut --}}
        <div style="flex:1;display:flex;flex-direction:column;justify-content:flex-end;">
            <h4 style="font-size:12px;font-weight:700;color:var(--text-secondary);margin:0 0 12px 0;text-transform:uppercase;letter-spacing:0.08em;">Komposisi Kualitas</h4>
            <div style="display:flex;align-items:center;gap:16px;">
                <div style="position:relative;width:80px;height:80px;flex-shrink:0;">
                    <canvas id="miniDonut" width="80" height="80"></canvas>
                </div>
                <div style="display:flex;flex-direction:column;gap:6px;">
                    @php
                        $totalStock = $stocks->count() ?: 1;
                        $sirCount  = $stocks->where('quality_type','SIR 20 SW')->count();
                        $rssCount  = $stocks->where('quality_type','RSS 1')->count();
                        $otherCount = $totalStock - $sirCount - $rssCount;
                    @endphp
                    <div style="display:flex;align-items:center;gap:8px;font-size:11px;">
                        <span style="width:8px;height:8px;border-radius:2px;background:var(--green);flex-shrink:0;"></span>
                        <span style="color:var(--text-secondary);">SIR 20 SW</span>
                        <span style="font-weight:700;color:var(--text-primary);margin-left:auto;">{{ $sirCount }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:11px;">
                        <span style="width:8px;height:8px;border-radius:2px;background:var(--blue);flex-shrink:0;"></span>
                        <span style="color:var(--text-secondary);">RSS 1</span>
                        <span style="font-weight:700;color:var(--text-primary);margin-left:auto;">{{ $rssCount }}</span>
                    </div>
                    @if($otherCount > 0)
                    <div style="display:flex;align-items:center;gap:8px;font-size:11px;">
                        <span style="width:8px;height:8px;border-radius:2px;background:var(--orange);flex-shrink:0;"></span>
                        <span style="color:var(--text-secondary);">Lainnya</span>
                        <span style="font-weight:700;color:var(--text-primary);margin-left:auto;">{{ $otherCount }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════ WAREHOUSE HEATMAP ═══════════════ --}}
<div class="card-premium p-6 mb-7 anim-fade-up delay-3">
    <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:20px;">
        <h3 class="section-title" style="margin:0;">
            <div class="section-icon bg-blue-10">
                <i data-lucide="grid-3x3" class="text-ptpn-blue" style="width:15px;height:15px;"></i>
            </div>
            Visualisasi Area Gudang
            <span class="badge badge-gray" style="margin-left:8px;">{{ $groupedSirStocks->count() }} Lot SIR</span>
        </h3>
        <div style="display:flex;gap:14px;flex-wrap:wrap;">
            @foreach([
                ['color' => 'var(--blue)',   'label' => 'Tersedia',   'class' => 'badge-blue'],
                ['color' => 'var(--orange)', 'label' => 'Booked',     'class' => 'badge-orange'],
                ['color' => '#fdba74',       'label' => 'Hold',       'class' => 'badge-gray'],
                ['color' => '#e2e8f0',       'label' => 'Kosong',     'class' => 'badge-gray'],
            ] as $legend)
            <span style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:600;color:var(--text-secondary);">
                <span style="width:10px;height:10px;border-radius:3px;background:{{ $legend['color'] }};flex-shrink:0;"></span>
                {{ $legend['label'] }}
            </span>
            @endforeach
        </div>
    </div>

    @php
        $blueLots = [];
        $yellowLots = [];
        $orangeLots = [];
        $emptyLots = [];
        
        foreach($groupedSirStocks as $prefix => $lotGroup) {
            $lastStock = $lotGroup->last();
            if ($lastStock->status === 'blue') {
                $blueLots[$prefix] = $lotGroup;
            } elseif ($lastStock->status === 'yellow') {
                $yellowLots[$prefix] = $lotGroup;
            } elseif ($lastStock->status === 'orange') {
                $orangeLots[$prefix] = $lotGroup;
            } else {
                $emptyLots[$prefix] = $lotGroup;
            }
        }
        
        $groups = [
            ['title' => 'Tersedia (Biru)', 'data' => $blueLots],
            ['title' => 'Booked (Kuning)', 'data' => $yellowLots],
            ['title' => 'Hold (Orange)', 'data' => $orangeLots],
            ['title' => 'Kosong', 'data' => $emptyLots],
        ];
    @endphp

    <div class="space-y-6">
        @foreach($groups as $group)
            @if(count($group['data']) > 0 || $group['title'] === 'Kosong')
            <div class="lot-group-section">
                <!-- <h4 style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:12px;">{{ $group['title'] }}</h4> -->
                <div id="heatmap-grid-{{ Str::slug($group['title']) }}" class="p-4 rounded-xl"
                     style="background:rgba(241,245,249,0.4);border:1px solid rgba(226,232,240,0.6);min-height:100px;display:flex;flex-wrap:wrap;gap:16px;">
                    
                    @foreach($group['data'] as $prefix => $lotGroup)
                        @php
                            $lastStock = $lotGroup->last();
                            $bgColor = match($lastStock->status) {
                                'blue'   => 'var(--blue)',
                                'yellow' => 'var(--orange)',
                                'orange' => '#fdba74',
                                default  => '#dde3ea'
                            };
                            $statusLabel = match($lastStock->status) {
                                'blue'   => 'Tersedia',
                                'yellow' => 'Booked',
                                'orange' => 'Hold',
                                default  => 'Kosong'
                            };
                            $palletsCount = 8;
                        @endphp
                        <div class="warehouse-lot"
                             style="background:rgba(255,255,255,0.7);border:1px solid rgba(203,213,225,0.6);border-radius:10px;padding:8px;width:calc(20% - 13px);min-width:140px;
                                    display:flex;flex-direction:column;gap:6px;opacity:0;animation:heatFadeIn 0.4s ease-out {{ $loop->index * 0.05 }}s forwards;">
                            
                            <div style="display:flex;justify-content:space-between;align-items:center;">
                                <span style="font-size:11px;font-weight:800;color:var(--text-secondary);font-family:monospace;">{{ $prefix }}</span>
                                <span style="width:8px;height:8px;border-radius:50%;background:{{ $bgColor }};" title="{{ $statusLabel }}"></span>
                            </div>
                            
                            <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:4px;">
                                @php
                                    $lot = $lotGroup->first();
                                    $details = $lot ? $lot->details : collect();
                                @endphp
                                @for($p = 0; $p < $palletsCount; $p++)
                                    @php
                                        $actualDetail = $details->get($p);
                                        $palletColor = $actualDetail ? $bgColor : '#e2e8f0'; 
                                        
                                        $palletNumberText = '';
                                        if ($actualDetail) {
                                            $fdf = $actualDetail->fdf_number ?? '';
                                            $palletNumberText = trim(str_replace('FDF', '', strtoupper($fdf)));
                                        }
                                        
                                        $palletLabel = $actualDetail ? "Lot: {$lot->lot_number} | $statusLabel" : "Slot Kosong";
                                    @endphp
                                    <div style="background:{{ $palletColor }};height:28px;border-radius:4px;box-shadow:inset 0 0 2px rgba(0,0,0,0.1);cursor:pointer;transition:transform 0.2s;display:flex;align-items:center;justify-content:center;color:white;font-size:9px;font-weight:700;overflow:hidden;"
                                         title="{{ $palletLabel }}"
                                         onmouseover="this.style.transform='scale(1.15)'"
                                         onmouseout="this.style.transform='scale(1)'">
                                         {{ $palletNumberText }}
                                    </div>
                                @endfor
                            </div>
                        </div>
                    @endforeach

                    @if($group['title'] === 'Kosong')
                        @for($i = 0; $i < max(0, 12 - $groupedSirStocks->count()); $i++)
                            <div style="background:rgba(226,232,240,0.2);border:1px dashed rgba(203,213,225,0.6);border-radius:10px;padding:8px;width:calc(20% - 13px);min-width:140px;display:flex;flex-direction:column;gap:6px;">
                                <div style="display:flex;justify-content:space-between;align-items:center;">
                                    <span style="font-size:10px;font-weight:600;color:var(--text-muted);">Sisa Slot</span>
                                    <span style="width:8px;height:8px;border-radius:50%;background:#e2e8f0;"></span>
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(4, 1fr);gap:4px;opacity:0.4;">
                                    @for($p = 0; $p < 8; $p++)
                                        <div style="background:#e2e8f0;height:24px;border-radius:4px;"></div>
                                    @endfor
                                </div>
                            </div>
                        @endfor
                    @endif
                </div>
            </div>
            @endif
        @endforeach
    </div>
</div>

{{-- ═══════════════ CONTRACT TABLE ═══════════════ --}}
<div class="card-premium p-6 anim-fade-up delay-4">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h3 class="section-title" style="margin:0;">
            <div class="section-icon bg-orange-10">
                <i data-lucide="clipboard-list" class="text-ptpn-orange" style="width:15px;height:15px;"></i>
            </div>
            Monitoring Kontrak Pembeli
        </h3>
        <a href="{{ route('reports.index') }}" class="btn btn-ghost" style="font-size:12px;padding:7px 14px;">
            <i data-lucide="external-link" style="width:13px;height:13px;"></i>
            Lihat Semua
        </a>
    </div>
    <div style="overflow-x:auto;border-radius:12px;border:1px solid rgba(226,232,240,0.5);">
        <table class="table-modern">
            <thead>
                <tr>
                    <th style="border-radius:12px 0 0 0;">Pembeli</th>
                    <th>No. Kontrak</th>
                    <th>Total (Ton)</th>
                    <th>Terkirim (Ton)</th>
                    <th>Sisa (Ton)</th>
                    <th style="border-radius:0 12px 0 0;">Progress</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contracts as $contract)
                @php
                    $pct = $contract->progress_percent;
                    $statusClass = $pct >= 80 ? 'badge-green' : ($pct >= 50 ? 'badge-blue' : 'badge-orange');
                    $statusLabel = $pct >= 80 ? 'On Track' : ($pct >= 50 ? 'Progress' : 'Awal');
                @endphp
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,var(--green),var(--blue));display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700;flex-shrink:0;">
                                {{ strtoupper(substr($contract->buyer_name, 0, 2)) }}
                            </div>
                            <span style="font-weight:700;color:var(--text-primary);">{{ $contract->buyer_name }}</span>
                        </div>
                    </td>
                    <td>
                        <span style="font-family:monospace;font-size:11px;background:rgba(241,245,249,0.8);padding:3px 8px;border-radius:6px;color:var(--text-secondary);border:1px solid rgba(226,232,240,0.6);">
                            {{ $contract->contract_number }}
                        </span>
                    </td>
                    <td style="font-weight:600;color:var(--text-primary);">{{ number_format($contract->total_tonnage) }}</td>
                    <td class="text-ptpn-green" style="font-weight:700;">{{ number_format($contract->shipped_tonnage, 1) }}</td>
                    <td style="font-weight:700;{{ $contract->remaining_tonnage < 100 ? 'color:#ef4444;' : 'color:var(--text-primary);' }}">
                        {{ number_format($contract->remaining_tonnage, 1) }}
                    </td>
                    <td style="width:200px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div class="progress-track" style="flex:1;height:7px;">
                                <div class="{{ $pct >= 80 ? 'bg-green-gradient' : ($pct >= 50 ? 'bg-blue-gradient' : 'bg-orange-gradient') }}"
                                     style="height:7px;border-radius:999px;width:{{ $pct }}%;transition:width 1s ease;"></div>
                            </div>
                            <span class="badge {{ $statusClass }}" style="font-size:10px;white-space:nowrap;">
                                {{ round($pct) }}%
                            </span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- ═══════════════ SCRIPTS ═══════════════ --}}
<style>
    @keyframes heatFadeIn {
        from { opacity: 0; transform: scale(0.8); }
        to   { opacity: 1; transform: scale(1); }
    }
    .chart-tab { background: transparent; color: #94a3b8; }
    .chart-tab.active-tab { background: white; color: var(--text-primary); box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Counter animation ──────────────────────────
    const counters = document.querySelectorAll('.counter-num');
    counters.forEach(el => {
        const target = parseFloat(el.dataset.target) || 0;
        const suffix = el.querySelector('span') ? el.querySelector('span').outerHTML : '';
        const textNodes = [...el.childNodes].filter(n => n.nodeType === 3);
        let start = 0;
        const duration = 1200;
        const step = timestamp => {
            if (!start) start = timestamp;
            const progress = Math.min((timestamp - start) / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);
            // Set text without overwriting the suffix span
            if (textNodes.length > 0) {
                textNodes[0].textContent = current.toLocaleString('id-ID') + ' ';
            } else {
                el.childNodes[0] && (el.childNodes[0].textContent = current.toLocaleString('id-ID') + ' ');
            }
            if (progress < 1) requestAnimationFrame(step);
        };
        setTimeout(() => requestAnimationFrame(step), 300);
    });

    // ── Progress bars ───────────────────────────────
    setTimeout(() => {
        document.querySelectorAll('.progress-fill').forEach(el => {
            el.style.width = el.dataset.target + '%';
        });
    }, 500);

    // ── Chart.js ────────────────────────────────────
    const chartData7d = {
        labels: {!! json_encode($chartData['labels']) !!},
        inbound: {!! json_encode($chartData['inbound']) !!},
        outbound: {!! json_encode($chartData['outbound']) !!},
    };

    const ctx = document.getElementById('mainChart').getContext('2d');
    let gradIn  = ctx.createLinearGradient(0, 0, 0, 320);
    gradIn.addColorStop(0, 'rgba(52,168,83,0.25)');
    gradIn.addColorStop(1, 'rgba(52,168,83,0.0)');
    let gradOut = ctx.createLinearGradient(0, 0, 0, 320);
    gradOut.addColorStop(0, 'rgba(245,166,35,0.25)');
    gradOut.addColorStop(1, 'rgba(245,166,35,0.0)');

    const mainChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: chartData7d.labels,
            datasets: [
                {
                    label: 'Inbound (Masuk)',
                    data: chartData7d.inbound,
                    borderColor: '#34A853',
                    backgroundColor: gradIn,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#34A853',
                    pointBorderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45
                },
                {
                    label: 'Outbound (Keluar)',
                    data: chartData7d.outbound,
                    borderColor: '#F5A623',
                    backgroundColor: gradOut,
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#F5A623',
                    pointBorderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    fill: true,
                    tension: 0.45
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top', align: 'end',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        boxWidth: 7,
                        padding: 20,
                        font: { size: 12, family: 'Inter', weight: '600' }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: '#0D1B2A',
                    titleFont: { size: 13, family: 'Inter', weight: '700' },
                    bodyFont: { size: 12, family: 'Inter' },
                    padding: 14,
                    cornerRadius: 12,
                    borderColor: 'rgba(255,255,255,0.08)',
                    borderWidth: 1,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(226,232,240,0.5)', borderDash: [4,4] },
                    border: { display: false },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', padding: 8 }
                },
                x: {
                    grid: { display: false },
                    border: { display: false },
                    ticks: { font: { size: 11, family: 'Inter' }, color: '#94a3b8', padding: 8 }
                }
            },
            interaction: { mode: 'nearest', axis: 'x', intersect: false },
            animation: { duration: 800, easing: 'easeInOutQuart' }
        }
    });

    // ── Mini Donut ──────────────────────────────────
    const donutCtx = document.getElementById('miniDonut').getContext('2d');
    new Chart(donutCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [{{ $sirCount }}, {{ $rssCount }}, {{ max($otherCount, 0) }}],
                backgroundColor: ['#34A853', '#4AADE4', '#F5A623'],
                borderWidth: 0,
                hoverOffset: 4
            }]
        },
        options: {
            responsive: false,
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: { enabled: false } },
            animation: { animateRotate: true, duration: 1000 }
        }
    });

    // ── Chart tab switch ────────────────────────────
    window.switchRange = function(range) {
        document.querySelectorAll('.chart-tab').forEach(b => b.classList.remove('active-tab'));
        document.getElementById('tab-' + range).classList.add('active-tab');
        // For now just re-animate; real data would come from AJAX
        mainChart.data.labels    = chartData7d.labels;
        mainChart.data.datasets[0].data = chartData7d.inbound;
        mainChart.data.datasets[1].data = chartData7d.outbound;
        mainChart.update('active');
    };

    // ── Heatmap hover ───────────────────────────────
    window.heatHover = function(el, isIn) {
        if (isIn) {
            el.style.transform = 'scale(1.12) translateY(-2px)';
            el.style.boxShadow = '0 6px 16px rgba(0,0,0,0.18)';
            el.style.zIndex = '10';
        } else {
            el.style.transform = 'scale(1)';
            el.style.boxShadow = 'none';
            el.style.zIndex = 'auto';
        }
    };

    if (window.lucide) lucide.createIcons();
});
</script>
@endsection