@extends('layouts.modern')

@section('title', 'Memproses OCR...')
@section('header', 'Memproses Dokumen')
@section('subheader', 'Silakan tunggu, AI sedang membaca dokumen Anda')

@section('actions')
<a href="{{ route('ocr.index', ['type' => $type]) }}" class="btn btn-ghost"
   style="font-size:12px;padding:8px 16px;">
    <i data-lucide="x" style="width:14px;height:14px;"></i> Batal
</a>
@endsection

@section('content')
@php
    $isOutbound = $type === 'outbound';
    $hex        = $isOutbound ? '#F5A623' : '#34A853';
    $hexAlpha   = $isOutbound ? 'rgba(245,166,35,' : 'rgba(52,168,83,';
    $jenisLabel = ['sir20'=>'Surat Pengantar SIR 20','rss1'=>'Bukti Pengantar RSS 1','do'=>'Delivery Order','surat_kuasa'=>'Surat Kuasa'];
@endphp

<style>
    @media (max-width: 768px) {
        .ocr-waiting-grid { grid-template-columns: 1fr !important; }
        .ocr-preview-sticky { position: static !important; }
    }
</style>
<div class="ocr-waiting-grid" style="display:grid;grid-template-columns:{{ $previewUrl ? '2fr 3fr' : '1fr' }};gap:24px;align-items:start;">

    {{-- ═══ KIRI: Preview Foto ═══ --}}
    @if($previewUrl)
    <div class="ocr-preview-sticky" style="display:flex;flex-direction:column;gap:16px;position:sticky;top:24px;">
        <div class="card-premium p-4" style="border-top:3px solid #4AADE4;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <i data-lucide="image" style="width:14px;height:14px;color:var(--text-muted);"></i>
                <span style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.08em;">Foto Dokumen</span>
                <span id="timerBadge" style="margin-left:auto;font-size:10px;color:var(--text-muted);">⏱ 0s</span>
            </div>
            <div style="border-radius:10px;overflow:hidden;border:1px solid var(--border);background:#f8fafc;">
                <img src="{{ $previewUrl }}" alt="Dokumen"
                     style="width:100%;height:auto;object-fit:contain;max-height:520px;display:block;">
            </div>
        </div>

        <div style="padding:12px 14px;border-radius:10px;background:{{ $hexAlpha }}0.06);border:1px solid {{ $hexAlpha }}0.2);">
            <div style="display:flex;align-items:center;gap:8px;">
                <div style="width:28px;height:28px;border-radius:7px;background:{{ $hexAlpha }}0.12);display:flex;align-items:center;justify-content:center;">
                    <i data-lucide="file-text" style="width:13px;height:13px;color:{{ $hex }};"></i>
                </div>
                <div>
                    <p style="font-size:11px;font-weight:700;color:var(--text-primary);margin:0;">{{ $jenisLabel[$jenis] ?? strtoupper($jenis) }}</p>
                    <p style="font-size:10px;color:var(--text-muted);margin:1px 0 0 0;">{{ ucfirst($type) }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══ KANAN: Status + Skeleton Form ═══ --}}
    <div>

        {{-- Status Card --}}
        <div class="card-premium p-6 anim-fade-up" style="margin-bottom:20px;border-top:3px solid {{ $hex }};">

            {{-- Processing state (default) --}}
            <div id="stateProcessing">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:20px;">
                    <div style="position:relative;width:52px;height:52px;flex-shrink:0;">
                        <div style="position:absolute;inset:0;border:3px solid {{ $hexAlpha }}0.15);border-radius:50%;"></div>
                        <div style="position:absolute;inset:0;border:3px solid transparent;border-top-color:{{ $hex }};border-radius:50%;animation:spin 0.9s linear infinite;"></div>
                        <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;">
                            <i data-lucide="cpu" style="width:18px;height:18px;color:{{ $hex }};"></i>
                        </div>
                    </div>
                    <div>
                        <h3 id="stateTitle" style="font-size:15px;font-weight:800;color:var(--text-primary);margin:0;">Mengekstrak Data...</h3>
                        <p id="stateSubtitle" style="font-size:12px;color:var(--text-muted);margin:3px 0 0 0;">AI sedang membaca dokumen</p>
                    </div>
                    <div id="statusBadge" style="margin-left:auto;display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;background:{{ $hexAlpha }}0.1);border:1px solid {{ $hexAlpha }}0.25);font-size:11px;font-weight:700;color:{{ $hex }};">
                        <span style="width:6px;height:6px;border-radius:50%;background:{{ $hex }};animation:pulse 1.5s ease infinite;display:inline-block;"></span>
                        Processing
                    </div>
                </div>

                {{-- Progress steps --}}
                <div style="display:flex;flex-direction:column;gap:6px;" id="progressSteps">
                    @foreach([
                        ['icon'=>'upload','label'=>'Foto diunggah','done'=>true],
                        ['icon'=>'cpu','label'=>'AI menganalisis gambar','done'=>false],
                        ['icon'=>'file-text','label'=>'Mengekstrak field data','done'=>false],
                    ] as $s)
                    <div class="progress-step" style="display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:9px;background:{{ $loop->first ? $hexAlpha.'0.06)' : 'var(--bg-secondary)' }};border:1px solid {{ $loop->first ? $hexAlpha.'0.2)' : 'var(--border)' }};transition:all 0.4s;">
                        <div class="step-icon" style="width:24px;height:24px;border-radius:6px;background:{{ $loop->first ? $hexAlpha.'0.12)' : 'rgba(0,0,0,0.04)' }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i data-lucide="{{ $s['icon'] }}" style="width:12px;height:12px;color:{{ $loop->first ? $hex : 'var(--text-muted)' }};"></i>
                        </div>
                        <span class="step-label" style="font-size:12px;font-weight:600;color:{{ $loop->first ? 'var(--text-primary)' : 'var(--text-muted)' }};">{{ $s['label'] }}</span>
                        @if($loop->first)
                        <i data-lucide="check" style="width:13px;height:13px;color:#34A853;margin-left:auto;"></i>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Error state (hidden) --}}
            <div id="stateError" style="display:none;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                    <div style="width:52px;height:52px;border-radius:14px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="alert-triangle" style="width:22px;height:22px;color:#ef4444;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#ef4444;margin:0;">OCR Gagal</h3>
                        <p id="errorMsg" style="font-size:12px;color:var(--text-muted);margin:3px 0 0 0;"></p>
                    </div>
                </div>
                <a href="{{ route('ocr.manual', ['type'=>$type,'jenis'=>$jenis]) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:9px;background:#ef4444;color:white;font-size:13px;font-weight:700;text-decoration:none;">
                    <i data-lucide="pen-line" style="width:14px;height:14px;"></i> Isi Manual
                </a>
            </div>

            {{-- Done state (hidden) --}}
            <div id="stateDone" style="display:none;">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px;">
                    <div style="width:52px;height:52px;border-radius:14px;background:rgba(52,168,83,0.1);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i data-lucide="check-circle-2" style="width:26px;height:26px;color:#34A853;"></i>
                    </div>
                    <div>
                        <h3 style="font-size:15px;font-weight:800;color:#34A853;margin:0;">Data Berhasil Diekstrak!</h3>
                        <p id="doneSubtitle" style="font-size:12px;color:var(--text-muted);margin:3px 0 0 0;"></p>
                    </div>
                </div>
                <a id="btnLanjut" href="{{ route('ocr.review_by_id', ['id'=>$jobId]) }}"
                   style="display:inline-flex;align-items:center;gap:6px;padding:11px 22px;border-radius:10px;background:#34A853;color:white;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 4px 12px rgba(52,168,83,0.3);">
                    <i data-lucide="arrow-right" style="width:15px;height:15px;"></i> Lihat & Verifikasi Data
                </a>
            </div>
        </div>

        {{-- Skeleton Form (tampil selama processing) --}}
        <div id="skeletonForm" class="card-premium p-5" style="border-top:3px solid {{ $hex }};">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);">
                <div style="width:32px;height:32px;border-radius:9px;background:var(--bg-secondary);"></div>
                <div>
                    <div class="skeleton-line" style="width:160px;height:13px;border-radius:6px;background:var(--bg-secondary);margin-bottom:5px;"></div>
                    <div class="skeleton-line" style="width:100px;height:10px;border-radius:5px;background:var(--bg-secondary);"></div>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                @foreach(range(1,6) as $i)
                <div style="{{ $i <= 2 ? 'grid-column:1/-1;' : '' }}">
                    <div class="skeleton-line" style="width:80px;height:9px;border-radius:4px;background:var(--bg-secondary);margin-bottom:6px;"></div>
                    <div class="skeleton-line" style="width:100%;height:38px;border-radius:9px;background:var(--bg-secondary);"></div>
                </div>
                @endforeach
            </div>
        </div>

    </div>
</div>

<style>
    @keyframes spin  { to { transform: rotate(360deg); } }
    @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position:  400px 0; }
    }
    .skeleton-line {
        background: linear-gradient(90deg, var(--bg-secondary) 25%, rgba(255,255,255,0.5) 50%, var(--bg-secondary) 75%);
        background-size: 800px 100%;
        animation: shimmer 1.6s infinite;
    }
</style>

<script>
    const JOB_ID      = {{ $jobId }};
    const STATUS_URL  = "{{ route('ocr.status', ['id' => $jobId]) }}";
    const MAX_WAIT_MS = 90_000; // 90 detik max polling
    const INTERVAL_MS = 1_500;  // poll tiap 1.5 detik

    let elapsed  = 0;
    let pollTimer;

    // Timer display
    const timerBadge = document.getElementById('timerBadge');
    setInterval(() => {
        elapsed++;
        if (timerBadge) timerBadge.textContent = '⏱ ' + elapsed + 's';
    }, 1000);

    function setStepActive(index) {
        const steps = document.querySelectorAll('.progress-step');
        const hex   = '{{ $hex }}';
        const alpha = '{{ $hexAlpha }}';
        steps.forEach((s, i) => {
            const isActive = i <= index;
            s.style.background = isActive ? alpha + '0.06)' : 'var(--bg-secondary)';
            s.style.border     = `1px solid ${isActive ? alpha + '0.2)' : 'var(--border)'}`;
            const lbl = s.querySelector('.step-label');
            if (lbl) lbl.style.color = isActive ? 'var(--text-primary)' : 'var(--text-muted)';
        });
    }

    function showError(msg) {
        clearInterval(pollTimer);
        document.getElementById('stateProcessing').style.display = 'none';
        document.getElementById('stateError').style.display      = 'block';
        document.getElementById('skeletonForm').style.display    = 'none';
        document.getElementById('errorMsg').textContent          = msg || 'Terjadi kesalahan. Coba isi manual.';
        if (window.lucide) lucide.createIcons();
    }

    function showDone(waktu_s) {
        clearInterval(pollTimer);
        document.getElementById('stateProcessing').style.display = 'none';
        document.getElementById('stateDone').style.display       = 'block';
        document.getElementById('skeletonForm').style.display    = 'none';
        document.getElementById('doneSubtitle').textContent      = `Selesai dalam ${waktu_s ?? elapsed}s — klik tombol untuk verifikasi`;
        if (window.lucide) lucide.createIcons();
        // Auto-redirect setelah 2 detik
        setTimeout(() => {
            window.location.href = document.getElementById('btnLanjut').href;
        }, 2000);
    }

    function poll() {
        fetch(STATUS_URL)
            .then(r => r.json())
            .then(data => {
                if (data.status === 'processing') {
                    setStepActive(1);
                } else if (data.status === 'done') {
                    setStepActive(2);
                    setTimeout(() => showDone(data.waktu_s), 300);
                } else if (data.status === 'failed') {
                    showError(data.error);
                }
                // pending → tetap di step 0
            })
            .catch(() => {
                // network error, coba lagi di interval berikutnya
            });
    }

    // Mulai polling
    pollTimer = setInterval(poll, INTERVAL_MS);
    // Poll pertama langsung
    poll();

    // Timeout 90 detik
    setTimeout(() => {
        if (document.getElementById('stateProcessing').style.display !== 'none') {
            showError('Waktu habis (90 detik). Server OCR mungkin sedang sibuk. Coba isi manual atau ulangi scan.');
        }
    }, MAX_WAIT_MS);

    if (window.lucide) lucide.createIcons();
</script>
@endsection
