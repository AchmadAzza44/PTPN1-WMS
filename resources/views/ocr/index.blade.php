@extends('layouts.modern')

@section('title', request('type') == 'outbound' ? 'OCR Outbound' : 'OCR Inbound')
@section('header', request('type') == 'outbound' ? 'Outbound — Scan Surat Jalan' : 'Inbound — Scan Nota Timbang')
@section('subheader', request('type') == 'outbound' ? 'Upload Surat Jalan / DO untuk membuat pengiriman' : 'Upload Nota Timbang / Surat Pengantar untuk input stok masuk')

@section('actions')
<div style="display:flex;gap:8px;">
    <a href="{{ route('ocr.index', ['type' => 'inbound']) }}"
       class="btn {{ request('type') != 'outbound' ? 'btn-green' : 'btn-ghost' }}"
       style="font-size:12px;padding:8px 18px;">
        <i data-lucide="arrow-down-to-line" style="width:14px;height:14px;"></i> Inbound
    </a>
    <a href="{{ route('ocr.index', ['type' => 'outbound']) }}"
       class="btn {{ request('type') == 'outbound' ? 'btn-orange' : 'btn-ghost' }}"
       style="font-size:12px;padding:8px 18px;">
        <i data-lucide="arrow-up-from-line" style="width:14px;height:14px;"></i> Outbound
    </a>
</div>
@endsection

@section('content')
@php
    $isOutbound = request('type') == 'outbound';
    $color      = $isOutbound ? 'orange' : 'green';
    $hex        = $isOutbound ? '#F5A623' : '#34A853';
    $hexAlpha   = $isOutbound ? 'rgba(245,166,35,' : 'rgba(52,168,83,';
    $docs = $isOutbound
        ? [['val'=>'do','label'=>'Delivery Order','icon'=>'file-output'],
           ['val'=>'surat_kuasa','label'=>'Surat Kuasa','icon'=>'file-key']]
        : [['val'=>'sir20','label'=>'SIR 20','icon'=>'package'],
           ['val'=>'rss1','label'=>'RSS 1','icon'=>'layers']];
@endphp

{{-- ── Responsive style untuk grid --}}
<style>
    .ocr-index-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; align-items: start; }
    .ocr-index-grid > * { min-width: 0; }
    @media (max-width: 768px) {
        .ocr-index-grid { grid-template-columns: 1fr; }
        .ocr-right-panel { order: -1; }
        #dropzoneEl { padding: 20px 16px !important; }
    }
</style>
<div class="ocr-index-grid">

    {{-- ═══ FORM CARD ═══ --}}
    <div class="card-premium p-6 anim-fade-up"
         style="border-top:3px solid {{ $hex }};position:relative;overflow:hidden;">

        {{-- Decorative bg --}}
        <div style="position:absolute;right:-20px;top:-20px;opacity:0.04;pointer-events:none;">
            <i data-lucide="scan-line" style="width:160px;height:160px;color:{{ $hex }};"></i>
        </div>

        {{-- ── Header mode ── --}}
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border);">
            <div style="width:42px;height:42px;border-radius:12px;background:{{ $hexAlpha }}0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i data-lucide="{{ $isOutbound ? 'arrow-up-from-line' : 'arrow-down-to-line' }}"
                   style="width:20px;height:20px;color:{{ $hex }};"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <h3 style="font-size:15px;font-weight:800;color:var(--text-primary);margin:0;letter-spacing:-0.02em;">
                    {{ $isOutbound ? 'Mode Outbound' : 'Mode Inbound' }}
                </h3>
                <p style="font-size:11px;color:var(--text-muted);margin:2px 0 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $isOutbound ? 'Surat Jalan / Delivery Order / Surat Kuasa' : 'Surat Pengantar SIR 20 / RSS 1' }}
                </p>
            </div>
            <span style="flex-shrink:0;display:inline-flex;align-items:center;gap:4px;padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;background:{{ $hexAlpha }}0.1);color:{{ $hex }};">
                <i data-lucide="cpu" style="width:9px;height:9px;"></i> AI
            </span>
        </div>

        <form action="{{ route('ocr.store') }}" method="POST" enctype="multipart/form-data" id="ocrForm">
            @csrf
            <input type="hidden" name="type" value="{{ request('type', 'inbound') }}">

            {{-- Error --}}
            @if(session('error'))
            <div style="margin-bottom:14px;padding:10px 14px;border-radius:10px;background:rgba(239,68,68,0.07);border:1px solid rgba(239,68,68,0.2);display:flex;align-items:center;gap:8px;">
                <i data-lucide="alert-circle" style="width:14px;height:14px;color:#ef4444;flex-shrink:0;"></i>
                <p style="font-size:12px;color:#ef4444;margin:0;">{{ session('error') }}</p>
            </div>
            @endif

            {{-- ── Upload zone ── --}}
            <div style="margin-bottom:16px;" x-data="{ dragging: false }">
                <p style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin:0 0 8px 0;">File Dokumen</p>
                <div id="dropzoneEl"
                     :style="dragging ? 'border-color:{{ $hex }};background:{{ $hexAlpha }}0.05);transform:scale(1.005);' : ''"
                     style="display:flex;flex-direction:column;align-items:center;justify-content:center;padding:32px 20px;border:2px dashed #cbd5e1;border-radius:14px;cursor:pointer;transition:all 0.25s;background:#fafbfc;text-align:center;"
                     @dragover.prevent="dragging=true"
                     @dragleave.prevent="dragging=false"
                     @drop.prevent="dragging=false; $refs.fi.files=$event.dataTransfer.files; handleFile($event.dataTransfer.files[0])"
                     onclick="document.getElementById('fu').click()"
                     onmouseover="if(!this.classList.contains('has-file'))this.style.borderColor='{{ $hex }}44'"
                     onmouseout="if(!this.classList.contains('has-file'))this.style.borderColor='#cbd5e1'">

                    <div id="drop-content">
                        <div style="width:48px;height:48px;background:white;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.08);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;border:1px solid #e9eef4;">
                            <i data-lucide="image-plus" style="width:22px;height:22px;color:{{ $hex }};"></i>
                        </div>
                        <p style="font-size:13px;margin:0;color:var(--text-secondary);">
                            <span style="font-weight:700;color:{{ $hex }};">Klik upload</span>
                            <span style="color:var(--text-muted);"> atau drag & drop</span>
                        </p>
                        <p style="font-size:10px;color:var(--text-muted);margin:5px 0 0 0;">JPG, PNG — Maks. 10MB</p>
                    </div>

                    <div id="file-selected" style="display:none;">
                        <div style="width:44px;height:44px;border-radius:12px;background:{{ $hexAlpha }}0.1);display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                            <i data-lucide="file-check" style="width:20px;height:20px;color:{{ $hex }};"></i>
                        </div>
                        <p id="file-name-display" style="font-size:12px;font-weight:700;color:var(--text-primary);margin:0;word-break:break-all;max-width:200px;"></p>
                        <p style="font-size:10px;color:var(--text-muted);margin:4px 0 0 0;">Klik untuk ganti</p>
                    </div>
                </div>
                <input id="fu" x-ref="fi" name="document" type="file" style="display:none;" required onchange="handleFile(this.files[0])">
            </div>

            {{-- ── Jenis Dokumen ── --}}
            <div style="margin-bottom:16px;">
                <p style="font-size:10px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;margin:0 0 8px 0;">Jenis Dokumen</p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                    @foreach($docs as $i => $doc)
                    <label class="jenis-label {{ $isOutbound ? 'jenis-outbound' : 'jenis-inbound' }}"
                           style="display:flex;align-items:center;gap:10px;padding:11px 14px;border-radius:10px;border:2px solid #e2e8f0;background:white;cursor:pointer;transition:all 0.2s;">
                        <input type="radio" name="jenis" value="{{ $doc['val'] }}" style="display:none;" {{ $i === 0 ? 'checked' : '' }}>
                        <div style="width:30px;height:30px;border-radius:8px;background:{{ $hexAlpha }}0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="{{ $doc['icon'] }}" style="width:14px;height:14px;color:{{ $hex }};"></i>
                        </div>
                        <span style="font-size:12px;font-weight:700;color:var(--text-primary);">{{ $doc['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Engine strip ── --}}
            <div style="margin-bottom:16px;display:flex;align-items:center;gap:8px;padding:9px 12px;border-radius:9px;background:rgba(245,166,35,0.06);border:1px solid rgba(245,166,35,0.15);">
                <i data-lucide="cpu" style="width:13px;height:13px;color:#F5A623;flex-shrink:0;"></i>
                <span style="font-size:11px;color:var(--text-muted);">HuggingFace API · <strong style="color:var(--text-secondary);">Qwen2.5-VL-7B</strong></span>
                <span style="margin-left:auto;font-size:9px;font-weight:700;padding:2px 8px;border-radius:20px;background:rgba(245,166,35,0.12);color:#d97706;">
                    ● Online
                </span>
            </div>

            {{-- ── Submit ── --}}
            <button type="submit" id="submitBtn"
                    style="width:100%;display:flex;align-items:center;justify-content:center;gap:8px;padding:13px;border:none;border-radius:12px;font-size:14px;font-weight:700;color:white;cursor:pointer;transition:all 0.2s;background:{{ $hex }};box-shadow:0 4px 14px {{ $hexAlpha }}0.35);"
                    onmouseover="this.style.opacity='0.9';this.style.transform='translateY(-1px)'"
                    onmouseout="this.style.opacity='1';this.style.transform='translateY(0)'">
                <i data-lucide="scan-text" style="width:17px;height:17px;"></i>
                Mulai Proses OCR
            </button>

            {{-- ── Input Manual link ── --}}
            <div style="text-align:center;margin-top:12px;">
                <a id="manualLink"
                   href="{{ route('ocr.manual', ['type' => request('type', 'inbound'), 'jenis' => $docs[0]['val']]) }}"
                   style="font-size:12px;color:var(--text-muted);text-decoration:none;display:inline-flex;align-items:center;gap:5px;">
                    <i data-lucide="pen-line" style="width:12px;height:12px;"></i>
                    Input manual tanpa foto
                </a>
            </div>

            {{-- Progress ── --}}
            <div id="upload-progress" style="display:none;margin-top:14px;">
                <div style="display:flex;justify-content:space-between;margin-bottom:5px;">
                    <span style="font-size:11px;font-weight:600;color:var(--text-secondary);">Memproses...</span>
                    <span id="progress-pct" style="font-size:11px;color:var(--text-muted);">0%</span>
                </div>
                <div style="height:5px;border-radius:999px;background:#e2e8f0;overflow:hidden;">
                    <div id="progress-bar" style="height:5px;border-radius:999px;width:0%;transition:width 0.3s;background:{{ $hex }};"></div>
                </div>
            </div>
        </form>
    </div>

    {{-- ═══ RIGHT PANEL ═══ --}}
    <div class="ocr-right-panel" style="display:flex;flex-direction:column;gap:16px;">

        {{-- Image preview (shown after file selected) --}}
        <div id="image-preview" class="card-premium p-4" style="display:none;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <i data-lucide="eye" style="width:14px;height:14px;color:var(--text-muted);"></i>
                <span style="font-size:12px;font-weight:700;color:var(--text-secondary);">Preview Dokumen</span>
                <span id="file-size-badge" style="margin-left:auto;font-size:10px;color:var(--text-muted);background:#f1f5f9;padding:2px 8px;border-radius:20px;"></span>
            </div>
            <div style="border-radius:10px;overflow:hidden;border:1px solid var(--border);background:#f8fafc;">
                <img id="preview-img" src="#" alt="Preview"
                     style="width:100%;height:auto;object-fit:contain;max-height:400px;display:block;">
            </div>
        </div>

        {{-- Empty state (shown by default) --}}
        <div id="empty-state" class="card-premium p-8"
             style="display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;min-height:380px;">

            <div style="position:relative;margin-bottom:24px;">
                <div style="width:86px;height:86px;border-radius:24px;background:{{ $hexAlpha }}0.1);display:flex;align-items:center;justify-content:center;animation:floatIcon 3.5s ease-in-out infinite;">
                    <i data-lucide="file-scan" style="width:44px;height:44px;color:{{ $hex }};"></i>
                </div>
                <div style="position:absolute;top:-4px;right:-4px;width:22px;height:22px;border-radius:50%;background:{{ $hex }};display:flex;align-items:center;justify-content:center;animation:orbitDot 3.5s ease-in-out infinite;">
                    <i data-lucide="sparkles" style="width:11px;height:11px;color:white;"></i>
                </div>
            </div>

            <h3 style="font-size:18px;font-weight:800;color:var(--text-primary);margin:0 0 8px 0;letter-spacing:-0.02em;">
                {{ $isOutbound ? 'Siap Scan Outbound' : 'Siap Scan Inbound' }}
            </h3>
            <p style="color:var(--text-muted);max-width:260px;font-size:12px;margin:0 0 24px 0;line-height:1.6;">
                Upload dokumen di sebelah kiri, pilih jenis, lalu klik Mulai Proses OCR.
            </p>

            <div style="display:flex;flex-direction:column;gap:8px;width:100%;max-width:240px;">
                @foreach([
                    ['zap',          'Ekstraksi data instan',  $hex,   $hexAlpha],
                    ['shield-check', 'Validasi otomatis',      '#34A853', 'rgba(52,168,83,'],
                    ['database',     'Simpan ke database',     '#4AADE4', 'rgba(74,173,228,'],
                ] as [$icon, $text, $c, $ca])
                <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:10px;background:var(--bg-secondary);border:1px solid var(--border);">
                    <div style="width:28px;height:28px;border-radius:7px;background:{{ $ca }}0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="{{ $icon }}" style="width:13px;height:13px;color:{{ $c }};"></i>
                    </div>
                    <span style="font-size:12px;font-weight:500;color:var(--text-secondary);">{{ $text }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ═══ LOADING OVERLAY ═══ --}}
<div id="loadingOverlay"
     style="position:fixed;inset:0;z-index:100;background:rgba(13,27,42,0.88);backdrop-filter:blur(8px);display:none;align-items:center;justify-content:center;">
    <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:24px;padding:44px;text-align:center;max-width:320px;width:90%;">
        <div style="position:relative;width:72px;height:72px;margin:0 auto 20px;">
            <div style="position:absolute;inset:0;border:3px solid {{ $hexAlpha }}0.15);border-radius:50%;"></div>
            <div style="position:absolute;inset:0;border:3px solid transparent;border-top-color:{{ $hex }};border-radius:50%;animation:spin 0.9s linear infinite;"></div>
            <div style="position:absolute;inset:10px;border:3px solid transparent;border-top-color:rgba(74,173,228,0.8);border-radius:50%;animation:spin 1.4s linear infinite reverse;"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                <i data-lucide="cpu" style="width:22px;height:22px;color:white;"></i>
            </div>
        </div>
        <h3 style="font-size:18px;font-weight:800;color:white;margin:0 0 6px 0;">Memproses OCR...</h3>
        <p style="color:#64748b;font-size:12px;margin:0 0 20px 0;">Mengekstrak data dengan HuggingFace API</p>
        <div style="display:flex;flex-direction:column;gap:6px;text-align:left;">
            @foreach([['Upload gambar','check'],['Analisis AI','loader'],['Ekstrak data','clock']] as $i => $s)
            <div class="loading-step" style="display:flex;align-items:center;gap:9px;padding:7px 11px;border-radius:9px;background:rgba(255,255,255,0.04);opacity:{{ $i===0?1:0.4 }};">
                <div style="width:18px;height:18px;border-radius:5px;background:{{ $i===0?$hexAlpha.'0.3)':'rgba(255,255,255,0.08)' }};display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="{{ $s[1] }}" style="width:10px;height:10px;color:{{ $i===0?$hex:'#64748b' }};"></i>
                </div>
                <span style="font-size:11px;color:{{ $i===0?'#e2e8f0':'#64748b' }};font-weight:500;">{{ $s[0] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

<style>
    @keyframes spin      { to { transform: rotate(360deg); } }
    @keyframes floatIcon { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-10px)} }
    @keyframes orbitDot  { 0%,100%{transform:translate(0,0) scale(1)} 50%{transform:translate(-3px,-7px) scale(0.85)} }

    .jenis-inbound:has(input:checked) {
        border-color: #34A853;
        box-shadow: 0 0 0 3px rgba(52,168,83,0.12);
        background: rgba(52,168,83,0.03);
    }
    .jenis-outbound:has(input:checked) {
        border-color: #F5A623;
        box-shadow: 0 0 0 3px rgba(245,166,35,0.12);
        background: rgba(245,166,35,0.03);
    }
</style>

<script>
// File asli yang dipilih user (sebelum kompres)
let selectedFile = null;
// Blob hasil kompres yang akan dikirim
let compressedBlob = null;

const MAX_PX  = 1600;   // max dimension setelah kompres
const QUALITY = 0.82;   // JPEG quality (0-1)

function handleFile(file) {
    if (!file) return;
    selectedFile = file;
    compressedBlob = null; // reset

    document.getElementById('drop-content').style.display = 'none';
    document.getElementById('file-selected').style.display = 'block';
    document.getElementById('file-name-display').textContent = file.name;

    const dz = document.getElementById('dropzoneEl');
    dz.style.borderColor = '{{ $hex }}';
    dz.style.borderStyle  = 'solid';
    dz.classList.add('has-file');

    const kb = (file.size / 1024).toFixed(1);
    document.getElementById('file-size-badge').textContent = kb + ' KB';

    if (file.type.startsWith('image/')) {
        const r = new FileReader();
        r.onload = e => {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
            document.getElementById('empty-state').style.display = 'none';
        };
        r.readAsDataURL(file);

        // Kompres di background segera setelah file dipilih
        compressImage(file).then(blob => {
            compressedBlob = blob;
            const kbAfter  = (blob.size / 1024).toFixed(1);
            const kbBefore = (file.size / 1024).toFixed(1);
            // Update badge kalau ukuran berkurang signifikan
            if (blob.size < file.size * 0.9) {
                document.getElementById('file-size-badge').textContent =
                    kbBefore + ' KB → ' + kbAfter + ' KB ✓';
            }
        }).catch(() => {
            // Gagal kompres → pakai file asli, tidak masalah
            compressedBlob = null;
        });
    }
}

function compressImage(file) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            URL.revokeObjectURL(url);
            let w = img.naturalWidth, h = img.naturalHeight;

            // Hitung dimensi baru
            if (Math.max(w, h) > MAX_PX) {
                const s = MAX_PX / Math.max(w, h);
                w = Math.round(w * s);
                h = Math.round(h * s);
            } else {
                // Gambar sudah kecil, tidak perlu kompres
                resolve(file);
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = w; canvas.height = h;
            canvas.getContext('2d').drawImage(img, 0, 0, w, h);
            canvas.toBlob(blob => {
                if (blob) resolve(blob);
                else reject(new Error('toBlob failed'));
            }, 'image/jpeg', QUALITY);
        };
        img.onerror = reject;
        img.src = url;
    });
}

document.getElementById('ocrForm').addEventListener('submit', async function(e) {
    // Kalau ada hasil kompres, ganti file di FormData
    if (compressedBlob && selectedFile) {
        e.preventDefault();

        document.getElementById('loadingOverlay').style.display = 'flex';
        document.querySelectorAll('.loading-step').forEach((s, i) => {
            setTimeout(() => {
                s.style.opacity = 1;
                s.style.background = 'rgba(255,255,255,0.06)';
            }, (i + 1) * 900);
        });

        // Buat FormData manual dengan blob hasil kompres
        const form = this;
        const fd   = new FormData(form);
        // Ganti field 'document' dengan blob yang sudah dikompres
        fd.set('document', compressedBlob, selectedFile.name.replace(/\.[^.]+$/, '') + '_compressed.jpg');

        try {
            const res = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            // Laravel redirect → ikuti redirect
            if (res.redirected) {
                window.location.href = res.url;
            } else {
                // Fallback: submit form biasa
                form.submit();
            }
        } catch {
            // Network error → submit form biasa dengan file asli
            form.submit();
        }
        return;
    }

    // Tidak ada kompres (bukan gambar / gambar sudah kecil) → submit normal
    document.getElementById('loadingOverlay').style.display = 'flex';
    document.querySelectorAll('.loading-step').forEach((s, i) => {
        setTimeout(() => {
            s.style.opacity = 1;
            s.style.background = 'rgba(255,255,255,0.06)';
        }, (i + 1) * 900);
    });
});

if (window.lucide) lucide.createIcons();
</script>
@endsection