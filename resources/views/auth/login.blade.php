<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <!-- PWA Setup -->
    <meta name="theme-color" content="#080F1A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-ptpn.png') }}">
    <title>Login — PTPN 1 WMS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --green:  #34A853;
            --green2: #2D9248;
            --orange: #F5A623;
            --blue:   #4AADE4;
            --navy:   #080F1A;
            --navy2:  #0D1B2A;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
            overflow: hidden;
        }

        /* ══════════════════════════════════
           FULL PAGE SCENE
        ══════════════════════════════════ */
        .scene {
            position: relative;
            width: 100%;
            height: 100vh;
            background: var(--navy);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Particle canvas */
        #particles { position: absolute; inset: 0; z-index: 0; }

        /* Giant ambient blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(120px);
            pointer-events: none;
            z-index: 0;
        }
        .blob-1 {
            width: 700px; height: 700px;
            top: -200px; left: -200px;
            background: radial-gradient(circle, rgba(52,168,83,0.18), transparent 70%);
            animation: blobDrift1 18s ease-in-out infinite alternate;
        }
        .blob-2 {
            width: 600px; height: 600px;
            bottom: -150px; right: -100px;
            background: radial-gradient(circle, rgba(74,173,228,0.15), transparent 70%);
            animation: blobDrift2 22s ease-in-out infinite alternate;
        }
        .blob-3 {
            width: 400px; height: 400px;
            top: 40%; right: 30%;
            background: radial-gradient(circle, rgba(245,166,35,0.1), transparent 70%);
            animation: blobDrift3 14s ease-in-out infinite alternate;
        }

        @keyframes blobDrift1 { to { transform: translate(60px, 80px) scale(1.1); } }
        @keyframes blobDrift2 { to { transform: translate(-50px, -60px) scale(1.08); } }
        @keyframes blobDrift3 { to { transform: translate(30px, -40px) scale(0.9); } }

        /* Subtle grid */
        .grid-overlay {
            position: absolute; inset: 0; z-index: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 72px 72px;
        }

        /* ══════════════════════════════════
           SPLIT LAYOUT WRAPPER
        ══════════════════════════════════ */
        .layout {
            position: relative; z-index: 10;
            display: flex;
            width: min(1120px, 96vw);
            min-height: min(640px, 96vh);
            border-radius: 28px;
            overflow: hidden;
            box-shadow:
                0 0 0 1px rgba(255,255,255,0.07),
                0 40px 120px rgba(0,0,0,0.7),
                0 0 80px rgba(52,168,83,0.07);
        }

        /* ══════════════════════════════════
           LEFT BRAND PANEL
        ══════════════════════════════════ */
        .brand {
            flex: 1.1;
            position: relative;
            padding: 52px 48px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            background: linear-gradient(145deg, #0A1628 0%, #0E2035 55%, #091520 100%);
            overflow: hidden;
        }

        /* Horizontal light streak */
        .brand::before {
            content: '';
            position: absolute;
            top: 25%; left: -100px;
            width: 160%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(52,168,83,0.25), rgba(74,173,228,0.15), transparent);
            transform: rotate(-8deg);
        }

        /* Glowing green circle behind logo */
        .logo-glow {
            position: absolute;
            top: 40px; left: 48px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(52,168,83,0.2), transparent 70%);
            filter: blur(30px);
        }

        .brand-top {
            position: relative; z-index: 2;
        }

        /* Logo badge */
        .logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            padding: 10px 18px 10px 10px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.09);
            border-radius: 16px;
            backdrop-filter: blur(10px);
            margin-bottom: 40px;
        }
        .logo-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--green), var(--green2));
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 4px 16px rgba(52,168,83,0.45);
            flex-shrink: 0;
        }
        .logo-icon img { width: 32px; height: 32px; object-fit: contain; }
        .logo-text p:first-child { font-size: 13px; font-weight: 800; color: white; letter-spacing: -0.01em; }
        .logo-text p:last-child  { font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 1px; }

        /* Main headline */
        .brand-headline {
            font-size: clamp(26px, 3vw, 38px);
            font-weight: 900;
            color: white;
            letter-spacing: -0.04em;
            line-height: 1.12;
            margin-bottom: 16px;
        }
        .brand-headline span {
            background: linear-gradient(90deg, var(--green), var(--blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .brand-sub {
            font-size: 14px;
            color: rgba(255,255,255,0.38);
            line-height: 1.6;
            max-width: 340px;
        }

        /* Feature list */
        .features { display: flex; flex-direction: column; gap: 10px; margin-top: 36px; }
        .feat {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 16px;
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.06);
            background: rgba(255,255,255,0.026);
            backdrop-filter: blur(4px);
            transition: all 0.35s ease;
            cursor: default;
        }
        .feat:hover {
            border-color: rgba(255,255,255,0.13);
            background: rgba(255,255,255,0.05);
            transform: translateX(5px);
        }
        .feat-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .feat-info p:first-child { font-size: 12.5px; font-weight: 700; color: rgba(255,255,255,0.88); }
        .feat-info p:last-child  { font-size: 11px; color: rgba(255,255,255,0.32); margin-top: 1px; }

        /* Status pill at bottom */
        .brand-bottom { position: relative; z-index: 2; }
        .status-pill {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 14px;
            border-radius: 999px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.08);
            font-size: 11px; color: rgba(255,255,255,0.35);
            font-weight: 600;
        }
        .status-dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 6px var(--green);
            animation: pulseDot 2s ease-in-out infinite;
        }
        @keyframes pulseDot {
            0%, 100% { box-shadow: 0 0 4px var(--green); }
            50%       { box-shadow: 0 0 12px var(--green); }
        }

        /* ══════════════════════════════════
           RIGHT FORM PANEL
        ══════════════════════════════════ */
        .form-panel {
            width: 420px;
            flex-shrink: 0;
            position: relative;
            padding: 52px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: rgba(13, 20, 35, 0.75);
            backdrop-filter: blur(40px) saturate(160%);
            border-left: 1px solid rgba(255,255,255,0.07);
        }

        /* Spotlight on hover */
        .form-panel::before {
            content: '';
            position: absolute;
            top: -30%; left: -30%;
            width: 160%; height: 80%;
            background: radial-gradient(ellipse 60% 50% at var(--mx, 50%) var(--my, 0%), rgba(52,168,83,0.08), transparent 65%);
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .form-eyebrow {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 4px 12px;
            border-radius: 999px;
            background: rgba(52,168,83,0.1);
            border: 1px solid rgba(52,168,83,0.2);
            margin-bottom: 20px;
            width: fit-content;
        }
        .form-eyebrow span { font-size: 10.5px; font-weight: 700; color: var(--green); text-transform: uppercase; letter-spacing: 0.08em; }

        .form-title { font-size: 28px; font-weight: 900; color: white; letter-spacing: -0.04em; margin-bottom: 6px; }
        .form-sub   { font-size: 13px; color: rgba(255,255,255,0.35); margin-bottom: 36px; }

        /* Error box */
        .error-box {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 16px;
            border-radius: 12px;
            background: rgba(239,68,68,0.08);
            border: 1px solid rgba(239,68,68,0.2);
            border-left: 3px solid #ef4444;
            margin-bottom: 22px;
        }
        .error-box span { font-size: 12.5px; color: #fca5a5; font-weight: 500; line-height: 1.5; }

        /* Input */
        .input-label {
            display: block;
            font-size: 10px; font-weight: 700;
            color: rgba(255,255,255,0.35);
            text-transform: uppercase; letter-spacing: 0.12em;
            margin-bottom: 8px;
        }
        .input-shell {
            display: flex; align-items: center;
            height: 50px;
            border-radius: 13px;
            border: 1.5px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            transition: all 0.25s;
            overflow: hidden;
            position: relative;
        }
        .input-shell::after {
            content: '';
            position: absolute;
            inset: 0; border-radius: 13px;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(52,168,83,0.06), transparent 70%);
            opacity: 0;
            transition: opacity 0.25s;
            pointer-events: none;
        }
        .input-shell:focus-within {
            border-color: rgba(52,168,83,0.5);
            background: rgba(52,168,83,0.04);
            box-shadow: 0 0 0 4px rgba(52,168,83,0.1), inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .input-shell:focus-within::after { opacity: 1; }
        .input-ico {
            width: 50px; display: flex; align-items: center; justify-content: center;
            color: rgba(255,255,255,0.25);
            flex-shrink: 0;
            transition: color 0.25s;
        }
        .input-shell:focus-within .input-ico { color: var(--green); }
        .input-field {
            flex: 1; height: 100%;
            border: none; background: transparent;
            font-size: 14px; color: white; font-weight: 500;
            outline: none; font-family: 'Inter', sans-serif;
            padding-right: 14px;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.18); font-weight: 400; }
        .toggle-btn {
            width: 46px; height: 100%; background: none; border: none;
            cursor: pointer; color: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            transition: color 0.2s; flex-shrink: 0;
        }
        .toggle-btn:hover { color: rgba(255,255,255,0.55); }

        /* Submit */
        .submit-btn {
            width: 100%; height: 52px;
            display: flex; align-items: center; justify-content: center; gap: 9px;
            font-size: 14px; font-weight: 800; color: white;
            border: none; border-radius: 14px; cursor: pointer;
            background: linear-gradient(135deg, #34A853 0%, #2D9248 100%);
            box-shadow: 0 6px 24px rgba(52,168,83,0.45), inset 0 1px 0 rgba(255,255,255,0.15);
            transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
            position: relative; overflow: hidden;
            letter-spacing: 0.01em;
        }
        .submit-btn::before {
            content: '';
            position: absolute; top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s ease;
        }
        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 36px rgba(52,168,83,0.55), inset 0 1px 0 rgba(255,255,255,0.2);
        }
        .submit-btn:hover::before { left: 100%; }
        .submit-btn:active { transform: translateY(0); }
        .submit-btn:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        /* Separator */
        .sep { display: flex; align-items: center; gap: 12px; margin: 20px 0; }
        .sep-line { flex: 1; height: 1px; background: rgba(255,255,255,0.06); }
        .sep span { font-size: 10px; color: rgba(255,255,255,0.2); white-space: nowrap; text-transform: uppercase; letter-spacing: 0.1em; }

        /* Footer */
        .form-footer { margin-top: 28px; text-align: center; }
        .form-footer p { font-size: 11px; color: rgba(255,255,255,0.2); }

        /* ══════════════════════════════════
           ANIMATIONS
        ══════════════════════════════════ */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .a0 { animation: fadeIn  0.6s ease-out 0.0s both; }
        .a1 { animation: fadeUp  0.6s ease-out 0.05s both; }
        .a2 { animation: fadeUp  0.6s ease-out 0.15s both; }
        .a3 { animation: fadeUp  0.6s ease-out 0.25s both; }
        .a4 { animation: fadeUp  0.6s ease-out 0.35s both; }
        .a5 { animation: fadeUp  0.6s ease-out 0.42s both; }
        .a6 { animation: fadeUp  0.6s ease-out 0.50s both; }

        .b1 { animation: fadeUp  0.7s ease-out 0.1s both; }
        .b2 { animation: fadeUp  0.7s ease-out 0.22s both; }
        .b3 { animation: fadeUp  0.7s ease-out 0.34s both; }

        /* ══════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════ */
        @media (max-width: 860px) {
            .brand { display: none; }
            .layout { width: 100%; height: 100vh; border-radius: 0; }
            .form-panel { width: 100%; padding: 48px 28px; }
        }
    </style>
</head>
<body>
<div class="scene">
    {{-- Particles --}}
    <canvas id="particles"></canvas>

    {{-- Ambient blobs --}}
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    <div class="blob blob-3"></div>
    <div class="grid-overlay"></div>

    {{-- Layout --}}
    <div class="layout a0" id="layout">

        {{-- ══ LEFT BRAND PANEL ══ --}}
        <div class="brand">
            <div class="logo-glow"></div>

            <div class="brand-top">
                {{-- Logo badge --}}
                <div class="logo-badge b1">
                    <div class="logo-icon">
                        <img src="{{ asset('images/logo-ptpn.png') }}" alt="PTPN 1"
                             onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<svg viewBox=\'0 0 32 32\' fill=\'none\' style=\'width:22px;height:22px\'><path d=\'M8 28 C2 22, 3 12, 12 8 C14 7, 16 8, 17 10 C18 13, 15 18, 12 21 C9 24, 8 26, 8 28Z\' fill=\'white\'/><path d=\'M16 20 C15 15, 16 9, 17 5 C18 3, 19 2, 20 5 C21 9, 22 14, 19 18 C18 19, 17 20, 16 20Z\' fill=\'rgba(255,255,255,0.7)\'/><path d=\'M24 28 C30 22, 28 12, 20 9 C19 8, 18 9, 17 11 C17 13, 19 17, 21 20 C23 23, 24 25, 24 28Z\' fill=\'rgba(255,255,255,0.5)\'/></svg>'">
                    </div>
                    <div class="logo-text">
                        <p>PT Perkebunan Nusantara 1</p>
                        <p>Warehouse Management System</p>
                    </div>
                </div>

                {{-- Headline --}}
                <h1 class="brand-headline b2">
                    Kelola Gudang<br>
                    dengan <span>Presisi AI</span>
                </h1>
                <p class="brand-sub b3">
                    Platform manajemen stok karet alam terintegrasi dengan teknologi OCR berbasis AI untuk akurasi data real-time.
                </p>

                {{-- Features --}}
                <div class="features">
                    @foreach([
                        ['icon' => 'scan-line',   'bg' => 'rgba(52,168,83,0.15)',  'color' => '#34A853', 'title' => 'Smart OCR Scanner',    'desc' => 'Ekstraksi data dokumen otomatis',       'delay' => 'b1'],
                        ['icon' => 'bar-chart-3', 'bg' => 'rgba(74,173,228,0.15)', 'color' => '#4AADE4', 'title' => 'Real-time Analytics',   'desc' => 'Monitor stok & pengiriman langsung',    'delay' => 'b2'],
                        ['icon' => 'truck',       'bg' => 'rgba(245,166,35,0.15)', 'color' => '#F5A623', 'title' => 'FIFO Management',       'desc' => 'Alokasi otomatis berdasarkan urutan',   'delay' => 'b3'],
                    ] as $f)
                    <div class="feat {{ $f['delay'] }}">
                        <div class="feat-icon" style="background:{{ $f['bg'] }};">
                            <i data-lucide="{{ $f['icon'] }}" style="width:17px;height:17px;color:{{ $f['color'] }};"></i>
                        </div>
                        <div class="feat-info">
                            <p>{{ $f['title'] }}</p>
                            <p>{{ $f['desc'] }}</p>
                        </div>
                        <i data-lucide="chevron-right" style="width:14px;height:14px;color:rgba(255,255,255,0.15);margin-left:auto;flex-shrink:0;"></i>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="brand-bottom b3">
                <div class="status-pill">
                    <div class="status-dot"></div>
                    Sistem Aktif · PTPN 1 v2.0
                </div>
            </div>
        </div>

        {{-- ══ RIGHT FORM PANEL ══ --}}
        <div class="form-panel" id="formPanel" x-data="{ showPwd: false, loading: false }">

            {{-- Eyebrow --}}
            <div class="form-eyebrow a1">
                <i data-lucide="lock" style="width:11px;height:11px;color:var(--green);"></i>
                <span>Secure Login</span>
            </div>

            <h2 class="form-title a2">Selamat Datang 👋</h2>
            <p class="form-sub a3">Masuk untuk melanjutkan ke dashboard WMS</p>

            {{-- Error --}}
            @if($errors->any())
            <div class="error-box a2">
                <i data-lucide="alert-circle" style="width:16px;height:16px;color:#f87171;flex-shrink:0;margin-top:1px;"></i>
                <span>@foreach($errors->all() as $e){{ $e }}@endforeach</span>
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" @submit="loading = true">
                @csrf

                {{-- Email --}}
                <div style="margin-bottom:16px;" class="a4">
                    <label class="input-label">Email</label>
                    <div class="input-shell">
                        <div class="input-ico">
                            <i data-lucide="mail" style="width:16px;height:16px;"></i>
                        </div>
                        <input class="input-field" type="email" name="email"
                               placeholder="nama@ptpn1.co.id"
                               value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                {{-- Password --}}
                <div style="margin-bottom:28px;" class="a5">
                    <label class="input-label">Password</label>
                    <div class="input-shell">
                        <div class="input-ico">
                            <i data-lucide="lock" style="width:16px;height:16px;"></i>
                        </div>
                        <input class="input-field"
                               :type="showPwd ? 'text' : 'password'"
                               name="password" placeholder="••••••••" required>
                        <button type="button" class="toggle-btn" @click="showPwd = !showPwd">
                            <template x-if="!showPwd">
                                <i data-lucide="eye" style="width:15px;height:15px;"></i>
                            </template>
                            <template x-if="showPwd">
                                <i data-lucide="eye-off" style="width:15px;height:15px;"></i>
                            </template>
                        </button>
                    </div>
                </div>

                {{-- Submit --}}
                <button type="submit" class="submit-btn a6" :disabled="loading">
                    <template x-if="!loading">
                        <span style="display:flex;align-items:center;gap:9px;">
                            <i data-lucide="log-in" style="width:17px;height:17px;"></i>
                            Masuk ke Sistem
                        </span>
                    </template>
                    <template x-if="loading">
                        <span style="display:flex;align-items:center;gap:10px;">
                            <svg style="width:17px;height:17px;animation:spin 0.85s linear infinite;" fill="none" viewBox="0 0 24 24">
                                <circle style="opacity:0.2;" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path style="opacity:0.8;" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Memproses...
                        </span>
                    </template>
                </button>
            </form>

            <div class="sep">
                <div class="sep-line"></div>
                <span>PT Perkebunan Nusantara 1</span>
                <div class="sep-line"></div>
            </div>

            <div class="form-footer">
                <p>© {{ date('Y') }} PTPN 1 · Semua hak dilindungi</p>
            </div>
        </div>
    </div>{{-- /.layout --}}
</div>{{-- /.scene --}}

<script>
/* ═══════════════════════════════════
   PARTICLE SYSTEM
═══════════════════════════════════ */
(function() {
    const canvas = document.getElementById('particles');
    const ctx    = canvas.getContext('2d');
    let W, H, particles = [], mouse = { x: -9999, y: -9999 };

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    const COLORS = ['rgba(52,168,83,', 'rgba(74,173,228,', 'rgba(245,166,35,'];

    function Particle() {
        this.reset();
    }
    Particle.prototype.reset = function() {
        this.x    = Math.random() * W;
        this.y    = Math.random() * H;
        this.vx   = (Math.random() - 0.5) * 0.35;
        this.vy   = (Math.random() - 0.5) * 0.35;
        this.r    = Math.random() * 1.5 + 0.4;
        this.a    = Math.random() * 0.5 + 0.1;
        this.col  = COLORS[Math.floor(Math.random() * COLORS.length)];
        this.life = 0;
        this.maxLife = Math.random() * 400 + 200;
    };
    Particle.prototype.update = function() {
        // Slight mouse repulsion
        let dx = this.x - mouse.x, dy = this.y - mouse.y;
        let dist = Math.sqrt(dx*dx + dy*dy);
        if (dist < 100) {
            this.vx += (dx / dist) * 0.04;
            this.vy += (dy / dist) * 0.04;
        }
        // Damping
        this.vx *= 0.99; this.vy *= 0.99;
        this.x += this.vx; this.y += this.vy;
        this.life++;
        if (this.x < 0 || this.x > W || this.y < 0 || this.y > H || this.life > this.maxLife) {
            this.reset();
        }
    };
    Particle.prototype.draw = function() {
        const fade = this.life < 30 ? this.life / 30 : (this.life > this.maxLife - 30 ? (this.maxLife - this.life) / 30 : 1);
        ctx.beginPath();
        ctx.arc(this.x, this.y, this.r, 0, Math.PI * 2);
        ctx.fillStyle = this.col + (this.a * fade) + ')';
        ctx.fill();
    };

    function initParticles(n) {
        for (let i = 0; i < n; i++) particles.push(new Particle());
    }

    function connectParticles() {
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                let dx = particles[i].x - particles[j].x;
                let dy = particles[i].y - particles[j].y;
                let d  = Math.sqrt(dx*dx + dy*dy);
                if (d < 110) {
                    ctx.strokeStyle = 'rgba(255,255,255,' + (0.03 * (1 - d / 110)) + ')';
                    ctx.lineWidth = 0.5;
                    ctx.beginPath();
                    ctx.moveTo(particles[i].x, particles[i].y);
                    ctx.lineTo(particles[j].x, particles[j].y);
                    ctx.stroke();
                }
            }
        }
    }

    function loop() {
        ctx.clearRect(0, 0, W, H);
        connectParticles();
        particles.forEach(p => { p.update(); p.draw(); });
        requestAnimationFrame(loop);
    }

    resize();
    initParticles(90);
    loop();
    window.addEventListener('resize', resize);
    window.addEventListener('mousemove', e => { mouse.x = e.clientX; mouse.y = e.clientY; });
})();

/* ═══════════════════════════════════
   FORM PANEL SPOTLIGHT
═══════════════════════════════════ */
const panel = document.getElementById('formPanel');
if (panel) {
    panel.addEventListener('mousemove', e => {
        const rect = panel.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / rect.width  * 100).toFixed(1);
        const y = ((e.clientY - rect.top)  / rect.height * 100).toFixed(1);
        panel.style.setProperty('--mx', x + '%');
        panel.style.setProperty('--my', y + '%');
    });
}

lucide.createIcons();

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(registration => {
            console.log('SW registered: ', registration);
        }).catch(registrationError => {
            console.log('SW registration failed: ', registrationError);
        });
    });
}
</script>
</body>
</html>