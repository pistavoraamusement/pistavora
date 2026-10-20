{{-- Place at: resources/views/admin/system-logs/index.blade.php --}}
@extends('layouts.sidebar')

@section('title', 'System Logs')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        /* ── Page header ── */
        .page-head { background: var(--card); padding: 18px 24px; margin-bottom: 16px; border-radius: 16px; box-shadow: var(--shadow-sm); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; }
        .page-head .eyebrow { font-size: .66rem; font-weight: 700; color: var(--pink-deep); text-transform: uppercase; letter-spacing: .09em; margin-bottom: 4px; }
        .page-head h2 { font-family: 'Source Serif 4', serif; font-size: 1.35rem; font-weight: 700; color: var(--ink); line-height: 1.2; }
        .page-head .sub { font-size: 12px; color: var(--muted); margin-top: 3px; }
        .stat-strip { display: flex; align-items: stretch; flex-wrap: wrap; gap: 8px; }
        .stat-pill { display: flex; flex-direction: column; min-width: 92px; padding: 8px 14px; background: var(--bg); border: 1px solid var(--line); border-radius: 12px; }
        .stat-pill .label { font-size: .62rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .07em; }
        .stat-pill .value { font-family: 'Source Serif 4', serif; font-size: 1.25rem; font-weight: 700; color: var(--ink); line-height: 1.25; }
        .stat-pill .value.is-warn { color: #C98A1F; }
        .stat-pill .value.is-crit { color: var(--deduct); }
        .stat-note { align-self: center; font-size: 11px; color: var(--muted); padding-left: 4px; }

        /* ── Main card ── */
        .table-box { background: var(--card); border-radius: 16px; box-shadow: var(--shadow-sm); overflow: hidden; }
        .box-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; padding: 18px 22px 14px; }
        .box-head h3 { font-family: 'Source Serif 4', serif; font-size: 1.05rem; font-weight: 700; color: var(--ink); }
        .box-head-right { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
        .box-head .count { font-size: 12px; color: var(--muted); }

        /* ── Live indicator ── */
        .live-pill { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border: 1px solid var(--line-strong); border-radius: 999px; background: #fff; color: var(--ink-soft); font-size: 11px; font-weight: 600; font-family: 'Inter', sans-serif; cursor: pointer; user-select: none; }
        .live-pill:hover { border-color: var(--pink); }
        .live-dot { width: 8px; height: 8px; border-radius: 50%; background: #2E9E5B; box-shadow: 0 0 0 0 rgba(46, 158, 91, .5); animation: livePulse 1.8s infinite; }
        .live-pill.paused .live-dot { background: var(--muted); animation: none; }
        .live-pill.error .live-dot { background: #D14343; animation: none; }
        .live-time { color: var(--muted); font-weight: 500; }
        @keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(46, 158, 91, .5); } 70% { box-shadow: 0 0 0 7px rgba(46, 158, 91, 0); } 100% { box-shadow: 0 0 0 0 rgba(46, 158, 91, 0); } }

        /* ── Filters ── */
        .filter-bar { padding: 0 22px 16px; border-bottom: 1px solid var(--line); }
        .filter-row { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .filter-row .f { flex: 1 1 150px; min-width: 0; position: relative; }
        .filter-row .f.search { flex: 2 1 240px; }
        .filter-row .f.search svg { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 15px; height: 15px; color: var(--muted); pointer-events: none; }
        .filter-bar input[type="text"], .filter-bar input[type="date"], .filter-bar select { width: 100%; height: 38px; padding: 0 12px; border: 1px solid var(--line-strong); border-radius: 10px; font-size: 12.5px; font-family: 'Inter', sans-serif; color: var(--ink-soft); background: var(--bg); transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
        .filter-bar input[type="text"] { padding-left: 34px; }
        .filter-bar input:hover, .filter-bar select:hover { border-color: var(--pink); }
        .filter-bar input:focus, .filter-bar select:focus { outline: none; border-color: var(--pink-deep); background: #fff; box-shadow: 0 0 0 3px var(--pink-light); }
        .filter-bar select { cursor: pointer; appearance: none; -webkit-appearance: none; padding-right: 32px; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; }
        .filter-bar select.has-value { border-color: var(--pink-deep); color: var(--pink-deep); font-weight: 600; background-color: #fff; }
        .chip-btn { display: inline-flex; align-items: center; gap: 6px; height: 38px; padding: 0 14px; border: 1px solid var(--line-strong); border-radius: 999px; background: #fff; color: var(--ink-soft); font-size: 12px; font-weight: 600; cursor: pointer; font-family: 'Inter', sans-serif; transition: all .15s ease; user-select: none; white-space: nowrap; }
        .chip-btn svg { width: 14px; height: 14px; }
        .chip-btn:hover { border-color: var(--pink); }
        .chip-btn input { display: none; }
        .chip-btn.active { background: var(--deduct); border-color: var(--deduct); color: #fff; }
        .chip-btn.on { border-color: var(--pink-deep); color: var(--pink-deep); background: var(--pink-pale); }
        .filter-clear { display: none; height: 38px; padding: 0 6px; align-items: center; border: none; background: none; cursor: pointer; color: var(--pink-deep); font-size: 12px; font-weight: 600; font-family: 'Inter', sans-serif; white-space: nowrap; }
        .filter-clear.show { display: inline-flex; }
        .filter-clear:hover { text-decoration: underline; }
        .date-panel { display: none; gap: 10px; margin-top: 10px; flex-wrap: wrap; align-items: center; padding: 10px 12px; background: var(--bg); border: 1px dashed var(--line-strong); border-radius: 12px; width: fit-content; max-width: 100%; }
        .date-panel.open { display: flex; }
        .date-panel .dp-field { display: flex; align-items: center; gap: 8px; }
        .date-panel .dp-label { font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        .date-panel input[type="date"] { width: 160px; background: #fff; }
        .date-panel .dp-arrow { color: var(--muted); font-size: 14px; }

        /* ── Table ── */
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; scrollbar-width: none; -ms-overflow-style: none; position: relative; }
        .table-scroll.loading::before { content: ""; position: absolute; top: 0; left: 0; height: 2px; width: 40%; background: var(--pink-deep); z-index: 2; animation: loadBar 1s ease-in-out infinite; }
        @keyframes loadBar { 0% { left: -40%; } 100% { left: 100%; } }
        .table-scroll::-webkit-scrollbar { display: none; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; font-size: 12.5px; font-variant-numeric: tabular-nums; }
        th, td { padding: 13px 14px; text-align: left; border-bottom: 1px solid var(--line); vertical-align: top; }
        thead th { background: var(--ink); color: #fff; font-weight: 600; font-size: 12px; padding: 13px 14px; border-bottom: none; white-space: nowrap; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--pink-pale); }
        tbody tr.row-new { animation: rowFlash 3.2s ease-out; }
        @keyframes rowFlash { 0% { background: var(--pink-light); } 100% { background: transparent; } }
        td { color: var(--ink-soft); }
        td.nowrap { white-space: nowrap; }
        td .time-date { color: var(--ink); font-weight: 600; }
        td .time-clock { display: block; font-size: 11px; color: var(--muted); margin-top: 2px; }
        td.event { color: var(--ink); font-weight: 600; }
        td.desc { max-width: 340px; line-height: 1.5; }

        /* Clickable (reviewable) rows */
        tbody tr.clickable { cursor: pointer; }
        tbody tr.clickable:focus-visible { outline: 2px solid var(--pink-deep); outline-offset: -2px; }

        .badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 999px; font-size: 10.5px; font-weight: 700; letter-spacing: .02em; line-height: 1.4; text-transform: capitalize; }
        .badge::before { content: ""; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
        .badge-info { background: var(--pink-light); color: var(--pink-deep); }
        .badge-warning { background: #FBEFD3; color: #8A5A0B; }
        .badge-critical { background: #FBE0E0; color: #B02020; }
        .badge-anomaly { background: var(--ink); color: #fff; margin-left: 6px; text-transform: none; }
        .badge-anomaly::before { display: none; }

        /* Review badges */
        .badge-review, .badge-ok, .badge-bad { margin-left: 6px; text-transform: none; }
        .badge-review::before, .badge-ok::before, .badge-bad::before { display: none; }
        .badge-review { background: #fff; color: var(--pink-deep); border: 1px dashed var(--pink-deep); }
        .badge-ok { background: #DDF3E6; color: #1F7A45; }
        .badge-bad { background: #FBE0E0; color: #B02020; }

        .badge-role::before { display: none; }
        .badge-role { padding: 3px 11px; }
        .role-admin { background: #E8E4FB; color: #4B3BB5; }
        .role-cashier { background: #DDF3E6; color: #1F7A45; }
        .role-user { background: #E4EEFB; color: #1F5FA8; }
        .role-other { background: var(--bg); color: var(--muted); border: 1px solid var(--line); }
        td .user-email { display: block; font-size: 11px; color: var(--muted); margin-top: 3px; word-break: break-all; }
        .muted-dash { color: var(--muted); }
        .empty-row td { text-align: center; padding: 44px 20px !important; color: var(--muted); }
        .empty-row:hover { background: transparent; }

        /* ── Pagination ── */
        .pager-bar { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; padding: 14px 22px; border-top: 1px solid var(--line); }
        .pager-info { font-size: 12px; color: var(--muted); }
        .pager-info strong { color: var(--ink); font-weight: 600; }
        .pagination { display: flex; flex-wrap: wrap; align-items: center; gap: 5px; }
        .pagination a, .pagination span.page { min-width: 32px; padding: 7px 10px; border: 1px solid var(--line-strong); background: #fff; color: var(--ink-soft); border-radius: 8px; font-size: 12px; font-weight: 600; font-family: 'Inter', sans-serif; text-align: center; text-decoration: none; line-height: 1.2; }
        .pagination a:hover { border-color: var(--pink); color: var(--pink-deep); }
        .pagination .active { background: var(--pink-deep); border-color: var(--pink-deep); color: #fff; }
        .pagination .disabled { opacity: .4; cursor: not-allowed; }
        .pagination .dots { color: var(--muted); padding: 0 4px; font-size: 12px; }

        /* ── Review modal ── */
        .lm-overlay { position: fixed; inset: 0; z-index: 1000; background: rgba(15, 23, 42, .55); display: flex; align-items: center; justify-content: center; padding: 16px; }
        .lm-overlay[hidden] { display: none; }
        .lm-card { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; box-shadow: 0 20px 50px rgba(0, 0, 0, .3); overflow: hidden; }
        .lm-head { position: relative; padding: 16px 52px 14px 22px; border-bottom: 1px solid var(--line); }
        .lm-head h4 { font-family: 'Source Serif 4', serif; font-size: 1.1rem; font-weight: 700; color: var(--ink); }
        .lm-head p { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .lm-x { position: absolute; right: 12px; top: 14px; width: 32px; height: 32px; border: none; border-radius: 50%; background: #E4E6EB; cursor: pointer; color: #1c1e21; }
        .lm-x:hover { background: #D8DADF; }
        .lm-body { padding: 16px 22px 6px; }
        .lm-info { border: 1px solid var(--line); border-radius: 12px; background: var(--bg); overflow: hidden; }
        .lm-row { display: flex; gap: 12px; padding: 9px 14px; font-size: 12.5px; border-bottom: 1px solid var(--line); }
        .lm-row:last-child { border-bottom: none; }
        .lm-row span:first-child { flex: 0 0 82px; font-size: 11px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .05em; padding-top: 1px; }
        .lm-row span:last-child { color: var(--ink); font-weight: 500; word-break: break-word; }
        .lm-q { margin: 16px 0 4px; font-size: 14px; font-weight: 700; color: var(--ink); text-align: center; }
        .lm-status { margin: 10px 0 0; padding: 8px 12px; border-radius: 10px; font-size: 12px; font-weight: 600; text-align: center; }
        .lm-status.confirmed { background: #DDF3E6; color: #1F7A45; }
        .lm-status.suspicious { background: #FBE0E0; color: #B02020; }
        .lm-status[hidden] { display: none; }
        .lm-actions { display: flex; gap: 10px; padding: 14px 22px 20px; }
        .lm-btn { flex: 1; height: 40px; border: none; border-radius: 10px; font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; gap: 7px; text-decoration: none; transition: background .15s, opacity .15s; }
        .lm-btn:disabled { opacity: .5; cursor: not-allowed; }
        .lm-btn.yes { background: #DDF3E6; color: #1F7A45; }
        .lm-btn.yes:hover:not(:disabled) { background: #C9EBD7; }
        .lm-btn.no { background: #DCE9FB; color: #1F5FA8; }
        .lm-btn.no:hover:not(:disabled) { background: #C8DCF7; }
        .lm-btn.primary { background: var(--pink-deep); color: #fff; }
        .lm-btn.primary:hover { background: var(--pink-dark); }
        .lm-btn.ghost { background: #F0F2F5; color: var(--ink-soft); }
        .lm-btn.ghost:hover { background: #E4E6EB; }
        .lm-secure { padding: 26px 22px 8px; text-align: center; }
        .lm-secure .ico { width: 56px; height: 56px; margin: 0 auto 12px; border-radius: 50%; background: #FBE0E0; color: #B02020; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .lm-secure h5 { font-family: 'Source Serif 4', serif; font-size: 1.1rem; font-weight: 700; color: var(--ink); }
        .lm-secure p { margin-top: 6px; font-size: 13px; line-height: 1.55; color: var(--ink-soft); }
        .lm-pane[hidden] { display: none; }
        .lm-top { display: flex; align-items: center; gap: 14px; padding: 20px 52px 16px 22px; border-bottom: 1px solid var(--line); position: relative; }
        .lm-top .lm-icon { width: 44px; height: 44px; flex-shrink: 0; border-radius: 12px; background: #FBEFD3; color: #8A5A0B; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .lm-top h4 { font-family: 'Source Serif 4', serif; font-size: 1.1rem; font-weight: 700; color: var(--ink); line-height: 1.2; }
        .lm-top p { font-size: 12px; color: var(--muted); margin-top: 3px; }
        .lm-top .lm-x { top: 18px; }
        .lm-summary { margin-bottom: 12px; padding: 11px 14px; border-radius: 12px; background: #FFF8E6; border: 1px solid #F3E2B3; color: #6B4A0A; font-size: 12.5px; line-height: 1.55; }
        .lm-summary strong { color: #4A3206; }
        .lm-error { margin: 10px 0 0; padding: 9px 12px; border-radius: 10px; background: #FBE0E0; color: #9B1C1C; font-size: 12px; line-height: 1.5; }
        .lm-error[hidden] { display: none; }
        .lm-ago { color: var(--muted); font-weight: 500; }

        @media(max-width:900px) {
            .page-head h2 { font-size: 1.1rem; }
            table { min-width: 780px; font-size: 12px; }
            th, td { padding: 10px 10px; }
        }
        @media(max-width:600px) {
            .stat-strip { width: 100%; }
            .stat-pill { flex: 1 1 calc(50% - 8px); }
            .stat-note { display: none; }
            .box-head, .filter-bar, .pager-bar { padding-left: 14px; padding-right: 14px; }
            .pager-bar { justify-content: center; }
            .filter-row .f, .filter-row .f.search { flex: 1 1 100%; }
            .date-panel { width: 100%; }
            .date-panel .dp-field { width: 100%; }
            .date-panel input[type="date"] { flex: 1; width: auto; }
            .lm-actions { flex-direction: column; }
        }
    </style>
@endsection

@section('content')

    @php
        // Fallback kung luma pa ang controller (walang $reviewableEvents)
        $reviewableEvents = $reviewableEvents ?? ['login_failed', 'password_change_failed'];
        $currentUsername = $currentUsername ?? null;

        $badgeClass = [
            'info' => 'badge-info',
            'warning' => 'badge-warning',
            'critical' => 'badge-critical',
        ];

        // Old log rows were saved in Tagalog. This translates them when shown.
        $descriptionMap = [
            'Bawal na access:' => 'Unauthorized access:',
            'Maling password' => 'Incorrect password',
            'Nabigong login' => 'Failed login',
            'Matagumpay na login' => 'Successful login',
            'Walang logs na tugma sa filter.' => 'No logs match your filters.',
            'failed login attempts mula sa IP' => 'failed login attempts from IP',
            'sa loob ng' => 'within',
            'minuto' => 'minutes',
            'Nag-login si' => 'Logged in:',
            'Nag-generate ng bagong check-in QR na naka-set sa petsang' => 'Generated a new check-in QR set to',
            '(hindi ngayon)' => '(not today)',
            'Na-approve ang reservation' => 'Approved reservation',
            '(hindi dumaan sa payment verification)' => '(did not go through payment verification)',
        ];

        $moduleLabels = [
            'auth' => 'Login',
            'attendance' => 'Attendance',
            'cashier' => 'Cashier',
        ];
        $defaultModules = ['attendance', 'auth', 'cashier'];
        $hiddenModules = ['system_logs', 'system-logs', 'system logs', 'systemlogs'];

        $moduleLabel = function ($mod) use ($moduleLabels) {
            $key = strtolower(trim((string) $mod));
            return $moduleLabels[$key] ?? ucfirst(str_replace(['_', '-'], ' ', $mod));
        };

        $visibleModules = collect($modules)
            ->merge($defaultModules)
            ->map(fn($m) => (string) $m)
            ->reject(fn($m) => $m === '' || in_array(strtolower(trim($m)), $hiddenModules, true))
            ->unique(fn($m) => strtolower(trim($m)))
            ->values();

        $roleOf = function ($log) {
            $role = data_get($log, 'role')
                ?? data_get($log, 'user_role')
                ?? data_get($log, 'user.role')
                ?? data_get($log, 'user.user_type')
                ?? null;

            if (!$role) {
                $desc = (string) data_get($log, 'description', '');
                if (preg_match('/\((admin|administrator|cashier|user|staff|employee)\)/i', $desc, $m)) {
                    $role = $m[1];
                } elseif (preg_match('/\b(administrator|admin|cashier)\b/i', $desc, $m)) {
                    $role = $m[1];
                }
            }

            if (!$role) {
                return null;
            }

            $role = strtolower(trim((string) $role));
            return $role === 'administrator' ? 'admin' : $role;
        };

        // Saan pupunta ang "Change password" button (My Account page)
        $accountUrl = \Illuminate\Support\Facades\Route::has('my-account.index')
            ? route('my-account.index')
            : (\Illuminate\Support\Facades\Route::has('my-account') ? route('my-account') : url('/admin/my-account'));

        $filterKeys = ['severity', 'module', 'event_type', 'from', 'to', 'search', 'anomaly_only'];
        $hasFilters = collect(request()->only($filterKeys))->filter()->isNotEmpty();
        $hasDates = request()->filled('from') || request()->filled('to');
    @endphp

    {{-- ── Compact header ── --}}
    <div class="page-head">
        <div>
            <div class="eyebrow">Admin &middot; Security</div>
            <h2>System Logs</h2>
            <p class="sub">Security and unusual events across the system.</p>
        </div>

        <div class="stat-strip" id="statStrip">
            <div class="stat-pill">
                <span class="label">Events</span>
                <span class="value">{{ $summary['total'] }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Anomalies</span>
                <span class="value {{ $summary['anomalies'] > 0 ? 'is-warn' : '' }}">{{ $summary['anomalies'] }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Critical</span>
                <span class="value {{ $summary['critical'] > 0 ? 'is-crit' : '' }}">{{ $summary['critical'] }}</span>
            </div>
            <div class="stat-pill">
                <span class="label">Failed logins</span>
                <span class="value">{{ $summary['failed_logins'] }}</span>
            </div>
            <span class="stat-note">Last 24 hrs</span>
        </div>
    </div>

    {{-- ── Log table card ── --}}
    <div class="table-box">

        <div class="box-head">
            <h3>Log entries</h3>
            <div class="box-head-right">
                <span class="count" id="tableCount">
                    @if ($logs->total() > 0)
                        Showing {{ $logs->firstItem() }}&ndash;{{ $logs->lastItem() }} of {{ $logs->total() }}
                    @else
                        No entries
                    @endif
                </span>
                <button type="button" class="live-pill" id="livePill" title="Click to pause / resume auto-refresh">
                    <span class="live-dot"></span>
                    <span id="liveLabel">Live</span>
                    <span class="live-time" id="liveTime"></span>
                </button>
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="filter-bar" id="filterForm">
            <div class="filter-row">
                <div class="f search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="7" />
                        <path d="M21 21l-4.3-4.3" />
                    </svg>
                    <input type="text" name="search" id="fSearch" value="{{ request('search') }}"
                        placeholder="Search email, IP or text&hellip;" aria-label="Search" autocomplete="off">
                </div>

                <div class="f">
                    <select name="severity" id="fSeverity" aria-label="Severity">
                        <option value="">All severity</option>
                        @foreach (['info', 'warning', 'critical'] as $sev)
                            <option value="{{ $sev }}" @selected(request('severity') === $sev)>{{ ucfirst($sev) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="f">
                    <select name="module" id="fModule" aria-label="Module">
                        <option value="">All modules</option>
                        @foreach ($visibleModules as $mod)
                            <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $moduleLabel($mod) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="f">
                    <select name="event_type" id="fEvent" aria-label="Event type">
                        <option value="">All events</option>
                        @foreach ($eventTypes as $type)
                            <option value="{{ $type }}" @selected(request('event_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="button" class="chip-btn {{ $hasDates ? 'on' : '' }}" id="dateToggle">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" />
                        <path d="M16 2v4M8 2v4M3 10h18" />
                    </svg>
                    Date range
                </button>

                <label class="chip-btn {{ request('anomaly_only') ? 'active' : '' }}" id="anomalyToggle">
                    <input type="checkbox" name="anomaly_only" value="1" id="anomalyOnly"
                        @checked(request('anomaly_only'))>
                    Anomalies only
                </label>

                <button type="button" class="filter-clear {{ $hasFilters ? 'show' : '' }}" id="filterClear">
                    Clear filters
                </button>
            </div>

            <div class="date-panel {{ $hasDates ? 'open' : '' }}" id="datePanel">
                <div class="dp-field">
                    <span class="dp-label">From</span>
                    <input type="date" name="from" id="fFrom" value="{{ request('from') }}" aria-label="From date">
                </div>
                <span class="dp-arrow">&rarr;</span>
                <div class="dp-field">
                    <span class="dp-label">To</span>
                    <input type="date" name="to" id="fTo" value="{{ request('to') }}" aria-label="To date">
                </div>
            </div>
        </form>

        <div class="table-scroll" id="tableScroll">
            <table>
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Severity</th>
                        <th>Module</th>
                        <th>Event</th>
                        <th>User</th>
                        <th>IP</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody id="logBody">
                    @forelse ($logs as $log)
                        @php
                            $role = $roleOf($log);
                            $who = data_get($log, 'user.fullname') ?: (data_get($log, 'user.username') ?: $log->email);
                            $descText = str_replace(array_keys($descriptionMap), array_values($descriptionMap), (string) $log->description);
                            // Username na sinubukan (galing sa description: "Failed login for username: admin")
                            $attempted = null;
                            if (preg_match('/username:\s*([^\s,]+)/i', $descText, $um)) {
                                $attempted = $um[1];
                            }
                            $reviewable = in_array($log->event_type, $reviewableEvents, true);
                            $rs = $log->review_status ?? null;
                        @endphp
                        <tr data-id="{{ $log->id }}"
                            @if ($reviewable)
                                class="clickable" tabindex="0" title="Click to review"
                                data-reviewable="1"
                                data-status="{{ $rs }}"
                                data-event="{{ $log->event_type }}"
                                data-date="{{ $log->created_at->format('M d, Y') }}"
                                data-clock="{{ $log->created_at->format('h:i:s A') }}"
                                data-ip="{{ $log->ip_address }}"
                                data-who="{{ $attempted ?: $who }}"
                                data-mine="{{ $attempted && $currentUsername && strcasecmp($attempted, $currentUsername) === 0 ? 1 : 0 }}"
                                data-ts="{{ $log->created_at->toIso8601String() }}"
                                data-desc="{{ $descText }}"
                            @endif>
                            <td class="nowrap">
                                <span class="time-date">{{ $log->created_at->format('M d, Y') }}</span>
                                <span class="time-clock">{{ $log->created_at->format('h:i:s A') }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $badgeClass[$log->severity] ?? 'badge-info' }}">{{ $log->severity }}</span>
                            </td>
                            <td>
                                @if ($log->module)
                                    {{ $moduleLabel($log->module) }}
                                @else
                                    <span class="muted-dash">—</span>
                                @endif
                            </td>
                            <td class="event">
                                {{ $log->event_type }}
                                @if ($log->is_anomaly)
                                    <span class="badge badge-anomaly">anomaly</span>
                                @endif
                                @if ($reviewable)
                                    @if ($rs === 'confirmed')
                                        <span class="badge badge-ok">It was me</span>
                                    @elseif ($rs === 'suspicious')
                                        <span class="badge badge-bad">Not me</span>
                                    @else
                                        <span class="badge badge-review">Review</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if ($role)
                                    <span class="badge badge-role {{ in_array($role, ['admin', 'cashier', 'user'], true) ? 'role-' . $role : 'role-other' }}">{{ $role }}</span>
                                @elseif ($who)
                                    {{ $who }}
                                @else
                                    <span class="muted-dash">—</span>
                                @endif
                            </td>
                            <td>
                                @if ($log->ip_address)
                                    {{ $log->ip_address }}
                                @else
                                    <span class="muted-dash">—</span>
                                @endif
                            </td>
                            <td class="desc">{{ $descText }}</td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7">No logs match your filters.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ── Pagination ── --}}
        <div id="pagerWrap">
            @if ($logs->total() > 0)
                @php
                    $logs->withQueryString();
                    $current = $logs->currentPage();
                    $last = $logs->lastPage();
                    $start = max(1, $current - 2);
                    $end = min($last, $current + 2);
                @endphp
                <div class="pager-bar">
                    <div class="pager-info">
                        Showing <strong>{{ $logs->firstItem() }}&ndash;{{ $logs->lastItem() }}</strong>
                        of <strong>{{ $logs->total() }}</strong> entries
                    </div>

                    @if ($logs->hasPages())
                        <div class="pagination">
                            @if ($logs->onFirstPage())
                                <span class="page disabled">&laquo; Prev</span>
                            @else
                                <a href="{{ $logs->previousPageUrl() }}">&laquo; Prev</a>
                            @endif

                            @if ($start > 1)
                                <a href="{{ $logs->url(1) }}">1</a>
                                @if ($start > 2)
                                    <span class="dots">&hellip;</span>
                                @endif
                            @endif

                            @for ($p = $start; $p <= $end; $p++)
                                @if ($p === $current)
                                    <span class="page active">{{ $p }}</span>
                                @else
                                    <a href="{{ $logs->url($p) }}">{{ $p }}</a>
                                @endif
                            @endfor

                            @if ($end < $last)
                                @if ($end < $last - 1)
                                    <span class="dots">&hellip;</span>
                                @endif
                                <a href="{{ $logs->url($last) }}">{{ $last }}</a>
                            @endif

                            @if ($logs->hasMorePages())
                                <a href="{{ $logs->nextPageUrl() }}">Next &raquo;</a>
                            @else
                                <span class="page disabled">Next &raquo;</span>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>

    </div>

    {{-- ── Review modal (failed login: "Yes, it's me" / "No, it's not me") ── --}}
    <div class="lm-overlay" id="lmOverlay" hidden>
        <div class="lm-card" role="dialog" aria-modal="true" aria-labelledby="lmTitle">

            {{-- Pane 1: details + question --}}
            <div class="lm-pane" id="lmAsk">
                <div class="lm-top">
                    <div class="lm-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
                    <div>
                        <h4 id="lmTitle">Failed login attempt</h4>
                        <p>Check if this was you</p>
                    </div>
                    <button type="button" class="lm-x" data-lm-close aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="lm-body">
                    <div class="lm-summary" id="lmSummary"></div>
                    <div class="lm-info">
                        <div class="lm-row"><span>When</span><span id="lmWhen">—</span></div>
                        <div class="lm-row"><span>Account</span><span id="lmWho">—</span></div>
                        <div class="lm-row"><span>IP address</span><span id="lmIp">—</span></div>
                    </div>
                    <div class="lm-status" id="lmStatus" hidden></div>
                    <div class="lm-error" id="lmError" hidden></div>
                    <p class="lm-q">Was this you?</p>
                </div>
                <div class="lm-actions">
                    <button type="button" class="lm-btn yes" id="lmYes"><i class="fa-solid fa-circle-check"></i> Yes, it's me</button>
                    <button type="button" class="lm-btn no" id="lmNo"><i class="fa-solid fa-circle-xmark"></i> No, it's not me</button>
                </div>
            </div>

            {{-- Pane 2: shown after "No, it's not me" --}}
            <div class="lm-pane" id="lmSecure" hidden>
                <div class="lm-secure">
                    <div class="ico"><i class="fa-solid fa-shield-halved"></i></div>
                    <h5 id="lmSecureTitle">Let's secure your account</h5>
                    <p id="lmSecureText"></p>
                </div>
                <div class="lm-actions">
                    <button type="button" class="lm-btn ghost" data-lm-close>Later</button>
                    <a href="{{ $accountUrl }}#pwForm" class="lm-btn primary" id="lmSecureBtn"><i class="fa-solid fa-key"></i> <span id="lmSecureBtnText">Change password</span></a>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('filterForm');
            const searchEl = document.getElementById('fSearch');
            const toggle = document.getElementById('anomalyToggle');
            const box = document.getElementById('anomalyOnly');
            const dateBtn = document.getElementById('dateToggle');
            const datePanel = document.getElementById('datePanel');
            const fromEl = document.getElementById('fFrom');
            const toEl = document.getElementById('fTo');
            const clearBtn = document.getElementById('filterClear');
            const selects = form.querySelectorAll('select');
            const scrollBox = document.getElementById('tableScroll');

            const REFRESH_MS = 10000;
            const PARTS = ['statStrip', 'tableCount', 'logBody', 'pagerWrap'];
            const pill = document.getElementById('livePill');
            const label = document.getElementById('liveLabel');
            const timeEl = document.getElementById('liveTime');

            let paused = false;
            let timer = null;
            let reqId = 0;
            let searchTimer = null;

            function markSelects() {
                selects.forEach(s => s.classList.toggle('has-value', s.value !== ''));
            }

            function hasAnyFilter() {
                return searchEl.value.trim() !== '' ||
                    Array.from(selects).some(s => s.value !== '') ||
                    fromEl.value !== '' || toEl.value !== '' || box.checked;
            }

            function syncUI() {
                markSelects();
                toggle.classList.toggle('active', box.checked);
                dateBtn.classList.toggle('on', fromEl.value !== '' || toEl.value !== '');
                clearBtn.classList.toggle('show', hasAnyFilter());
            }

            function applyFilters() {
                const params = new URLSearchParams();
                new FormData(form).forEach((v, k) => {
                    v = String(v).trim();
                    if (v !== '') params.set(k, v);
                });
                const qs = params.toString();
                history.replaceState(null, '', window.location.pathname + (qs ? '?' + qs : ''));
                syncUI();
                refresh(true);
            }

            selects.forEach(s => s.addEventListener('change', applyFilters));
            fromEl.addEventListener('change', applyFilters);
            toEl.addEventListener('change', applyFilters);
            box.addEventListener('change', applyFilters);

            searchEl.addEventListener('input', () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(applyFilters, 400);
            });

            form.addEventListener('submit', e => {
                e.preventDefault();
                clearTimeout(searchTimer);
                applyFilters();
            });

            dateBtn.addEventListener('click', () => datePanel.classList.toggle('open'));

            clearBtn.addEventListener('click', () => {
                searchEl.value = '';
                selects.forEach(s => s.value = '');
                fromEl.value = '';
                toEl.value = '';
                box.checked = false;
                applyFilters();
            });

            // ── LIVE AUTO-REFRESH ──
            function setState(state) {
                pill.classList.toggle('paused', state === 'paused');
                pill.classList.toggle('error', state === 'error');
                label.textContent = state === 'paused' ? 'Paused' : state === 'error' ? 'Offline' : 'Live';
            }

            function stamp() {
                timeEl.textContent = '· ' + new Date().toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                });
            }

            async function refresh(force = false) {
                if (!force && (paused || document.hidden)) return;
                const my = ++reqId;
                if (force) scrollBox.classList.add('loading');

                try {
                    const res = await fetch(window.location.href, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        },
                        cache: 'no-store',
                        credentials: 'same-origin'
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);

                    const html = await res.text();
                    if (my !== reqId) return;

                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    if (!doc.getElementById('logBody')) throw new Error('Unexpected response');

                    const oldIds = new Set(
                        Array.from(document.querySelectorAll('#logBody tr[data-id]')).map(r => r.dataset.id)
                    );

                    let bodyChanged = false;
                    PARTS.forEach(id => {
                        const cur = document.getElementById(id);
                        const next = doc.getElementById(id);
                        if (!cur || !next) return;
                        if (cur.innerHTML.trim() !== next.innerHTML.trim()) {
                            cur.innerHTML = next.innerHTML;
                            if (id === 'logBody') bodyChanged = true;
                        }
                    });

                    if (bodyChanged && !force) {
                        document.querySelectorAll('#logBody tr[data-id]').forEach(row => {
                            if (!oldIds.has(row.dataset.id)) row.classList.add('row-new');
                        });
                    }

                    setState(paused ? 'paused' : 'live');
                    stamp();
                } catch (e) {
                    if (my === reqId) setState('error');
                } finally {
                    if (my === reqId) scrollBox.classList.remove('loading');
                }
            }

            function start() {
                clearInterval(timer);
                timer = setInterval(() => refresh(false), REFRESH_MS);
            }

            pill.addEventListener('click', () => {
                paused = !paused;
                setState(paused ? 'paused' : 'live');
                if (!paused) refresh(false);
            });

            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) refresh(false);
            });

            // ── REVIEW MODAL (failed login) ──
            const CSRF = '{{ csrf_token() }}';
            const REVIEW_BASE = '{{ url('/admin/system-logs') }}';
            const overlay = document.getElementById('lmOverlay');
            const paneAsk = document.getElementById('lmAsk');
            const paneSecure = document.getElementById('lmSecure');
            const lmTitle = document.getElementById('lmTitle');
            const lmWhen = document.getElementById('lmWhen');
            const lmWho = document.getElementById('lmWho');
            const lmIp = document.getElementById('lmIp');
            const lmSummary = document.getElementById('lmSummary');
            const lmError = document.getElementById('lmError');
            const lmSecureTitle = document.getElementById('lmSecureTitle');
            const lmSecureText = document.getElementById('lmSecureText');
            const lmSecureBtn = document.getElementById('lmSecureBtn');
            const lmSecureBtnText = document.getElementById('lmSecureBtnText');
            const MY_ACCOUNT_URL = '{{ $accountUrl }}#pwForm';
            const ACCOUNTS_URL = '{{ url('/accounts') }}';
            let currentMine = true;
            let currentWho = '';
            const lmStatus = document.getElementById('lmStatus');
            const lmYes = document.getElementById('lmYes');
            const lmNo = document.getElementById('lmNo');
            let currentId = null;

            function esc(t) {
                const d = document.createElement('div');
                d.textContent = t;
                return d.innerHTML;
            }

            function ago(iso) {
                const sec = Math.round((new Date(iso).getTime() - Date.now()) / 1000);
                if (isNaN(sec)) return '';
                const rtf = new Intl.RelativeTimeFormat('en', { numeric: 'auto' });
                const abs = Math.abs(sec);
                if (abs < 60) return rtf.format(sec, 'second');
                if (abs < 3600) return rtf.format(Math.round(sec / 60), 'minute');
                if (abs < 86400) return rtf.format(Math.round(sec / 3600), 'hour');
                return rtf.format(Math.round(sec / 86400), 'day');
            }

            function openReview(row) {
                const d = row.dataset;
                currentId = d.id;
                const isPw = d.event === 'password_change_failed';
                const who = (d.who || '').trim();
                currentMine = d.mine === '1';
                currentWho = who;

                lmTitle.textContent = isPw ? 'Failed password change' : 'Failed login attempt';

                if (isPw) {
                    lmSummary.innerHTML = 'A password change' + (who ? ' for <strong>' + esc(who) + '</strong>' : '') +
                        ' was blocked because the current password was incorrect.';
                } else {
                    lmSummary.innerHTML = 'Someone tried to sign in' + (who ? ' as <strong>' + esc(who) + '</strong>' : '') +
                        ' but the username or password was incorrect.';
                }

                const rel = ago(d.ts);
                lmWhen.innerHTML = esc(d.date + ' · ' + d.clock) + (rel ? ' <span class="lm-ago">(' + esc(rel) + ')</span>' : '');
                lmWho.textContent = who || 'Unknown';
                lmIp.textContent = d.ip || 'Unknown';
                lmError.hidden = true;

                if (d.status === 'confirmed' || d.status === 'suspicious') {
                    lmStatus.hidden = false;
                    lmStatus.className = 'lm-status ' + d.status;
                    lmStatus.textContent = d.status === 'confirmed'
                        ? 'You marked this as you. You can change your answer below.'
                        : 'You marked this as not you. You can change your answer below.';
                } else {
                    lmStatus.hidden = true;
                }

                lmYes.disabled = false;
                lmNo.disabled = false;
                paneAsk.hidden = false;
                paneSecure.hidden = true;
                overlay.hidden = false;
            }

            function showSecurePane() {
                if (currentMine) {
                    lmSecureTitle.textContent = "Let's secure your account";
                    lmSecureText.textContent = 'We marked this attempt as suspicious. Since it targeted your account, we recommend changing your password right away.';
                    lmSecureBtn.href = MY_ACCOUNT_URL;
                    lmSecureBtnText.textContent = 'Change password';
                } else {
                    lmSecureTitle.textContent = 'Suspicious attempt recorded';
                    lmSecureText.textContent = currentWho
                        ? 'We marked this attempt on "' + currentWho + '" as suspicious. Consider resetting that account\'s password from Account Management.'
                        : 'We marked this attempt as suspicious. Review the accounts involved and reset passwords if needed.';
                    lmSecureBtn.href = ACCOUNTS_URL;
                    lmSecureBtnText.textContent = 'Open Accounts';
                }
            }

            function closeReview() {
                overlay.hidden = true;
                currentId = null;
            }

            async function sendDecision(decision) {
                if (!currentId) return;
                lmYes.disabled = true;
                lmNo.disabled = true;
                lmError.hidden = true;

                try {
                    const res = await fetch(REVIEW_BASE + '/' + currentId + '/review', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ decision: decision })
                    });

                    if (!res.ok) {
                        let msg = '';
                        try { msg = (await res.json()).message || ''; } catch (_) {}
                        const e = new Error(msg);
                        e.status = res.status;
                        throw e;
                    }

                    if (decision === 'suspicious') {
                        showSecurePane();
                        paneAsk.hidden = true;
                        paneSecure.hidden = false;
                    } else {
                        closeReview();
                    }
                    refresh(true);
                } catch (e) {
                    let text = 'Could not save your answer. Please try again.';
                    if (e.status === 404 || e.status === 405) {
                        text = 'The review route was not found. Add the POST route for /admin/system-logs/{id}/review.';
                    } else if (e.status === 419) {
                        text = 'Your session expired. Refresh the page and try again.';
                    } else if (e.status === 403) {
                        text = 'You are not allowed to do this.';
                    } else if (e.status === 500) {
                        text = 'Server error. Make sure the review migration was run (php artisan migrate).' + (e.message ? ' (' + e.message + ')' : '');
                    }
                    lmError.textContent = text;
                    lmError.hidden = false;
                    lmYes.disabled = false;
                    lmNo.disabled = false;
                }
            }

            lmYes.addEventListener('click', () => sendDecision('confirmed'));
            lmNo.addEventListener('click', () => sendDecision('suspicious'));

            overlay.addEventListener('click', e => {
                if (e.target === overlay || e.target.closest('[data-lm-close]')) closeReview();
            });
            document.addEventListener('keydown', e => {
                if (e.key === 'Escape' && !overlay.hidden) closeReview();
            });

            // Delegated: gumagana kahit napalitan ng live refresh ang rows
            const logBody = document.getElementById('logBody');
            logBody.addEventListener('click', e => {
                const row = e.target.closest('tr[data-reviewable]');
                if (row) openReview(row);
            });
            logBody.addEventListener('keydown', e => {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                const row = e.target.closest('tr[data-reviewable]');
                if (row) { e.preventDefault(); openReview(row); }
            });

            syncUI();
            stamp();
            start();
        })();
    </script>
@endpush