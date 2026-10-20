<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Account') · Pistavora Amusement Park</title>
    <link rel="stylesheet" href="{{ asset('css/user-app-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-landing-theme.css') }}?v={{ filemtime(public_path('css/user-landing-theme.css')) }}">

    {{-- Apply saved theme before first paint, avoids light-mode flash --}}
    <script>
        (function () {
            var saved = localStorage.getItem('reks_theme');
            if (saved === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    <style>
        /* ===========================================================
           Dark mode — additive overrides, scoped to [data-theme="dark"].
           Matches user-app-theme.css classes 1:1. Keeps --pink-deep as
           the accent in both modes; only surfaces/text/borders flip.
           =========================================================== */
        [data-theme="dark"]{
            --d-bg:#0B1020;
            --d-surface:#111827;
            --d-surface-soft:#1F2937;
            --d-border:rgba(255,255,255,.09);
            --d-text:#F1F5F9;
            --d-text-soft:#CBD5E1;
            --d-muted:#94A3B8;
        }

        [data-theme="dark"] #userView,
        [data-theme="dark"] .app-frame,
        [data-theme="dark"] .app-body{ background:var(--d-bg); }

        [data-theme="dark"] .phone-head{
            background:var(--d-surface); border-color:var(--d-border);
        }
        [data-theme="dark"] .phone-head h2{ color:var(--d-text); }

        [data-theme="dark"] .u-card,
        [data-theme="dark"] .u-profile-card,
        [data-theme="dark"] .pkg,
        [data-theme="dark"] .cal-embed,
        [data-theme="dark"] .promo-card,
        [data-theme="dark"] .waiver-box,
        [data-theme="dark"] .u-empty,
        [data-theme="dark"] .chat-window,
        [data-theme="dark"] .bubble.bot,
        [data-theme="dark"] table{
            background:var(--d-surface); border-color:var(--d-border);
        }
        [data-theme="dark"] .u-card h4,
        [data-theme="dark"] .u-profile-top h4,
        [data-theme="dark"] .pkg b,
        [data-theme="dark"] .promo-body h4,
        [data-theme="dark"] .u-empty h4,
        [data-theme="dark"] .cal-month-head span,
        [data-theme="dark"] .cal-day{ color:var(--d-text); }
        [data-theme="dark"] .u-card p,
        [data-theme="dark"] .u-empty p,
        [data-theme="dark"] .pkg span,
        [data-theme="dark"] .promo-body > p{ color:var(--d-text-soft); }
        [data-theme="dark"] .u-field-label,
        [data-theme="dark"] .u-role-pill,
        [data-theme="dark"] th{ color:var(--d-muted); }

        [data-theme="dark"] .pkg:has(input:checked){
            background:rgba(99,102,241,.14); border-color:var(--pink-deep);
        }

        [data-theme="dark"] .u-input,
        [data-theme="dark"] .cal-slot,
        [data-theme="dark"] .date-chip,
        [data-theme="dark"] .cal-month-head button,
        [data-theme="dark"] .pg-btn,
        [data-theme="dark"] .u-icon-btn{
            background:var(--d-surface-soft); color:var(--d-text); border-color:var(--d-border);
        }
        [data-theme="dark"] .u-input::placeholder{ color:var(--d-muted); }

        [data-theme="dark"] .u-btn.ghost{
            background:var(--d-surface); color:var(--pink); border-color:var(--d-border);
        }
        [data-theme="dark"] .u-btn.ghost:hover{ background:var(--d-surface-soft); }

        [data-theme="dark"] .bottomnav{
            background:var(--d-surface); border-color:var(--d-border);
        }
        [data-theme="dark"] .bottomnav .bn-item{ color:var(--d-muted); }

        [data-theme="dark"] .service-switch,
        [data-theme="dark"] .cal-fmt-toggle{ background:var(--d-surface-soft); }
        [data-theme="dark"] .service-tab.active{ background:var(--d-surface); }
        [data-theme="dark"] .service-tab b{ color:var(--d-text-soft); }

        [data-theme="dark"] .promo-media{ background:var(--d-surface-soft); }
        [data-theme="dark"] .promo-tier{ border-color:var(--d-border); color:var(--d-text); }
        [data-theme="dark"] .promo-tier.selected{ background:rgba(99,102,241,.14); }
        [data-theme="dark"] .promo-inclusions li{ color:var(--d-text-soft); }

        [data-theme="dark"] .tag.green{ background:rgba(18,181,166,.16); }
        [data-theme="dark"] .tag.amber{ background:rgba(177,115,10,.18); }
        [data-theme="dark"] .tag.rose,
        [data-theme="dark"] .tag.pink{ background:rgba(226,75,74,.18); }

        [data-theme="dark"] .promo-selection-summary{
            background:rgba(99,102,241,.12); border-color:var(--d-border); color:var(--d-text);
        }
        [data-theme="dark"] .promo-selection-summary.empty{
            background:var(--d-surface); color:var(--d-muted);
        }

        [data-theme="dark"] .u-alert.success{ background:rgba(18,181,166,.16); }
        [data-theme="dark"] .u-alert.error{ background:rgba(226,75,74,.16); }

        [data-theme="dark"] .chat-body{ background:var(--d-bg); }
        [data-theme="dark"] .quick{ color:var(--pink); border-color:var(--d-border); }
        [data-theme="dark"] .chat-input{ border-color:var(--d-border); }
        [data-theme="dark"] .chat-input input{
            background:var(--d-surface-soft); color:var(--d-text); border-color:var(--d-border);
        }

        /* Desktop sidebar (.bottomnav) is already dark (var(--ink)) by design
           in both modes — no override needed there. */

        .theme-toggle{
            width:38px;height:38px;border-radius:50%;border:1px solid var(--line);
            background:#fff;color:var(--ink-soft);display:inline-flex;align-items:center;
            justify-content:center;cursor:pointer;flex-shrink:0;
            transition:border-color .15s var(--ease), color .15s var(--ease), background .15s var(--ease);
        }
        .theme-toggle:hover{ border-color:var(--pink-deep); color:var(--pink-deep); background:var(--pink-pale); }
        [data-theme="dark"] .theme-toggle{
            background:var(--d-surface-soft); border-color:var(--d-border); color:var(--d-text);
        }
        [data-theme="dark"] .theme-toggle:hover{ background:var(--d-surface); }
        .theme-toggle .icon-moon{ display:none; }

        /* Pages that hide their title card keep only the theme toggle, right-aligned, no card */
        .phone-head.phone-head--toggle-only{
            justify-content:flex-end !important; padding:0 !important; min-height:0;
            background:transparent !important; border:0 !important; box-shadow:none !important;
        }
        [data-theme="dark"] .theme-toggle .icon-sun{ display:none; }
        [data-theme="dark"] .theme-toggle .icon-moon{ display:block; }
    </style>

    @stack('styles')
</head>
<body>
<div id="userView">
    <div class="app-frame">
        @include('partials.bottomnav')
        <div class="app-main">
            {{-- A page can hide the big title card with @section('hide-page-title', '1');
                 the dark-mode toggle always stays. --}}
            <div class="phone-head @hasSection('hide-page-title') phone-head--toggle-only @endif" style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;">
                @hasSection('hide-page-title')
                @else
                <div>
                    <p>@yield('page-subtitle', 'Manage your account')</p>
                    <h2>@yield('page-title', 'Account')</h2>
                </div>
                @endif
                <button type="button" class="theme-toggle" id="themeToggleBtn" title="Toggle dark mode">
                    <svg class="icon-sun" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"></circle><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41"></path></svg>
                    <svg class="icon-moon" viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                </button>
            </div>
            <div class="app-body @yield('body-class')">
                @if (session('success'))
                    <div class="u-alert success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                    <div class="u-alert error">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="u-alert error">{{ $errors->first() }}</div>
                @endif
                @yield('content')
            </div>
        </div>
    </div>
</div>
@include('partials.chat-widget')
@include('partials.ui-feedback')
@stack('scripts')

<script>
    (function () {
        var btn = document.getElementById('themeToggleBtn');
        if (!btn) return;
        btn.addEventListener('click', function () {
            var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('reks_theme', 'light');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('reks_theme', 'dark');
            }
        });
    })();
</script>
</body>
</html>