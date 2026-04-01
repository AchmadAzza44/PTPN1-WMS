@extends('layouts.modern')

@section('title', 'Stock Opname')
@section('header', 'Stock Opname (Audit)')
@section('subheader', 'Penyesuaian stok fisik dengan sistem')

@section('actions')
    <button onclick="openModal()" class="btn btn-green">
        <i data-lucide="plus" style="width:15px;height:15px;"></i>
        Buat Penyesuaian
    </button>
@endsection

@push('styles')
    <style>
        .opname-desktop {
            display: block;
        }

        .opname-mobile {
            display: none;
        }

        @media (max-width: 780px) {
            .opname-desktop {
                display: none !important;
            }

            .opname-mobile {
                display: block;
            }
        }

        /* ── Opname Card ── */
        .opname-card {
            background: #fff;
            border-radius: 18px;
            border: 1px solid rgba(203, 213, 225, 0.5);
            margin: 0 16px 14px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            transition: transform .15s, box-shadow .15s;
        }

        .opname-card:active {
            transform: scale(0.985);
        }

        .opname-card-head {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(203, 213, 225, 0.35);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
        }

        .opname-lot {
            font-family: var(--font-mono, monospace);
            font-size: 16px;
            font-weight: 900;
            color: #111827;
            letter-spacing: -0.01em;
        }

        .opname-lot small {
            display: block;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #94a3b8;
            margin-bottom: 1px;
            font-family: 'Inter', sans-serif;
        }

        .opname-row {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            padding: 10px 16px;
            border-bottom: 1px solid rgba(203, 213, 225, 0.3);
            gap: 10px;
            min-height: 42px;
        }

        .opname-row:last-child {
            border-bottom: none;
        }

        .opname-key {
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            flex-shrink: 0;
            padding-top: 2px;
            max-width: 40%;
        }

        .opname-val {
            font-size: 13px;
            font-weight: 600;
            color: #111827;
            text-align: right;
            flex: 1;
            line-height: 1.4;
        }

        .opname-val .muted {
            font-size: 11px;
            font-weight: 400;
            color: #94a3b8;
            display: block;
            margin-top: 1px;
        }

        /* Colored weight badge */
        .opname-weight-pos {
            color: #16803c;
            font-weight: 800;
            font-family: var(--font-mono, monospace);
            font-size: 15px;
        }

        .opname-weight-neg {
            color: #dc2626;
            font-weight: 800;
            font-family: var(--font-mono, monospace);
            font-size: 15px;
        }

        @media (max-width: 780px) {
            .mobile-empty-state {
                margin: 16px;
                padding: 32px 20px;
                background: rgba(248, 250, 252, 0.7);
                border: 1.5px dashed rgba(203, 213, 225, 0.7);
                border-radius: 16px;
                text-align: center;
            }
        }
    </style>
@endpush

@section('content')

    <div class="card-premium anim-fade-up" style="overflow:hidden;">

        {{-- Card Header --}}
        <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;">
            <div
                style="width:40px;height:40px;border-radius:12px;background:rgba(52,168,83,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="history" style="width:18px;height:18px;color:var(--green);"></i>
            </div>
            <div>
                <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);margin:0;">Riwayat Penyesuaian</h3>
                <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0;">Daftar stock opname / audit yang pernah
                    dilakukan</p>
            </div>
        </div>

        {{-- Desktop Table --}}
        <div class="opname-desktop">
            <table class="table-modern">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Lot Number</th>
                        <th>Jenis Penyesuaian</th>
                        <th>Berat (Kg)</th>
                        <th>Keterangan</th>
                        <th>Petugas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($adjustments as $adj)
                        @php
                            $adjType = strtolower($adj->type);
                            $badgeCls = match ($adjType) { 'loss' => 'badge-red', 'correction' => 'badge-orange', default => 'badge-green'};
                            $wtColor = $adj->weight_adjusted_kg < 0 ? 'color:var(--red)' : 'color:var(--green)';
                        @endphp
                        <tr>
                            <td style="white-space:nowrap;font-size:12px;">{{ $adj->created_at->format('d M Y H:i') }}</td>
                            <td style="font-weight:700;font-family:var(--font-mono);">{{ $adj->stockLot->lot_number ?? '-' }}
                            </td>
                            <td><span class="badge {{ $badgeCls }}">{{ ucfirst($adj->type) }}</span></td>
                            <td class="mono" style="font-weight:700;{{ $wtColor }}">
                                {{ $adj->weight_adjusted_kg > 0 ? '+' : '' }}{{ number_format($adj->weight_adjusted_kg, 2) }}
                            </td>
                            <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--text-secondary);"
                                title="{{ $adj->reason }}">
                                {{ $adj->reason }}
                            </td>
                            <td>
                                <div style="display:flex;align-items:center;gap:8px;">
                                    <div
                                        style="width:26px;height:26px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--blue));display:flex;align-items:center;justify-content:center;color:white;font-size:10px;font-weight:700;">
                                        {{ substr($adj->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span style="font-size:12px;font-weight:600;">{{ $adj->user->name ?? 'System' }}</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <div class="empty-state-icon">
                                        <i data-lucide="clipboard-x"
                                            style="width:30px;height:30px;color:var(--text-muted);"></i>
                                    </div>
                                    <h3 style="font-size:15px;font-weight:700;color:var(--text-primary);margin:0;">Belum Ada
                                        Riwayat</h3>
                                    <p style="font-size:13px;color:var(--text-muted);margin:0;">Belum ada riwayat stock opname
                                        yang dilakukan.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Cards --}}
        <div class="opname-mobile" style="padding-top:10px;padding-bottom:4px;">
            @forelse($adjustments as $adj)
                @php
                    $adjType = strtolower($adj->type);
                    $badgeCls = match ($adjType) { 'loss' => 'badge-red', 'correction' => 'badge-orange', default => 'badge-green'};
                    $isNeg = $adj->weight_adjusted_kg < 0;
                    $headBg = match ($adjType) {
                        'loss' => 'rgba(239,68,68,0.06)',
                        'correction' => 'rgba(244,161,27,0.06)',
                        default => 'rgba(52,168,83,0.06)',
                    };
                    $headBd = match ($adjType) {
                        'loss' => 'rgba(239,68,68,0.15)',
                        'correction' => 'rgba(244,161,27,0.15)',
                        default => 'rgba(52,168,83,0.15)',
                    };
                @endphp
                <div class="opname-card">
                    <div class="opname-card-head" style="background:{{ $headBg }};border-bottom-color:{{ $headBd }};">
                        <div class="opname-lot">
                            <small>Lot Number</small>
                            {{ $adj->stockLot->lot_number ?? '-' }}
                        </div>
                        <span class="badge {{ $badgeCls }}"
                            style="flex-shrink:0;font-size:11px;">{{ ucfirst($adj->type) }}</span>
                    </div>
                    <div>
                        <div class="opname-row">
                            <span class="opname-key">Penyesuaian</span>
                            <span class="opname-val">
                                <span class="{{ $isNeg ? 'opname-weight-neg' : 'opname-weight-pos' }}">
                                    {{ $adj->weight_adjusted_kg > 0 ? '+' : '' }}{{ number_format($adj->weight_adjusted_kg, 2) }}
                                </span>
                                <span style="font-size:11px;color:#94a3b8;font-weight:400;"> kg</span>
                            </span>
                        </div>
                        <div class="opname-row">
                            <span class="opname-key">Keterangan</span>
                            <span class="opname-val" style="color:#374151;">{{ $adj->reason }}</span>
                        </div>
                        <div class="opname-row">
                            <span class="opname-key">Petugas</span>
                            <span class="opname-val" style="display:flex;align-items:center;justify-content:flex-end;gap:7px;">
                                <div
                                    style="width:24px;height:24px;border-radius:50%;background:linear-gradient(135deg,#34a853,#4AADE4);display:flex;align-items:center;justify-content:center;color:white;font-size:9px;font-weight:700;flex-shrink:0;">
                                    {{ substr($adj->user->name ?? 'S', 0, 1) }}
                                </div>
                                {{ $adj->user->name ?? 'System' }}
                            </span>
                        </div>
                        <div class="opname-row" style="background:rgba(248,250,252,0.5);">
                            <span class="opname-key">Tanggal</span>
                            <span class="opname-val" style="color:#374151;">{{ $adj->created_at->format('d M Y, H:i') }}</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="mobile-empty-state">
                    <i data-lucide="clipboard-x" style="width:40px;height:40px;color:#cbd5e1;margin-bottom:12px;"></i>
                    <h3 style="font-size:15px;font-weight:700;color:#374151;margin:0 0 6px;">Belum Ada Riwayat</h3>
                    <p style="font-size:13px;color:#94a3b8;margin:0;">Belum ada riwayat stock opname yang dilakukan.</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        function openModal() {
            if (document.getElementById('adjustModal')) {
                document.getElementById('adjustModal').classList.remove('hidden');
            } else {
                window.toast('Fitur Buat Penyesuaian saat ini tidak tersedia.', 'info');
            }
        }
        document.addEventListener('DOMContentLoaded', () => lucide.createIcons());
    </script>
@endsection