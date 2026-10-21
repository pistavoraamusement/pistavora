   @php $securePrefix = config('security.secure_prefix', 'admin'); @endphp
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PISTAVORA | @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>Pistavora System | @yield('title', 'Dashboard')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    {{-- Apply the saved sidebar state before first paint so the rail never flashes open. --}}
    <script>
        try {
            if (localStorage.getItem('wp.sidebar.collapsed') === '1') {
                document.documentElement.classList.add('sb-collapsed');
            }
        } catch (e) {}
    </script>

    {{-- Page-specific extra <link> tags (extra fonts, etc.) go here --}}
    @stack('head')

    <style>
        :root {
            /* ---- primary (legacy --pink-* names kept so every existing page re-themes automatically) ---- */
            --pink: #6366F1;
            --pink-dark: #4338CA;
            --pink-deep: #4F46E5;
            --pink-light: #E0E7FF;
            --pink-pale: #EEF2FF;

            /* ---- neutrals ---- */
            --ink: #0F172A;
            --ink-soft: #475569;
            --muted: #64748B;
            --line: #E2E8F0;
            --line-strong: #CBD5E1;
            --bg: #F8FAFC;
            --card: #FFFFFF;

            /* ---- status / accents ---- */
            --green: #0F766E;
            --green-light: #CCFBF1;
            --amber: #B45309;
            --amber-light: #FEF3C7;
            --danger: #B91C1C;
            --danger-soft: #FEE2E2;
            --present: #0D9488;
            --present-soft: #CCFBF1;
            --deduct: #BE123C;
            --deduct-soft: #FFE4E6;
            --rest: #CBD5E1;
            --violet: #6D28D9;
            --sky: #0369A1;
            --sky-soft: #E0F2FE;

            /* ---- elevation / radius ---- */
            --shadow-sm: 0 1px 2px rgba(15, 23, 42, .05), 0 4px 12px rgba(15, 23, 42, .04);
            --shadow-md: 0 4px 6px rgba(15, 23, 42, .04), 0 16px 36px rgba(15, 23, 42, .12);
            --shadow-pink: 0 8px 20px rgba(79, 70, 229, .28);
            --radius-sm: 10px;
            --radius-md: 12px;
            --radius-lg: 16px;

            /* ---- type ---- */
            --font-display: 'Plus Jakarta Sans', 'Inter', sans-serif;

            /* ---- sidebar sizes ---- */
            --sb-w: 280px;
            --sb-w-rail: 76px;
            --sb-ease: cubic-bezier(.4, 0, .2, 1);

            /* ---- sidebar tokens (dark) ---- */
            --sb-bg: #0B1020;
            --sb-panel: rgba(255, 255, 255, .07);
            --sb-panel-soft: rgba(255, 255, 255, .05);
            --sb-line: rgba(255, 255, 255, .08);
            --sb-text: #CBD5E1;
            --sb-text-dim: #7C89A3;
            --sb-text-bright: #FFFFFF;
            --sb-accent: #A5B4FC;
            --sb-active-bg: rgba(99, 102, 241, .22);
            --sb-active-ring: rgba(129, 140, 248, .35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4 {
            font-family: var(--font-display);
            letter-spacing: -.01em;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        :focus-visible {
            outline: 3px solid rgba(99, 102, 241, .45);
            outline-offset: 2px;
        }

        .app-shell {
            display: grid;
            grid-template-columns: var(--sb-w) minmax(0, 1fr);
            min-height: 100vh;
        }

        .main-content {
            min-width: 0;
        }

        @media print {
            .no-print { display: none !important; }
        }

        /* ===== MOBILE TOPBAR ===== */
        .mobile-topbar {
            display: none;
            align-items: center;
            justify-content: space-between;
            background: #fff;
            padding: 10px 16px;
            position: sticky;
            top: 0;
            z-index: 40;
            border-bottom: 1px solid var(--line);
        }

        /* FIX: lift the topbar (and the fixed notif panel inside it) above page content while the panel is open.
           Stays under the sidebar overlay (45) and sidebar (50). */
        .mobile-topbar:has(.notif-panel.open) {
            z-index: 44;
        }

        .mobile-topbar img {
            height: 30px;
        }

        .mobile-topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .hamburger-btn {
            width: 38px;
            height: 38px;
            border-radius: var(--radius-sm);
            background: var(--bg);
            border: 1px solid var(--line);
            color: var(--ink);
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s, transform .1s;
        }

        .hamburger-btn:hover {
            background: var(--pink-pale);
        }

        .hamburger-btn:active {
            transform: scale(.94);
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .5);
            backdrop-filter: blur(2px);
            z-index: 45;
        }

        .sidebar-overlay.active {
            display: block;
        }

        /* =====================================================
           SIDEBAR  (dark, grouped, compact, scroll position kept)
           ===================================================== */
        .sidebar {
            background:
                radial-gradient(120% 60% at 0% 0%, rgba(99, 102, 241, .16), transparent 55%),
                var(--sb-bg);
            display: flex;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            height: 100dvh;
            border-right: 1px solid var(--sb-line);
            z-index: 60; /* FIX: raise the whole sidebar (and the notif dropdown inside it) above page content */
        }

        /* --- Brand --- */
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 18px 16px 16px;
            border-bottom: 1px solid var(--sb-line);
            flex-shrink: 0;
        }

        .sidebar-brand .mark {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(0, 0, 0, .35);
        }

        .sidebar-brand .mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .sidebar-brand .brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            overflow: hidden;
            flex: 1;
            min-width: 0;
        }

        .sidebar-brand .brand-text .brand-name {
            color: var(--sb-text-bright);
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.02rem;
            letter-spacing: -.01em;
            white-space: nowrap;
        }

        .sidebar-brand .brand-text .brand-sub {
            color: var(--sb-text-dim);
            font-weight: 500;
            font-size: .72rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 1px;
        }

        /* --- Notification bell + dropdown --- */
        .notif-trigger {
            position: relative;
            flex-shrink: 0;
        }

        .notif-btn {
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            border: 1px solid var(--line);
            background: var(--bg);
            color: var(--ink-soft);
            font-size: .9rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            transition: background .15s, color .15s, border-color .15s;
        }

        .notif-btn:hover,
        .notif-btn.open {
            background: var(--pink-pale);
            border-color: var(--pink-light);
            color: var(--pink-deep);
        }

        .notif-btn.has-unread {
            background: var(--pink-pale);
            border-color: var(--pink-light);
            color: var(--pink-deep);
        }

        /* bell inside the dark sidebar */
        .sidebar .notif-btn {
            background: var(--sb-panel);
            border-color: var(--sb-line);
            color: var(--sb-text);
        }

        .sidebar .notif-btn:hover,
        .sidebar .notif-btn.open,
        .sidebar .notif-btn.has-unread {
            background: var(--sb-active-bg);
            border-color: rgba(129, 140, 248, .45);
            color: #fff;
        }

        .sidebar .notif-badge {
            border-color: var(--sb-bg);
        }

        .notif-btn.has-unread .notif-badge {
            animation: notifPulse 1.6s ease-in-out infinite;
        }

        @keyframes notifPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.12); }
        }

        .notif-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            border-radius: 9px;
            background: #E11D48;
            border: 2px solid #fff;
            color: #fff;
            font-size: .6rem;
            font-weight: 800;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .notif-panel {
            display: none;
            position: absolute;
            top: calc(100% + 12px);
            left: 0;
            width: 344px;
            max-width: 82vw;
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            z-index: 200; /* FIX: was 1 */
        }

        .notif-panel.open {
            display: block;
            animation: notifPanelIn .16s ease;
        }

        @keyframes notifPanelIn {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .notif-panel-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
        }

        .notif-panel-header h4 {
            font-size: .92rem;
            font-weight: 800;
            color: var(--ink);
        }

        .notif-mark-read {
            background: var(--pink-pale);
            border: none;
            color: var(--pink-dark);
            font-size: .72rem;
            font-weight: 700;
            cursor: pointer;
            padding: 5px 11px;
            border-radius: 999px;
            transition: background .12s;
        }

        .notif-mark-read:hover {
            background: var(--pink-light);
        }

        .notif-list {
            max-height: 320px;
            overflow-y: auto;
        }

        .notif-item {
            display: flex;
            gap: 11px;
            width: 100%;
            padding: 12px 16px;
            border-bottom: 1px solid var(--line);
            border-left: none;
            background: none;
            font: inherit;
            text-align: left;
            cursor: pointer;
            transition: background .12s;
        }

        .notif-item:last-child {
            border-bottom: none;
        }

        .notif-item:hover,
        .notif-item:focus-visible {
            background: var(--bg);
            outline: none;
        }

        .notif-item.unread {
            background: var(--pink-pale);
        }

        .notif-icon {
            width: 34px;
            height: 34px;
            border-radius: var(--radius-sm);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .82rem;
        }

        .notif-icon.type-reservation,
        .notif-icon.type-booking {
            background: var(--pink-light);
            color: var(--pink-dark);
        }

        .notif-icon.type-low_stock,
        .notif-icon.type-inventory {
            background: var(--amber-light);
            color: var(--amber);
        }

        .notif-icon.type-pos {
            background: var(--sky-soft);
            color: var(--sky);
        }

        .notif-body {
            flex: 1;
            min-width: 0;
        }

        .notif-title-row {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .notif-title {
            font-size: .83rem;
            font-weight: 700;
            color: var(--ink);
        }

        .notif-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--pink-deep);
            flex-shrink: 0;
        }

        .notif-message {
            font-size: .78rem;
            color: var(--ink-soft);
            margin-top: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .notif-time {
            font-size: .7rem;
            color: var(--muted);
            margin-top: 4px;
        }

        .notif-empty {
            padding: 32px 16px;
            text-align: center;
            font-size: .82rem;
            color: var(--ink-soft);
        }

        .notif-empty-text {
            font-size: .82rem;
            color: var(--ink-soft);
        }

        .notif-panel-footer {
            padding: 10px 16px;
            text-align: center;
            border-top: 1px solid var(--line);
            background: var(--bg);
        }

        .notif-panel-footer a {
            font-size: .8rem;
            font-weight: 700;
            color: var(--pink-dark);
        }

        /* --- Scroll area (position is remembered by the script after </nav>) --- */
        .sidebar-nav {
            position: relative;               /* needed for scroll-to-active math */
            flex: 1 1 auto;
            min-height: 0;                    /* lets the flex child actually scroll */
            overflow-y: auto;
            overscroll-behavior: contain;     /* wheel doesn't scroll the page behind it */
            padding: 10px 12px 28px;
            display: flex;
            flex-direction: column;
            gap: 0;
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, .18) transparent;
            /* soft fade at the edges hints that there is more to scroll */
            -webkit-mask-image: linear-gradient(to bottom, transparent 0, #000 10px, #000 calc(100% - 28px), transparent 100%);
                    mask-image: linear-gradient(to bottom, transparent 0, #000 10px, #000 calc(100% - 28px), transparent 100%);
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .16);
            border-radius: 99px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .3);
        }

        /* --- Sections --- */
        .nav-section {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding: 4px 0;
            margin-bottom: 2px;
        }

        .nav-section + .nav-section {
            border-top: 1px solid var(--sb-line);
            padding-top: 12px;
            margin-top: 8px;
        }

        .nav-label {
            font-size: .74rem;
            font-weight: 600;
            letter-spacing: .01em;
            color: var(--sb-text-dim);
            padding: 2px 10px 8px;
        }

        .nav-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-right: 6px;
            border-radius: 8px;
            transition: background .12s, color .12s;
        }

        a.nav-label-row:hover {
            background: var(--sb-panel);
        }

        a.nav-label-row:hover span:first-child {
            color: #fff;
        }

        .nav-label-count {
            background: #F43F5E;
            color: #fff;
            font-size: .64rem;
            font-weight: 800;
            padding: 2px 8px;
            border-radius: 999px;
            line-height: 1.4;
        }

        .nav-label-count.is-zero {
            display: none;
        }

        .nav-notif-list {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-row {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 8px 10px;
            border-radius: var(--radius-sm);
            color: var(--sb-text);
            font-weight: 500;
            font-size: .86rem;
            transition: background .14s, color .14s;
        }

        .nav-row i {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: var(--sb-panel-soft);
            text-align: center;
            font-size: .82rem;
            color: var(--sb-text-dim);
            transition: color .14s, background .14s;
            flex-shrink: 0;
        }

        .nav-row span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        a.nav-row:hover {
            background: var(--sb-panel);
            color: #fff;
        }

        a.nav-row:hover i {
            color: #C7D2FE;
            background: rgba(129, 140, 248, .25);
        }

        .nav-row.unread span::after {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--sb-accent);
            margin-left: 6px;
            vertical-align: middle;
        }

        .nav-row.nav-row-empty {
            cursor: default;
            color: var(--sb-text-dim);
        }

        .nav-notif-viewall {
            display: block;
            text-align: center;
            padding: 8px 8px 4px;
            font-size: .74rem;
            font-weight: 700;
            color: var(--sb-accent);
        }

        /* --- Links --- */
        .sidebar-nav a {
            position: relative;
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 7px 10px;
            border-radius: 11px;
            color: var(--sb-text);
            font-weight: 500;
            font-size: .86rem;
            transition: background .14s, color .14s;
        }

        .sidebar-nav a i {
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            background: var(--sb-panel-soft);
            text-align: center;
            font-size: .8rem;
            color: var(--sb-text-dim);
            transition: color .14s, background .14s;
            flex-shrink: 0;
        }

        .sidebar-nav a:hover {
            background: var(--sb-panel);
            color: #fff;
        }

        .sidebar-nav a:hover i {
            color: #C7D2FE;
            background: rgba(129, 140, 248, .25);
        }

        /* active = tinted pill + glowing icon + edge bar */
        .sidebar-nav a.active {
            background: linear-gradient(90deg, rgba(99, 102, 241, .30), rgba(99, 102, 241, .10));
            color: #fff;
            font-weight: 600;
            box-shadow: inset 0 0 0 1px var(--sb-active-ring);
        }

        .sidebar-nav a.active i {
            color: #fff;
            background: linear-gradient(135deg, #818CF8, #4F46E5);
            box-shadow: 0 6px 14px rgba(79, 70, 229, .5);
        }

        .sidebar-nav a.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 9px;
            bottom: 9px;
            width: 3px;
            border-radius: 0 4px 4px 0;
            background: var(--sb-accent);
        }

        .sidebar-nav a.has-unread {
            background: rgba(99, 102, 241, .16);
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(129, 140, 248, .3);
        }

        .sidebar-nav a.has-unread i {
            color: #fff;
            background: var(--pink-deep);
        }

        /* ===== NESTED SUBMENUS ===== */
        .nav-group {
            display: flex;
            flex-direction: column;
        }

        .nav-parent-row {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .nav-parent-row .nav-parent-link {
            flex: 1;
            min-width: 0;
        }

        .nav-parent-row .nav-parent-link span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .nav-caret-btn {
            width: 30px;
            height: 30px;
            flex-shrink: 0;
            border: none;
            background: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: var(--sb-text-dim);
            transition: .15s;
        }

        .nav-caret-btn:hover {
            background: var(--sb-panel);
            color: #fff;
        }

        .nav-caret-btn i {
            font-size: .7rem;
            transition: transform .18s ease;
        }

        .nav-caret-btn.open i {
            transform: rotate(180deg);
        }

        .nav-submenu {
            max-height: 0;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            gap: 1px;
            transition: max-height .2s ease;
        }

        .nav-submenu.open {
            max-height: 520px;
            padding: 3px 0 6px;
        }

        .nav-submenu a {
            margin-left: 25px;
            padding: 6px 10px 6px 12px;
            font-size: .82rem;
            border-left: 1px solid var(--sb-line);
            border-radius: 0 10px 10px 0;
        }

        .nav-submenu a i {
            width: 24px;
            height: 24px;
            font-size: .7rem;
        }

        .nav-submenu a.active {
            border-left: 2px solid var(--sb-accent);
            background: var(--sb-active-bg);
            box-shadow: none;
        }

        .nav-submenu a.active::before {
            display: none;
        }

        .nav-submenu a.active i {
            box-shadow: none;
        }

        /* --- Footer / account card --- */
        .sidebar-footer {
            padding: 12px;
            border-top: 1px solid var(--sb-line);
            background: rgba(0, 0, 0, .18);
            flex-shrink: 0;
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px;
            border-radius: 14px;
            background: var(--sb-panel-soft);
            border: 1px solid var(--sb-line);
        }

        .sidebar-user .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366F1 0%, #4338CA 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: .85rem;
            flex-shrink: 0;
            box-shadow: 0 4px 10px rgba(79, 70, 229, .3);
        }

        .sidebar-user .user-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
            flex: 1;
        }

        .sidebar-user .name {
            color: #fff;
            font-weight: 600;
            font-size: .83rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user .role {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            color: #C7D2FE;
            background: rgba(99, 102, 241, .28);
            font-size: .64rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            padding: 1px 8px;
            border-radius: 999px;
            margin-top: 3px;
            width: fit-content;
        }

        .sidebar-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 9px;
            color: var(--sb-text-dim);
            font-size: .85rem;
            transition: .15s;
            background: none;
            border: none;
            cursor: pointer;
            flex-shrink: 0;
        }

        .sidebar-logout:hover {
            background: rgba(244, 63, 94, .18);
            color: #FDA4AF;
        }

        /* --- Logout modal --- */
        .logout-modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(3px);
            z-index: 100;
            align-items: center;
            justify-content: center;
        }

        .logout-modal-overlay.active {
            display: flex;
        }

        .logout-modal {
            background: var(--card);
            border-radius: 20px;
            padding: 36px 30px 24px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: var(--shadow-md);
            animation: modalPop .18s ease;
        }

        @keyframes modalPop {
            from { transform: scale(.95); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .logout-modal-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: var(--pink-light);
            color: var(--pink-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .logout-modal h3 {
            font-size: 1.35rem;
            font-weight: 800;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .logout-modal p {
            font-size: .92rem;
            color: var(--ink-soft);
            margin-bottom: 6px;
        }

        .logout-modal .logout-modal-subtext {
            font-size: .82rem;
            color: var(--muted);
            line-height: 1.55;
            margin-bottom: 24px;
        }

        .logout-modal-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .logout-modal-actions button,
        .logout-modal-actions a {
            flex: 1;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            font-weight: 700;
            font-size: .92rem;
            cursor: pointer;
            border: none;
            display: inline-block;
            transition: background .14s, transform .08s;
        }

        .logout-modal-actions button:active,
        .logout-modal-actions a:active {
            transform: scale(.97);
        }

        .btn-cancel {
            background: var(--bg);
            color: var(--ink-soft);
            border: 1px solid var(--line) !important;
        }

        .btn-cancel:hover {
            background: var(--line);
        }

        .btn-confirm-logout {
            background: var(--pink-deep);
            color: #fff;
            box-shadow: var(--shadow-pink);
        }

        .btn-confirm-logout:hover {
            background: var(--pink-dark);
        }

        .logout-modal-hint {
            margin-top: 18px;
            font-size: .78rem;
            color: var(--muted);
        }

        .logout-modal-hint .lm-kbd {
            background: var(--bg);
            border: 1px solid var(--line-strong);
            border-radius: 5px;
            padding: 1px 7px;
            font-size: .72rem;
            font-weight: 700;
            color: var(--ink-soft);
            margin: 0 2px;
        }

        /* =====================================================
           SIDEBAR COLLAPSE (icon rail, desktop only)
           State lives on <html class="sb-collapsed">.
           ===================================================== */
        .app-shell {
            transition: grid-template-columns .28s var(--sb-ease);
        }

        /* hamburger — stays at the exact same x in both states, so it never moves */
        .sidebar-toggle {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: 12px;
            border: 1px solid var(--sb-line);
            background: var(--sb-panel);
            color: var(--sb-text);
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background .15s, color .15s, transform .1s;
        }

        .sidebar-toggle:hover {
            background: var(--sb-active-bg);
            color: #fff;
        }

        .sidebar-toggle:active {
            transform: scale(.94);
        }

        .sidebar-toggle:focus-visible {
            outline-color: rgba(165, 180, 252, .7);
        }

        /* brand row: toggle | logo | name | bell */
        .sidebar-brand {
            gap: 9px;
            padding: 18px 16px 16px 18px;
            transition: gap .28s var(--sb-ease);
        }

        .sidebar-brand .mark {
            width: 36px;
            height: 36px;
            padding: 5px;
            transition: width .28s var(--sb-ease), padding .28s var(--sb-ease), opacity .18s;
        }

        .sidebar-brand .brand-text {
            max-width: 110px;
            transition: max-width .28s var(--sb-ease), opacity .18s;
        }

        .sidebar-brand .notif-trigger {
            max-width: 36px;
            transition: max-width .28s var(--sb-ease), opacity .18s;
        }

        /* nav bits that need to animate */
        .sidebar-nav {
            transition: padding .28s var(--sb-ease);
        }

        .nav-label {
            max-height: 30px;
            overflow: hidden;
            white-space: nowrap;
            transition: max-height .28s var(--sb-ease), opacity .18s, padding .28s var(--sb-ease);
        }

        .sidebar-nav a {
            transition: background .14s, color .14s, padding .28s var(--sb-ease), gap .28s var(--sb-ease);
        }

        .sidebar-nav a > span:not(.nav-label-count) {
            max-width: 170px;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
            transition: max-width .28s var(--sb-ease), opacity .18s;
        }

        .nav-parent-row {
            transition: gap .28s var(--sb-ease);
        }

        .nav-caret-btn {
            transition: width .28s var(--sb-ease), opacity .18s, background .15s, color .15s;
        }

        /* the floating label shown next to rail icons */
        .sb-tooltip {
            position: fixed;
            z-index: 10050;
            transform: translateY(-50%);
            background: #1F2937;
            color: #fff;
            font-size: .78rem;
            font-weight: 500;
            line-height: 1;
            padding: 8px 11px;
            border-radius: 7px;
            white-space: nowrap;
            box-shadow: 0 6px 18px rgba(0, 0, 0, .3);
            opacity: 0;
            pointer-events: none;
            transition: opacity .12s;
        }

        .sb-tooltip.show {
            opacity: 1;
        }

        @media (min-width: 901px) {
            html.sb-collapsed .app-shell {
                grid-template-columns: var(--sb-w-rail) minmax(0, 1fr);
            }

            /* clip content only while the width is animating (keeps the bell dropdown unclipped otherwise) */
            html.sb-animating .sidebar {
                overflow: hidden;
            }

            /* brand: only the hamburger remains */
            html.sb-collapsed .sidebar-brand {
                gap: 0;
            }

            html.sb-collapsed .sidebar-brand .mark {
                width: 0;
                padding: 0;
                opacity: 0;
                box-shadow: none;
            }

            html.sb-collapsed .sidebar-brand .brand-text {
                max-width: 0;
                opacity: 0;
            }

            html.sb-collapsed .sidebar-brand .notif-trigger {
                max-width: 0;
                opacity: 0;
                overflow: hidden;
                pointer-events: none;
            }

            /* nav: icons only */
            html.sb-collapsed .sidebar-nav {
                padding-left: 16px;
                padding-right: 16px;
            }

            html.sb-collapsed .nav-label {
                max-height: 0;
                opacity: 0;
                padding-top: 0;
                padding-bottom: 0;
            }

            html.sb-collapsed .sidebar-nav a {
                justify-content: center;
                gap: 0;
                padding-left: 0;
                padding-right: 0;
            }

            html.sb-collapsed .sidebar-nav a > span:not(.nav-label-count) {
                max-width: 0;
                opacity: 0;
            }

            html.sb-collapsed .sidebar-nav a.active::before {
                left: -16px;
            }

            html.sb-collapsed .nav-parent-row {
                gap: 0;
            }

            html.sb-collapsed .nav-caret-btn {
                width: 0;
                opacity: 0;
                overflow: hidden;
                pointer-events: none;
            }

            html.sb-collapsed .nav-submenu,
            html.sb-collapsed .nav-submenu.open {
                max-height: 0;
                padding-top: 0;
                padding-bottom: 0;
            }

            html.sb-collapsed .nav-notif-viewall {
                display: none;
            }

            /* unread count becomes a small corner badge on the bell icon */
            html.sb-collapsed #navNotifCount {
                position: absolute;
                top: 1px;
                right: 1px;
                min-width: 17px;
                padding: 1px 4px;
                font-size: .58rem;
                text-align: center;
                border: 2px solid var(--sb-bg);
                margin: 0 !important;
            }

            /* footer: avatar + logout stacked */
            html.sb-collapsed .sidebar-footer {
                padding: 12px 8px;
            }

            html.sb-collapsed .sidebar-user {
                flex-direction: column;
                align-items: center;
                gap: 6px;
                padding: 8px 0;
                background: none;
                border-color: transparent;
            }

            html.sb-collapsed .sidebar-user .user-info {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .app-shell,
            .app-shell * {
                transition: none !important;
            }
        }

        /* --- Notification DETAIL popup --- */
        .notif-detail-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, .55);
            backdrop-filter: blur(3px);
            z-index: 10100;
            align-items: center;
            justify-content: center;
        }

        .notif-detail-overlay.active {
            display: flex;
        }

        .notif-detail-modal {
            background: var(--card);
            border-radius: var(--radius-lg);
            padding: 28px 26px 22px;
            width: 90%;
            max-width: 380px;
            text-align: center;
            box-shadow: var(--shadow-md);
            animation: modalPop .18s ease;
        }

        .notif-detail-icon {
            width: 54px;
            height: 54px;
            margin: 0 auto 14px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            background: var(--pink-light);
            color: var(--pink-dark);
        }

        .notif-detail-icon.type-low_stock,
        .notif-detail-icon.type-inventory {
            background: var(--amber-light);
            color: var(--amber);
        }

        .notif-detail-icon.type-pos {
            background: var(--sky-soft);
            color: var(--sky);
        }

        .notif-detail-title {
            font-family: var(--font-display);
            font-size: 1.08rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 8px;
        }

        .notif-detail-message {
            font-size: .86rem;
            color: var(--ink-soft);
            line-height: 1.65;
            margin-bottom: 14px;
            word-break: break-word;
            white-space: pre-line;
            text-align: left;
            background: var(--bg);
            border: 1px solid var(--line);
            border-radius: var(--radius-sm);
            padding: 12px 14px;
        }

        .notif-detail-time {
            font-size: .76rem;
            color: var(--muted);
            margin-bottom: 22px;
        }

        .notif-detail-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .notif-detail-actions button,
        .notif-detail-actions a {
            flex: 1;
            padding: 10px 16px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            font-size: .86rem;
            cursor: pointer;
            border: none;
            display: inline-block;
            text-align: center;
            transition: background .14s, transform .08s;
        }

        .notif-detail-actions button:active,
        .notif-detail-actions a:active {
            transform: scale(.97);
        }

        .btn-notif-exit {
            background: var(--bg);
            color: var(--ink-soft);
            border: 1px solid var(--line) !important;
        }

        .btn-notif-exit:hover {
            background: var(--line);
        }

        .btn-notif-goto {
            background: var(--pink-deep);
            color: #fff;
        }

        .btn-notif-goto:hover {
            background: var(--pink-dark);
        }

        .content-body {
            padding: clamp(20px, 3vw, 40px);
            max-width: 1320px;
            margin-inline: auto;
        }

        @media(max-width:900px) {
            .app-shell {
                display: block;
            }

            .mobile-topbar {
                display: flex;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                height: 100dvh;
                width: 280px;
                transform: translateX(-100%);
                transition: transform .25s ease;
                z-index: 50;
                box-shadow: 0 0 48px rgba(15, 23, 42, .35);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: none;
            }

            .sidebar-brand .notif-trigger {
                display: none;
            }

            .notif-panel {
                position: fixed;
                top: 64px;
                left: 12px;
                right: 12px;
                width: auto;
                max-width: none;
            }

            .content-body {
                padding: 18px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .nav-submenu,
            .nav-caret-btn i,
            .sidebar,
            .notif-btn.has-unread .notif-badge {
                transition: none;
                animation: none;
            }
        }
    </style>

    {{-- Page-specific styles (metrics grid, tables, modals, etc.) --}}
    @yield('styles')
    @vite(['resources/js/app.js'])
</head>

<body>

    @php
        $sidebarNotifications = $sidebarNotifications ?? collect();
        $unreadNotifCount = $unreadNotifCount ?? 0;
        $notifTotalCount = $notifTotalCount ?? 0;

        $canViewNotifications = !in_array(session('role'), ['cashier', 'customer']);
    @endphp

    <div class="mobile-topbar no-print">
        <img src="{{ asset('images/pistavora-logo-horizontal.svg') }}" alt="Pistavora">
        <div class="mobile-topbar-actions">
            @if ($canViewNotifications)
                <div class="notif-trigger">
                    <button type="button" class="notif-btn {{ $unreadNotifCount > 0 ? 'has-unread' : '' }}" id="notifBtnMobile" aria-label="Notifications">
                        <i class="fa-solid fa-bell"></i>
                        @if ($unreadNotifCount > 0)
                            <span class="notif-badge">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                        @endif
                    </button>
                    <div class="notif-panel" id="notifPanelMobile">
                        <div class="notif-panel-header">
                            <h4>Notifications</h4>
                            @if ($unreadNotifCount > 0)
                                <button type="button" class="notif-mark-read" onclick="markAllNotifsRead(this)">Mark all read</button>
                            @endif
                        </div>
                        <div class="notif-list">
                            @forelse ($sidebarNotifications as $n)
                                <div class="notif-item {{ data_get($n, 'is_read', true) ? '' : 'unread' }}"
                                     role="button" tabindex="0"
                                     data-id="{{ data_get($n, 'id') }}"
                                     data-title="{{ data_get($n, 'title') }}"
                                     data-message="{{ data_get($n, 'message') }}"
                                     data-time="{{ data_get($n, 'time') }}"
                                     data-url="{{ data_get($n, 'url', '#') }}"
                                     data-type="{{ data_get($n, 'type', 'reservation') }}"
                                     data-read-url="{{ route('notifications.read', data_get($n, 'id')) }}">
                                    <div class="notif-icon type-{{ data_get($n, 'type', 'reservation') }}">
                                        <i class="fa-solid {{ in_array(data_get($n, 'type'), ['low_stock', 'inventory']) ? 'fa-boxes-stacked' : (data_get($n, 'type') === 'pos' ? 'fa-receipt' : 'fa-ticket') }}"></i>
                                    </div>
                                    <div class="notif-body">
                                        <div class="notif-title-row">
                                            <span class="notif-title">{{ data_get($n, 'title') }}</span>
                                            @unless (data_get($n, 'is_read', true))
                                                <span class="notif-dot"></span>
                                            @endunless
                                        </div>
                                        <div class="notif-message">{{ data_get($n, 'message') }}</div>
                                        <div class="notif-time">{{ data_get($n, 'time') }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="notif-empty"><span class="notif-empty-text">No notifications yet.</span></div>
                            @endforelse
                        </div>
                        <div class="notif-panel-footer">
                            <a href="{{ route('notifications.index') }}">View all</a>
                        </div>
                    </div>
                </div>
            @endif
            <button class="hamburger-btn" id="hamburgerBtn" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        </div>
    </div>

    <div class="sidebar-overlay no-print" id="sidebarOverlay"></div>

    <div class="app-shell">

        <aside class="sidebar no-print" id="sidebar">
            <div class="sidebar-brand">
                <button type="button" class="sidebar-toggle no-print" id="sidebarToggle" aria-label="Main menu" aria-expanded="true" aria-controls="sidebar">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="mark"><img src="{{ asset('images/pistavora-mark.svg') }}" alt="Pistavora"></div>
                <div class="brand-text">
                    <span class="brand-name">PISTAVORA</span>
                    <span class="brand-sub">Lipa Branch &middot; Operations</span>
                </div>
                @if ($canViewNotifications)
                    <div class="notif-trigger">
                        <button type="button" class="notif-btn {{ $unreadNotifCount > 0 ? 'has-unread' : '' }}" id="notifBtnDesktop" aria-label="Notifications">
                            <i class="fa-solid fa-bell"></i>
                            @if ($unreadNotifCount > 0)
                                <span class="notif-badge">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                            @endif
                        </button>
                        <div class="notif-panel" id="notifPanelDesktop">
                            <div class="notif-panel-header">
                                <h4>Notifications</h4>
                                @if ($unreadNotifCount > 0)
                                    <button type="button" class="notif-mark-read" onclick="markAllNotifsRead(this)">Mark all read</button>
                                @endif
                            </div>
                            <div class="notif-list">
                                @forelse ($sidebarNotifications as $n)
                                    <div class="notif-item {{ data_get($n, 'is_read', true) ? '' : 'unread' }}"
                                         role="button" tabindex="0"
                                         data-id="{{ data_get($n, 'id') }}"
                                         data-title="{{ data_get($n, 'title') }}"
                                         data-message="{{ data_get($n, 'message') }}"
                                         data-time="{{ data_get($n, 'time') }}"
                                         data-url="{{ data_get($n, 'url', '#') }}"
                                         data-type="{{ data_get($n, 'type', 'reservation') }}"
                                         data-read-url="{{ route('notifications.read', data_get($n, 'id')) }}">
                                        <div class="notif-icon type-{{ data_get($n, 'type', 'reservation') }}">
                                            <i class="fa-solid {{ in_array(data_get($n, 'type'), ['low_stock', 'inventory']) ? 'fa-boxes-stacked' : (data_get($n, 'type') === 'pos' ? 'fa-receipt' : 'fa-ticket') }}"></i>
                                        </div>
                                        <div class="notif-body">
                                            <div class="notif-title-row">
                                                <span class="notif-title">{{ data_get($n, 'title') }}</span>
                                                @unless (data_get($n, 'is_read', true))
                                                    <span class="notif-dot"></span>
                                                @endunless
                                            </div>
                                            <div class="notif-message">{{ data_get($n, 'message') }}</div>
                                            <div class="notif-time">{{ data_get($n, 'time') }}</div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="notif-empty"><span class="notif-empty-text">No notifications yet.</span></div>
                                @endforelse
                            </div>
                            <div class="notif-panel-footer">
                                <a href="{{ route('notifications.index') }}">View all</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <div class="nav-label">Operations</div>

                    @if (session('role') === 'cashier')
                        <a href="{{ route('pos.index') }}" class="{{ request()->is('pos') ? 'active' : '' }}">
                            <i class="fa-solid fa-cash-register"></i> <span>Point of Sale</span>
                        </a>
                        <a href="{{ route('inventory.index') }}" class="{{ request()->is('inventory') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> <span>Inventory</span>
                        </a>
                    @else
                        <a href="{{ route('inventory.index') }}" class="{{ request()->is('inventory') ? 'active' : '' }}">
                            <i class="fa-solid fa-boxes-stacked"></i> <span>Inventory</span>
                        </a>
                    @endif
                </div>

                @if ($canViewNotifications)
                    <div class="nav-section">
                        <div class="nav-label">Notifications</div>
                        <a href="{{ route('notifications.index') }}" id="navNotifLink"
                        class="{{ request()->routeIs('notifications.index') ? 'active' : '' }} {{ $unreadNotifCount > 0 ? 'has-unread' : '' }}">
                            <i class="fa-solid fa-bell"></i><span>Notifications</span>
                            <span id="navNotifCount" class="nav-label-count {{ $unreadNotifCount > 0 ? '' : 'is-zero' }}" style="margin-left:auto;">{{ $unreadNotifCount > 9 ? '9+' : $unreadNotifCount }}</span>
                        </a>

                        @if ($notifTotalCount > 4)
                            <a href="{{ route('notifications.index') }}" class="nav-notif-viewall">View all ({{ $notifTotalCount }})</a>
                        @endif
                    </div>
                @endif

                @if (session('role') !== 'cashier')
                    <div class="nav-section">
                        <div class="nav-label">People</div>

                        @php
                            $isOnVisitorRoute = request()->routeIs('visitor-summary') || request()->routeIs('reservations.*');
                            $isOnManpowerRoute = request()->routeIs('attendance') || request()->routeIs('salary') || request()->routeIs('payroll-history') || request()->routeIs('employees.*');
                        @endphp

                        {{-- Visitor Login and Booking Summary group --}}
                        <div class="nav-group">
                            <div class="nav-parent-row">
                                <a href="javascript:void(0)" class="nav-parent-link {{ $isOnVisitorRoute ? 'active' : '' }}">
                                    <i class="fa-solid fa-users"></i> <span>Visitor &amp; Booking</span>
                                </a>
                                <button type="button" class="nav-caret-btn {{ $isOnVisitorRoute ? 'open' : '' }}"
                                    id="peopleSubmenuToggle" aria-label="Toggle Visitor Login and Booking Summary submenu">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="nav-submenu {{ $isOnVisitorRoute ? 'open' : '' }}" id="peopleSubmenu">
                                <a href="{{ route('visitor-summary') }}" class="{{ request()->routeIs('visitor-summary') ? 'active' : '' }}">
                                    <i class="fa-solid fa-user-clock"></i> <span>Summary</span>
                                </a>
                                <a href="{{ route('reservations.index') }}" class="{{ request()->routeIs('reservations.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-ticket"></i> <span>Reservation</span>
                                </a>
                            </div>
                        </div>

                        {{-- Manpower group --}}
                        <div class="nav-group">
                            <div class="nav-parent-row">
                                <a href="javascript:void(0)" class="nav-parent-link {{ $isOnManpowerRoute ? 'active' : '' }}">
                                    <i class="fa-solid fa-clipboard-check"></i> <span>Manpower</span>
                                </a>
                                <button type="button" class="nav-caret-btn {{ $isOnManpowerRoute ? 'open' : '' }}"
                                    id="manpowerSubmenuToggle" aria-label="Toggle Manpower submenu">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                            </div>
                            <div class="nav-submenu {{ $isOnManpowerRoute ? 'open' : '' }}" id="manpowerSubmenu">
                                <a href="{{ route('employees.index') }}" class="{{ request()->routeIs('employees.*') ? 'active' : '' }}">
                                    <i class="fa-solid fa-id-badge"></i> <span>Employees</span>
                                </a>
                                <a href="{{ route('attendance') }}" class="{{ request()->routeIs('attendance') ? 'active' : '' }}">
                                    <i class="fa-solid fa-clipboard-check"></i> <span>Attendance</span>
                                </a>
                                <a href="{{ route('salary') }}" class="{{ request()->routeIs('salary') ? 'active' : '' }}">
                                    <i class="fa-solid fa-money-check-dollar"></i> <span>Salary &amp; Deductions</span>
                                </a>
                                <a href="{{ route('payroll-history') }}" class="{{ request()->routeIs('payroll-history') ? 'active' : '' }}">
                                    <i class="fa-solid fa-file-invoice-dollar"></i> <span>Payroll &amp; Payslip</span>
                                </a>
                            </div>
                        </div>

                        <a href="{{ route('accounts.create') }}" class="{{ request()->is('accounts/create') ? 'active' : '' }}">
                            <i class="fa-solid fa-user-plus"></i> <span>Account Management</span>
                        </a>
                    </div>

                    {{-- ============================================================
                         CONTENT (CMS) nav-section — Admin only.
                         ============================================================ --}}
                    @if (session('role') === 'admin')
                        <div class="nav-section">
                            <div class="nav-label">Content</div>

                            @php
                                $isOnCmsRoute = request()->routeIs('cms.*');
                            @endphp

                            <div class="nav-group">
                                <div class="nav-parent-row">
                                    <a href="{{ route('cms.index') }}" class="nav-parent-link {{ $isOnCmsRoute ? 'active' : '' }}">
                                        <i class="fa-solid fa-pen-to-square"></i> <span>Website Management</span>
                                    </a>
                                    <button type="button" class="nav-caret-btn {{ $isOnCmsRoute ? 'open' : '' }}"
                                        id="cmsSubmenuToggle" aria-label="Toggle Website Management submenu">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="nav-submenu {{ $isOnCmsRoute ? 'open' : '' }}" id="cmsSubmenu">
                                    <a href="{{ route('cms.section.edit', 'hero') }}" class="{{ request()->is('admin/cms/section/hero') ? 'active' : '' }}">
                                        <i class="fa-solid fa-house"></i> <span>Homepage Banner</span>
                                    </a>
                                    <a href="{{ route('cms.booking.pricing') }}" class="{{ request()->is('admin/cms/booking*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-money-bill-wave"></i> <span>Booking &amp; Pricing</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'pass') }}" class="{{ request()->is('admin/cms/cards/pass*') || request()->is('admin/cms/card/*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-ticket"></i> <span>Tickets Passes</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'attraction') }}" class="{{ request()->is('admin/cms/cards/attraction*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-rocket"></i> <span>Attractions and Rides</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'service') }}" class="{{ request()->is('admin/cms/cards/service*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-concierge-bell"></i> <span>Guest Information</span>
                                    </a>
                                    <a href="{{ route('cms.cards', 'step') }}" class="{{ request()->is('admin/cms/cards/step*') ? 'active' : '' }}">
                                        <i class="fa-solid fa-shoe-prints"></i> <span>Visitor Guide</span>
                                    </a>
                                    <a href="{{ route('cms.section.edit', 'contact') }}" class="{{ request()->is('admin/cms/section/contact') ? 'active' : '' }}">
                                        <i class="fa-solid fa-location-dot"></i> <span>Contact Info</span>
                                    </a>
                                    <a href="{{ route('cms.section.edit', 'footer') }}" class="{{ request()->is('admin/cms/section/footer') ? 'active' : '' }}">
                                        <i class="fa-solid fa-shoe-prints"></i> <span>Footer</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="nav-section">
                        <div class="nav-label">Insights</div>

                        @php
                            // Snackbar is now its own zone, separate from Skataya (RollerFever)
                            $zoneRoutes = ['fieldOfRides', 'RollerFever', 'DinoAdventure', 'Snackbar'];
                            $isOnZoneRoute = request()->is($zoneRoutes);
                            $isOnForecastRoute = request()->is('ml-forecast');
                            $submenuOpen = $isOnZoneRoute || $isOnForecastRoute;
                        @endphp

                        @if (session('role') === 'admin')
                            <div class="nav-group">
                                <div class="nav-parent-row">
                                    <a href="{{ route('ml.forecast') }}" class="nav-parent-link {{ $isOnForecastRoute ? 'active' : '' }}">
                                        <i class="fa-solid fa-brain"></i> <span>Sales Forecast</span>
                                    </a>
                                    <button type="button" class="nav-caret-btn {{ $submenuOpen ? 'open' : '' }}"
                                        id="forecastSubmenuToggle" aria-label="Toggle Sales Forecast submenu">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </button>
                                </div>
                                <div class="nav-submenu {{ $submenuOpen ? 'open' : '' }}" id="forecastSubmenu">
                                    <a href="{{ route('zone.fieldOfRides') }}" class="{{ request()->is('fieldOfRides') ? 'active' : '' }}">
                                        <i class="fa-solid fa-flag-checkered"></i> <span>PiyestaRides</span>
                                    </a>
                                    <a href="{{ route('zone.rollerFever') }}" class="{{ request()->is('RollerFever') ? 'active' : '' }}">
                                        <i class="fa-solid fa-bolt"></i> <span>Skataya</span>
                                    </a>
                                    <a href="{{ route('zone.snackbar') }}" class="{{ request()->is('Snackbar') ? 'active' : '' }}">
                                        <i class="fa-solid fa-burger"></i> <span>Snackbar</span>
                                    </a>
                                    <a href="{{ route('zone.dinoAdventure') }}" class="{{ request()->is('DinoAdventure') ? 'active' : '' }}">
                                        <i class="fa-solid fa-dragon"></i> <span>DinoLakbay</span>
                                    </a>
                                </div>
                            </div>
                        @else
                            <a href="{{ route('zone.fieldOfRides') }}" class="{{ request()->is('fieldOfRides') ? 'active' : '' }}">
                                <i class="fa-solid fa-flag-checkered"></i> <span>PiyestaRides</span>
                            </a>
                            <a href="{{ route('zone.rollerFever') }}" class="{{ request()->is('RollerFever') ? 'active' : '' }}">
                                <i class="fa-solid fa-bolt"></i> <span>Skataya</span>
                            </a>
                            <a href="{{ route('zone.snackbar') }}" class="{{ request()->is('Snackbar') ? 'active' : '' }}">
                                <i class="fa-solid fa-burger"></i> <span>Snackbar</span>
                            </a>
                            <a href="{{ route('zone.dinoAdventure') }}" class="{{ request()->is('DinoAdventure') ? 'active' : '' }}">
                                <i class="fa-solid fa-dragon"></i> <span>DinoLakbay</span>
                            </a>
                        @endif
                    </div>

                    {{-- ============================================================
                         SYSTEM nav-section — Admin only. Sakop ang buong system.
                         ============================================================ --}}
                    @if (session('role') === 'admin')
                        <div class="nav-section">
                            <div class="nav-label">System</div>

                            <a href="{{ route('my-account.index') }}" class="{{ request()->routeIs('my-account.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-user-gear"></i> <span>Account</span>
                            </a>

                            <a href="{{ route('system-logs.index') }}" class="{{ request()->routeIs('system-logs.*') ? 'active' : '' }}">
                                <i class="fa-solid fa-shield-halved"></i> <span>System Logs</span>
                            </a>
                        </div>
                    @endif
                @endif
            </nav>

            {{-- Remember the sidebar scroll position across page loads.
                 Runs right after the nav is parsed, so it applies before first paint (no jump to top). --}}
            <script>
                (function () {
                    const nav = document.querySelector('.sidebar-nav');
                    if (!nav) return;

                    const KEY = 'wp.sidebar.scrollTop';
                    const store = {
                        get() { try { return sessionStorage.getItem(KEY); } catch (e) { return null; } },
                        set(v) { try { sessionStorage.setItem(KEY, v); } catch (e) {} }
                    };

                    // Restore the last position; on the very first visit, center the active link instead.
                    const saved = store.get();
                    if (saved !== null) {
                        nav.scrollTop = parseInt(saved, 10) || 0;
                    } else {
                        const active = nav.querySelector('a.active:not(.nav-parent-link)') || nav.querySelector('a.active');
                        if (active) {
                            const navTop = nav.getBoundingClientRect().top;
                            const aTop = active.getBoundingClientRect().top;
                            nav.scrollTop = (aTop - navTop) - (nav.clientHeight / 2) + (active.offsetHeight / 2);
                        }
                    }

                    // Save while scrolling (at most one write per frame).
                    let ticking = false;
                    nav.addEventListener('scroll', () => {
                        if (ticking) return;
                        ticking = true;
                        requestAnimationFrame(() => { store.set(nav.scrollTop); ticking = false; });
                    }, { passive: true });

                    // Save right before leaving, in case a click lands mid-scroll.
                    nav.addEventListener('click', () => store.set(nav.scrollTop), true);
                    window.addEventListener('pagehide', () => store.set(nav.scrollTop));

                    // Re-apply once layout settles (fonts/icons can shift heights slightly).
                    window.addEventListener('load', () => {
                        const s = store.get();
                        if (s !== null && Math.abs(nav.scrollTop - parseInt(s, 10)) > 2) {
                            nav.scrollTop = parseInt(s, 10);
                        }
                    });
                })();
            </script>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="avatar" data-tip="{{ session('fullname') }}">{{ strtoupper(substr(session('fullname', 'U'), 0, 1)) }}</div>
                    <div class="user-info">
                        <div class="name">{{ session('fullname') }}</div>
                        <div class="role">{{ session('role') }}</div>
                    </div>
                    <button type="button" class="sidebar-logout" onclick="openLogoutModal()" aria-label="Log out">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </div>
            </div>
        </aside>

        <div class="main-content">
            <div class="content-body">
                @yield('content')
            </div>
        </div>

    </div>

    <!-- Logout Confirmation Modal -->
    <div class="logout-modal-overlay" id="logoutModalOverlay">
        <div class="logout-modal">
            <div class="logout-modal-icon">
                <i class="fas fa-arrow-right-from-bracket"></i>
            </div>
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out?</p>
            <div class="logout-modal-subtext">
                Active {{ session('role') === 'cashier' ? 'cashier ' : '' }}session for
                <strong>{{ session('fullname', 'this account') }}</strong> will be ended.
            </div>
            <div class="logout-modal-actions">
                <button type="button" class="btn-cancel" onclick="closeLogoutModal()">Cancel</button>
                <a href="/logout" class="btn-confirm-logout">Yes, Logout</a>
            </div>
            <div class="logout-modal-hint">
                Press <span class="lm-kbd">Esc</span> to cancel &bull; <span class="lm-kbd">Enter</span> to confirm
            </div>
        </div>
    </div>

    <!-- Notification DETAIL Popup (opens when any notif-item is clicked) -->
    <div class="notif-detail-overlay" id="notifDetailOverlay">
        <div class="notif-detail-modal">
            <div class="notif-detail-icon" id="notifDetailIcon">
                <i class="fa-solid fa-bell" id="notifDetailIconGlyph"></i>
            </div>
            <div class="notif-detail-title" id="notifDetailTitle"></div>
            <div class="notif-detail-message" id="notifDetailMessage"></div>
            <div class="notif-detail-time" id="notifDetailTime"></div>
            <div class="notif-detail-actions">
                <button type="button" class="btn-notif-exit" id="notifDetailExitBtn">Exit</button>
                <a href="#" class="btn-notif-goto" id="notifDetailGotoBtn">View More</a>
            </div>
        </div>
    </div>

    <script>
        // Desktop: collapse the sidebar to an icon rail (state is remembered)
        (function() {
            const root = document.documentElement;
            const btn = document.getElementById('sidebarToggle');
            const nav = document.querySelector('.sidebar-nav');
            if (!btn) return;

            const KEY = 'wp.sidebar.collapsed';
            const desktop = window.matchMedia('(min-width: 901px)');
            const isCollapsed = () => root.classList.contains('sb-collapsed');
            let timer;

            function setCollapsed(value) {
                root.classList.add('sb-animating');
                root.classList.toggle('sb-collapsed', value);
                btn.setAttribute('aria-expanded', String(!value));
                try { localStorage.setItem(KEY, value ? '1' : '0'); } catch (e) {}

                clearTimeout(timer);
                timer = setTimeout(() => {
                    root.classList.remove('sb-animating');
                    // let charts / tables on the page re-measure after the layout settles
                    window.dispatchEvent(new Event('resize'));
                }, 320);
            }

            btn.setAttribute('aria-expanded', String(!isCollapsed()));
            btn.addEventListener('click', () => setCollapsed(!isCollapsed()));

            // In the rail, a group with no page of its own (Visitor & Booking, Manpower)
            // expands the sidebar and opens its submenu instead of doing nothing.
            nav?.addEventListener('click', (e) => {
                if (!desktop.matches || !isCollapsed()) return;
                const parent = e.target.closest('.nav-parent-link');
                if (!parent || !(parent.getAttribute('href') || '').startsWith('javascript')) return;

                e.preventDefault();
                e.stopPropagation(); // keeps the normal toggle handler from closing it again

                setCollapsed(false);
                const row = parent.closest('.nav-parent-row');
                row?.nextElementSibling?.classList.add('open');
                row?.querySelector('.nav-caret-btn')?.classList.add('open');
            }, true);
        })();

        // Tooltips for rail icons (and the hamburger, which always has one)
        (function() {
            const root = document.documentElement;
            const desktop = window.matchMedia('(min-width: 901px)');
            const SELECTOR = '#sidebarToggle, .sidebar-nav a, .sidebar-logout, .sidebar-user .avatar';

            const tip = document.createElement('div');
            tip.className = 'sb-tooltip';
            tip.setAttribute('role', 'tooltip');
            document.body.appendChild(tip);

            function textFor(el) {
                if (el.id === 'sidebarToggle') return 'Main menu';
                if (!root.classList.contains('sb-collapsed')) return '';
                if (el.matches('.sidebar-logout')) return 'Log out';
                if (el.matches('.avatar')) return el.dataset.tip || '';
                return el.querySelector(':scope > span:not(.nav-label-count)')?.textContent.trim() || '';
            }

            function show(el) {
                const text = desktop.matches ? textFor(el) : '';
                if (!text) return hide();
                const r = el.getBoundingClientRect();
                tip.textContent = text;
                tip.style.top = (r.top + r.height / 2) + 'px';
                tip.style.left = (r.right + 10) + 'px';
                tip.classList.add('show');
            }

            function hide() { tip.classList.remove('show'); }

            document.addEventListener('mouseover', (e) => {
                const el = e.target.closest(SELECTOR);
                if (el) show(el);
            });
            document.addEventListener('mouseout', (e) => {
                const el = e.target.closest(SELECTOR);
                if (el && !el.contains(e.relatedTarget)) hide();
            });
            document.addEventListener('focusin', (e) => {
                const el = e.target.closest(SELECTOR);
                if (el) show(el);
            });
            document.addEventListener('focusout', hide);
            document.addEventListener('click', hide);
            document.querySelector('.sidebar-nav')?.addEventListener('scroll', hide, { passive: true });
        })();

        (function() {
            const hamburgerBtn = document.getElementById('hamburgerBtn');
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            if (!hamburgerBtn) return;

            function openSidebar() {
                sidebar.classList.add('open');
                overlay.classList.add('active');
            }

            function closeSidebar() {
                sidebar.classList.remove('open');
                overlay.classList.remove('active');
            }

            hamburgerBtn.addEventListener('click', openSidebar);
            overlay.addEventListener('click', closeSidebar);
            document.querySelectorAll('.sidebar-nav a').forEach(link => link.addEventListener('click', closeSidebar));
        })();

        (function() {
            const toggleBtn = document.getElementById('forecastSubmenuToggle');
            const submenu = document.getElementById('forecastSubmenu');
            if (!toggleBtn || !submenu) return;

            toggleBtn.addEventListener('click', () => {
                submenu.classList.toggle('open');
                toggleBtn.classList.toggle('open');
            });
        })();

        (function() {
            function wireGroup(toggleId, submenuId) {
                const toggleBtn = document.getElementById(toggleId);
                const submenu = document.getElementById(submenuId);
                if (!toggleBtn || !submenu) return;

                const parentLink = toggleBtn.closest('.nav-parent-row')?.querySelector('.nav-parent-link');

                function toggle() {
                    submenu.classList.toggle('open');
                    toggleBtn.classList.toggle('open');
                }

                toggleBtn.addEventListener('click', toggle);
                parentLink?.addEventListener('click', toggle);
            }

            wireGroup('peopleSubmenuToggle', 'peopleSubmenu');
            wireGroup('manpowerSubmenuToggle', 'manpowerSubmenu');
            wireGroup('cmsSubmenuToggle', 'cmsSubmenu');
        })();

        (function() {
            const pairs = [
                ['notifBtnDesktop', 'notifPanelDesktop'],
                ['notifBtnMobile', 'notifPanelMobile'],
            ];

            pairs.forEach(([btnId, panelId]) => {
                const btn = document.getElementById(btnId);
                const panel = document.getElementById(panelId);
                if (!btn || !panel) return;

                btn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const isOpen = panel.classList.contains('open');
                    document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
                    document.querySelectorAll('.notif-btn.open').forEach(b => b.classList.remove('open'));
                    if (!isOpen) {
                        panel.classList.add('open');
                        btn.classList.add('open');
                    }
                });
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('.notif-trigger')) {
                    document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
                    document.querySelectorAll('.notif-btn.open').forEach(b => b.classList.remove('open'));
                }
            });
        })();

        (function() {
            const NOTIF_READ_URL_PATTERN = @json(route('notifications.read', ['notification' => '__ID__']));

            const overlay     = document.getElementById('notifDetailOverlay');
            const iconWrap    = document.getElementById('notifDetailIcon');
            const iconGlyph   = document.getElementById('notifDetailIconGlyph');
            const titleEl     = document.getElementById('notifDetailTitle');
            const messageEl   = document.getElementById('notifDetailMessage');
            const timeEl      = document.getElementById('notifDetailTime');
            const exitBtn     = document.getElementById('notifDetailExitBtn');
            const gotoBtn     = document.getElementById('notifDetailGotoBtn');
            if (!overlay) return;

            function openDetail(item) {
                const title   = item.dataset.title || 'Notification';
                const message = item.dataset.message || '';
                const time    = item.dataset.time || '';
                const url     = item.dataset.url && item.dataset.url !== '#' ? item.dataset.url : '/inventory';
                const type    = item.dataset.type || 'reservation';

                titleEl.textContent   = title;
                messageEl.textContent = message;
                timeEl.textContent    = time;
                gotoBtn.href          = url;

                iconWrap.className  = 'notif-detail-icon type-' + type;
                iconGlyph.className = 'fa-solid ' + (['low_stock', 'inventory'].includes(type) ? 'fa-boxes-stacked' : (type === 'pos' ? 'fa-receipt' : 'fa-ticket'));

                const wasUnread = item.classList.contains('unread');
                item.classList.remove('unread');
                item.querySelector('.notif-dot')?.remove();

                if (wasUnread) {
                    const id = item.dataset.id;
                    const readUrl = item.dataset.readUrl
                        || (id ? NOTIF_READ_URL_PATTERN.replace('__ID__', id) : null);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    if (readUrl) {
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                        }).catch(() => {});
                    }
                }

                overlay.classList.add('active');
                document.querySelectorAll('.notif-panel.open').forEach(p => p.classList.remove('open'));
                document.querySelectorAll('.notif-btn.open').forEach(b => b.classList.remove('open'));
            }

            function closeDetail() {
                overlay.classList.remove('active');
            }

            document.addEventListener('click', (e) => {
                if (e.target.closest('.notif-row-actions')) return;
                const item = e.target.closest('.notif-item, .notif-row');
                if (!item) return;
                openDetail(item);
            });

            document.addEventListener('keydown', (e) => {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                if (e.target.closest('.notif-row-actions')) return;
                const item = e.target.closest('.notif-item, .notif-row');
                if (!item) return;
                e.preventDefault();
                openDetail(item);
            });

            exitBtn.addEventListener('click', closeDetail);
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) closeDetail();
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') closeDetail();
            });
        })();

        function markAllNotifsRead(btn) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            }).catch(() => {});

            document.querySelectorAll('.notif-item.unread, .nav-notif-item.unread').forEach(item => item.classList.remove('unread'));
            document.querySelectorAll('.notif-dot').forEach(dot => dot.remove());
            document.querySelectorAll('.notif-badge').forEach(badge => badge.remove());
            // All notifications are now read, so remove the persistent highlight from both bell buttons.
            document.querySelectorAll('.notif-btn').forEach(b => b.classList.remove('has-unread'));
            document.getElementById('navNotifLink')?.classList.remove('has-unread');
            const navCount = document.getElementById('navNotifCount');
            if (navCount) {
                navCount.textContent = '0';
                navCount.classList.add('is-zero');
            }
            btn.remove();
        }

        function openLogoutModal() {
            document.getElementById('logoutModalOverlay').classList.add('active');
        }

        function closeLogoutModal() {
            document.getElementById('logoutModalOverlay').classList.remove('active');
        }

        document.getElementById('logoutModalOverlay')?.addEventListener('click', function(e) {
            if (e.target === this) closeLogoutModal();
        });
        document.addEventListener('keydown', function(e) {
            const overlay = document.getElementById('logoutModalOverlay');
            if (!overlay || !overlay.classList.contains('active')) return;

            if (e.key === 'Escape') {
                closeLogoutModal();
            } else if (e.key === 'Enter') {
                e.preventDefault();
                window.location.href = '/logout';
            }
        });
    </script>

    <script>
        (function() {
            @if (!$canViewNotifications)
                return;
            @endif

            const iconMap = { low_stock: 'fa-boxes-stacked', inventory: 'fa-boxes-stacked', pos: 'fa-receipt' };
            const NOTIF_POLL_URL = @json(route('notifications.poll'));

            // ---- Badge + bell highlight helpers ----
            function setBadgeCount(btnSel, count) {
                const btn = document.querySelector(btnSel);
                if (!btn) return;

                let el = btn.querySelector('.notif-badge');

                if (count <= 0) {
                    el?.remove();
                    btn.classList.remove('has-unread');
                    return;
                }

                if (!el) {
                    el = document.createElement('span');
                    el.className = 'notif-badge';
                    btn.appendChild(el);
                }
                el.textContent = count > 9 ? '9+' : count;
                btn.classList.add('has-unread');
            }

            function buildItemHtml(n) {
                const icon = iconMap[n.type] || 'fa-ticket';
                const unreadClass = n.is_read ? '' : 'unread';
                const dot = n.is_read ? '' : '<span class="notif-dot"></span>';
                return `
                    <div class="notif-item ${unreadClass}" role="button" tabindex="0"
                         data-id="${n.id}" data-read-url="${n.read_url}"
                         data-title="${n.title}" data-message="${n.message}"
                         data-time="${n.time}" data-url="${n.url || '#'}" data-type="${n.type}">
                        <div class="notif-icon type-${n.type}">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <div class="notif-body">
                            <div class="notif-title-row">
                                <span class="notif-title">${n.title}</span>
                                ${dot}
                            </div>
                            <div class="notif-message">${n.message}</div>
                            <div class="notif-time">${n.time}</div>
                        </div>
                    </div>`;
            }

            function renderList(sel, notifications) {
                const list = document.querySelector(sel);
                if (!list) return;

                if (!notifications.length) {
                    list.innerHTML = '<div class="notif-empty"><span class="notif-empty-text">No notifications yet.</span></div>';
                    return;
                }

                list.innerHTML = notifications.map(buildItemHtml).join('');
            }

            // ---- Core refresh routine, used by both polling and manual triggers ----
            window.refreshNotifications = function refreshNotifications() {
                fetch(NOTIF_POLL_URL, {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(r => r.json())
                    .then(data => {
                        setBadgeCount('#notifBtnDesktop', data.unread_count);
                        setBadgeCount('#notifBtnMobile', data.unread_count);
                        renderList('#notifPanelDesktop .notif-list', data.notifications);
                        renderList('#notifPanelMobile .notif-list', data.notifications);

                        const navCount = document.getElementById('navNotifCount');
                        const navLink = document.getElementById('navNotifLink');
                        const hasUnread = data.unread_count > 0;

                        if (navCount) {
                            navCount.textContent = data.unread_count > 9 ? '9+' : data.unread_count;
                            navCount.classList.toggle('is-zero', !hasUnread);
                        }
                        if (navLink) {
                            navLink.classList.toggle('has-unread', hasUnread);
                        }
                    })
                    .catch(() => {});
            };

            // Poll every 15 seconds for new notifications, no full page reload needed.
            setInterval(refreshNotifications, 15000);

            // Real-time push via Echo, if configured, updates instantly on top of polling.
            if (typeof Echo !== 'undefined') {
                Echo.private('admin-notifications')
                    .listen('.new-notification', () => refreshNotifications());
            }
        })();
    </script>

    {{-- Page-specific scripts --}}
    @stack('scripts')

</body>

</html>