@extends('layouts.sidebar')

@section('title', 'Home')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        /* ===== TOOLBAR (matched to Attendance page) ===== */
        .toolbar {
            background: var(--card);
            padding: 22px 26px;
            margin-bottom: 24px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 18px;
        }

        .toolbar .eyebrow {
            font-size: .68rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 6px;
        }

        .toolbar h1 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: -.01em;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .role-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-light);
            color: var(--pink-dark);
            padding: 8px 15px;
            border-radius: 999px;
            font-size: .76rem;
            font-weight: 700;
            text-transform: capitalize;
        }

        .role-chip::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--pink-deep);
        }

        /* ===== STATS ROW (matched to Attendance page's stat-card) ===== */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }

        .stat-card {
            background: var(--card);
            border-radius: 16px;
            padding: 18px 20px;
            box-shadow: var(--shadow-sm);
        }

        .stat-card .label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 9px;
        }

        .stat-card .value {
            font-family: 'Source Serif 4', serif;
            font-size: 1.7rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1;
            white-space: nowrap;
        }

        .stat-card .value i {
            color: var(--pink-deep);
            font-size: 1rem;
            margin-right: 6px;
        }

        .stat-card .sub {
            font-size: 11px;
            color: var(--muted);
            margin-top: 7px;
        }

        .section-label {
            font-size: .72rem;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
            color: var(--muted);
            margin-bottom: 12px;
            margin-top: 26px;
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 14px;
        }

        .app-card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 14px;
            box-shadow: var(--shadow-sm);
            transition: .18s;
        }

        .app-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-3px);
            border-color: var(--pink-light);
        }

        .app-card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            color: #fff;
            flex-shrink: 0;
            transition: .18s;
        }

        .app-card:nth-child(4n+1) .app-card-icon {
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
        }

        .app-card:nth-child(4n+2) .app-card-icon {
            background: linear-gradient(135deg, #A78BFA 0%, #7C5CE0 100%);
        }

        .app-card:nth-child(4n+3) .app-card-icon {
            background: linear-gradient(135deg, #4DD4C4 0%, #1FAE9E 100%);
        }

        .app-card:nth-child(4n+4) .app-card-icon {
            background: linear-gradient(135deg, #FFB648 0%, #F2932A 100%);
        }

        .app-card-text {
            min-width: 0;
        }

        .app-card-text h2 {
            font-size: .88rem;
            font-weight: 600;
            color: var(--ink);
            margin-bottom: 2px;
        }

        .app-card-text p {
            font-size: .76rem;
            color: var(--muted);
            line-height: 1.4;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .app-card-arrow {
            margin-left: auto;
            color: var(--muted);
            font-size: .8rem;
            transition: .18s;
            flex-shrink: 0;
        }

        .app-card:hover .app-card-arrow {
            color: var(--pink-dark);
            transform: translateX(3px);
        }

        /* ===== FORECAST GROUP CARD (Sales Forecast + nested zone links) ===== */
        .forecast-group {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            transition: .18s;
        }

        .forecast-group:hover {
            box-shadow: var(--shadow-md);
        }

        .forecast-main-row {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px;
        }

        .forecast-main-row .app-card-icon {
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
        }

        .forecast-caret-btn {
            width: 32px;
            height: 32px;
            flex-shrink: 0;
            border: none;
            background: var(--pink-pale);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 9px;
            color: var(--pink-dark);
            transition: .15s;
        }

        .forecast-caret-btn:hover {
            background: var(--pink-light);
        }

        .forecast-caret-btn i {
            transition: transform .18s ease;
        }

        .forecast-caret-btn.open i {
            transform: rotate(180deg);
        }

        .forecast-sublist {
            max-height: 0;
            overflow: hidden;
            transition: max-height .2s ease;
            border-top: 1px solid var(--line);
        }

        .forecast-sublist.open {
            max-height: 220px;
        }

        .forecast-sublist a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px 12px 54px;
            font-size: .82rem;
            font-weight: 600;
            color: var(--ink-soft);
            border-bottom: 1px solid var(--line);
        }

        .forecast-sublist a:last-child {
            border-bottom: none;
        }

        .forecast-sublist a:hover {
            background: var(--pink-pale);
            color: var(--pink-dark);
        }

        .forecast-sublist a i {
            width: 14px;
            color: var(--pink-deep);
            font-size: .78rem;
        }

        @media(max-width:900px) {
            .toolbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .stats-row {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media(max-width:520px) {
            .card-grid {
                grid-template-columns: 1fr;
            }

            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('content')

    <div class="toolbar no-print">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Dashboard</div>
            <h1>Welcome back, {{ explode(' ', session('fullname'))[0] ?? session('fullname') }} 👋</h1>
        </div>
        <div class="toolbar-actions">
            <span class="role-chip">{{ session('role') }}</span>
        </div>
    </div>

    <div class="stats-row no-print">
        <div class="stat-card">
            <div class="label">Today</div>
            <div class="value"><i class="fa-solid fa-calendar-day"></i><span
                    id="liveDate">{{ now()->format('M j, Y') }}</span></div>
            <div class="sub">Current date</div>
        </div>
        <div class="stat-card">
            <div class="label">Time</div>
            <div class="value"><i class="fa-solid fa-clock"></i><span id="liveTime">{{ now()->format('g:i:s A') }}</span>
            </div>
            <div class="sub">Live clock</div>
        </div>
        <div class="stat-card">
            <div class="label">Branch</div>
            <div class="value"><i class="fa-solid fa-location-dot"></i>Lipa</div>
            <div class="sub">Current location</div>
        </div>
    </div>

    @if (session('role') === 'cashier')
        <div class="section-label">Your tools</div>
        <div class="card-grid">
            <a href="/pos" class="app-card">
                <div class="app-card-icon"><i class="fa-solid fa-cash-register"></i></div>
                <div class="app-card-text">
                    <h2>Point of Sale</h2>
                    <p>Process transactions and payments</p>
                </div>
                <i class="fa-solid fa-chevron-right app-card-arrow"></i>
            </a>
            <a href="/inventory" class="app-card">
                <div class="app-card-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="app-card-text">
                    <h2>Inventory</h2>
                    <p>Track and manage stock levels</p>
                </div>
                <i class="fa-solid fa-chevron-right app-card-arrow"></i>
            </a>
        </div>
    @else
        <div class="section-label">Operations</div>
        <div class="card-grid">
            <a href="/inventory" class="app-card">
                <div class="app-card-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                <div class="app-card-text">
                    <h2>Inventory</h2>
                    <p>Track and manage stock levels</p>
                </div>
                <i class="fa-solid fa-chevron-right app-card-arrow"></i>
            </a>
        </div>

        <div class="section-label">Insights</div>
        <div class="card-grid">

            @if (session('role') === 'admin')
                {{-- Sales Forecast is the parent card; the 3 zone pages are nested inside it --}}
                <div class="forecast-group" style="grid-column: span 1;">
                    <div class="forecast-main-row">
                        <a href="/ml-forecast" class="app-card" style="border:none;box-shadow:none;padding:0;flex:1;">
                            <div class="app-card-icon"><i class="fa-solid fa-chart-line"></i></div>
                            <div class="app-card-text">
                                <h2>Sales Forecast</h2>
                                <p>Sales trends and performance reports</p>
                            </div>
                        </a>
                        <button type="button" class="forecast-caret-btn" id="forecastCardToggle"
                            aria-label="Toggle zone breakdown">
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>
                    </div>
                    <div class="forecast-sublist" id="forecastCardSublist">
                        <a href="/fieldOfRides"><i class="fa-solid fa-ferris-wheel"></i> PiyestaRides</a>
                        <a href="/RollerFever"><i class="fa-solid fa-bolt"></i> Skataya</a>
                        <a href="/DinoAdventure"><i class="fa-solid fa-dragon"></i> DinoLakbay</a>
                    </div>
                </div>
            @else
                {{-- Non-admin, non-cashier roles: no Sales Forecast access, so these stay as plain cards --}}
                <a href="/fieldOfRides" class="app-card">
                    <div class="app-card-icon"><i class="fa-solid fa-ferris-wheel"></i></div>
                    <div class="app-card-text">
                        <h2>PiyestaRides</h2>
                        <p>Sales in PiyestaRides</p>
                    </div>
                    <i class="fa-solid fa-chevron-right app-card-arrow"></i>
                </a>
                <a href="/RollerFever" class="app-card">
                    <div class="app-card-icon"><i class="fa-solid fa-bolt"></i></div>
                    <div class="app-card-text">
                        <h2>Skataya</h2>
                        <p>Sales in Skataya</p>
                    </div>
                    <i class="fa-solid fa-chevron-right app-card-arrow"></i>
                </a>
                <a href="/DinoAdventure" class="app-card">
                    <div class="app-card-icon"><i class="fa-solid fa-dragon"></i></div>
                    <div class="app-card-text">
                        <h2>DinoLakbay</h2>
                        <p>Sales in DinoLakbay</p>
                    </div>
                    <i class="fa-solid fa-chevron-right app-card-arrow"></i>
                </a>
            @endif

        </div>
    @endif

@endsection

@push('scripts')
    <script>
        function updateClock() {
            const now = new Date();
            const dateEl = document.getElementById('liveDate');
            const timeEl = document.getElementById('liveTime');
            if (dateEl) {
                dateEl.textContent = now.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
            }
            if (timeEl) {
                timeEl.textContent = now.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });
            }
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Sales Forecast card — expand/collapse the nested zone links (admin only)
        (function() {
            const toggleBtn = document.getElementById('forecastCardToggle');
            const sublist = document.getElementById('forecastCardSublist');
            if (!toggleBtn || !sublist) return;

            toggleBtn.addEventListener('click', () => {
                sublist.classList.toggle('open');
                toggleBtn.classList.toggle('open');
            });
        })();
    </script>
@endpush