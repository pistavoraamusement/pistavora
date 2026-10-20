{{--
    resources/views/user/activity.blade.php
    Route: GET /app/activity -> user.activity
    Controller: App\Http\Controllers\User\ActivityLogController@index
    Expects: $logs (LengthAwarePaginator of system_logs rows), $group (string), $user
    Query string: ?type=auth|booking|waiver|account|security  &q=search text  &from=YYYY-MM-DD  &to=YYYY-MM-DD
    NOTE: the controller must apply q / from / to to the query (see ActivityLogController).
--}}
@extends('layouts.user')

@section('title', 'Activity Log')
@section('page-title', 'Activity Log')
@section('page-subtitle', 'Your recent account activity')
@section('hide-page-title', '1')
@section('body-class', 'page-uniform')

@push('styles')
<style>
    /* ===== Page hero (identical on every page) ===== */
    .pg-hero{
        --pg-a:var(--pink-deep); --pg-b:#F2762E;
        position:relative; overflow:hidden; display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap;
        margin:0 0 18px; padding:28px 30px; border-radius:26px; color:#fff; font-family:inherit;
        background:linear-gradient(135deg,var(--pg-a) 0%,var(--pg-b) 100%);
        border:1px solid rgba(255,255,255,.18);
        box-shadow:0 18px 40px -22px var(--pg-a);
    }
    [data-theme="dark"] .pg-hero{ --pg-a:#8E2347; --pg-b:#B5501C; }
    .pg-hero::before{ content:""; position:absolute; inset:0; pointer-events:none; opacity:.55;
        background-image:radial-gradient(rgba(255,255,255,.22) 1.4px, transparent 1.6px); background-size:18px 18px;
        -webkit-mask-image:linear-gradient(115deg, transparent 20%, #000 100%); mask-image:linear-gradient(115deg, transparent 20%, #000 100%); }
    .pg-hero::after{ content:""; position:absolute; right:-70px; top:-90px; width:260px; height:260px; border-radius:50%;
        background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.35), rgba(255,255,255,0) 70%); pointer-events:none; }
    .pg-hero-glow{ position:absolute; left:-60px; bottom:-110px; width:240px; height:240px; border-radius:50%;
        background:radial-gradient(circle, rgba(255,255,255,.18), rgba(255,255,255,0) 70%); pointer-events:none; }
    .pg-deco{ position:absolute; z-index:0; pointer-events:none; user-select:none; line-height:1;
        filter:drop-shadow(0 6px 10px rgba(0,0,0,.18)); animation:pgFloat 6s ease-in-out infinite; }
    .pg-deco.d1{ right:32%; top:14px;    font-size:26px; opacity:.45; animation-delay:0s; }
    .pg-deco.d2{ right:20%; bottom:10px; font-size:34px; opacity:.4;  animation-delay:1.2s; }
    .pg-deco.d3{ right:44%; bottom:14px; font-size:22px; opacity:.35; animation-delay:2.4s; }
    @keyframes pgFloat{ 0%,100%{ transform:translateY(0) rotate(-6deg);} 50%{ transform:translateY(-8px) rotate(6deg);} }
    .pg-hero-text{ position:relative; z-index:1; min-width:0; }
    .pg-hero-badge{ display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; background:rgba(255,255,255,.18); color:#fff;
        font-weight:800; letter-spacing:.1em; font-size:11px; text-transform:uppercase; backdrop-filter:blur(4px); }
    .pg-hero h2{ margin:10px 0 4px; font-family:inherit; font-size:30px; font-weight:800; letter-spacing:-.01em; line-height:1.2; color:#fff; text-shadow:0 2px 12px rgba(0,0,0,.18); }
    .pg-hero p{ margin:0; font-family:inherit; font-size:14px; color:rgba(255,255,255,.9); }
    @media (max-width:720px){
        .pg-hero{ padding:22px 20px; }
        .pg-hero h2{ font-size:26px; }
        .pg-deco.d1{ right:8%; } .pg-deco.d2{ right:4%; } .pg-deco.d3{ display:none; }
    }
    /* ===== Theme toggle inside hero ===== */
    .pg-theme-toggle{
      position:absolute; z-index:2; top:50%; right:22px; transform:translateY(-50%);
      width:44px; height:44px; border-radius:50%; display:grid; place-items:center;
      border:1.5px solid rgba(255,255,255,.55); background:rgba(255,255,255,.16); color:#fff;
      font-size:20px; line-height:1; cursor:pointer; backdrop-filter:blur(4px); padding:0;
      transition:background .2s, transform .2s;
    }
    .pg-theme-toggle:hover{ background:rgba(255,255,255,.28); transform:translateY(-50%) scale(1.08); }
    .pg-theme-toggle:focus-visible{ outline:3px solid rgba(255,255,255,.6); outline-offset:2px; }
    @media (max-width:720px){
      .pg-theme-toggle{ top:18px; right:16px; transform:none; width:40px; height:40px; font-size:18px; }
      .pg-theme-toggle:hover{ transform:scale(1.08); }
      .pg-hero-text{ padding-right:52px; }
    }
    @media (prefers-reduced-motion:reduce){ .pg-deco{ animation:none; } }

    .al-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;}
    .al-filters .mb-tab{display:inline-flex;align-items:center;text-decoration:none;}

    /* ---------- search + date range ---------- */
    .al-search{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:16px;}
    .al-field{display:flex;flex-direction:column;gap:4px;}
    .al-field label{font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);}
    .al-field.grow{flex:1 1 240px;min-width:200px;}
    .al-input{
        width:100%;height:40px;padding:0 12px;border-radius:10px;font:inherit;font-size:13.5px;
        color:var(--ink);background:var(--card-bg,#fff);border:1px solid var(--line);outline:none;
        transition:border-color .15s, box-shadow .15s;
    }
    .al-input:focus{border-color:var(--pink-deep,#B82850);box-shadow:0 0 0 3px rgba(184,40,80,.15);}
    [data-theme="dark"] .al-input{background:rgba(255,255,255,.05);color:var(--d-text,#fff);color-scheme:dark;}

    /* ---------- apply / clear buttons ---------- */
    .al-btns{display:flex;gap:8px;align-items:center;}

    .al-apply{
        display:inline-flex;align-items:center;justify-content:center;gap:8px;
        height:40px;padding:0 20px;border:0;border-radius:10px;cursor:pointer;
        font:inherit;font-size:13.5px;font-weight:700;letter-spacing:.01em;white-space:nowrap;
        color:#fff;background:linear-gradient(135deg,var(--pink,#E0496E) 0%,var(--pink-deep,#B82850) 100%);
        box-shadow:0 2px 8px rgba(184,40,80,.28);
        transition:transform .12s ease, box-shadow .15s ease, filter .15s ease;
    }
    .al-apply i{font-size:13px;}
    .al-apply:hover{transform:translateY(-1px);box-shadow:0 6px 14px rgba(184,40,80,.32);filter:brightness(1.04);}
    .al-apply:active{transform:translateY(0) scale(.97);box-shadow:0 2px 6px rgba(184,40,80,.25);}
    .al-apply:focus-visible{outline:none;box-shadow:0 0 0 3px rgba(184,40,80,.3);}

    .al-clear{
        display:inline-flex;align-items:center;gap:6px;height:40px;padding:0 14px;border-radius:10px;
        font-size:13px;font-weight:700;color:var(--muted);text-decoration:none;white-space:nowrap;
        background:transparent;border:1px solid var(--line);
        transition:background .15s ease, color .15s ease, border-color .15s ease;
    }
    .al-clear:hover{color:var(--ink);background:var(--gray-tile,#F6F4F7);border-color:var(--muted);text-decoration:none;}
    [data-theme="dark"] .al-clear:hover{background:rgba(255,255,255,.06);}

    /* ---------- table ---------- */
    .al-card{padding:0;overflow:hidden;}
    .al-card:hover{transform:none;box-shadow:var(--shadow-card);}
    .al-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch;}
    .al-table{width:100%;min-width:720px;border-collapse:collapse;font-size:13.5px;}
    .al-table thead th{
        padding:12px 18px;text-align:left;white-space:nowrap;
        font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;color:var(--muted);
        background:var(--gray-tile,#F6F4F7);border-bottom:1px solid var(--line);
    }
    .al-table tbody td{padding:14px 18px;vertical-align:middle;border-bottom:1px solid var(--line);color:var(--ink);}
    .al-table tbody tr:last-child td{border-bottom:none;}
    .al-table tbody tr:hover td{background:var(--gray-tile,#F6F4F7);}
    [data-theme="dark"] .al-table thead th,
    [data-theme="dark"] .al-table tbody tr:hover td{background:rgba(255,255,255,.04);}

    .al-act{display:flex;align-items:center;gap:12px;min-width:240px;}
    .al-act .wp-ico{flex-shrink:0;}
    .al-title{margin:0;font-size:14px;font-weight:600;color:var(--ink);word-break:break-word;}
    .al-sub{display:block;margin-top:2px;font-size:12px;color:var(--muted);}
    .al-muted{color:var(--muted);white-space:nowrap;}
    .al-when{white-space:nowrap;}
    .al-when b{display:block;font-size:13px;font-weight:700;color:var(--ink);}
    .al-when span{font-size:12px;color:var(--muted);}
    .al-types{display:flex;gap:6px;flex-wrap:wrap;align-items:center;}

    .al-empty{margin:18px;padding:28px 18px;border-radius:12px;background:var(--gray-tile,#F3F3F3);text-align:center;color:var(--ink-soft);font-size:14px;}

    /* ---------- pager ---------- */
    .al-pager{display:flex;justify-content:center;align-items:center;gap:12px;margin-top:16px;}
    .al-pager .pg-btn{display:inline-flex;align-items:center;padding:8px 16px;text-decoration:none;}
    .al-pager .al-disabled{opacity:.45;pointer-events:none;}
    .al-page{font-size:12.5px;color:var(--muted);}
    .al-count{margin:10px 4px 0;font-size:12.5px;color:var(--muted);}

    @media (max-width:640px){
        .al-field{flex:1 1 calc(50% - 5px);}
        .al-field.grow{flex:1 1 100%;}
        .al-btns{flex:1 1 100%;}
        .al-apply{flex:1;}
    }

    /* ============ DARK MODE FIXES ============ */
    [data-theme="dark"] .al-filters,
    [data-theme="dark"] .al-search,
    [data-theme="dark"] .al-card,
    [data-theme="dark"] .al-count,
    [data-theme="dark"] .al-pager{
        --ink:var(--d-text,#F4EEF8);
        --ink-soft:var(--d-text-soft,#CFC5DC);
        --muted:#A99DBA;
        --line:var(--d-border,rgba(255,255,255,.16));
        --gray-tile:rgba(255,255,255,.05);
    }
    [data-theme="dark"] .al-card{ background:var(--d-surface,#1D1930); border:1px solid var(--line); }

    /* text */
    [data-theme="dark"] .al-title,
    [data-theme="dark"] .al-when b,
    [data-theme="dark"] .al-table tbody td{ color:var(--ink); }
    [data-theme="dark"] .al-sub,
    [data-theme="dark"] .al-muted,
    [data-theme="dark"] .al-when span,
    [data-theme="dark"] .al-page,
    [data-theme="dark"] .al-count,
    [data-theme="dark"] .al-field label,
    [data-theme="dark"] .al-table thead th{ color:var(--muted); }
    [data-theme="dark"] .al-empty{ background:rgba(255,255,255,.05); color:var(--ink-soft); }

    /* search / date inputs */
    [data-theme="dark"] .al-input{ background:rgba(255,255,255,.06); color:var(--ink); border-color:var(--line); }
    [data-theme="dark"] .al-input::placeholder{ color:var(--muted); opacity:1; }
    [data-theme="dark"] .al-input:focus{ border-color:#FF8FAE; box-shadow:0 0 0 3px rgba(255,143,174,.22); }
    [data-theme="dark"] .al-clear{ color:var(--ink-soft); border-color:var(--line); }
    [data-theme="dark"] .al-clear:hover{ color:var(--ink); border-color:var(--muted); }

    /* filter tabs + pager buttons */
    [data-theme="dark"] .al-filters .mb-tab:not(.active){ color:var(--ink-soft); background:rgba(255,255,255,.05); border:1px solid var(--line); }
    [data-theme="dark"] .al-filters .mb-tab:not(.active):hover{ color:var(--ink); background:rgba(255,255,255,.1); }
    [data-theme="dark"] .al-pager .pg-btn{ color:var(--ink); background:rgba(255,255,255,.06); border:1px solid var(--line); border-radius:10px; }

    /* type badges */
    [data-theme="dark"] .al-card .wp-badge{ background:rgba(255,255,255,.1); color:#E8E0F2; }
    [data-theme="dark"] .al-card .wp-badge.info{ background:rgba(122,76,224,.28); color:#D2C0FF; }
    [data-theme="dark"] .al-card .wp-badge.success{ background:rgba(18,181,166,.22); color:#5EE8DA; }
    [data-theme="dark"] .al-card .wp-badge.warn{ background:rgba(255,201,72,.18); color:#FFD98A; }
    [data-theme="dark"] .al-card .wp-badge.danger{ background:rgba(226,75,74,.24); color:#FF9C9B; }

    /* row icons */
    [data-theme="dark"] .al-card .wp-ico{ background:rgba(255,255,255,.08); color:#D9CFE6; }
    [data-theme="dark"] .al-card .wp-ico.c-violet{ background:rgba(122,76,224,.28); color:#D2C0FF; }
    [data-theme="dark"] .al-card .wp-ico.c-teal{ background:rgba(18,181,166,.22); color:#5EE8DA; }
    [data-theme="dark"] .al-card .wp-ico.c-amber{ background:rgba(255,201,72,.18); color:#FFD98A; }
    [data-theme="dark"] .al-card .wp-ico.c-danger{ background:rgba(226,75,74,.24); color:#FF9C9B; }
</style>
@endpush

@section('content')
    @php
        $filters = [
            ''         => 'All',
            'auth'     => 'Sign in',
            'booking'  => 'Bookings',
            'waiver'   => 'Waiver',
            'account'  => 'Account',
            'security' => 'Security',
        ];
        $look = [
            'auth'     => ['icon' => 'bi-box-arrow-in-right', 'ico' => 'c-violet', 'badge' => 'info',    'label' => 'Sign in'],
            'booking'  => ['icon' => 'bi-calendar-check',     'ico' => 'c-teal',   'badge' => 'success', 'label' => 'Booking'],
            'waiver'   => ['icon' => 'bi-file-earmark-check', 'ico' => 'c-amber',  'badge' => 'warn',    'label' => 'Waiver'],
            'account'  => ['icon' => 'bi-person-gear',        'ico' => 'c-violet', 'badge' => '',        'label' => 'Account'],
            'security' => ['icon' => 'bi-shield-lock',        'ico' => '',         'badge' => 'danger',  'label' => 'Security'],
        ];

        // Search + date range (read from the query string; the controller applies them to the query)
        $q    = trim((string) request()->query('q', ''));
        $from = (string) request()->query('from', '');
        $to   = (string) request()->query('to', '');
        $isDate = fn ($v) => (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $v);
        if (!$isDate($from)) { $from = ''; }
        if (!$isDate($to))   { $to   = ''; }
        if ($from !== '' && $to !== '' && $from > $to) { [$from, $to] = [$to, $from]; }

        $hasFilter = ($group ?? '') !== '' || $q !== '' || $from !== '' || $to !== '';
        $keep = array_filter([
            'q'    => $q !== ''    ? $q    : null,
            'from' => $from !== '' ? $from : null,
            'to'   => $to !== ''   ? $to   : null,
        ]);

        // Keep every filter when going to the next / previous page
        $logs->withQueryString();
    @endphp

    {{-- ================= PAGE HERO ================= --}}
    <div class="pg-hero">
        <span class="pg-hero-glow" aria-hidden="true"></span>
        <span class="pg-deco d1" aria-hidden="true">🕒</span>
        <span class="pg-deco d2" aria-hidden="true">🔐</span>
        <span class="pg-deco d3" aria-hidden="true">📋</span>

        <div class="pg-hero-text">
            <span class="pg-hero-badge">🕒 ACCOUNT HISTORY</span>
            <h2>Activity Log</h2>
            <p>Your recent account activity</p>
        </div>

        <button type="button" class="pg-theme-toggle" id="pgThemeToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
            <span class="pg-tt-ico" aria-hidden="true">🌙</span>
        </button>
    </div>

    @if ($hasFilter || $logs->total() > 5)
    <div class="al-filters" role="tablist" aria-label="Filter activity">
        @foreach ($filters as $key => $label)
            <a href="{{ route('user.activity', $key === '' ? $keep : ($keep + ['type' => $key])) }}"
               class="mb-tab {{ ($group ?? '') === $key ? 'active' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    <form class="al-search" method="GET" action="{{ route('user.activity') }}" role="search" aria-label="Search activity">
        @if (($group ?? '') !== '')
            <input type="hidden" name="type" value="{{ $group }}">
        @endif
        <div class="al-field grow">
            <label for="al-q">Search</label>
            <input id="al-q" class="al-input" type="search" name="q" value="{{ $q }}"
                   placeholder="Search activity, IP address…" autocomplete="off">
        </div>
        <div class="al-field">
            <label for="al-from">From</label>
            <input id="al-from" class="al-input" type="date" name="from" value="{{ $from }}" @if ($to !== '') max="{{ $to }}" @endif>
        </div>
        <div class="al-field">
            <label for="al-to">To</label>
            <input id="al-to" class="al-input" type="date" name="to" value="{{ $to }}" @if ($from !== '') min="{{ $from }}" @endif>
        </div>
        <div class="al-btns">
            <button type="submit" class="al-apply"><i class="bi bi-funnel-fill" aria-hidden="true"></i> Apply</button>
            @if ($q !== '' || $from !== '' || $to !== '')
                <a class="al-clear" href="{{ route('user.activity', ($group ?? '') !== '' ? ['type' => $group] : []) }}"><i class="bi bi-x-lg" aria-hidden="true"></i> Clear</a>
            @endif
        </div>
    </form>
    @endif

    <div class="u-card al-card">
        @if ($logs->count())
            <div class="al-scroll">
                <table class="al-table">
                    <thead>
                        <tr>
                            <th scope="col">Activity</th>
                            <th scope="col">Type</th>
                            <th scope="col">Device</th>
                            <th scope="col">IP address</th>
                            <th scope="col">Date &amp; time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            @php
                                $action  = $log->action ?? $log->event_type ?? '';
                                $g       = preg_split('/[._]/', $action)[0];
                                if (in_array($g, ['reservation', 'payment'], true)) {
                                    $g = 'booking';
                                }
                                $v       = $look[$g] ?? $look['account'];
                                $failed  = str_contains($action, 'failed');
                                $when    = \Carbon\Carbon::parse($log->created_at);
                                $ua      = (string) ($log->user_agent ?? '');
                                $browser = str_contains($ua, 'Edg/') ? 'Edge'
                                    : (str_contains($ua, 'Firefox') ? 'Firefox'
                                    : (str_contains($ua, 'Chrome') ? 'Chrome'
                                    : (str_contains($ua, 'Safari') ? 'Safari' : 'Browser')));
                                $device  = (str_contains($ua, 'Mobile') || str_contains($ua, 'Android') || str_contains($ua, 'iPhone')) ? 'Mobile' : 'Desktop';
                                $ip      = $log->ip_address ?? null;
                            @endphp
                            <tr>
                                <td>
                                    <div class="al-act">
                                        <span class="wp-ico {{ $failed ? 'c-danger' : $v['ico'] }}" aria-hidden="true">
                                            <i class="bi {{ $failed ? 'bi-exclamation-triangle' : $v['icon'] }}"></i>
                                        </span>
                                        <p class="al-title">{{ $log->description }}</p>
                                    </div>
                                </td>
                                <td>
                                    <div class="al-types">
                                        <span class="wp-badge {{ $v['badge'] }}">{{ $v['label'] }}</span>
                                        @if ($failed)<span class="wp-badge danger">Failed</span>@endif
                                    </div>
                                </td>
                                <td class="al-muted">{{ $browser }} · {{ $device }}</td>
                                <td class="al-muted">{{ $ip ?: '—' }}</td>
                                <td class="al-when" title="{{ $when->format('Y-m-d H:i:s') }}">
                                    <b>{{ $when->format('M j, Y') }}</b>
                                    <span>{{ $when->format('g:i A') }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @elseif ($hasFilter)
            <div class="al-empty">No activity matches your filters. Try a different search or date range.</div>
        @else
            <div class="al-empty">No activity yet. Your sign-ins, bookings, and account changes will show up here.</div>
        @endif
    </div>

    @if ($logs->total() > 0)
        <p class="al-count">Showing {{ $logs->firstItem() }}–{{ $logs->lastItem() }} of {{ $logs->total() }}</p>
    @endif

    @if ($logs->hasPages())
        <div class="al-pager">
            @if ($logs->onFirstPage())
                <span class="pg-btn al-disabled">Previous</span>
            @else
                <a class="pg-btn" href="{{ $logs->previousPageUrl() }}">Previous</a>
            @endif
            <span class="al-page">Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}</span>
            @if ($logs->hasMorePages())
                <a class="pg-btn" href="{{ $logs->nextPageUrl() }}">Next</a>
            @else
                <span class="pg-btn al-disabled">Next</span>
            @endif
        </div>
    @endif
@endsection

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('pgThemeToggle');
    if (!btn) return;
    var ico = btn.querySelector('.pg-tt-ico');
    var OLD = '#themeToggle, #theme-toggle, #darkModeToggle, .theme-toggle, .theme-switch, [data-theme-toggle]';

    function themeEl() { return document.querySelector('[data-theme]') || document.documentElement; }
    function isDark()  { return themeEl().getAttribute('data-theme') === 'dark'; }
    function paint()   { ico.textContent = isDark() ? '\u2600\uFE0F' : '\uD83C\uDF19'; }

    // Hide the old floating toggle (kept in the DOM so it can still be clicked)
    document.querySelectorAll(OLD).forEach(function (el) {
        if (el !== btn) el.style.display = 'none';
    });

    btn.addEventListener('click', function () {
        var old = document.querySelector(OLD);
        if (old && old !== btn) {
            old.click();
        } else {
            var next = isDark() ? 'light' : 'dark';
            themeEl().setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        }
        setTimeout(paint, 50);
    });

    new MutationObserver(paint).observe(themeEl(), { attributes: true, attributeFilter: ['data-theme'] });
    paint();
})();
</script>
@endpush