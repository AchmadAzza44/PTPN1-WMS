@extends('layouts.modern')

@section('title', 'Verifikasi Data OCR')
@section('header', 'Verifikasi & Koreksi Data')
@section('subheader', 'Periksa hasil ekstraksi OCR, koreksi jika ada yang salah, lalu simpan')

@section('actions')
    <div style="display:flex;gap:8px;align-items:center;">
        @if(isset($mode) && $mode === 'ocr')
            <span
                style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;background:rgba(52,168,83,0.1);border:1px solid rgba(52,168,83,0.2);font-size:11px;font-weight:700;color:#34A853;">
                <i data-lucide="cpu" style="width:11px;height:11px;"></i> Hasil OCR
            </span>
        @else
            <span
                style="display:inline-flex;align-items:center;gap:6px;padding:5px 12px;border-radius:20px;background:rgba(74,173,228,0.1);border:1px solid rgba(74,173,228,0.2);font-size:11px;font-weight:700;color:#4AADE4;">
                <i data-lucide="pen-line" style="width:11px;height:11px;"></i> Input Manual
            </span>
        @endif
        <a href="{{ route('ocr.index', ['type' => $type]) }}" class="btn btn-ghost"
            style="font-size:12px;padding:8px 16px;">
            <i data-lucide="arrow-left" style="width:14px;height:14px;"></i> Scan Lagi
        </a>
    </div>
@endsection

@section('content')
    @php
        $isOutbound = $type === 'outbound';
        $color = $isOutbound ? 'orange' : 'green';
        $hex = $isOutbound ? '#F5A623' : '#34A853';
        $hexAlpha = $isOutbound ? 'rgba(245,166,35,' : 'rgba(52,168,83,';

        $jenisLabel = ['sir20' => 'Surat Pengantar SIR 20', 'rss1' => 'Bukti Pengantar RSS 1', 'do' => 'Delivery Order', 'surat_kuasa' => 'Surat Kuasa'];

        // Definisi field per jenis
        $fieldDefs = [
            'sir20' => [
                ['key' => 'no_surat', 'label' => 'No. Surat', 'type' => 'text'],
                ['key' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
                ['key' => 'no_kendaraan', 'label' => 'No. Kendaraan', 'type' => 'text'],
                ['key' => 'nama_supir', 'label' => 'Nama Supir', 'type' => 'text'],
                ['key' => 'total_kg', 'label' => 'Total Berat (kg)', 'type' => 'number'],
                ['key' => 'total_bale', 'label' => 'Total Bale', 'type' => 'number'],
            ],
            'rss1' => [
                ['key' => 'no_dokumen', 'label' => 'No. Dokumen', 'type' => 'text'],
                ['key' => 'tanggal', 'label' => 'Tanggal', 'type' => 'date'],
                ['key' => 'kebun', 'label' => 'Kebun/Asal', 'type' => 'text'],
                ['key' => 'mutu', 'label' => 'Mutu', 'type' => 'text'],
                ['key' => 'jumlah_bale', 'label' => 'Jumlah Bale', 'type' => 'number'],
                ['key' => 'berat_netto_total', 'label' => 'Berat Netto (kg)', 'type' => 'number'],
                ['key' => 'pengangkut', 'label' => 'Pengangkut', 'type' => 'text'],
                ['key' => 'no_kendaraan', 'label' => 'No. Kendaraan', 'type' => 'text'],
            ],
            'do' => [
                ['key' => 'no_so_internal',      'label' => 'No. SO Internal',      'type' => 'text'],
                ['key' => 'tanggal_so',           'label' => 'Tanggal SO',           'type' => 'date'],
                ['key' => 'no_kontrak_internal',  'label' => 'No. Kontrak Internal', 'type' => 'text'],
                ['key' => 'no_po',                'label' => 'No. PO / No. DO',      'type' => 'text'],
                ['key' => 'tanggal_po',           'label' => 'Tanggal PO',           'type' => 'date'],
                ['key' => 'nama_pembeli',         'label' => 'Nama Pembeli',         'type' => 'text'],
                ['key' => 'no_kontrak',           'label' => 'No. Kontrak',          'type' => 'text'],
                ['key' => 'tanggal_kontrak',      'label' => 'Tanggal Kontrak',      'type' => 'date'],
                ['key' => 'incoterms',            'label' => 'Incoterms',            'type' => 'text'],
                ['key' => 'volume',               'label' => 'Volume Pesanan (kg)',  'type' => 'number'],
                ['key' => 'jumlah_palet',         'label' => 'Jumlah Palet',         'type' => 'number'],
                ['key' => 'deskripsi',            'label' => 'Deskripsi Produk',     'type' => 'text'],
            ],
            'surat_kuasa' => [
                ['key' => 'no_surat_kuasa',     'label' => 'No. Surat / Referensi',  'type' => 'text'],
                ['key' => 'tanggal',            'label' => 'Tanggal',                'type' => 'date'],
                ['key' => 'perusahaan_pemberi', 'label' => 'Perusahaan Pemberi',     'type' => 'text'],
                ['key' => 'nama_pemberi',       'label' => 'Nama Pemberi Kuasa',     'type' => 'text'],
                ['key' => 'nama_penerima',      'label' => 'Nama Penerima Kuasa',    'type' => 'text'],
                ['key' => 'no_do',              'label' => 'No. DO / PO',            'type' => 'text'],
                ['key' => 'no_kontrak',         'label' => 'No. Kontrak',            'type' => 'text'],
                ['key' => 'bl_invoice',         'label' => 'BL / Invoice',           'type' => 'text'],
                ['key' => 'jenis_karet',        'label' => 'Jenis Karet',            'type' => 'text'],
                ['key' => 'jumlah_kg',          'label' => 'Jumlah (kg)',            'type' => 'number'],
                ['key' => 'jumlah_pallet',      'label' => 'Jumlah Palet',           'type' => 'number'],
                ['key' => 'packing',            'label' => 'Packing',                'type' => 'text'],
                ['key' => 'jasa_expedisi',      'label' => 'Jasa Expedisi / PIC',    'type' => 'text'],
                ['key' => 'trucking',           'label' => 'Trucking',               'type' => 'text'],
                ['key' => 'stuffing',           'label' => 'Stuffing',               'type' => 'text'],
                ['key' => 'tujuan',             'label' => 'Tujuan Pengiriman',      'type' => 'text'],
                ['key' => 'no_kendaraan',       'label' => 'No. Kendaraan',          'type' => 'text'],
            ],
        ];
        $fields = $fieldDefs[(string)$jenis] ?? [];

        // Baris tabel hanya untuk sir20 dan rss1
        $hasBaris   = in_array($jenis, ['sir20', 'rss1']) && !empty($hasil['baris']);
        $hasNomBale = $jenis === 'rss1' && !empty($hasil['nomor_bale']);
        $baris      = $hasil['baris'] ?? [];
        $nomorBale  = $hasil['nomor_bale'] ?? [];
        $nomorUrut  = $hasil['nomor_urut_bale'] ?? [];
        $ocr_job_id = $ocr_job_id ?? null;
    @endphp

    <style>
        /* ═══ DESKTOP LAYOUT ═══ */
        .review-main-grid {
            display: grid;
            grid-template-columns: {{ $imageUrl ? '2fr 3fr' : '1fr' }};
            gap: 24px;
            align-items: start;
        }
        .review-main-grid > * { min-width: 0; }
        .review-left-col { display: flex; flex-direction: column; gap: 16px; position: sticky; top: 24px; }
        .review-fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .review-actions { display: flex; gap: 10px; justify-content: flex-end; }

        /* ═══ MOBILE: Sticky Photo Header + Scrollable Form ═══ */
        @media (max-width: 768px) {
            .review-main-grid { display: flex !important; flex-direction: column !important; gap: 0 !important; }

            /* Sticky photo header */
            .review-left-col {
                position: sticky !important;
                top: 0;
                z-index: 50;
                background: var(--bg-primary, #fff);
                padding: 10px 0 6px 0;
                border-bottom: 1px solid var(--border, #e2e8f0);
                box-shadow: 0 2px 12px rgba(0,0,0,0.06);
                gap: 0 !important;
                transition: all 0.3s cubic-bezier(0.4,0,0.2,1);
            }
            .review-left-col.collapsed {
                padding: 0;
            }
            .review-left-col .card-premium { margin: 0 !important; border-radius: 0 !important; border-top: none !important; }
            .review-left-col .doc-info-bar { display: none; }
            .review-left-col .error-bar-desktop { display: none; }

            /* Photo container mobile */
            .mobile-photo-wrap {
                max-height: 35vh;
                overflow: hidden;
                transition: max-height 0.35s cubic-bezier(0.4,0,0.2,1);
                position: relative;
            }
            .mobile-photo-wrap.collapsed { max-height: 0; }
            .mobile-photo-wrap img { max-height: 35vh !important; object-fit: contain; }

            /* Mini preview bar when collapsed */
            .mobile-mini-bar {
                display: none;
                align-items: center;
                gap: 10px;
                padding: 8px 14px;
                background: rgba(248,250,252,0.95);
                backdrop-filter: blur(8px);
                border-bottom: 1px solid var(--border, #e2e8f0);
                cursor: pointer;
            }
            .mobile-mini-bar.visible { display: flex; }
            .mobile-mini-bar img { width: 48px; height: 48px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border, #e2e8f0); }

            /* Toggle collapse button */
            .mobile-collapse-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
                width: 100%;
                padding: 6px 0;
                border: none;
                background: rgba(248,250,252,0.8);
                color: var(--text-muted, #94a3b8);
                font-size: 11px;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.2s;
            }
            .mobile-collapse-btn:active { background: rgba(226,232,240,0.6); }
            .mobile-collapse-btn i { transition: transform 0.35s; }
            .mobile-collapse-btn.flipped i { transform: rotate(180deg); }

            /* Form scrolls below */
            .review-right-col { order: 2; padding-top: 12px; }
            .review-fields-grid { grid-template-columns: 1fr !important; }
            .review-fields-grid > div { grid-column: auto !important; }
            .review-actions { flex-direction: column-reverse; gap: 8px; }
            .review-actions a, .review-actions button {
                width: 100% !important;
                justify-content: center !important;
            }
        }

        /* Floating photo toggle (mobile only) */
        .fab-photo-toggle {
            display: none;
            position: fixed;
            bottom: 24px;
            right: 20px;
            width: 48px; height: 48px;
            border-radius: 50%;
            background: {{ $hex }};
            color: white;
            border: none;
            box-shadow: 0 4px 16px {{ $hexAlpha }}0.35);
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 99;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .fab-photo-toggle:active { transform: scale(0.92); }
        @media (max-width: 768px) {
            .fab-photo-toggle { display: flex; }
        }

        /* ═══ Zoom overlay ═══ */
        .zoom-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 200;
            background: rgba(15,23,42,0.85);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
        }
        .zoom-overlay.active { display: flex; }
        .zoom-overlay img {
            max-width: 95vw;
            max-height: 92vh;
            object-fit: contain;
            border-radius: 12px;
            box-shadow: 0 8px 40px rgba(0,0,0,0.4);
        }

        /* Desktop hides mobile-only elements */
        @media (min-width: 769px) {
            .mobile-collapse-btn { display: none !important; }
            .mobile-mini-bar { display: none !important; }
        }
    </style>
    {{-- ═══ ZOOM OVERLAY (fullscreen photo viewer) ═══ --}}
    @if($imageUrl)
    <div class="zoom-overlay" id="zoomOverlay" onclick="closeZoom()">
        <img src="{{ $imageUrl }}" alt="Dokumen Zoom">
    </div>
    @endif

    <div class="review-main-grid">

        {{-- ═══ KOLOM KIRI: Foto Dokumen ═══ --}}
        @if($imageUrl)
            <div class="review-left-col" id="photoCol">

                {{-- Error alert (desktop) --}}
                @if(!empty($error))
                    <div class="error-bar-desktop"
                        style="padding:12px 14px;border-radius:10px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);display:flex;align-items:flex-start;gap:8px;">
                        <i data-lucide="alert-triangle"
                            style="width:14px;height:14px;color:#ef4444;flex-shrink:0;margin-top:1px;"></i>
                        <p style="font-size:12px;color:#ef4444;margin:0;">{{ $error }}</p>
                    </div>
                @endif

                {{-- Mini preview bar (mobile collapsed state) --}}
                <div class="mobile-mini-bar" id="miniBar" onclick="togglePhoto()">
                    <img src="{{ $imageUrl }}" alt="Preview">
                    <div style="flex:1;">
                        <p style="font-size:11px;font-weight:700;color:var(--text-primary);margin:0;">{{ $jenisLabel[$jenis] ?? strtoupper($jenis) }}</p>
                        <p style="font-size:10px;color:var(--text-muted);margin:1px 0 0 0;">Tap untuk buka foto</p>
                    </div>
                    <i data-lucide="chevron-down" style="width:16px;height:16px;color:var(--text-muted);"></i>
                </div>

                <div class="card-premium p-4" style="border-top:3px solid #4AADE4;">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                        <i data-lucide="image" style="width:14px;height:14px;color:var(--text-muted);"></i>
                        <span
                            style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Foto
                            Dokumen</span>
                        @if($waktu_s)
                            <span style="margin-left:auto;font-size:10px;color:var(--text-muted);">⏱ {{ $waktu_s }}s</span>
                        @endif
                    </div>
                    <div class="mobile-photo-wrap" id="photoWrap" style="border-radius:10px;overflow:hidden;border:1px solid var(--border);background:#f8fafc;cursor:zoom-in;" onclick="openZoom()">
                        <img src="{{ $imageUrl }}" alt="Dokumen" id="docPhoto"
                            style="width:100%;height:auto;object-fit:contain;max-height:520px;display:block;">
                    </div>
                    <p style="font-size:10px;color:var(--text-muted);margin:6px 0 0 0;text-align:center;">Tap foto untuk zoom penuh</p>

                    {{-- Collapse/expand toggle (mobile only) --}}
                    <button type="button" class="mobile-collapse-btn" id="collapseBtn" onclick="togglePhoto()">
                        <i data-lucide="chevron-up" style="width:14px;height:14px;"></i>
                        <span id="collapseBtnText">Sembunyikan Foto</span>
                    </button>
                </div>

                {{-- Info jenis dokumen (desktop only) --}}
                <div class="doc-info-bar"
                    style="padding:12px 14px;border-radius:10px;background:{{ $hexAlpha }}0.06);border:1px solid {{ $hexAlpha }}0.2);">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <div
                            style="width:28px;height:28px;border-radius:7px;background:{{ $hexAlpha }}0.12);display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="file-text" style="width:13px;height:13px;color:{{ $hex }};"></i>
                        </div>
                        <div>
                            <p style="font-size:11px;font-weight:700;color:var(--text-primary);margin:0;">
                                {{ $jenisLabel[$jenis] ?? strtoupper($jenis) }}</p>
                            <p style="font-size:10px;color:var(--text-muted);margin:1px 0 0 0;">{{ ucfirst($type) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- ═══ KOLOM KANAN: Form Verifikasi ═══ --}}
        <div class="review-right-col">

            @if(!$imageUrl && !empty($error))
                <div
                    style="margin-bottom:16px;padding:12px 14px;border-radius:10px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);display:flex;align-items:flex-start;gap:8px;">
                    <i data-lucide="alert-triangle"
                        style="width:14px;height:14px;color:#ef4444;flex-shrink:0;margin-top:1px;"></i>
                    <p style="font-size:12px;color:#ef4444;margin:0;">{{ $error }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('ocr.simpan') }}" id="reviewForm">
                @csrf
                <input type="hidden" name="_method_review" value="1">
                <input type="hidden" name="jenis" value="{{ $jenis }}">
                <input type="hidden" name="type" value="{{ $type }}">
                @if($ocr_job_id)
                <input type="hidden" name="ocr_job_id" value="{{ $ocr_job_id }}">
                @endif

                {{-- ── Field Header ── --}}
                <div class="card-premium p-5 anim-fade-up" style="margin-bottom:16px;border-top:3px solid {{ $hex }};">
                    <div
                        style="display:flex;align-items:center;gap:10px;margin-bottom:18px;padding-bottom:14px;border-bottom:1px solid var(--border);">
                        <div
                            style="width:36px;height:36px;border-radius:10px;background:{{ $hexAlpha }}0.1);display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="edit-3" style="width:16px;height:16px;color:{{ $hex }};"></i>
                        </div>
                        <div>
                            <h3 style="font-size:14px;font-weight:800;color:var(--text-primary);margin:0;">
                                {{ $jenisLabel[$jenis] ?? strtoupper($jenis) }}</h3>
                            <p style="font-size:11px;color:var(--text-muted);margin:1px 0 0 0;">Periksa dan koreksi data
                                berikut sebelum disimpan</p>
                        </div>
                    </div>

                    <div class="review-fields-grid">
                        @foreach($fields as $f)
                            @php
                                $rawVal = $hasil[$f['key']] ?? '';
                                // Flatten jika OCR mengembalikan array (misal ["30 Jan 2026"])
                                $fieldVal = is_array($rawVal)
                                    ? implode(', ', array_map('strval', $rawVal))
                                    : (string)$rawVal;
                            @endphp
                            <div
                                style="{{ in_array($f['key'], ['no_surat', 'no_dokumen', 'no_so_internal', 'no_surat_kuasa', 'perusahaan_pemberi', 'alamat_pemberi', 'tujuan', 'jasa_expedisi', 'alamat_pembeli']) ? 'grid-column:1/-1;' : '' }}">
                                <label
                                    style="display:block;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:5px;">
                                    {{ $f['label'] }}
                                </label>
                                <input type="{{ $f['type'] }}" name="hasil[{{ $f['key'] }}]"
                                    value="{{ $fieldVal }}" placeholder="{{ $f['label'] }}"
                                    style="width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13px;color:var(--text-primary);background:white;transition:border-color 0.2s;box-sizing:border-box;"
                                    onfocus="this.style.borderColor='{{ $hex }}';this.style.boxShadow='0 0 0 3px {{ $hexAlpha }}0.1)'"
                                    onblur="this.style.borderColor='#e2e8f0';this.style.boxShadow='none'">
                            </div>
                        @endforeach

                    </div>
                </div>

                {{-- ── Tabel Baris (SIR 20) ── --}}
                @if($jenis === 'sir20')
                    <div class="card-premium p-5 anim-fade-up" style="margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                            <i data-lucide="table" style="width:13px;height:13px;color:var(--text-muted);"></i>
                            <span
                                style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Detail
                                Baris Lot</span>
                            <button type="button" onclick="tambahBaris()"
                                style="margin-left:auto;display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:7px;background:{{ $hexAlpha }}0.08);color:{{ $hex }};border:1px solid {{ $hexAlpha }}0.2);font-size:11px;font-weight:700;cursor:pointer;">
                                <i data-lucide="plus" style="width:11px;height:11px;"></i> Tambah baris
                            </button>
                        </div>
                        <div style="overflow-x:auto;border-radius:9px;border:1px solid var(--border);">
                        <table style="width:100%;min-width:520px;border-collapse:collapse;font-size:13px;" id="tabelBaris">
                                <thead>
                                    <tr style="background:rgba(248,250,252,0.9);">
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);min-width:90px;">Peti/Lot</th>
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);min-width:100px;">No. Lot</th>
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);min-width:70px;">Bale</th>
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);min-width:100px;">Berat (kg)</th>
                                        <th style="padding:8px 10px;text-align:center;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);width:52px;">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody id="barisTbody">
                                    @forelse($baris as $i => $b)
                                        <tr data-idx="{{ $i }}" style="border-bottom:1px solid var(--border);">
                                            <td style="padding:6px 8px;"><input type="text" name="hasil[baris][{{ $i }}][no_peti]"
                                                    value="{{ $b['no_peti'] ?? '' }}" class="td-input" placeholder="No. Peti"></td>
                                            <td style="padding:6px 8px;"><input type="text" name="hasil[baris][{{ $i }}][no_lot]"
                                                    value="{{ $b['no_lot'] ?? '' }}" class="td-input" placeholder="No. Lot"></td>
                                            <td style="padding:6px 8px;"><input type="number" name="hasil[baris][{{ $i }}][jml_bale]"
                                                    value="{{ $b['jml_bale'] ?? '' }}" class="td-input" placeholder="0"></td>
                                            <td style="padding:6px 8px;"><input type="number" name="hasil[baris][{{ $i }}][berat_kg]"
                                                    value="{{ $b['berat_kg'] ?? '' }}" class="td-input" step="0.01"
                                                    placeholder="0"></td>
                                            <td style="padding:6px 8px;text-align:center;"><button type="button"
                                                    onclick="hapusBaris(this)"
                                                    style="padding:4px 8px;border-radius:6px;background:rgba(239,68,68,0.08);color:#ef4444;border:none;cursor:pointer;font-size:11px;">✕</button>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- kosong, user akan tambah manual --}}
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ── Nomor Bale (RSS 1) ── --}}
                @if($jenis === 'rss1')
                    <div class="card-premium p-5 anim-fade-up" style="margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                            <i data-lucide="hash" style="width:13px;height:13px;color:var(--text-muted);"></i>
                            <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Nomor Bale (Range)</span>
                        </div>
                        <textarea name="hasil[nomor_bale]" rows="3"
                            placeholder="Masukkan nomor bale, pisah dengan koma (contoh: 7458-7470, 7471-7515)"
                            style="width:100%;padding:10px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:12px;resize:vertical;box-sizing:border-box;"
                            onfocus="this.style.borderColor='{{ $hex }}'"
                            onblur="this.style.borderColor='#e2e8f0'">{{ implode(', ', $nomorBale) }}</textarea>
                    </div>

                    {{-- ── Nomor Urut Bale Individual (RSS 1) ── --}}
                    @if(!empty($nomorUrut))
                    <div class="card-premium p-5 anim-fade-up" style="margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                            <i data-lucide="list-ordered" style="width:13px;height:13px;color:var(--text-muted);"></i>
                            <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Nomor Urut Bale Individual</span>
                            <span style="margin-left:auto;display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;background:{{ $hexAlpha }}0.1);color:{{ $hex }};font-size:10px;font-weight:700;">
                                {{ count($nomorUrut) }} bale
                            </span>
                        </div>
                        {{-- Grid chip nomor bale, dikelompok per range --}}
                        @php
                            // Kelompokkan nomor urut berdasarkan range yg ada di nomor_bale
                            $groups = [];
                            foreach ($nomorBale as $range) {
                                $parts = explode('-', $range);
                                if (count($parts) === 2) {
                                    $a = (int)trim($parts[0]); $b = (int)trim($parts[1]);
                                    $group = [];
                                    foreach ($nomorUrut as $n) {
                                        $num = (int)$n;
                                        if ($num >= $a && $num <= $b) $group[] = $n;
                                    }
                                    if ($group) $groups[$range] = $group;
                                }
                            }
                            // Nomor yang tidak masuk range manapun
                            $allGrouped = array_merge(...array_values($groups));
                            $ungrouped  = array_diff($nomorUrut, $allGrouped);
                            if ($ungrouped) $groups['Lainnya'] = array_values($ungrouped);
                        @endphp

                        @foreach($groups as $rangeLabel => $nums)
                        <div style="margin-bottom:14px;">
                            <p style="font-size:10px;font-weight:700;color:var(--text-muted);margin:0 0 6px 0;letter-spacing:0.06em;">
                                Range: {{ $rangeLabel }}
                            </p>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                                @foreach($nums as $num)
                                <span style="display:inline-flex;align-items:center;padding:3px 9px;border-radius:6px;font-size:11px;font-weight:600;background:{{ $hexAlpha }}0.08);color:{{ $hex }};border:1px solid {{ $hexAlpha }}0.2);font-variant-numeric:tabular-nums;">
                                    {{ $num }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endforeach

                        {{-- Hidden input kirim array nomor urut --}}
                        <input type="hidden" name="hasil[nomor_urut_bale]" value="{{ implode(',', $nomorUrut) }}">
                    </div>
                    @elseif($hasNomBale)
                    <div class="card-premium p-4" style="margin-bottom:16px;border:1px dashed var(--border);">
                        <p style="font-size:11px;color:var(--text-muted);margin:0;text-align:center;">
                            <i data-lucide="info" style="width:12px;height:12px;vertical-align:middle;"></i>
                            Nomor urut bale individual tidak terdeteksi oleh OCR
                        </p>
                    </div>
                    @endif
                @endif

                {{-- ── Items Shipment (Surat Kuasa multi-item) ── --}}
                @if($jenis === 'surat_kuasa' && !empty($hasil['items']))
                    <div class="card-premium p-5 anim-fade-up" style="margin-bottom:16px;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
                            <i data-lucide="package" style="width:13px;height:13px;color:var(--text-muted);"></i>
                            <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Detail Shipment Items</span>
                            <span style="margin-left:auto;display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:20px;background:{{ $hexAlpha }}0.1);color:{{ $hex }};font-size:10px;font-weight:700;">
                                {{ count($hasil['items']) }} item
                            </span>
                        </div>
                        <div style="overflow-x:auto;border-radius:9px;border:1px solid var(--border);">
                            <table style="width:100%;min-width:520px;border-collapse:collapse;font-size:13px;">
                                <thead>
                                    <tr style="background:rgba(248,250,252,0.9);">
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);">BL / No</th>
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);">Grade</th>
                                        <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);">Qty (kg)</th>
                                        <th style="padding:8px 10px;text-align:right;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);">Pallet</th>
                                        <th style="padding:8px 10px;text-align:left;font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;border-bottom:1px solid var(--border);">DO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hasil['items'] as $i => $item)
                                    <tr style="border-bottom:1px solid var(--border);">
                                        <td style="padding:6px 10px;font-size:12px;">{{ $item['bl_number'] ?? '-' }}</td>
                                        <td style="padding:6px 10px;font-size:12px;">{{ $item['grade'] ?? '-' }}</td>
                                        <td style="padding:6px 10px;font-size:12px;text-align:right;">{{ number_format($item['quantity_kg'] ?? 0) }}</td>
                                        <td style="padding:6px 10px;font-size:12px;text-align:right;">{{ $item['pallet_count'] ?? '-' }}</td>
                                        <td style="padding:6px 10px;font-size:12px;">{{ $item['do_number'] ?? '-' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                {{-- ── Tombol Aksi ── --}}
                <div class="review-actions">
                    <a href="{{ route('ocr.index', ['type' => $type]) }}"
                        style="display:inline-flex;align-items:center;gap:6px;padding:11px 20px;border-radius:10px;border:1.5px solid #e2e8f0;background:white;font-size:13px;font-weight:600;color:var(--text-secondary);text-decoration:none;">
                        <i data-lucide="x" style="width:15px;height:15px;"></i> Batal
                    </a>
                    <button type="submit"
                        style="display:inline-flex;align-items:center;gap:6px;padding:11px 24px;border-radius:10px;border:none;background:{{ $hex }};color:white;font-size:13px;font-weight:700;cursor:pointer;box-shadow:0 4px 12px {{ $hexAlpha }}0.3);">
                        @if($isOutbound)
                            <i data-lucide="truck" style="width:15px;height:15px;"></i> Proses Pesanan
                        @else
                            <i data-lucide="save" style="width:15px;height:15px;"></i> Simpan Data
                        @endif
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .td-input {
            width: 100%;
            padding: 5px 8px;
            border: 1.5px solid transparent;
            border-radius: 6px;
            font-size: 12px;
            background: rgba(248, 250, 252, 0.7);
            transition: all 0.2s;
            box-sizing: border-box;
        }

        .td-input:focus {
            outline: none;
            border-color: {{ $hex }};
            background: white;
        }
    </style>

    {{-- ═══ Floating Photo Toggle (mobile only) ═══ --}}
    @if($imageUrl)
    <button type="button" class="fab-photo-toggle" id="fabPhoto" onclick="togglePhoto()" title="Lihat foto dokumen">
        <i data-lucide="camera" style="width:22px;height:22px;"></i>
    </button>
    @endif

    <script>
        // ── Mobile photo toggle ──────────────────────────
        let photoCollapsed = false;
        function togglePhoto() {
            const wrap = document.getElementById('photoWrap');
            const miniBar = document.getElementById('miniBar');
            const btn = document.getElementById('collapseBtn');
            const btnText = document.getElementById('collapseBtnText');
            if (!wrap) return;

            photoCollapsed = !photoCollapsed;
            if (photoCollapsed) {
                wrap.classList.add('collapsed');
                miniBar && miniBar.classList.add('visible');
                btn && btn.classList.add('flipped');
                if (btnText) btnText.textContent = 'Tampilkan Foto';
            } else {
                wrap.classList.remove('collapsed');
                miniBar && miniBar.classList.remove('visible');
                btn && btn.classList.remove('flipped');
                if (btnText) btnText.textContent = 'Sembunyikan Foto';
                // Scroll ke atas agar foto terlihat
                document.getElementById('photoCol')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        // ── Fullscreen zoom overlay ──────────────────────
        function openZoom() {
            const overlay = document.getElementById('zoomOverlay');
            if (overlay) overlay.classList.add('active');
        }
        function closeZoom() {
            const overlay = document.getElementById('zoomOverlay');
            if (overlay) overlay.classList.remove('active');
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') closeZoom(); });

        // ── Table row management ─────────────────────────
        let barisIdx = {{ count($baris) }};

        function tambahBaris() {
            const i = barisIdx++;
            const tr = document.createElement('tr');
            tr.style.borderBottom = '1px solid var(--border)';
            tr.innerHTML = `
            <td style="padding:6px 8px;"><input type="text"   name="hasil[baris][${i}][no_peti]"   class="td-input" placeholder="No. Peti"></td>
            <td style="padding:6px 8px;"><input type="text"   name="hasil[baris][${i}][no_lot]"    class="td-input" placeholder="No. Lot"></td>
            <td style="padding:6px 8px;"><input type="number" name="hasil[baris][${i}][jml_bale]"  class="td-input" placeholder="0"></td>
            <td style="padding:6px 8px;"><input type="number" name="hasil[baris][${i}][berat_kg]"  class="td-input" step="0.01" placeholder="0"></td>
            <td style="padding:6px 8px;text-align:center;"><button type="button" onclick="hapusBaris(this)" style="padding:4px 8px;border-radius:6px;background:rgba(239,68,68,0.08);color:#ef4444;border:none;cursor:pointer;font-size:11px;">✕</button></td>
        `;
            document.getElementById('barisTbody').appendChild(tr);
        }

        function hapusBaris(btn) {
            btn.closest('tr').remove();
        }

        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const form = this;
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i data-lucide="loader" style="width:15px;height:15px;animation:spin 1s linear infinite;"></i> Menyimpan...';
            if (window.lucide) lucide.createIcons();

            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                if (status === 200 && body.success) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            html: `
                                <div style="display:flex;flex-direction:column;align-items:center;gap:16px;padding:10px 0;">
                                    <div style="width:60px;height:60px;border-radius:50%;background:{{ $hexAlpha }}0.1);display:flex;align-items:center;justify-content:center;">
                                        <i data-lucide="check-circle-2" style="width:32px;height:32px;color:{{ $hex }};"></i>
                                    </div>
                                    <div>
                                        <h3 style="margin:0 0 6px 0;font-size:18px;font-weight:800;color:var(--text-primary);">Berhasil!</h3>
                                        <p style="margin:0;font-size:13px;color:var(--text-muted);">${body.message || 'Data berhasil disimpan'}</p>
                                    </div>
                                </div>
                            `,
                            showConfirmButton: false,
                            timer: 2000,
                            background: '#ffffff',
                            backdrop: `rgba(15,23,42,0.4)`,
                            customClass: {
                                popup: 'card-premium' // Applying the theme's card CSS
                            },
                            didOpen: () => {
                                if (window.lucide) lucide.createIcons();
                            }
                        }).then(() => {
                            @if($isOutbound)
                                let params = new URLSearchParams();
                                @if($jenis === 'do')
                                    params.append('do_number_manual', document.querySelector('[name="hasil[no_po]"]')?.value || '');
                                    params.append('contract_number_ref', document.querySelector('[name="hasil[no_kontrak]"]')?.value || '');
                                    params.append('documented_qty_kg', document.querySelector('[name="hasil[volume]"]')?.value || '');
                                @elseif($jenis === 'surat_kuasa')
                                    params.append('do_number_manual', document.querySelector('[name="hasil[no_do]"]')?.value || '');
                                    params.append('contract_number_ref', document.querySelector('[name="hasil[no_kontrak]"]')?.value || '');
                                    params.append('documented_qty_kg', document.querySelector('[name="hasil[jumlah_kg]"]')?.value || '');
                                @endif
                                window.location.href = "{{ route('shipments.create') }}?" + params.toString();
                            @else
                                window.location.href = "{{ route('ocr.index', ['type' => $type]) }}";
                            @endif
                        });
                    } else {
                        alert(body.message || 'Data berhasil disimpan');
                        @if($isOutbound)
                            let params = new URLSearchParams();
                            @if($jenis === 'do')
                                params.append('do_number_manual', document.querySelector('[name="hasil[no_po]"]')?.value || '');
                                params.append('contract_number_ref', document.querySelector('[name="hasil[no_kontrak]"]')?.value || '');
                                params.append('documented_qty_kg', document.querySelector('[name="hasil[volume]"]')?.value || '');
                            @elseif($jenis === 'surat_kuasa')
                                params.append('do_number_manual', document.querySelector('[name="hasil[no_do]"]')?.value || '');
                                params.append('documented_qty_kg', document.querySelector('[name="hasil[jumlah_kg]"]')?.value || '');
                            @endif
                            window.location.href = "{{ route('shipments.create') }}?" + params.toString();
                        @else
                            window.location.href = "{{ route('ocr.index', ['type' => $type]) }}";
                        @endif
                    }
                } else {
                    let errorMsg = body.message || 'Terjadi kesalahan saat menyimpan data.';
                    if (status === 422 && body.errors) {
                        errorMsg = Object.values(body.errors).flat().join('\n');
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: errorMsg
                        });
                    } else {
                        alert('Gagal: ' + errorMsg);
                    }
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    if (window.lucide) lucide.createIcons();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error Server',
                        text: 'Terjadi kesalahan jaringan atau server.'
                    });
                } else {
                    alert('Terjadi kesalahan jaringan atau server.');
                }
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
                if (window.lucide) lucide.createIcons();
            });
        });

        if (window.lucide) lucide.createIcons();
    </script>
    <style>
        @keyframes spin { 100% { transform: rotate(360deg); } }
    </style>
@endsection