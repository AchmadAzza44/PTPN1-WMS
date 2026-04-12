<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <!-- PWA Setup -->
    <meta name="theme-color" content="#34A853">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-ptpn.png') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="notif-api-url" content="{{ route('notifications.api') }}">
    <title>{{ config('app.name', 'PTPN 1 WMS') }} - @yield('title')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800;0,14..32,900;1,14..32,400&family=JetBrains+Mono:wght@400;600;700&display=swap"
        rel="stylesheet">

    <!-- Scripts & Icons -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            /* Brand colors */
            --green: #34A853;
            --green-dark: #2D9248;
            --green-light: #4eca6d;
            --orange: #F5A623;
            --orange-dark: #E09515;
            --orange-light: #fbbf60;
            --blue: #4AADE4;
            --blue-dark: #3D9AD1;
            --blue-light: #7ac8ec;
            --red: #ef4444;
            --red-dark: #dc2626;

            /* Layout */
            --sidebar-w: 260px;
            --sidebar-w-sm: 70px;
            --topbar-h: 64px;

            /* Surface */
            --bg: #EFF3F8;
            --bg2: #E8EDF4;
            --card: rgba(255, 255, 255, 0.96);
            --card-hover: rgba(255, 255, 255, 1);
            --border: rgba(203, 213, 225, 0.55);
            --border-strong: rgba(148, 163, 184, 0.4);

            /* Typography */
            --text-primary: #0F172A;
            --text-secondary: #374151;
            --text-muted: #94a3b8;
            --font-mono: 'JetBrains Mono', 'Fira Code', monospace;

            /* Sidebar */
            --sidebar-bg: #0A1628;
            --sidebar-bg2: #0D1E36;
            --sidebar-hover: rgba(255, 255, 255, 0.055);
            --sidebar-active: rgba(52, 168, 83, 0.16);
            --sidebar-text: #8aa6bb;
            --sidebar-text-active: #f0f6ff;

            /* Radius */
            --r-sm: 8px;
            --r-md: 12px;
            --r-lg: 16px;
            --r-xl: 20px;
            --r-2xl: 24px;

            /* Shadows */
            --shadow-sm: 0 1px 4px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 16px rgba(0, 0, 0, 0.07);
            --shadow-lg: 0 8px 32px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 48px rgba(0, 0, 0, 0.12);
            --shadow-green: 0 4px 18px rgba(52, 168, 83, 0.28);
            --shadow-orange: 0 4px 18px rgba(245, 166, 35, 0.28);
            --shadow-blue: 0 4px 18px rgba(74, 173, 228, 0.28);
            --shadow-card: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
            --shadow-card-hover: 0 8px 28px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.04);

            /* Transitions */
            --ease: cubic-bezier(0.4, 0, 0.2, 1);
            --ease-bounce: cubic-bezier(0.34, 1.56, 0.64, 1);
            --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.1);
            --duration: 220ms;
        }

        *,
        *::before,
        *::after {
            box-sizing: border-box;
        }

        body,
        button,
        input,
        select,
        textarea {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        code,
        kbd,
        samp,
        .mono {
            font-family: var(--font-mono);
        }

        [x-cloak] {
            display: none !important;
        }

        /* ══════════════════════════════════════════
           SCROLL
        ══════════════════════════════════════════ */
        ::-webkit-scrollbar {
            width: 4px;
            height: 4px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* ══════════════════════════════════════════
           BODY / BACKGROUND
        ══════════════════════════════════════════ */
        body {
            margin: 0;
            min-height: 100vh;
            max-width: 100vw;
            overflow-x: hidden;
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse 80% 60% at 5% 25%, rgba(52, 168, 83, 0.08) 0%, transparent 65%),
                radial-gradient(ellipse 60% 50% at 95% 5%, rgba(74, 173, 228, 0.07) 0%, transparent 65%),
                radial-gradient(ellipse 50% 55% at 60% 95%, rgba(245, 166, 35, 0.06) 0%, transparent 65%),
                radial-gradient(circle at 50% 50%, rgba(52, 168, 83, 0.015) 0%, transparent 50%);
            background-attachment: fixed;
        }

        /* ══════════════════════════════════════════
           SIDEBAR
        ══════════════════════════════════════════ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            z-index: 50;
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            background-image:
                linear-gradient(180deg, rgba(52, 168, 83, 0.07) 0%, transparent 35%),
                radial-gradient(ellipse 80% 40% at 50% 0%, rgba(52, 168, 83, 0.12) 0%, transparent 55%),
                radial-gradient(ellipse 60% 30% at 0% 100%, rgba(74, 173, 228, 0.07) 0%, transparent 55%);
            border-right: 1px solid rgba(52, 168, 83, 0.08);
            color: white;
            display: flex;
            flex-direction: column;
            transition: width 0.35s var(--ease-spring), transform 0.35s var(--ease);
            overflow: hidden;
            box-shadow: 4px 0 32px rgba(0, 0, 0, 0.35), 1px 0 0 rgba(52, 168, 83, 0.1);
        }

        .sidebar.collapsed {
            width: var(--sidebar-w-sm);
        }

        .sidebar.mobile-hidden {
            transform: translateX(-100%);
        }

        /* Logo area */
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 18px;
            height: 72px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
            white-space: nowrap;
            overflow: hidden;
        }

        .sidebar-logo-img {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, rgba(52, 168, 83, 0.2), rgba(74, 173, 228, 0.2));
            padding: 4px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-logo-text {
            transition: opacity 0.2s, transform 0.2s;
            transform-origin: left;
        }

        .sidebar.collapsed .sidebar-logo-text {
            opacity: 0;
            transform: scaleX(0);
        }

        /* Collapse toggle */
        .sidebar-toggle {
            position: absolute;
            right: -12px;
            top: 80px;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--green);
            border: 2px solid var(--sidebar-bg);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 60;
            transition: all 0.2s;
            box-shadow: 0 2px 8px rgba(52, 168, 83, 0.5);
        }

        .sidebar-toggle:hover {
            transform: scale(1.15);
            box-shadow: 0 4px 14px rgba(52, 168, 83, 0.6);
        }

        .sidebar-toggle svg {
            width: 12px;
            height: 12px;
            transition: transform 0.3s;
        }

        .sidebar.collapsed .sidebar-toggle svg {
            transform: rotate(180deg);
        }

        /* Nav */
        nav.sidebar-nav {
            flex: 1;
            padding: 12px 8px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .nav-section-label {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #3b5268;
            padding: 0 10px;
            margin: 8px 0 8px 0;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: opacity 0.2s;
        }

        .nav-section-label::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.05);
        }

        .sidebar.collapsed .nav-section-label {
            opacity: 0;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 10px;
            border-radius: 11px;
            color: var(--sidebar-text);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            margin-bottom: 2px;
            transition: all var(--duration) var(--ease);
            white-space: nowrap;
            overflow: hidden;
            position: relative;
        }

        .nav-link:hover {
            background: var(--sidebar-hover);
            color: #c8d9e8;
            transform: translateX(3px);
        }

        .nav-link.active {
            background: var(--sidebar-active);
            color: var(--sidebar-text-active);
        }

        .nav-link.active-orange {
            background: rgba(245, 166, 35, 0.12);
            color: #fff;
        }

        .nav-link.active-blue {
            background: rgba(74, 173, 228, 0.12);
            color: #fff;
        }

        /* Animated left bar */
        .nav-link::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            border-radius: 0 3px 3px 0;
            background: var(--green);
            transition: height 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover::before {
            height: 50%;
        }

        .nav-link.active::before {
            height: 60%;
            background: var(--green);
        }

        .nav-link.active-orange::before {
            height: 60%;
            background: var(--orange);
        }

        .nav-link.active-blue::before {
            height: 60%;
            background: var(--blue);
        }

        .nav-icon {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .nav-icon.default {
            background: rgba(255, 255, 255, 0.03);
            color: #3b5268;
        }

        .nav-icon.green {
            background: rgba(52, 168, 83, 0.18);
            color: var(--green);
        }

        .nav-icon.orange {
            background: rgba(245, 166, 35, 0.18);
            color: var(--orange);
        }

        .nav-icon.blue {
            background: rgba(74, 173, 228, 0.18);
            color: var(--blue);
        }

        .nav-link:hover .nav-icon.default {
            background: rgba(255, 255, 255, 0.07);
            color: #8aaec5;
        }

        .nav-text {
            transition: opacity 0.2s, width 0.3s;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0;
            width: 0;
        }

        /* Tooltip for collapsed icons */
        .nav-link .nav-tooltip {
            display: none;
            position: absolute;
            left: calc(var(--sidebar-w-sm) - 4px);
            background: #1e3a4a;
            color: #e2e8f0;
            font-size: 12px;
            padding: 5px 10px;
            border-radius: 6px;
            white-space: nowrap;
            pointer-events: none;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .sidebar.collapsed .nav-link:hover .nav-tooltip {
            display: block;
        }

        /* User profile */
        .sidebar-user {
            padding: 12px 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            flex-shrink: 0;
            overflow: hidden;
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            transition: background 0.2s;
            cursor: default;
        }

        .user-card:hover {
            background: rgba(255, 255, 255, 0.04);
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--green), var(--blue));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
            position: relative;
            box-shadow: 0 0 0 2px rgba(52, 168, 83, 0.3);
            transition: transform 0.3s var(--ease), box-shadow 0.3s var(--ease);
        }

        .user-card:hover .user-avatar {
            transform: scale(1.05);
            box-shadow: 0 0 0 3px rgba(52, 168, 83, 0.4), 0 4px 12px rgba(52, 168, 83, 0.2);
        }

        .user-avatar::after {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: 50%;
            border: 2px solid transparent;
            background: linear-gradient(135deg, var(--green), var(--blue)) border-box;
            -webkit-mask: linear-gradient(#fff 0 0) padding-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: destination-out;
            mask-composite: exclude;
            animation: avatarRingSpin 8s linear infinite;
        }

        @keyframes avatarRingSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .user-info {
            flex: 1;
            min-width: 0;
            transition: opacity 0.2s, width 0.3s;
        }

        .sidebar.collapsed .user-info {
            opacity: 0;
            width: 0;
        }

        .logout-btn {
            padding: 6px;
            border-radius: 8px;
            background: none;
            border: none;
            color: #3b5268;
            cursor: pointer;
            display: flex;
            flex-shrink: 0;
            transition: all 0.2s;
        }

        .logout-btn:hover {
            color: #ef4444;
            background: rgba(239, 68, 68, 0.1);
        }

        .sidebar.collapsed .logout-btn {
            display: none;
        }

        /* ══════════════════════════════════════════
           MAIN AREA
        ══════════════════════════════════════════ */
        .main-area {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin-left: var(--sidebar-w);
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-area.sidebar-collapsed {
            margin-left: var(--sidebar-w-sm);
        }

        /* ══════════════════════════════════════════
           TOP BAR
        ══════════════════════════════════════════ */
        .top-bar {
            position: sticky;
            top: 0;
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: var(--topbar-h);
            padding: 0 28px;
            background: rgba(239, 243, 248, 0.82);
            backdrop-filter: blur(28px) saturate(200%);
            -webkit-backdrop-filter: blur(28px) saturate(200%);
            border-bottom: 1px solid rgba(203, 213, 225, 0.4);
            box-shadow: 0 1px 0 rgba(255, 255, 255, 0.7), 0 4px 16px rgba(0, 0, 0, 0.03);
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
        }

        .breadcrumb-home {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background: rgba(52, 168, 83, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--green);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .topbar-divider {
            width: 1px;
            height: 22px;
            background: rgba(226, 232, 240, 0.8);
        }

        .topbar-icon-btn {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.7);
            border: 1px solid rgba(226, 232, 240, 0.7);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }

        .topbar-icon-btn:hover {
            background: white;
            color: var(--green);
            border-color: rgba(52, 168, 83, 0.3);
            box-shadow: 0 2px 10px rgba(52, 168, 83, 0.15);
            transform: translateY(-1px);
        }

        .notif-badge {
            position: absolute;
            top: -3px;
            right: -3px;
            min-width: 14px;
            height: 14px;
            border-radius: 7px;
            padding: 0 3px;
            background: var(--orange);
            border: 2px solid var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 7px;
            font-weight: 700;
            color: white;
            animation: notifPulse 2.5s ease-in-out infinite;
        }

        @keyframes notifPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        /* ══════════════════════════════════════════
           NOTIFICATION DROPDOWN PANEL
        ══════════════════════════════════════════ */
        .notif-panel-wrap {
            position: relative;
        }

        .notif-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: 340px;
            max-height: 480px;
            background: #fff;
            border: 1px solid rgba(203, 213, 225, 0.6);
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.06);
            z-index: 200;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            transform-origin: top right;
            animation: notifPanelIn 0.22s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }

        @keyframes notifPanelIn {
            from { opacity: 0; transform: scale(0.88) translateY(-8px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }

        .notif-panel-header {
            padding: 14px 16px 10px;
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-shrink: 0;
            background: linear-gradient(to bottom, #f8fafc, #fff);
        }

        .notif-panel-title {
            font-size: 13px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .enable-push-btn {
            display: none;
            font-size: 10px;
            padding: 4px 10px;
            background: rgba(52, 168, 83, 0.1);
            color: var(--green);
            border: 1px solid rgba(52, 168, 83, 0.3);
            border-radius: 6px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
        }
        .enable-push-btn:hover { background: rgba(52, 168, 83, 0.2); }

        .notif-live-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 2s infinite;
        }

        .notif-panel-body {
            flex: 1;
            overflow-y: auto;
            padding: 8px 0;
        }

        .notif-item {
            display: flex;
            gap: 10px;
            align-items: flex-start;
            padding: 10px 16px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid rgba(241,245,249,0.9);
        }

        .notif-item:hover {
            background: rgba(52, 168, 83, 0.04);
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 15px;
        }

        .notif-item-icon.warning { background: rgba(245, 166, 35, 0.12); }
        .notif-item-icon.success { background: rgba(52, 168, 83, 0.12); }
        .notif-item-icon.info    { background: rgba(74, 173, 228, 0.12); }

        .notif-item-content {
            flex: 1;
            min-width: 0;
        }

        .notif-item-title {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 2px;
            line-height: 1.3;
        }

        .notif-item-body {
            font-size: 11px;
            color: var(--text-muted);
            line-height: 1.4;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .notif-item-time {
            font-size: 10px;
            color: #b0bec5;
            margin-top: 3px;
        }

        .notif-empty {
            padding: 32px 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
        }

        .notif-empty-icon {
            font-size: 32px;
            margin-bottom: 8px;
            opacity: 0.4;
        }

        .notif-panel-footer {
            padding: 10px 16px;
            border-top: 1px solid rgba(226, 232, 240, 0.8);
            text-align: center;
            flex-shrink: 0;
            background: linear-gradient(to top, #f8fafc, #fff);
        }

        .notif-panel-footer a {
            font-size: 12px;
            font-weight: 600;
            color: var(--green);
            text-decoration: none;
        }

        .notif-panel-footer a:hover {
            text-decoration: underline;
        }

        /* ══════════════════════════════════════════
           IN-APP NOTIFICATION POPUP (WhatsApp-style)
        ══════════════════════════════════════════ */
        #notif-popup-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .notif-popup {
            background: #0A1628;
            border: 1px solid rgba(52, 168, 83, 0.3);
            border-left: 4px solid var(--orange);
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            min-width: 300px;
            max-width: 360px;
            box-shadow: 0 12px 40px rgba(0,0,0,0.4), 0 0 0 1px rgba(52,168,83,0.08);
            animation: notifPopupIn 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) both;
            pointer-events: all;
            cursor: pointer;
        }

        .notif-popup.success { border-left-color: var(--green); }
        .notif-popup.info    { border-left-color: var(--blue); }

        @keyframes notifPopupIn {
            from { opacity: 0; transform: translateX(120px) scale(0.9); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }

        .notif-popup.hiding {
            animation: notifPopupOut 0.35s ease forwards;
        }

        @keyframes notifPopupOut {
            to { opacity: 0; transform: translateX(120px) scale(0.9); }
        }

        .notif-popup-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: rgba(245, 166, 35, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-size: 18px;
        }

        .notif-popup.success .notif-popup-icon { background: rgba(52, 168, 83, 0.15); }
        .notif-popup.info    .notif-popup-icon { background: rgba(74, 173, 228, 0.15); }

        .notif-popup-content { flex: 1; min-width: 0; }

        .notif-popup-title {
            font-size: 12px;
            font-weight: 700;
            color: #e2e8f0;
            margin-bottom: 3px;
        }

        .notif-popup-body {
            font-size: 11px;
            color: #5f7d96;
            line-height: 1.4;
        }

        .notif-popup-app {
            font-size: 9px;
            color: #3b5268;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .notif-popup-close {
            background: none;
            border: none;
            color: #3b5268;
            cursor: pointer;
            padding: 2px;
            flex-shrink: 0;
            line-height: 1;
            font-size: 14px;
        }

        .notif-popup-close:hover { color: #8aa6bb; }

        .status-chip {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 20px;
            background: rgba(52, 168, 83, 0.08);
            border: 1px solid rgba(52, 168, 83, 0.15);
            font-size: 12px;
            font-weight: 600;
            color: var(--green-dark);
        }

        /* ══════════════════════════════════════════
           PULSE DOT
        ══════════════════════════════════════════ */
        .pulse-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(52, 168, 83, 0.6);
            }

            50% {
                box-shadow: 0 0 0 5px rgba(52, 168, 83, 0);
            }
        }

        /* ══════════════════════════════════════════
           CONTENT
        ══════════════════════════════════════════ */
        .page-main {
            flex: 1;
            padding: 24px 28px;
            overflow-x: hidden;
        }

        .page-header {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 28px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--text-primary);
            margin: 0;
            letter-spacing: -0.03em;
            line-height: 1.2;
        }

        .page-subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* ══════════════════════════════════════════
           GLASS & CARDS
        ══════════════════════════════════════════ */
        .glass {
            background: var(--card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--border);
        }

        .card-premium {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--r-xl);
            transition: all 0.32s var(--ease);
            box-shadow: var(--shadow-card);
        }

        .card-premium:hover {
            box-shadow: var(--shadow-card-hover);
            transform: translateY(-2px);
            border-color: rgba(200, 215, 230, 0.9);
            background: var(--card-hover);
        }

        /* Top gradient border on hover */
        .card-premium {
            position: relative;
            overflow: hidden;
        }

        .card-premium::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 2.5px;
            background: linear-gradient(90deg, var(--green), var(--blue), var(--orange));
            opacity: 0;
            transition: opacity 0.35s ease;
        }

        .card-premium:hover::before {
            opacity: 1;
        }

        /* Color variants */
        .card-premium.border-green::before {
            background: linear-gradient(90deg, var(--green), var(--green-light));
            opacity: 1;
        }

        .card-premium.border-blue::before {
            background: linear-gradient(90deg, var(--blue), var(--blue-light));
            opacity: 1;
        }

        .card-premium.border-orange::before {
            background: linear-gradient(90deg, var(--orange), var(--orange-light));
            opacity: 1;
        }

        /* ══════════════════════════════════════════
           COLOR UTILITIES
        ══════════════════════════════════════════ */
        .bg-green-10 {
            background: rgba(52, 168, 83, 0.09);
        }

        .bg-green-20 {
            background: rgba(52, 168, 83, 0.18);
        }

        .bg-blue-10 {
            background: rgba(74, 173, 228, 0.09);
        }

        .bg-blue-20 {
            background: rgba(74, 173, 228, 0.18);
        }

        .bg-orange-10 {
            background: rgba(245, 166, 35, 0.09);
        }

        .bg-orange-20 {
            background: rgba(245, 166, 35, 0.18);
        }

        .text-ptpn-green {
            color: var(--green);
        }

        .text-ptpn-green-dark {
            color: var(--green-dark);
        }

        .text-ptpn-blue {
            color: var(--blue);
        }

        .text-ptpn-orange {
            color: var(--orange);
        }

        .bg-green-gradient {
            background: linear-gradient(135deg, var(--green), var(--green-dark));
        }

        .bg-blue-gradient {
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        }

        .bg-orange-gradient {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
        }

        .shadow-green {
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.3);
        }

        .shadow-blue {
            box-shadow: 0 6px 20px rgba(74, 173, 228, 0.3);
        }

        .shadow-orange {
            box-shadow: 0 6px 20px rgba(245, 166, 35, 0.3);
        }

        .border-green-20 {
            border-color: rgba(52, 168, 83, 0.2);
        }

        .border-blue-20 {
            border-color: rgba(74, 173, 228, 0.2);
        }

        .border-orange-20 {
            border-color: rgba(245, 166, 35, 0.2);
        }

        /* ══════════════════════════════════════════
           ICON BG (decorative)
        ══════════════════════════════════════════ */
        .card-icon-bg {
            position: absolute;
            right: -4px;
            top: -4px;
            padding: 16px;
            opacity: 0.05;
            transition: opacity 0.3s;
        }

        .card-premium:hover .card-icon-bg {
            opacity: 0.1;
        }

        /* ══════════════════════════════════════════
           BUTTONS
        ══════════════════════════════════════════ */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 18px;
            border-radius: var(--r-md);
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.25s var(--ease);
            text-decoration: none;
            white-space: nowrap;
            user-select: none;
            -webkit-tap-highlight-color: transparent;
            position: relative;
            overflow: hidden;
        }

        .btn:active {
            transform: scale(0.96) translateY(1px);
        }

        /* Shimmer effect on hover */
        .btn::after {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 100%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s;
        }
        .btn:hover::after {
            left: 100%;
        }

        .btn-green {
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(52, 168, 83, 0.35);
        }

        .btn-green:hover {
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.5);
            transform: translateY(-1px);
        }

        .btn-orange {
            background: linear-gradient(135deg, var(--orange), var(--orange-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(245, 166, 35, 0.35);
        }

        .btn-orange:hover {
            box-shadow: 0 6px 20px rgba(245, 166, 35, 0.5);
            transform: translateY(-1px);
        }

        .btn-blue {
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: white;
            box-shadow: 0 4px 14px rgba(74, 173, 228, 0.35);
        }

        .btn-blue:hover {
            box-shadow: 0 6px 20px rgba(74, 173, 228, 0.5);
            transform: translateY(-1px);
        }

        .btn-ghost {
            background: rgba(255, 255, 255, 0.8);
            color: var(--text-secondary);
            border: 1px solid var(--border);
        }

        .btn-ghost:hover {
            background: white;
            color: var(--text-primary);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.07);
        }

        /* ══════════════════════════════════════════
           BADGES / PILLS
        ══════════════════════════════════════════ */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.01em;
            transition: all 0.2s var(--ease);
        }

        .badge-green {
            background: rgba(52, 168, 83, 0.08);
            color: var(--green-dark);
            border: 1px solid rgba(52, 168, 83, 0.15);
        }

        .badge-blue {
            background: rgba(74, 173, 228, 0.08);
            color: var(--blue-dark);
            border: 1px solid rgba(74, 173, 228, 0.15);
        }

        .badge-orange {
            background: rgba(245, 166, 35, 0.08);
            color: var(--orange-dark);
            border: 1px solid rgba(245, 166, 35, 0.15);
        }

        .badge-red {
            background: rgba(239, 68, 68, 0.08);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.15);
        }

        .badge-gray {
            background: rgba(148, 163, 184, 0.08);
            color: #475569;
            border: 1px solid rgba(148, 163, 184, 0.15);
        }

        /* ══════════════════════════════════════════
           TABLES
        ══════════════════════════════════════════ */
        .table-modern {
            width: 100%;
            font-size: 13px;
            border-collapse: collapse;
            color: var(--text-secondary);
        }

        .table-modern thead tr {
            background: rgba(241, 245, 249, 0.9);
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .table-modern th {
            padding: 11px 18px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: var(--text-muted);
            white-space: nowrap;
        }

        .table-modern tbody tr {
            border-bottom: 1px solid rgba(241, 245, 249, 0.9);
            transition: background 0.2s var(--ease);
        }

        .table-modern tbody tr:hover {
            background: linear-gradient(90deg, rgba(52, 168, 83, 0.03), rgba(74, 173, 228, 0.02));
        }

        .table-modern td {
            padding: 13px 18px;
            vertical-align: middle;
        }

        /* ══════════════════════════════════════════
           SECTION TITLES
        ══════════════════════════════════════════ */
        .section-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--text-primary);
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        /* ══════════════════════════════════════════
           FLASH MESSAGES
        ══════════════════════════════════════════ */
        .flash-success {
            background: rgba(52, 168, 83, 0.08);
            border: 1px solid rgba(52, 168, 83, 0.2);
            color: var(--green-dark);
            border-left: 3px solid var(--green);
        }

        .flash-error {
            background: rgba(239, 68, 68, 0.07);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #b91c1c;
            border-left: 3px solid #ef4444;
        }

        /* ══════════════════════════════════════════
           ANIMATIONS
        ══════════════════════════════════════════ */
        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(18px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes scaleIn {
            from {
                opacity: 0;
                transform: scale(0.94);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(24px) scale(0.98);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .anim-fade-up {
            animation: fadeUp 0.5s var(--ease-spring) both;
        }

        .anim-fade {
            animation: fadeIn 0.4s ease-out both;
        }

        .anim-slide-in {
            animation: slideInLeft 0.4s var(--ease-spring) both;
        }

        .anim-scale-in {
            animation: scaleIn 0.45s var(--ease-spring) both;
        }

        .anim-slide-up {
            animation: slideUp 0.5s var(--ease-spring) both;
        }

        .delay-1 { animation-delay: 0.06s; }
        .delay-2 { animation-delay: 0.12s; }
        .delay-3 { animation-delay: 0.18s; }
        .delay-4 { animation-delay: 0.24s; }
        .delay-5 { animation-delay: 0.30s; }
        .delay-6 { animation-delay: 0.36s; }

        /* ══════════════════════════════════════════
           MOBILE OVERLAY
        ══════════════════════════════════════════ */
        .sidebar-overlay {
            position: fixed;
            inset: 0;
            z-index: 40;
            background: rgba(13, 27, 42, 0.6);
            backdrop-filter: blur(4px);
        }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        .mobile-toggle {
            display: none;
        }

        /* Tablet & Mobile — sidebar tersembunyi, main area full width */
        @media (max-width: 1023px) {
            .main-area {
                margin-left: 0 !important;
            }

            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-w) !important;
            }

            .sidebar.mobile-show {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: none;
            }

            .mobile-toggle {
                display: flex !important;
            }

            /* Sembunyikan status chip & tanggal/jam di tablet kecil */
            .topbar-meta {
                display: none !important;
            }
        }

        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0) !important;
            }

            /* Bottom nav hanya muncul di mobile */
            .bottom-nav,
            .bottom-nav-more {
                display: none !important;
            }
        }

        /* Mobile — layar kecil (HP portrait) */
        @media (max-width: 640px) {
            .page-main {
                padding: 16px 14px calc(16px + 78px + env(safe-area-inset-bottom));
            }

            .top-bar {
                padding: 0 14px;
                padding-left: max(14px, env(safe-area-inset-left));
                padding-right: max(14px, env(safe-area-inset-right));
            }

            /* Sembunyikan user avatar mini di topbar HP, tapi biarkan Bell Notifikasi */
            .topbar-user {
                display: none !important;
            }

            /* Stack page header vertical di mobile */
            .page-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .page-title {
                font-size: 1.3rem;
            }

            /* Status chip compact */
            .status-chip span {
                display: none;
            }

            /* Fix Panel Notifikasi di Layar Kecil (Mobile) supaya tidak miring/kepotong */
            .notif-panel {
                position: fixed;
                top: 60px;
                right: 14px;
                left: 14px;
                width: auto;
                max-width: none;
                transform-origin: top;
            }
        }

        /* ══════════════════════════════════════════
           TABLE RESPONSIVE WRAPPER
        ══════════════════════════════════════════ */
        .table-wrap {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .table-wrap .table-modern {
            min-width: 600px;
        }

        /* ══════════════════════════════════════════
           RESPONSIVE GRID UTILITIES
        ══════════════════════════════════════════ */
        .grid-responsive {
            display: grid;
            gap: 20px;
        }

        .grid-cols-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        .grid-cols-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-cols-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        @media (max-width: 1023px) {
            .grid-cols-4 {
                grid-template-columns: repeat(2, 1fr);
            }

            .grid-cols-3 {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 640px) {

            .grid-cols-4,
            .grid-cols-3,
            .grid-cols-2 {
                grid-template-columns: 1fr;
            }

            .grid-responsive {
                gap: 12px;
            }
        }

        /* ══════════════════════════════════════════
           BOTTOM NAVIGATION BAR (Mobile PWA)
        ══════════════════════════════════════════ */
        .bottom-nav {
            position: fixed;
            bottom: 8px;
            left: 10px;
            right: 10px;
            z-index: 45;
            height: calc(60px + env(safe-area-inset-bottom));
            padding-bottom: env(safe-area-inset-bottom);
            background: rgba(10, 22, 40, 0.92);
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-around;
            padding-top: 8px;
            box-shadow: 0 -2px 20px rgba(0, 0, 0, 0.3), 0 8px 32px rgba(0, 0, 0, 0.2), inset 0 1px 0 rgba(255,255,255,0.06);
        }

        .bottom-nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            padding: 2px 12px;
            border-radius: 14px;
            text-decoration: none;
            color: #3b5268;
            transition: all 0.25s var(--ease);
            min-width: 52px;
            cursor: pointer;
            background: none;
            border: none;
            position: relative;
        }

        .bottom-nav-item:active {
            transform: scale(0.90);
        }

        .bottom-nav-item.active {
            color: var(--green);
        }

        .bottom-nav-item.active-orange {
            color: var(--orange);
        }

        .bottom-nav-item.active-blue {
            color: var(--blue);
        }

        /* Active indicator dot */
        .bottom-nav-item.active::after,
        .bottom-nav-item.active-orange::after,
        .bottom-nav-item.active-blue::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 50%;
            transform: translateX(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: currentColor;
            animation: scaleIn 0.3s var(--ease-bounce) both;
        }

        .bottom-nav-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.25s var(--ease);
        }

        .bottom-nav-item.active .bottom-nav-icon {
            background: rgba(52, 168, 83, 0.18);
            box-shadow: 0 2px 8px rgba(52, 168, 83, 0.15);
        }

        .bottom-nav-item.active-orange .bottom-nav-icon {
            background: rgba(245, 166, 35, 0.18);
            box-shadow: 0 2px 8px rgba(245, 166, 35, 0.15);
        }

        .bottom-nav-item.active-blue .bottom-nav-icon {
            background: rgba(74, 173, 228, 0.18);
            box-shadow: 0 2px 8px rgba(74, 173, 228, 0.15);
        }

        .bottom-nav-label {
            font-size: 9.5px;
            font-weight: 600;
            letter-spacing: 0.01em;
            white-space: nowrap;
        }

        /* More menu button */
        .bottom-nav-more {
            position: fixed;
            bottom: calc(72px + env(safe-area-inset-bottom));
            left: 0;
            right: 0;
            background: rgba(13, 27, 42, 0.98);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.06);
            padding: 16px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            transform: translateY(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 44;
        }

        .bottom-nav-more.open {
            transform: translateY(0);
        }

        .more-menu-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            color: #8aaec5;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .more-menu-item:active {
            background: rgba(255, 255, 255, 0.08);
        }

        .more-menu-item.active {
            color: white;
            border-color: rgba(255, 255, 255, 0.12);
        }

        /* ══════════════════════════════════════════
           PROGRESS BAR ANIMATED
        ══════════════════════════════════════════ */
        .progress-track {
            width: 100%;
            background: rgba(226, 232, 240, 0.6);
            border-radius: 999px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 999px;
            transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
            width: 0%;
            position: relative;
            overflow: hidden;
        }

        /* Animated shine on progress bar */
        .progress-fill::after {
            content: '';
            position: absolute;
            top: 0; left: -50%;
            width: 50%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            animation: progressShine 2s ease-in-out infinite;
        }

        @keyframes progressShine {
            0% { left: -50%; }
            100% { left: 150%; }
        }

        /* ══════════════════════════════════════════
           FORMS (global)
        ══════════════════════════════════════════ */
        .form-input {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1.5px solid var(--border);
            background: rgba(255, 255, 255, 0.85);
            font-size: 13px;
            color: var(--text-primary);
            transition: all 0.25s var(--ease);
            font-family: 'Inter', sans-serif;
            box-sizing: border-box;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(52, 168, 83, 0.1), 0 2px 8px rgba(52, 168, 83, 0.08);
            background: white;
        }

        .form-input:hover:not(:focus) {
            border-color: rgba(148, 163, 184, 0.5);
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 6px;
            display: block;
        }

        select.form-input {
            appearance: none;
            -webkit-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 12px center;
            padding-right: 36px;
            cursor: pointer;
        }

        textarea.form-input {
            resize: vertical;
            min-height: 90px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 18px;
        }

        /* ══════════════════════════════════════════
           SKELETON LOADER
        ══════════════════════════════════════════ */
        @keyframes shimmer {
            0% {
                background-position: -600px 0;
            }

            100% {
                background-position: 600px 0;
            }
        }

        .skeleton {
            background: linear-gradient(90deg,
                    rgba(226, 232, 240, 0.6) 25%,
                    rgba(241, 245, 249, 0.9) 50%,
                    rgba(226, 232, 240, 0.6) 75%);
            background-size: 600px 100%;
            animation: shimmer 1.4s infinite ease-in-out;
            border-radius: var(--r-md);
        }

        .skeleton-text {
            height: 14px;
            margin-bottom: 8px;
        }

        .skeleton-title {
            height: 22px;
            width: 60%;
            margin-bottom: 12px;
        }

        .skeleton-card {
            height: 100px;
        }

        /* ══════════════════════════════════════════
           TOAST NOTIFICATION
        ══════════════════════════════════════════ */
        #toast-container {
            position: fixed;
            bottom: calc(80px + env(safe-area-inset-bottom));
            right: 20px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        @media (min-width: 1024px) {
            #toast-container {
                bottom: 24px;
            }
        }

        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--r-lg);
            background: #0D1B2A;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            color: #e2e8f0;
            font-size: 13px;
            font-weight: 500;
            min-width: 260px;
            max-width: 360px;
            pointer-events: all;
            animation: toastIn 0.35s var(--ease-bounce) forwards;
        }

        .toast.hiding {
            animation: toastOut 0.25s var(--ease) forwards;
        }

        @keyframes toastIn {
            from {
                opacity: 0;
                transform: translateY(16px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes toastOut {
            from {
                opacity: 1;
                transform: translateY(0) scale(1);
            }

            to {
                opacity: 0;
                transform: translateY(8px) scale(0.96);
            }
        }

        .toast-icon {
            width: 30px;
            height: 30px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .toast-success .toast-icon {
            background: rgba(52, 168, 83, 0.2);
            color: var(--green);
        }

        .toast-error .toast-icon {
            background: rgba(239, 68, 68, 0.15);
            color: var(--red);
        }

        .toast-info .toast-icon {
            background: rgba(74, 173, 228, 0.15);
            color: var(--blue);
        }

        /* ══════════════════════════════════════════
           TABLE CARD MODE (Mobile)
        ══════════════════════════════════════════ */
        @media (max-width: 600px) {

            .table-card-mode table,
            .table-card-mode thead,
            .table-card-mode tbody,
            .table-card-mode th,
            .table-card-mode td,
            .table-card-mode tr {
                display: block;
            }

            .table-card-mode thead tr {
                display: none;
            }

            .table-card-mode tbody tr {
                background: var(--card);
                border: 1px solid var(--border);
                border-radius: var(--r-xl);
                margin-bottom: 16px;
                padding: 0;
                box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
                overflow: hidden;
            }

            .table-card-mode tbody tr:hover {
                background: var(--card-hover);
            }

            .table-card-mode td {
                padding: 12px 16px;
                display: flex;
                align-items: center;
                justify-content: space-between;
                font-size: 13px;
                border: none;
                border-bottom: 1px dashed rgba(203, 213, 225, 0.5);
                text-align: right;
            }

            .table-card-mode td:last-child {
                border-bottom: none;
                background: rgba(248, 250, 252, 0.6);
            }

            /* The Label */
            .table-card-mode td::before {
                content: attr(data-label);
                font-size: 11px;
                font-weight: 700;
                color: var(--text-muted);
                text-transform: uppercase;
                letter-spacing: 0.04em;
                margin-right: 12px;
                text-align: left;
                flex-shrink: 0;
                width: 40%;
                /* Fixed width for alignment */
            }

            /* Treat the first td as a prominent Card Header */
            .table-card-mode td:first-child {
                background: rgba(52, 168, 83, 0.06);
                border-bottom: 1px solid rgba(52, 168, 83, 0.15);
                justify-content: space-between;
                text-align: left;
                padding: 14px 16px;
            }

            .table-card-mode td:first-child::before {
                display: none;
                /* Hide label for the title */
            }

            /* Make the action buttons span full width */
            .table-card-mode td:last-child {
                justify-content: center;
                gap: 8px;
            }

            .table-card-mode td:last-child::before {
                display: none;
            }

            /* Hidden utilities for mobile */
            .table-card-mode .mobile-hidden {
                display: none !important;
            }
        }

        /* ══════════════════════════════════════════
           PWA INSTALL BANNER
        ══════════════════════════════════════════ */
        #pwa-banner {
            position: fixed;
            bottom: calc(72px + env(safe-area-inset-bottom) + 8px);
            left: 12px;
            right: 12px;
            z-index: 46;
            background: var(--sidebar-bg);
            border: 1px solid rgba(52, 168, 83, 0.3);
            border-radius: var(--r-lg);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            animation: toastIn 0.4s var(--ease-bounce) forwards;
        }

        #pwa-banner.hidden {
            display: none;
        }

        @media (min-width: 1024px) {
            #pwa-banner {
                bottom: 24px;
                left: auto;
                right: 24px;
                max-width: 360px;
            }
        }

        /* ══════════════════════════════════════════
           PAGE TRANSITION
        ══════════════════════════════════════════ */
        .page-content-wrap {
            animation: fadeUp 0.38s var(--ease) both;
        }

        /* ══════════════════════════════════════════
           EMPTY STATE
        ══════════════════════════════════════════ */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 56px 24px;
            text-align: center;
        }

        .empty-state-icon {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: rgba(226, 232, 240, 0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ══════════════════════════════════════════
           CHIP / FILTER TABS
        ══════════════════════════════════════════ */
        .filter-tabs {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 600;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.7);
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s var(--ease);
            user-select: none;
        }

        .filter-tab:hover {
            border-color: rgba(52, 168, 83, 0.3);
            color: var(--green-dark);
        }

        .filter-tab.active {
            background: var(--green);
            color: white;
            border-color: var(--green);
            box-shadow: var(--shadow-green);
        }

        .filter-tab.active-orange {
            background: var(--orange);
            color: white;
            border-color: var(--orange);
            box-shadow: var(--shadow-orange);
        }

        .filter-tab.active-blue {
            background: var(--blue);
            color: white;
            border-color: var(--blue);
            box-shadow: var(--shadow-blue);
        }
    </style>
    @stack('styles')
</head>


<body x-data="{ sidebarOpen: false, sidebarCollapsed: false }" class="antialiased">

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="sidebar-overlay lg:hidden" x-cloak>
    </div>

    <!-- ═══════════════════════ SIDEBAR ═══════════════════════ -->
    <aside class="sidebar" :class="{
               'collapsed': sidebarCollapsed,
               'mobile-show': sidebarOpen
           }">

        <!-- Collapse Toggle (desktop only) -->
        <button class="sidebar-toggle lg:flex hidden" @click="sidebarCollapsed = !sidebarCollapsed"
            title="Toggle Sidebar">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
            </svg>
        </button>

        <!-- Logo -->
        <div class="sidebar-logo">
            <img src="{{ asset('images/logo-ptpn.png') }}" alt="PTPN1" class="sidebar-logo-img"
                onerror="this.onerror=null; this.src='{{ asset('images/logo-ptpn1.svg') }}'">
            <div class="sidebar-logo-text">
                <h1
                    style="font-size: 15px; font-weight: 800; color: white; margin: 0; line-height: 1; letter-spacing: -0.01em;">
                    PTPN 1</h1>
                <p
                    style="font-size: 9px; font-weight: 600; color: #3b5268; margin: 3px 0 0 0; text-transform: uppercase; letter-spacing: 0.18em;">
                    Warehouse System</p>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">

            @php $role = Auth::user()->role; @endphp

            {{-- ══════ MENU UTAMA ══════ --}}
            <div class="nav-section-label">Menu Utama</div>

            {{-- Dashboard — Krani (admin) + Petugas (operator) --}}
            @if(in_array($role, ['admin', 'operator']))
            <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('dashboard') ? 'green' : 'default' }}">
                    <i data-lucide="layout-dashboard" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
            @endif

            {{-- Manajemen Stok (Lihat) — Semua role --}}
            <a href="{{ route('stocks.index') }}" class="nav-link {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('stocks.*') ? 'green' : 'default' }}">
                    <i data-lucide="package-2" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Manajemen Stok</span>
                <span class="nav-tooltip">Manajemen Stok</span>
            </a>

            {{-- ══════ PETUGAS GUDANG (operator) ══════ --}}
            @if($role === 'operator')
            <div class="nav-section-label" style="margin-top: 16px;">Inbound & Verifikasi</div>

            {{-- Scan OCR / Inbound --}}
            <a href="{{ route('ocr.index', ['type' => 'inbound']) }}"
                class="nav-link {{ request()->routeIs('ocr.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('ocr.*') ? 'green' : 'default' }}">
                    <i data-lucide="scan-line" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Scan OCR / Inbound</span>
                <span class="nav-tooltip">Scan OCR / Inbound</span>
            </a>

            {{-- Verifikasi Barang Keluar --}}
            <a href="{{ route('shipments.verification') }}"
                class="nav-link {{ request()->routeIs('shipments.verification') ? 'active-orange' : '' }}">
                <div class="nav-icon {{ request()->routeIs('shipments.verification') ? 'orange' : 'default' }}">
                    <i data-lucide="clipboard-check" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Verifikasi Barang Keluar</span>
                <span class="nav-tooltip">Verifikasi Barang Keluar</span>
            </a>

            {{-- Stock Opname --}}
            <a href="{{ route('stock-opname.index') }}"
                class="nav-link {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
                <div class="nav-icon {{ request()->routeIs('stock-opname.*') ? 'green' : 'default' }}">
                    <i data-lucide="scale" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Stock Opname</span>
                <span class="nav-tooltip">Stock Opname</span>
            </a>
            @endif

            {{-- ══════ KRANI GUDANG (admin) ══════ --}}
            @if($role === 'admin')
            <div class="nav-section-label" style="margin-top: 16px;">Outbound</div>

            {{-- Scan OCR Outbound (DO, Surat Kuasa) --}}
            <a href="{{ route('ocr.index', ['type' => 'outbound']) }}"
                class="nav-link {{ request()->routeIs('ocr.*') ? 'active-orange' : '' }}">
                <div class="nav-icon {{ request()->routeIs('ocr.*') ? 'orange' : 'default' }}">
                    <i data-lucide="scan-line" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Scan OCR Outbound</span>
                <span class="nav-tooltip">Scan OCR Outbound</span>
            </a>

            {{-- Buat Pengiriman --}}
            <a href="{{ route('shipments.create') }}"
                class="nav-link {{ request()->routeIs('shipments.create') ? 'active-orange' : '' }}">
                <div class="nav-icon {{ request()->routeIs('shipments.create') ? 'orange' : 'default' }}">
                    <i data-lucide="package-plus" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Buat Pengiriman</span>
                <span class="nav-tooltip">Buat Pengiriman</span>
            </a>

            {{-- Data Pengiriman --}}
            <a href="{{ route('shipments.index') }}"
                class="nav-link {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') ? 'active-orange' : '' }}">
                <div class="nav-icon {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') ? 'orange' : 'default' }}">
                    <i data-lucide="truck" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Data Pengiriman</span>
                <span class="nav-tooltip">Data Pengiriman</span>
            </a>
            @endif

            {{-- Data Pengiriman untuk Operator (untuk lihat dan verifikasi) --}}
            @if($role === 'operator')
            <a href="{{ route('shipments.index') }}"
                class="nav-link {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') ? 'active-orange' : '' }}">
                <div class="nav-icon {{ request()->routeIs('shipments.index') || request()->routeIs('shipments.show') ? 'orange' : 'default' }}">
                    <i data-lucide="truck" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Data Pengiriman</span>
                <span class="nav-tooltip">Data Pengiriman</span>
            </a>
            @endif

            {{-- ══════ LAPORAN — Krani + Petugas ══════ --}}
            @if(in_array($role, ['admin', 'operator']))
            <div class="nav-section-label" style="margin-top: 16px;">Laporan</div>

            <a href="{{ route('reports.index') }}"
                class="nav-link {{ request()->routeIs('reports.*') ? 'active-blue' : '' }}">
                <div class="nav-icon {{ request()->routeIs('reports.*') ? 'blue' : 'default' }}">
                    <i data-lucide="bar-chart-3" style="width:17px;height:17px;"></i>
                </div>
                <span class="nav-text">Laporan & Analitik</span>
                <span class="nav-tooltip">Laporan & Analitik</span>
            </a>
            @endif
        </nav>

        <!-- User Profile -->
        <div class="sidebar-user">
            <div class="user-card">
                <div class="user-avatar">{{ substr(Auth::user()->name, 0, 1) }}</div>
                <div class="user-info">
                    <p
                        style="font-size: 13px; font-weight: 700; color: #e2e8f0; margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                        {{ Auth::user()->name }}
                    </p>
                    <p style="font-size: 10px; color: #3b5268; margin: 2px 0 0 0; font-weight: 500;">
                        {{ ucfirst(Auth::user()->role) }}
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Keluar">
                        <i data-lucide="log-out" style="width:15px;height:15px;"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ═══════════════════════ MAIN CONTENT ═══════════════════════ -->
    <div class="main-area" :class="{ 'sidebar-collapsed': sidebarCollapsed }">

        <!-- Top Bar -->
        <header class="top-bar">
            <div style="display:flex; align-items:center; gap:14px;">
                <!-- Mobile toggle -->
                <button @click="sidebarOpen = true" class="mobile-toggle topbar-icon-btn" style="display:none;">
                    <i data-lucide="menu" style="width:18px;height:18px;"></i>
                </button>
                <!-- Breadcrumb -->
                <div class="breadcrumb">
                    <div class="breadcrumb-home">
                        <i data-lucide="home" style="width:13px;height:13px;"></i>
                    </div>
                    <i data-lucide="chevron-right" style="width:12px;height:12px;color:#cbd5e1;"></i>
                    <span style="color:var(--text-secondary); font-weight:600;">@yield('title', 'Dashboard')</span>
                </div>
            </div>

            <div class="topbar-actions">
                <!-- Online status + date (disembunyikan di mobile via CSS) -->
                <div class="topbar-meta" style="display:flex;align-items:center;gap:12px;">
                    <div class="status-chip">
                        <div class="pulse-dot"></div>
                        <span>Sistem Online</span>
                    </div>
                    <div class="topbar-divider"></div>
                    <span style="font-size:12px; font-weight:500; color:var(--text-muted);">
                        {{ now()->translatedFormat('d M Y') }}
                    </span>
                    <span id="live-clock"
                        style="font-size:12px; font-weight:600; color:var(--text-secondary); font-variant-numeric: tabular-nums;"></span>
                    <div class="topbar-divider"></div>
                </div>
                <!-- Notification bell + User avatar (disembunyikan di HP kecil via CSS) -->
                <div class="topbar-extras" style="display:flex;align-items:center;gap:10px;">
                    <!-- Notification Bell Dropdown -->
                    <div class="notif-panel-wrap" id="notif-panel-wrap">
                        <button id="notif-bell-btn" class="topbar-icon-btn" onclick="toggleNotifPanel()" title="Notifikasi" style="position:relative;">
                            <i data-lucide="bell" style="width:16px;height:16px;"></i>
                            <span class="notif-badge" id="notif-badge-count" style="display:none;">0</span>
                        </button>

                        <!-- Notification Dropdown Panel -->
                        <div class="notif-panel" id="notif-panel" style="display:none;">
                            <div class="notif-panel-header">
                                <div class="notif-panel-title">
                                    <div class="notif-live-dot"></div>
                                    Notifikasi
                                </div>
                                <div style="display:flex; gap:8px; align-items:center;">
                                    <button id="enable-push-btn" class="enable-push-btn" onclick="requestPushPermission()">Aktifkan Alert HP</button>
                                    <span id="notif-panel-count" style="font-size:11px;font-weight:600;color:var(--text-muted);">Memuat...</span>
                                </div>
                            </div>
                            <div class="notif-panel-body" id="notif-panel-body">
                                <div class="notif-empty">
                                    <div class="notif-empty-icon">🔔</div>
                                    <div>Memuat notifikasi...</div>
                                </div>
                            </div>
                            <div class="notif-panel-footer">
                                @if(Auth::user()->role === 'operator')
                                    <a href="{{ route('shipments.verification') }}">Lihat Semua Verifikasi →</a>
                                @else
                                    <a href="{{ route('shipments.index') }}">Lihat Semua Pengiriman →</a>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div
                        style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--green),var(--blue));display:flex;align-items:center;justify-content:center;color:white;font-size:12px;font-weight:700;box-shadow:0 2px 8px rgba(52,168,83,0.3);">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="page-main">

            <!-- Page Header -->
            @hasSection('header')
                <div class="page-header anim-fade-up">
                    <div>
                        <h2 class="page-title">@yield('header')</h2>
                        @hasSection('subheader')
                            <p class="page-subtitle">@yield('subheader')</p>
                        @endif
                    </div>
                    @yield('actions')
                </div>
            @endif

            <!-- Flash Messages -->
            @if(session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0" class="flash-success anim-fade-up"
                    style="margin-bottom:20px; padding:12px 16px; border-radius:12px; display:flex; align-items:center; font-size:13px; font-weight:500;">
                    <div class="bg-green-20"
                        style="width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0;">
                        <i data-lucide="check-circle-2" class="text-ptpn-green" style="width:15px;height:15px;"></i>
                    </div>
                    {!! session('success') !!}
                </div>
            @endif

            @if(session('error'))
                <div class="flash-error anim-fade-up"
                    style="margin-bottom:20px; padding:12px 16px; border-radius:12px; display:flex; align-items:center; font-size:13px; font-weight:500;">
                    <div
                        style="width:30px;height:30px;border-radius:8px;background:rgba(239,68,68,0.1);display:flex;align-items:center;justify-content:center;margin-right:12px;flex-shrink:0;">
                        <i data-lucide="alert-circle" style="width:15px;height:15px;color:#ef4444;"></i>
                    </div>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Content -->
            <div class="page-content-wrap">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Toast Container -->
    <div id="toast-container"></div>

    <!-- In-App Notification Popup Container -->
    <div id="notif-popup-container"></div>

    <!-- PWA Install Banner -->
    <div id="pwa-banner" class="hidden">
        <div
            style="width:36px;height:36px;border-radius:10px;background:rgba(52,168,83,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i data-lucide="download" style="width:17px;height:17px;color:var(--green);"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <p style="font-size:13px;font-weight:700;color:#e2e8f0;margin:0;">Install Aplikasi</p>
            <p style="font-size:11px;color:#5f7d96;margin:2px 0 0 0;">Pasang ke layar utama untuk akses cepat</p>
        </div>
        <button id="pwa-install-btn" class="btn btn-green" style="padding:7px 14px;font-size:12px;">Pasang</button>
        <button onclick="document.getElementById('pwa-banner').classList.add('hidden');"
            style="background:none;border:none;color:#3b5268;cursor:pointer;padding:6px;">
            <i data-lucide="x" style="width:14px;height:14px;"></i>
        </button>
    </div>

    <!-- ══════════════ BOTTOM NAVIGATION BAR (Mobile PWA) ══════════════ -->
    <div x-data="{ moreOpen: false }">

        @php $mobileRole = Auth::user()->role; @endphp

        <!-- More Menu Popup -->
        <div class="bottom-nav-more" :class="{ 'open': moreOpen }">
            @if($mobileRole === 'operator')
                <a href="{{ route('stock-opname.index') }}"
                    class="more-menu-item {{ request()->routeIs('stock-opname.*') ? 'active' : '' }}">
                    <i data-lucide="scale" style="width:16px;height:16px;color:var(--green);"></i>
                    Stock Opname
                </a>
                <a href="{{ route('shipments.index') }}"
                    class="more-menu-item {{ request()->routeIs('shipments.index') ? 'active' : '' }}">
                    <i data-lucide="truck" style="width:16px;height:16px;color:var(--orange);"></i>
                    Data Pengiriman
                </a>
            @endif
            @if(in_array($mobileRole, ['admin', 'operator']))
                <a href="{{ route('dashboard') }}"
                    class="more-menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i data-lucide="layout-dashboard" style="width:16px;height:16px;color:var(--green);"></i>
                    Dashboard
                </a>
                <a href="{{ route('reports.index') }}"
                    class="more-menu-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i data-lucide="bar-chart-3" style="width:16px;height:16px;color:var(--blue);"></i>
                    Laporan
                </a>
            @endif
            <div class="more-menu-item" style="cursor:default;background:none;border-color:transparent;">
                <i data-lucide="user" style="width:16px;height:16px;color:#3b5268;"></i>
                <span style="font-size:11px;color:#3b5268;">{{ Auth::user()->name }} ({{ ucfirst($mobileRole) }})</span>
            </div>
        </div>

        <!-- Backdrop -->
        <div x-show="moreOpen" @click="moreOpen = false"
            style="position:fixed;inset:0;z-index:43;background:rgba(0,0,0,0.3);" x-cloak
            x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

        <!-- Bottom Nav Bar -->
        <nav class="bottom-nav">

            {{-- ══════ OPERATOR Bottom Nav ══════ --}}
            @if($mobileRole === 'operator')
                <a href="{{ route('stocks.index') }}"
                    class="bottom-nav-item {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                    <div class="bottom-nav-icon"><i data-lucide="package-2" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Stok</span>
                </a>
                <a href="{{ route('ocr.index', ['type' => 'inbound']) }}"
                    class="bottom-nav-item {{ request()->routeIs('ocr.*') ? 'active' : '' }}">
                    <div class="bottom-nav-icon"><i data-lucide="scan-line" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Inbound</span>
                </a>
                <a href="{{ route('shipments.verification') }}"
                    class="bottom-nav-item {{ request()->routeIs('shipments.verification') ? 'active-orange' : '' }}" style="position: relative;">
                    <div class="bottom-nav-icon"><i data-lucide="clipboard-check" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Verifikasi</span>
                    @php
                        $pendingMobileCount = \App\Models\Shipment::where('status', 'draft')->count();
                    @endphp
                    @if($pendingMobileCount > 0)
                        <span style="position: absolute; top: 0px; right: 10px; width: 12px; height: 12px; border-radius: 50%; background: var(--orange); border: 2px solid #0D1B2A;"></span>
                    @endif
                </a>
            @endif

            {{-- ══════ ADMIN (Krani) Bottom Nav ══════ --}}
            @if($mobileRole === 'admin')
                <a href="{{ route('dashboard') }}"
                    class="bottom-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <div class="bottom-nav-icon"><i data-lucide="layout-dashboard" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Dashboard</span>
                </a>
                <a href="{{ route('stocks.index') }}"
                    class="bottom-nav-item {{ request()->routeIs('stocks.*') ? 'active' : '' }}">
                    <div class="bottom-nav-icon"><i data-lucide="package-2" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Stok</span>
                </a>
                <a href="{{ route('shipments.create') }}"
                    class="bottom-nav-item {{ request()->routeIs('shipments.create') ? 'active-orange' : '' }}">
                    <div class="bottom-nav-icon"><i data-lucide="package-plus" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Kirim</span>
                </a>
                <a href="{{ route('shipments.index') }}"
                    class="bottom-nav-item {{ request()->routeIs('shipments.index') ? 'active-orange' : '' }}">
                    <div class="bottom-nav-icon"><i data-lucide="truck" style="width:18px;height:18px;"></i></div>
                    <span class="bottom-nav-label">Pengiriman</span>
                </a>
            @endif



            <!-- More -->
            <button type="button" @click="moreOpen = !moreOpen" class="bottom-nav-item">
                <div class="bottom-nav-icon"><i data-lucide="grid-2x2" style="width:18px;height:18px;"></i></div>
                <span class="bottom-nav-label">Lainnya</span>
            </button>
        </nav>
    </div>

    <!-- Initialize Lucide + Utilities -->
    <script>
        // ── Lucide icons ─────────────────────────────
        lucide.createIcons();

        // ══════════════════════════════════════════
        // NOTIFICATION SYSTEM
        // ══════════════════════════════════════════
        let notifPanelOpen = false;
        let lastNotifCount  = -1;   // -1 = first load
        let lastNotifIds    = new Set();
        const notifIcons = { warning: '🚨', success: '✅', info: '⏳' };

        function toggleNotifPanel() {
            const panel = document.getElementById('notif-panel');
            notifPanelOpen = !notifPanelOpen;
            panel.style.display = notifPanelOpen ? 'flex' : 'none';
            if (notifPanelOpen) fetchNotifications();
        }

        // Close panel when clicking outside
        document.addEventListener('click', function(e) {
            const wrap = document.getElementById('notif-panel-wrap');
            if (wrap && !wrap.contains(e.target) && notifPanelOpen) {
                document.getElementById('notif-panel').style.display = 'none';
                notifPanelOpen = false;
            }
        });

        function fetchNotifications() {
            const apiURL = document.querySelector('meta[name="notif-api-url"]').getAttribute('content');
            fetch(apiURL, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(r => r.json())
            .then(data => {
                updateNotifBadge(data.count);
                updateNotifPanel(data.notifications);
                checkForNewNotifs(data);
            })
            .catch(err => console.warn('Notification fetch failed:', err));
        }

        function updateNotifBadge(count) {
            const badge = document.getElementById('notif-badge-count');
            if (!badge) return;
            if (count > 0) {
                badge.style.display = 'flex';
                badge.textContent = count > 9 ? '9+' : count;
            } else {
                badge.style.display = 'none';
            }
        }

        function updateNotifPanel(notifs) {
            const body   = document.getElementById('notif-panel-body');
            const header = document.getElementById('notif-panel-count');
            if (!body || !header) return;

            header.textContent = notifs.length > 0
                ? notifs.length + ' notifikasi'
                : 'Tidak ada notifikasi';

            if (notifs.length === 0) {
                body.innerHTML = `
                    <div class="notif-empty">
                        <div class="notif-empty-icon">✨</div>
                        <div style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">Semua Clear!</div>
                        <div>Tidak ada notifikasi aktif saat ini.</div>
                    </div>`;
                return;
            }

            body.innerHTML = notifs.map(n => `
                <a href="${n.url}" class="notif-item">
                    <div class="notif-item-icon ${n.type}">${notifIcons[n.type] || '🔔'}</div>
                    <div class="notif-item-content">
                        <div class="notif-item-title">${n.title}</div>
                        <div class="notif-item-body">${n.body}</div>
                        <div class="notif-item-time">${n.time}</div>
                    </div>
                </a>`
            ).join('');
        }

        function checkForNewNotifs(data) {
            const newIds = new Set(data.notifications.map(n => n.id));

            // On first load, just save state
            if (lastNotifCount === -1) {
                lastNotifCount = data.count;
                lastNotifIds   = newIds;
                return;
            }

            // Find truly new notifications
            const brandNew = data.notifications.filter(n => !lastNotifIds.has(n.id));

            // Show popup for each new notification
            brandNew.forEach((n, i) => {
                setTimeout(() => showNotifPopup(n), i * 600);
            });

            // If count just increased and panel is closed, show first
            if (data.count > lastNotifCount && brandNew.length === 0 && !notifPanelOpen) {
                const newest = data.notifications[0];
                if (newest) showNotifPopup(newest);
            }

            lastNotifCount = data.count;
            lastNotifIds   = newIds;
        }

        function showNotifPopup(notif) {
            const container = document.getElementById('notif-popup-container');
            if (!container) return;

            const el = document.createElement('div');
            el.className = `notif-popup ${notif.type}`;
            el.innerHTML = `
                <div class="notif-popup-icon">${notifIcons[notif.type] || '🔔'}</div>
                <div class="notif-popup-content">
                    <div class="notif-popup-app">PTPN 1 WMS</div>
                    <div class="notif-popup-title">${notif.title}</div>
                    <div class="notif-popup-body">${notif.body}</div>
                </div>
                <button class="notif-popup-close" onclick="dismissNotifPopup(this.parentElement)" title="Tutup">✕</button>`;

            el.addEventListener('click', function(e) {
                if (e.target.classList.contains('notif-popup-close')) return;
                window.location.href = notif.url;
            });

            container.prepend(el);

            // Auto-dismiss after 8 seconds
            setTimeout(() => dismissNotifPopup(el), 8000);
        }

        function dismissNotifPopup(el) {
            if (!el || !el.parentElement) return;
            el.classList.add('hiding');
            el.addEventListener('animationend', () => el.remove(), { once: true });
        }

        // Initial fetch + polling every 30s
        document.addEventListener('DOMContentLoaded', function() {
            fetchNotifications();
            setInterval(fetchNotifications, 30000);
        });

        // ── Live clock ──────────────────────────────
        function updateClock() {
            const el = document.getElementById('live-clock');
            if (el) {
                const now = new Date();
                el.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ── Toast notification system ────────────────
        window.toast = function (message, type = 'success', duration = 4000) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const icons = {
                success: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>',
                error: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
                info: '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
            };
            const el = document.createElement('div');
            el.className = `toast toast-${type}`;
            el.innerHTML = `<div class="toast-icon">${icons[type] || icons.info}</div><span style="flex:1;">${message}</span>`;
            container.appendChild(el);
            setTimeout(() => {
                el.classList.add('hiding');
                el.addEventListener('animationend', () => el.remove(), { once: true });
            }, duration);
        };

        // ── Auto-fire session flash as toast ─────────
        @if(session('success'))
            window.addEventListener('DOMContentLoaded', () => toast({!! json_encode(session('success')) !!}, 'success'));
        @endif
        @if(session('error'))
            window.addEventListener('DOMContentLoaded', () => toast({!! json_encode(session('error')) !!}, 'error'));
        @endif
        @if(session('info'))
            window.addEventListener('DOMContentLoaded', () => toast({!! json_encode(session('info')) !!}, 'info'));
        @endif

        // ── PWA: Service Worker + Install prompt ─────
        let pwaInstallPrompt = null;
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            pwaInstallPrompt = e;
            const banner = document.getElementById('pwa-banner');
            if (banner && !sessionStorage.getItem('pwa-banner-dismissed')) {
                banner.classList.remove('hidden');
                lucide.createIcons();
            }
        });

        const installBtn = document.getElementById('pwa-install-btn');
        if (installBtn) {
            installBtn.addEventListener('click', async () => {
                if (!pwaInstallPrompt) return;
                pwaInstallPrompt.prompt();
                const result = await pwaInstallPrompt.userChoice;
                document.getElementById('pwa-banner').classList.add('hidden');
                sessionStorage.setItem('pwa-banner-dismissed', '1');
                pwaInstallPrompt = null;
            });
        }

        document.getElementById('pwa-banner')?.querySelector('button:last-child')?.addEventListener('click', () => {
            sessionStorage.setItem('pwa-banner-dismissed', '1');
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('{{ asset("sw.js") }}')
                    .then(registration => {
                        console.log('SW registered:', registration.scope);
                        initPush();
                    })
                    .catch(e => console.log('SW error:', e));
            });
        }

        function initPush() {
            const btn = document.getElementById('enable-push-btn');
            if (!btn) return;
            
            if (!("Notification" in window)) {
                btn.style.display = 'none';
                return;
            }
            
            @auth
            if (Notification.permission === "default") {
                btn.style.display = 'block';
                btn.textContent = 'Aktifkan Alert HP';
            } else if (Notification.permission === "denied") {
                btn.style.display = 'block';
                btn.textContent = 'Alert HP Diblokir';
                btn.style.opacity = '0.6';
                btn.style.background = 'rgba(239, 68, 68, 0.1)';
                btn.style.color = 'var(--red)';
                btn.style.borderColor = 'rgba(239, 68, 68, 0.3)';
            } else if (Notification.permission === "granted") {
                btn.style.display = 'none'; // hide if already granted
                subscribeUser();
            }
            @endauth
        }

        window.requestPushPermission = function() {
            if (!("Notification" in window)) {
                if (typeof toast === 'function') toast('Browser tidak mendukung notifikasi.', 'error');
                return;
            }
            if (Notification.permission === "denied") {
                if (typeof toast === 'function') toast('Izin diblokir. Harap buka Pengaturan Situs di browser PHP dan izinkan notifikasi.', 'error', 6000);
                return;
            }
            
            Notification.requestPermission().then(function (permission) {
                if (permission === "granted") {
                    subscribeUser();
                    const btn = document.getElementById('enable-push-btn');
                    if (btn) btn.style.display = 'none';
                    if (typeof toast === 'function') {
                        toast('Push Notifikasi Berhasil Diaktifkan!', 'success');
                    }
                } else {
                    if (typeof toast === 'function') {
                        toast('Izin Push Notifikasi ditolak oleh sistem HP.', 'error');
                    }
                    initPush(); // refresh button state
                }
            });
        };

        function subscribeUser() {
            navigator.serviceWorker.ready.then((registration) => {
                const subscribeOptions = {
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array('{{ config("webpush.vapid.public_key") }}')
                };
                return registration.pushManager.subscribe(subscribeOptions);
            }).then((pushSubscription) => {
                storePushSubscription(pushSubscription);
            }).catch(err => console.log('Push subscription error: ', err));
        }

        function urlBase64ToUint8Array(base64String) {
            if(!base64String) return new Uint8Array(0);
            var padding = '='.repeat((4 - base64String.length % 4) % 4);
            var base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');
            var rawData = window.atob(base64);
            var outputArray = new Uint8Array(rawData.length);
            for (var i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }
            return outputArray;
        }

        function storePushSubscription(pushSubscription) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/push-subscribe', {
                method: 'POST',
                body: JSON.stringify(pushSubscription),
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': token
                }
            })
            .then(res => res.json())
            .then(res => console.log('Push Subscribed:', res))
            .catch(err => console.log(err));
        }
    </script>

</body>

</html>