<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'WMS PTPN I' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }

        [x-cloak] {
            display: none !important;
        }

        .sidebar {
            position: fixed;
            inset: 0;
            right: auto;
            z-index: 50;
            width: 270px;
            background: linear-gradient(180deg, #1A2332 0%, #0F1923 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            color: white;
            display: flex;
            flex-direction: column;
            height: 100vh;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .sidebar.mobile-hidden {
            transform: translateX(-100%);
        }

        .main-area {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: 270px;
        }

        .top-bar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            padding: 0 24px;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
        }

        @media (max-width: 1023px) {
            .main-area {
                margin-left: 0;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-show {
                transform: translateX(0);
            }

            .mobile-toggle {
                display: block !important;
            }
        }

        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0) !important;
            }

            .mobile-toggle {
                display: none !important;
            }
        }

        .mesh-bg {
            background-color: #F4F7FA;
            background-image:
                radial-gradient(ellipse at 20% 50%, rgba(52, 168, 83, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(74, 173, 228, 0.04) 0%, transparent 50%),
                radial-gradient(ellipse at 60% 80%, rgba(245, 166, 35, 0.03) 0%, transparent 50%);
        }

        .glass {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 12px;
            font-size: 13px;
            font-weight: 500;
            border-radius: 8px;
            color: #94a3b8;
            transition: all 0.2s;
            position: relative;
            text-decoration: none;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #e2e8f0;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            border-radius: 0 4px 4px 0;
            background: #34A853;
            transition: height 0.2s;
        }

        .nav-link:hover::before {
            height: 60%;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.08);
            color: white;
        }

        .nav-link.active::before {
            height: 60%;
        }

        .nav-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .nav-icon.default {
            background: rgba(255, 255, 255, 0.04);
            color: #64748b;
        }

        .nav-icon.green {
            background: rgba(52, 168, 83, 0.2);
            color: #34A853;
        }

        .nav-icon.blue {
            background: rgba(74, 173, 228, 0.2);
            color: #4AADE4;
        }

        .nav-icon.orange {
            background: rgba(245, 166, 35, 0.2);
            color: #F5A623;
        }

        .card-glow {
            position: relative;
            overflow: hidden;
        }

        .card-glow::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #34A853, #4AADE4, #F5A623);
            opacity: 0;
            transition: opacity 0.3s;
        }

        .card-glow:hover::after {
            opacity: 1;
        }

        .bg-green-10 {
            background: rgba(52, 168, 83, 0.1);
        }

        .bg-green-20 {
            background: rgba(52, 168, 83, 0.2);
        }

        .bg-blue-10 {
            background: rgba(74, 173, 228, 0.1);
        }

        .bg-blue-20 {
            background: rgba(74, 173, 228, 0.2);
        }

        .bg-orange-10 {
            background: rgba(245, 166, 35, 0.1);
        }

        .bg-orange-20 {
            background: rgba(245, 166, 35, 0.2);
        }

        .text-ptpn-green {
            color: #34A853;
        }

        .text-ptpn-blue {
            color: #4AADE4;
        }

        .text-ptpn-orange {
            color: #F5A623;
        }

        .bg-green-gradient {
            background: linear-gradient(135deg, #34A853, #2D9248);
        }

        .bg-blue-gradient {
            background: linear-gradient(135deg, #4AADE4, #3D9AD1);
        }

        .bg-orange-gradient {
            background: linear-gradient(135deg, #F5A623, #E09515);
        }

        .shadow-green {
            box-shadow: 0 4px 14px rgba(52, 168, 83, 0.25);
        }

        .shadow-blue {
            box-shadow: 0 4px 14px rgba(74, 173, 228, 0.25);
        }

        .shadow-orange {
            box-shadow: 0 4px 14px rgba(245, 166, 35, 0.25);
        }

        .card-icon-bg {
            position: absolute;
            right: 0;
            top: 0;
            padding: 16px;
            opacity: 0.07;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(12px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-up {
            animation: fadeUp 0.4s ease-out forwards;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #34A853;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(52, 168, 83, 0.5);
            }

            50% {
                box-shadow: 0 0 0 6px rgba(52, 168, 83, 0);
            }
        }

        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 40;
            background: rgba(15, 25, 35, 0.6);
            backdrop-filter: blur(4px);
        }

        .flash-success {
            background: rgba(52, 168, 83, 0.1);
            border: 1px solid rgba(52, 168, 83, 0.2);
            color: #2D9248;
        }

        .flash-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }
    </style>
</head>

<body class="antialiased mesh-bg" style="margin: 0; min-height: 100vh;" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity class="sidebar-overlay lg:hidden"></div>

    <aside class="sidebar" :class="sidebarOpen ? 'mobile-show' : ''">
        <div
            style="display: flex; align-items: center; gap: 12px; padding: 0 24px; height: 72px; border-bottom: 1px solid rgba(255,255,255,0.06);">
            <div style="width: 36px; height: 36px; flex-shrink: 0;">
                <img src="{{ asset('images/logo-ptpn.png') }}" alt="PTPN1"
                    style="width: 100%; height: 100%; object-fit: contain;"
                    onerror="this.onerror=null; this.src='{{ asset('images/logo-ptpn1.svg') }}'">
            </div>
            <div>
                <h1 style="font-size: 15px; font-weight: 700; color: white; margin: 0; line-height: 1;">PTPN 1</h1>
                <p
                    style="font-size: 10px; font-weight: 500; color: #64748b; margin: 2px 0 0 0; text-transform: uppercase; letter-spacing: 0.15em;">
                    Warehouse System</p>
            </div>
        </div>

        <nav style="flex: 1; padding: 20px 12px; overflow-y: auto;">
            <p
                style="padding: 0 12px; font-size: 10px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 12px 0;">
                Menu Utama</p>

            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('dashboard') ? 'green' : 'default' }}">
                    <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                </div>Dashboard
            </a>
            <a href="{{ route('stocks.index') }}" class="nav-link {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('stocks.*') ? 'green' : 'default' }}">
                    <i data-lucide="package" style="width: 18px; height: 18px;"></i>
                </div>Manajemen Stok
            </a>
            <a href="{{ route('ocr.index', ['type' => 'inbound']) }}"
                class="nav-link {{ request()->routeIs('ocr.*') && request('type') != 'outbound' ? 'active' : '' }}">
                <div
                    class="nav-icon {{ request()->routeIs('ocr.*') && request('type') != 'outbound' ? 'green' : 'default' }}">
                    <i data-lucide="arrow-down-to-line" style="width: 18px; height: 18px;"></i>
                </div>Inbound (Masuk)
            </a>
            <a href="{{ route('ocr.index', ['type' => 'outbound']) }}"
                class="nav-link {{ request()->routeIs('ocr.*') && request('type') == 'outbound' ? 'active' : '' }}">
                <div
                    class="nav-icon {{ request()->routeIs('ocr.*') && request('type') == 'outbound' ? 'orange' : 'default' }}">
                    <i data-lucide="arrow-up-from-line" style="width: 18px; height: 18px;"></i>
                </div>Outbound (Keluar)
            </a>
            <a href="{{ route('shipments.index') }}"
                class="nav-link {{ request()->routeIs('shipments.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('shipments.*') ? 'orange' : 'default' }}">
                    <i data-lucide="truck" style="width: 18px; height: 18px;"></i>
                </div>Data Pengiriman
            </a>
            <a href="{{ route('stock-opname.index') }}"
                class="nav-link {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('stock-opname.*') ? 'green' : 'default' }}">
                    <i data-lucide="clipboard-check" style="width: 18px; height: 18px;"></i>
                </div>Stock Opname
            </a>

            <div style="padding-top: 20px; padding-bottom: 8px;">
                <p
                    style="padding: 0 12px; font-size: 10px; font-weight: 600; color: #475569; text-transform: uppercase; letter-spacing: 0.15em; margin: 0 0 12px 0;">
                    Laporan</p>
            </div>
            <a href="{{ route('reports.index') }}"
                class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('reports.*') ? 'blue' : 'default' }}">
                    <i data-lucide="file-bar-chart" style="width: 18px; height: 18px;"></i>
                </div>Laporan & Analitik
            </a>
        </nav>

        <div style="padding: 12px 16px; border-top: 1px solid rgba(255,255,255,0.06);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div
                    style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #34A853, #4AADE4); display: flex; align-items: center; justify-content: center; color: white; font-size: 12px; font-weight: 700; flex-shrink: 0;">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div style="flex: 1; min-width: 0;">
                    <p
                        style="font-size: 13px; font-weight: 600; color: white; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ Auth::user()->name ?? 'User' }}
                    </p>
                    <p style="font-size: 10px; color: #64748b; margin: 2px 0 0 0;">
                        {{ ucfirst(Auth::user()->role ?? 'Role') }}
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        style="padding: 6px; border-radius: 8px; background: none; border: none; color: #64748b; cursor: pointer; display: flex;"
                        title="Keluar">
                        <i data-lucide="log-out" style="width: 16px; height: 16px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="main-area">
        <header class="top-bar">
            <div style="display: flex; align-items: center; gap: 16px;">
                <button @click="sidebarOpen = true" class="mobile-toggle"
                    style="background: none; border: none; color: #64748b; cursor: pointer; display: none;">
                    <i data-lucide="menu" style="width: 20px; height: 20px;"></i>
                </button>
                <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                    <span style="color: #94a3b8;"><i data-lucide="home" style="width: 16px; height: 16px;"></i></span>
                    <i data-lucide="chevron-right" style="width: 12px; height: 12px; color: #cbd5e1;"></i>
                    <span style="color: #475569; font-weight: 500;">{{ $header ?? 'Dashboard' }}</span>
                </div>
            </div>
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #94a3b8;">
                    <div class="pulse-dot"></div><span>Online</span>
                </div>
                <div style="height: 20px; width: 1px; background: #e2e8f0;"></div>
                <span
                    style="font-size: 12px; font-weight: 500; color: #64748b;">{{ now()->translatedFormat('l, d M Y') }}</span>
            </div>
        </header>

        <main style="flex: 1; padding: 20px 24px; overflow-x: hidden;">
            @if(isset($header))
                <div class="animate-fade-up"
                    style="margin-bottom: 28px; display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px;">
                    <div>
                        <h2 style="font-size: 1.5rem; font-weight: 700; color: #1e293b; margin: 0;">{{ $header }}</h2>
                        @if(isset($subheader))
                            <p style="margin: 4px 0 0 0; font-size: 14px; color: #64748b;">{{ $subheader }}</p>
                        @endif
                    </div>
                    @if(isset($actions))
                        <div style="display: flex; gap: 8px;">{{ $actions }}</div>
                    @endif
                </div>
            @endif

            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                    class="flash-success"
                    style="margin-bottom: 24px; padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; font-size: 13px;">
                    <div class="bg-green-20"
                        style="width: 32px; height: 32px; border-radius: 8px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i data-lucide="check-circle-2" class="text-ptpn-green" style="width: 16px; height: 16px;"></i>
                    </div>
                    {!! session('success') !!}
                </div>
            @endif
            @if(session('error'))
                <div class="flash-error"
                    style="margin-bottom: 24px; padding: 12px 16px; border-radius: 12px; display: flex; align-items: center; font-size: 13px;">
                    <div
                        style="width: 32px; height: 32px; border-radius: 8px; background: #fee2e2; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <i data-lucide="alert-circle" style="width: 16px; height: 16px; color: #ef4444;"></i>
                    </div>
                    {{ session('error') }}
                </div>
            @endif

            <div class="animate-fade-up" style="animation-delay: 0.1s;">
                {{ $slot }}
            </div>
        </main>
    </div>

    <script>lucide.createIcons();</script>
</body>

</html>