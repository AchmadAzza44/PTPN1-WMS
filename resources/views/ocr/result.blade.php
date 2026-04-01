@extends('layouts.modern')

@section('title', 'Hasil OCR')
@section('header', 'Hasil Ekstraksi OCR')
@section('subheader', 'Data yang berhasil diekstrak dari dokumen — periksa dan koreksi sebelum disimpan')

@section('actions')
    <a href="{{ route('ocr.index', ['type' => $type]) }}" class="btn btn-ghost" style="font-size:12px;padding:8px 16px;">
        <i data-lucide="arrow-left" style="width:14px;height:14px;"></i>
        Scan Lagi
    </a>
@endsection

@section('content')
    @php
        $isOutbound = $type === 'outbound';
        $color = $isOutbound ? 'orange' : 'green';
        $labels = [
            'sir20' => 'Surat Pengantar SIR 20',
            'rss1' => 'Bukti Pengantar RSS 1',
            'do' => 'Delivery Order',
            'surat_kuasa' => 'Surat Kuasa',
        ];
        $fieldLabels = [
            'no_surat' => 'No. Surat',
            'no_dokumen' => 'No. Dokumen',
            'no_so_internal' => 'No. SO Internal',
            'no_kontrak_internal' => 'No. Kontrak Internal',
            'no_surat_kuasa' => 'No. Surat Kuasa',
            'tanggal' => 'Tanggal',
            'tanggal_so' => 'Tanggal SO',
            'no_kendaraan' => 'No. Kendaraan',
            'nama_supir' => 'Nama Supir',
            'total_kg' => 'Total Berat (kg)',
            'total_bale' => 'Total Bale',
            'kebun' => 'Kebun',
            'mutu' => 'Mutu',
            'jumlah_bale' => 'Jumlah Bale',
            'berat_netto_total' => 'Berat Netto Total (kg)',
            'pengangkut' => 'Pengangkut',
            'no_po' => 'No. PO',
            'tanggal_po' => 'Tanggal PO',
            'no_kontrak' => 'No. Kontrak',
            'tanggal_kontrak' => 'Tanggal Kontrak',
            'incoterms' => 'Incoterms',
            'lokasi' => 'Lokasi',
            'nama_pembeli' => 'Nama Pembeli',
            'alamat_pembeli' => 'Alamat Pembeli',
            'no_material' => 'No. Material',
            'deskripsi' => 'Deskripsi',
            'volume' => 'Volume',
            'terbilang' => 'Terbilang',
            'nama_pemberi' => 'Nama Pemberi Kuasa',
            'perusahaan_pemberi' => 'Perusahaan Pemberi',
            'alamat_pemberi' => 'Alamat Pemberi',
            'nama_penerima' => 'Nama Penerima',
            'jenis_karet' => 'Jenis Karet',
            'jumlah_kg' => 'Jumlah (kg)',
            'jumlah_pallet' => 'Jumlah Pallet',
            'no_do' => 'No. DO',
            'trucking' => 'Trucking',
            'stuffing' => 'Stuffing',
            'tujuan' => 'Tujuan',
        ];

        // Pisahkan field skalar dan array
        $scalarFields = array_filter($hasil, fn($v) => !is_array($v));
        $arrayFields = array_filter($hasil, fn($v) => is_array($v));

        // Confidence & blur
        $conf       = $confidence ?? null;
        $confScore  = $conf['score'] ?? 100;
        $confLevel  = $conf['level'] ?? 'high';
        $confWarn   = $conf['warnings'] ?? [];
        $blurStatus = $blur['status'] ?? 'ok';
        $blurScore  = $blur['score'] ?? 999;
        $pageWarning = $warning ?? null;
    @endphp

    {{-- ═══ BANNER: foto buram ═══ --}}
    @if($blurStatus === 'warning' || $pageWarning)
    <div style="display:flex;align-items:flex-start;gap:12px;background:#fffbeb;border:1.5px solid #f59e0b;border-radius:12px;padding:14px 18px;margin-bottom:20px;">
        <i data-lucide="alert-triangle" style="width:18px;height:18px;color:#d97706;flex-shrink:0;margin-top:1px;"></i>
        <div>
            <p style="font-size:13px;font-weight:700;color:#92400e;margin:0 0 2px;">Foto Kurang Jelas</p>
            <p style="font-size:12px;color:#78350f;margin:0;">
                Foto agak buram (skor {{ $blurScore }}). Hasil OCR mungkin kurang akurat — periksa semua data dengan teliti sebelum simpan.
            </p>
        </div>
    </div>
    @endif

    {{-- ═══ BANNER: halusinasi terdeteksi ═══ --}}
    @if(!empty($confWarn))
    <div style="display:flex;align-items:flex-start;gap:12px;background:#fef2f2;border:1.5px solid #f87171;border-radius:12px;padding:14px 18px;margin-bottom:20px;">
        <i data-lucide="shield-alert" style="width:18px;height:18px;color:#dc2626;flex-shrink:0;margin-top:1px;"></i>
        <div>
            <p style="font-size:13px;font-weight:700;color:#991b1b;margin:0 0 4px;">Potensi Data Tidak Akurat</p>
            <p style="font-size:12px;color:#7f1d1d;margin:0;">
                Terdeteksi: {{ implode(', ', $confWarn) }}.
                Kemungkinan OCR tidak terbaca dengan baik. Mohon periksa dan koreksi data baris lot di bawah.
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- ═══ INFO CARD ═══ --}}
        <div class="card-premium border-{{ $color }} p-6 anim-fade-up">

            {{-- Header --}}
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:24px;">
                <div class="bg-{{ $color }}-10"
                    style="width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="file-text" class="text-ptpn-{{ $color }}" style="width:20px;height:20px;"></i>
                </div>
                <div>
                    <h3 style="font-size:15px;font-weight:800;color:var(--text-primary);margin:0;">
                        {{ $labels[$jenis] ?? strtoupper($jenis) }}
                    </h3>
                    <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0 0;">
                        Diproses dalam {{ $waktu_s }}s
                    </p>
                </div>
                <span class="badge badge-{{ $color }}" style="margin-left:auto;">
                    <i data-lucide="check-circle" style="width:9px;height:9px;"></i> Selesai
                </span>
                {{-- Confidence badge --}}
                @php
                    $confColor = $confLevel === 'high' ? '#16a34a' : ($confLevel === 'medium' ? '#d97706' : '#dc2626');
                    $confBg    = $confLevel === 'high' ? '#f0fdf4' : ($confLevel === 'medium' ? '#fffbeb' : '#fef2f2');
                    $confIcon  = $confLevel === 'high' ? 'shield-check' : ($confLevel === 'medium' ? 'shield-alert' : 'shield-x');
                @endphp
                <span style="display:inline-flex;align-items:center;gap:4px;background:{{ $confBg }};color:{{ $confColor }};border:1px solid {{ $confColor }}30;border-radius:20px;padding:3px 10px;font-size:10px;font-weight:700;">
                    <i data-lucide="{{ $confIcon }}" style="width:9px;height:9px;"></i>
                    {{ $confScore }}% akurat
                </span>
            </div>

            {{-- Scalar fields --}}
            <div
                style="display:flex;flex-direction:column;gap:1px;border-radius:12px;overflow:hidden;border:1px solid var(--border);">
                @foreach($scalarFields as $key => $value)
                    @if($key !== 'baris' && $key !== 'nomor_bale')
                        <div
                            style="display:flex;align-items:baseline;gap:12px;padding:10px 14px;background:{{ $loop->even ? 'rgba(248,250,252,0.8)' : 'white' }};">
                            <span style="font-size:11px;font-weight:600;color:var(--text-muted);min-width:140px;flex-shrink:0;">
                                {{ $fieldLabels[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                            </span>
                            <span
                                style="font-size:13px;font-weight:600;color:{{ $value ? 'var(--text-primary)' : 'var(--text-muted)' }};">
                                {{ $value ?? '—' }}
                            </span>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- ═══ TABLE / ARRAY DATA ═══ --}}
        <div style="display:flex;flex-direction:column;gap:16px;">

            @foreach($arrayFields as $key => $rows)
                @if(count($rows) > 0)
                    <div class="card-premium p-5 anim-scale-in">
                        <h4
                            style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;margin:0 0 14px 0;">
                            {{ $key === 'baris' ? 'Detail Baris' : ($key === 'nomor_bale' ? 'Nomor Bale' : ucwords(str_replace('_', ' ', $key))) }}
                        </h4>

                        @if($key === 'nomor_bale')
                            {{-- Nomor bale: tampilkan sebagai tags --}}
                            <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                @foreach($rows as $bale)
                                    <span class="badge badge-blue" style="font-size:11px;">{{ $bale }}</span>
                                @endforeach
                            </div>

                        @elseif($key === 'baris' && count($rows) > 0 && is_array(reset($rows)))
                            {{-- Baris tabel: tampilkan sebagai tabel --}}
                            <div style="overflow-x:auto;border-radius:8px;border:1px solid var(--border);">
                                <table style="width:100%;border-collapse:collapse;font-size:12px;">
                                    <thead>
                                        <tr style="background:rgba(248,250,252,0.9);">
                                            @foreach(array_keys(reset($rows)) as $col)
                                                <th
                                                    style="padding:8px 10px;text-align:left;font-weight:700;color:var(--text-muted);font-size:10px;text-transform:uppercase;border-bottom:1px solid var(--border);white-space:nowrap;">
                                                    {{ $fieldLabels[$col] ?? ucwords(str_replace('_', ' ', $col)) }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($rows as $i => $row)
                                            <tr
                                                style="border-bottom:1px solid var(--border);{{ $loop->last ? 'border-bottom:none;' : '' }}">
                                                @foreach($row as $val)
                                                    <td
                                                        style="padding:9px 10px;color:{{ $val ? 'var(--text-primary)' : 'var(--text-muted)' }};font-weight:{{ $loop->first ? '700' : '400' }};">
                                                        {{ $val ?? '—' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif
            @endforeach

            @if(empty(array_filter($arrayFields, fn($r) => count($r) > 0)))
                {{-- Tidak ada data tabel --}}
                <div class="card-premium p-8" style="display:flex;flex-direction:column;align-items:center;text-align:center;">
                    <div class="bg-blue-10"
                        style="width:56px;height:56px;border-radius:16px;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                        <i data-lucide="info" class="text-ptpn-blue" style="width:24px;height:24px;"></i>
                    </div>
                    <p style="font-size:13px;color:var(--text-muted);margin:0;">Tidak ada data tabel untuk dokumen ini.</p>
                </div>
            @endif

            {{-- Raw JSON collapsible --}}
            <details style="border:1px solid var(--border);border-radius:10px;overflow:hidden;">
                <summary
                    style="padding:10px 14px;font-size:11px;font-weight:700;color:var(--text-muted);cursor:pointer;background:rgba(248,250,252,0.8);text-transform:uppercase;letter-spacing:0.08em;list-style:none;display:flex;align-items:center;gap:8px;">
                    <i data-lucide="code-2" style="width:12px;height:12px;"></i> Raw JSON
                </summary>
                <pre
                    style="margin:0;padding:14px;font-size:11px;color:var(--text-secondary);background:#f8fafc;overflow-x:auto;line-height:1.6;">{{ json_encode($hasil, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
            </details>
        </div>

    </div>
@endsection