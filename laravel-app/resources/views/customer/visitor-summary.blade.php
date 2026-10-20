@extends('layouts.sidebar')

@section('title', 'Visitor Login & Booking Summary')

@section('styles')
    @include('partials.ui-theme')
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Bungee&family=Nunito:wght@600;700;800&display=swap">
    <style>
        :root {
            --text-sub: var(--ink-soft);
            --text-faint: var(--muted);
            --r-sm: 8px;
            --r-md: 10px;
            --r-lg: 14px;
        }

        /* ===== TOOLBAR EXTRAS ===== */
        .toolbar-actions select {
            height: 40px;
            padding: 0 12px;
            font-size: 12.5px;
            color: var(--ink);
            background: #fff;
            border: 1px solid var(--line-strong);
            border-radius: var(--r-md);
            cursor: pointer;
        }

        /* ===== PANEL (same as .table-box.res-box) ===== */
        .table-box.res-box { padding: 0; border: 1px solid var(--line); margin-bottom: 20px; }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            padding: 16px 20px;
            border-bottom: 1px solid var(--line);
        }

        .panel-head h3 { font-size: 15px; font-weight: 700; color: var(--ink); }
        .panel-head p { font-size: 12px; color: var(--text-sub); margin-top: 3px; }

        .panel-body { padding: 20px; }

        /* ===== FILTER BAR (same look as reservations) ===== */
        .filter-card {
            background: var(--card);
            border: 1px solid var(--line);
            padding: 14px 20px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }
        .filter-card .search-wrap { position: relative; flex: 1; min-width: 220px; }
        .filter-card .search-wrap i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 12.5px; pointer-events: none; }
        .filter-card input[type="text"],
        .filter-card select { height: 40px; background: #fff; color: var(--ink); }
        .filter-card input[type="text"] { width: 100%; max-width: none; padding: 0 12px 0 36px; }
        .filter-card select { padding: 0 12px; }
        .filter-count { font-size: 12px; color: var(--text-sub); white-space: nowrap; }

        /* ===== TABLES (same pattern as .res-table) ===== */
        .res-table { table-layout: fixed; min-width: 760px; font-size: 13px; }
        .res-table th, .res-table td { padding: 12px 16px; }
        .res-table td { overflow: hidden; }
        .res-table tbody tr:last-child td { border-bottom: none; }

        .cell-main { font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cell-sub { font-size: 12px; color: var(--text-sub); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dash { color: var(--text-faint); }
        .sub-ok { color: var(--present); font-weight: 600; }
        .sub-wait { color: var(--amber); font-weight: 600; }

        .icon-name { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .icon-name > div { min-width: 0; }


        .rank-badge {
            width: 24px;
            height: 24px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            background: var(--bg);
            color: var(--ink-soft);
        }
        .rank-badge.rank-1 { background: var(--pink-deep); color: #fff; }


        .bar-track { flex: 1; height: 8px; border-radius: 999px; background: var(--line); overflow: hidden; }
        .bar-fill { height: 100%; border-radius: 999px; background: var(--present); }

        /* ===== VISITOR LOGINS ===== */
        .visitor-row { cursor: pointer; transition: background .12s; }
        .visitor-row:hover,
        .visitor-row.is-open { background: var(--pink-pale); }
        .visitor-row.is-open td { border-bottom-color: transparent; }

        .visitor-avatar {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            flex-shrink: 0;
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
            color: #fff;
            font-weight: 700;
            font-size: 12.5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .visitor-status {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 12px;
            border-radius: 999px;
            background: var(--present-soft);
            color: var(--present);
            font-weight: 700;
            font-size: 12px;
        }
        .visitor-status.is-offline { background: var(--bg); color: var(--muted); }
        .visitor-status i { font-size: 7px; }

        .expand-chevron {
            width: 28px;
            height: 28px;
            border-radius: var(--r-sm);
            border: 1px solid var(--line-strong);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-sub);
            background: #fff;
            font-size: 11px;
            transition: transform .2s ease, color .15s, background .15s, border-color .15s;
        }
        .visitor-row.is-open .expand-chevron {
            transform: rotate(180deg);
            background: var(--pink-light);
            border-color: var(--pink-light);
            color: var(--pink-deep);
        }

        .reservation-panel-row td { padding: 0; border-bottom: 1px solid var(--line); }
        .reservation-panel-row:last-child td { border-bottom: none; }

        .reservation-panel {
            display: grid;
            grid-template-rows: 0fr;
            transition: grid-template-rows .28s ease;
            background: var(--pink-pale);
        }
        .reservation-panel-row.is-open .reservation-panel { grid-template-rows: 1fr; }
        .reservation-panel-inner { overflow: hidden; }

        .reservation-panel-content {
            padding: 18px 22px 22px 66px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            box-shadow: inset 3px 0 0 var(--pink-light);
        }

        .reservation-loading { display: flex; align-items: center; gap: 10px; font-size: 12.5px; color: var(--ink-soft); padding: 6px 0; }
        .reservation-loading .spinner {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            border: 2px solid var(--pink-light);
            border-top-color: var(--pink-deep);
            animation: spin .7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .reservation-summary { display: flex; flex-wrap: wrap; gap: 12px 32px; }
        .reservation-field { display: flex; flex-direction: column; gap: 4px; min-width: 110px; }
        .reservation-field .k { font-size: 10.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; }
        .reservation-field .v { font-size: 13px; font-weight: 600; color: var(--ink); }

        /* status pill inside the reservation preview */
        .res-badge {
            display: inline-flex; align-items: center; align-self: flex-start;
            padding: 3px 12px; border-radius: 999px;
            font-size: 12px; font-weight: 700; text-transform: capitalize;
            background: var(--bg); color: var(--ink-soft);
        }
        .res-badge[data-s="pending"]  { background: var(--amber-light); color: var(--amber); }
        .res-badge[data-s="approved"],
        .res-badge[data-s="confirmed"] { background: var(--present-soft); color: var(--present); }
        .res-badge[data-s="done"],
        .res-badge[data-s="completed"],
        .res-badge[data-s="redeemed"],
        .res-badge[data-s="paid"]     { background: var(--sky-soft); color: var(--sky); }
        .res-badge[data-s="rejected"],
        .res-badge[data-s="cancelled"] { background: var(--danger-soft); color: var(--danger); }

        .reservation-link-btn {
            align-self: flex-start;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 16px;
            border-radius: var(--r-md);
            background: var(--ink);
            color: #fff;
            font-weight: 600;
            font-size: 12.5px;
            text-decoration: none;
            transition: .15s;
        }
        .reservation-link-btn:hover { background: var(--pink-deep); }
        .reservation-error { font-size: 12.5px; color: var(--danger); font-weight: 600; padding: 6px 0; }

        .empty-row td { text-align: center; color: var(--muted); padding: 28px 16px !important; }

        /* ===== PROMO GENERATOR (carnival style) ===== */
        .table-box.res-box.ai-panel {
            --f-bg: #FFF1F7; --f-bg2: #E8E0FF; --f-ink: #2A1650; --f-muted: #6E5A94;
            --f-pink: #FF3D81; --f-yellow: #FFC914; --f-cyan: #12C4D6;
            position: relative; overflow: hidden;
            background: radial-gradient(circle at 15% 0, var(--f-bg2), var(--f-bg) 65%);
            border: 2px solid var(--f-ink); border-radius: 16px;
            box-shadow: 3px 3px 0 var(--f-ink);
            margin: 0 4px 20px 0;
            font-family: "Nunito", system-ui, sans-serif; font-weight: 700; color: var(--f-ink);
        }

        /* header with marquee bulbs + mascot */
        .ai-head { background: none; border-bottom: 0; padding: 12px 16px 18px; position: relative; }
        .ai-head::after {
            content: ""; position: absolute; left: 16px; right: 16px; bottom: 4px; height: 8px;
            background: radial-gradient(circle, var(--f-yellow) 2.5px, transparent 3.5px) 0 0 / 18px 8px repeat-x;
            animation: fBulbs 1.2s steps(2) infinite;
        }
        @keyframes fBulbs { 50% { filter: hue-rotate(150deg) brightness(1.15); } }
        .ai-head-title { display: flex; align-items: center; gap: 10px; }
        .fun-mascot {
            width: 34px; height: 34px; border-radius: 50%; flex: none; position: relative;
            background: var(--f-yellow); border: 2px solid var(--f-ink); transition: transform .2s;
        }
        .fun-mascot:hover { transform: rotate(-12deg) scale(1.1); }
        .fun-mascot::before, .fun-mascot::after {
            content: ""; position: absolute; top: 9px; width: 6px; height: 8px; border-radius: 50%;
            background: var(--f-ink); animation: fBlink 4s infinite;
        }
        .fun-mascot::before { left: 7px; } .fun-mascot::after { right: 7px; }
        .fun-mascot i { position: absolute; left: 9px; right: 9px; bottom: 6px; height: 7px; border-bottom: 2px solid var(--f-ink); border-radius: 0 0 20px 20px; }
        @keyframes fBlink { 0%, 94%, 100% { transform: scaleY(1); } 97% { transform: scaleY(.1); } }

        .ai-head h3 {
            display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin: 0;
            font: 400 18px/1.1 "Bungee", system-ui, sans-serif;
            color: var(--f-pink); text-shadow: 2px 2px 0 var(--f-cyan);
        }
        .ai-head p { margin-top: 3px; font-size: 12px; font-weight: 700; color: var(--f-muted); }
        .ai-chip {
            display: inline-flex; align-items: center; gap: 5px;
            font: 800 10px "Nunito", system-ui, sans-serif; letter-spacing: 0; text-shadow: none;
            padding: 1px 8px; border-radius: 999px;
            background: var(--f-cyan); color: var(--f-ink); border: 2px solid var(--f-ink);
        }
        .ai-chip i { color: var(--f-ink); font-size: 10px; }
        .ai-status {
            display: inline-flex; align-items: center; gap: 8px;
            font-size: 11.5px; font-weight: 800; color: var(--f-ink);
            padding: 3px 11px; border-radius: 999px;
            background: #fff; border: 2px solid var(--f-ink); box-shadow: 2px 2px 0 var(--f-ink);
        }
        .ai-dot { width: 7px; height: 7px; border-radius: 50%; background: #1FBF75; animation: aiBlink 1.6s ease-in-out infinite; }
        @keyframes aiBlink { 50% { opacity: .3; } }

        .ai-chat { padding: 2px 16px 16px; background: none; }

        /* steps */
        .pg-step { display: flex; align-items: center; gap: 8px; margin: 16px 0 8px; font: 400 13px/1.2 "Bungee", system-ui, sans-serif; color: var(--f-ink); }
        .pg-step:first-child { margin-top: 0; }
        .pg-step b {
            width: 22px; height: 22px; border-radius: 50%; flex: none;
            background: var(--f-pink); color: #fff; border: 2px solid var(--f-ink);
            font: 400 11px "Bungee", system-ui, sans-serif;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .pg-step small { font: 700 12px "Nunito", system-ui, sans-serif; color: var(--f-muted); }

        /* attractions */
        .pg-parks { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 10px; }
        .pg-park {
            all: unset; box-sizing: border-box; cursor: pointer;
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 12px;
            background: #fff; border: 2px solid var(--f-ink); box-shadow: 2px 2px 0 var(--f-ink);
            transition: transform .12s, background .12s;
        }
        .pg-park:hover { transform: translateY(-2px); }
        .pg-park:focus-visible { outline: 3px solid var(--f-cyan); outline-offset: 3px; }
        .pg-park[aria-pressed="true"] { background: var(--f-yellow); }
        .pg-park-icon { width: 30px; height: 30px; border-radius: 9px; border: 2px solid var(--f-ink); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 12px; flex-shrink: 0; }
        .pg-park-name { display: block; font: 400 12.5px/1.2 "Bungee", system-ui, sans-serif; color: var(--f-ink); }
        .pg-park-sub { display: block; font-size: 11px; font-weight: 700; color: var(--f-muted); margin-top: 2px; }
        .pg-park[aria-pressed="true"] .pg-park-sub { color: var(--f-ink); }

        /* chips */
        .pg-chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .pg-chip {
            all: unset; box-sizing: border-box; cursor: pointer;
            font: 800 12.5px "Nunito", system-ui, sans-serif; color: var(--f-ink);
            padding: 4px 12px; border-radius: 999px;
            background: #fff; border: 2px solid var(--f-ink); box-shadow: 2px 2px 0 var(--f-ink);
            transition: transform .12s, background .12s;
        }
        .pg-chip:hover { transform: translateY(-2px); }
        .pg-chip:focus-visible { outline: 3px solid var(--f-cyan); outline-offset: 3px; }
        .pg-chip[aria-pressed="true"] { background: var(--f-yellow); }

        .pg-field {
            display: block; width: 100%; max-width: 480px; height: 36px; margin-top: 8px; padding: 0 12px;
            border: 2px solid var(--f-ink); border-radius: 10px; background: #fff;
            font: 700 13px "Nunito", system-ui, sans-serif; color: var(--f-ink);
        }
        .pg-field::placeholder { color: var(--f-muted); }
        .pg-field:focus { outline: 3px solid var(--f-cyan); outline-offset: 2px; }
        .pg-hint { margin: 8px 0 0; font-size: 11.5px; font-weight: 700; color: var(--f-muted); }

        /* generate button */
        .ai-send {
            all: unset; box-sizing: border-box; cursor: pointer;
            display: inline-flex; align-items: center; gap: 10px;
            padding: 8px 20px; border-radius: 12px;
            background: var(--f-pink); color: #fff; border: 2px solid var(--f-ink); box-shadow: 3px 3px 0 var(--f-ink);
            font: 400 14px "Bungee", system-ui, sans-serif; transition: transform .1s, box-shadow .1s;
        }
        .ai-send:hover { filter: brightness(1.05); }
        .ai-send:active { transform: translate(3px, 3px); box-shadow: none; }
        .ai-send:focus-visible { outline: 3px solid var(--f-cyan); outline-offset: 3px; }
        .ai-send[disabled] { opacity: .5; cursor: not-allowed; }
        .ai-send[disabled]:active { transform: none; box-shadow: 3px 3px 0 var(--f-ink); }

        /* result */
        .pg-out { margin-top: 16px; }
        .pg-out:empty { display: none; }
        .pg-out-title {
            display: inline-block; margin-bottom: 10px; padding: 3px 12px; border-radius: 999px;
            font: 800 12.5px/1.3 "Nunito", system-ui, sans-serif; color: var(--f-ink);
            background: var(--f-yellow); border: 2px solid var(--f-ink); box-shadow: 2px 2px 0 var(--f-ink);
        }
        .ai-msg { display: flex; gap: 10px; max-width: 100%; animation: fPop .3s cubic-bezier(.3, 1.6, .5, 1); }
        @keyframes fPop { from { opacity: 0; transform: scale(.88); } }
        .ai-avatar {
            width: 28px; height: 28px; border-radius: 50%; flex-shrink: 0; font-size: 11px;
            background: var(--f-yellow); color: var(--f-ink); border: 2px solid var(--f-ink);
            display: flex; align-items: center; justify-content: center;
        }
        .pg-out .ai-msg.is-ai .ai-bubble {
            flex: 1; min-width: 0; max-width: none; padding: 10px 14px;
            background: #fff; color: var(--f-ink); font-size: 13px; font-weight: 700; line-height: 1.55;
            border: 2px solid var(--f-ink); border-radius: 16px; border-top-left-radius: 4px; box-shadow: 3px 3px 0 var(--f-ink);
        }
        .pg-out .ai-msg.is-ai .ai-bubble.is-error { background: #FFE0E8; color: #B3123F; }
        .ai-bubble p { margin: 0 0 10px; }
        .ai-bubble p:last-child { margin-bottom: 0; }
        .ai-bubble ul { margin: 0 0 10px; padding-left: 20px; }
        .ai-bubble li { margin-bottom: 6px; }
        .ai-bubble li::marker { color: var(--f-pink); }
        .ai-bubble strong { color: var(--f-pink); font-weight: 800; }
        .ai-typing { display: inline-flex; gap: 6px; padding: 6px 0; }
        .ai-typing span { width: 11px; height: 11px; border-radius: 50%; background: var(--f-pink); animation: fHop .7s infinite; }
        .ai-typing span:nth-child(2) { background: var(--f-yellow); animation-delay: .12s; }
        .ai-typing span:nth-child(3) { background: var(--f-cyan); animation-delay: .24s; }
        @keyframes fHop { 50% { transform: translateY(-8px); } }
        .pg-copy {
            all: unset; box-sizing: border-box; cursor: pointer; margin: 10px 0 0 38px;
            display: inline-flex; align-items: center; gap: 6px;
            font: 800 12px "Nunito", system-ui, sans-serif; color: var(--f-ink);
            padding: 3px 12px; border-radius: 999px;
            background: var(--f-yellow); border: 2px solid var(--f-ink); box-shadow: 2px 2px 0 var(--f-ink);
        }
        .pg-copy:active { transform: translate(2px, 2px); box-shadow: none; }
        .pg-copy:focus-visible { outline: 3px solid var(--f-cyan); outline-offset: 3px; }

        .fun-confetti { position: fixed; width: 10px; height: 14px; pointer-events: none; z-index: 9999; animation: fFall 1s ease-out forwards; }
        @keyframes fFall { to { transform: translate(var(--x), var(--y)) rotate(var(--r)); opacity: 0; } }

        @media (max-width: 520px) {
            .ai-head h3 { font-size: 16px; }
            .pg-copy { margin-left: 0; }
        }
        @media (prefers-reduced-motion: reduce) {
            .ai-head::after, .fun-mascot::before, .fun-mascot::after, .ai-dot, .ai-typing span, .ai-msg { animation: none; }
        }

        /* ===== ONLINE PERFORMANCE (master / detail) ===== */
        .perf-total {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 999px;
            background: var(--bg); border: 1px solid var(--line);
            font-size: 12px; font-weight: 700; color: var(--ink);
        }
        .perf-total i { color: var(--pink-deep); }

        .perf-summary {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 20px;
            background: var(--pink-pale);
            border-bottom: 1px solid var(--pink-light);
            font-size: 13px; color: var(--ink-soft); line-height: 1.6;
        }
        .perf-summary > i { color: var(--pink-deep); }
        .perf-summary strong { color: var(--ink); }

        .perf-layout { display: grid; grid-template-columns: 340px 1fr; }

        /* left: attraction list */
        .perf-list {
            display: flex; flex-direction: column; gap: 8px;
            padding: 16px; border-right: 1px solid var(--line); background: var(--bg);
        }
        .perf-item {
            all: unset; box-sizing: border-box; cursor: pointer;
            display: grid; grid-template-columns: 24px 36px 1fr auto; align-items: center; gap: 12px;
            padding: 12px 14px; border-radius: var(--r-lg);
            background: #fff; border: 1px solid var(--line);
            transition: .15s;
        }
        .perf-item:hover { border-color: var(--pink); }
        .perf-item:focus-visible { box-shadow: 0 0 0 3px var(--pink-light); }
        .perf-item.is-active {
            border-color: var(--pink-deep);
            background: var(--pink-pale);
            box-shadow: 0 0 0 3px var(--pink-light);
        }
        .perf-item-icon {
            width: 36px; height: 36px; border-radius: var(--r-md); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .perf-item-body { display: flex; flex-direction: column; gap: 7px; min-width: 0; }
        .perf-item-name { font-size: 13.5px; font-weight: 700; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .perf-item-body .bar-track { display: block; flex: none; height: 6px; }
        .perf-item-body .bar-fill { display: block; }
        .perf-item-num { text-align: right; font-size: 1.2rem; font-weight: 700; color: var(--ink); line-height: 1.1; }
        .perf-item-num small { display: block; font-size: 11px; font-weight: 600; color: var(--muted); margin-top: 3px; }

        /* right: selected attraction */
        .perf-pane { display: none; padding: 22px 26px 26px; }
        .perf-pane.is-active { display: block; }

        .perf-pane-head { display: flex; align-items: center; gap: 14px; margin-bottom: 18px; }
        .perf-pane-icon {
            width: 44px; height: 44px; border-radius: 12px; color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 17px; flex-shrink: 0;
        }
        .perf-pane-title { font-size: 17px; font-weight: 700; color: var(--ink); }
        .perf-best {
            margin-left: auto;
            font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em;
            background: var(--pink-deep); color: #fff; padding: 4px 11px; border-radius: 999px; white-space: nowrap;
        }

        .perf-kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 24px; }
        .perf-kpi { background: var(--bg); border: 1px solid var(--line); border-radius: var(--r-md); padding: 12px 14px; min-width: 0; }
        .perf-kpi .k { display: block; font-size: 10.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 5px; }
        .perf-kpi .v { display: block; font-size: 14px; font-weight: 700; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .perf-pane-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 28px; }
        .perf-block-title { display: flex; align-items: center; gap: 8px; font-size: 13.5px; font-weight: 700; color: var(--ink); padding-bottom: 10px; border-bottom: 1px solid var(--line); }
        .perf-block-title i { color: var(--pink-deep); }

        .origin-row { display: grid; grid-template-columns: 24px 1fr 56px; align-items: center; gap: 12px; padding: 11px 0; border-bottom: 1px solid var(--line); }
        .origin-row:last-child { border-bottom: none; }
        .origin-place { font-size: 13px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .origin-place.has-bar { margin-bottom: 6px; }
        .origin-count { text-align: right; font-size: 13.5px; font-weight: 700; color: var(--ink); }
        .origin-count span { display: block; font-size: 11px; font-weight: 500; color: var(--muted); }

        .perf-empty {
            margin-top: 12px; padding: 22px 16px; text-align: center;
            border: 1px dashed var(--line-strong); border-radius: var(--r-md);
            font-size: 12.5px; color: var(--muted);
        }
        .perf-empty i { display: block; font-size: 18px; margin-bottom: 8px; color: var(--pink); }

        @media (max-width: 900px) {
            .perf-layout { grid-template-columns: 1fr; }
            .perf-list { border-right: none; border-bottom: 1px solid var(--line); }
            .perf-pane-grid { grid-template-columns: 1fr; }
            .perf-kpis { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 520px) {
            .perf-pane { padding: 18px 16px 20px; }
            .perf-total { width: 100%; justify-content: center; }
        }

        /* ===== TOP ONLINE BOOKERS ===== */
        .place-tag {
            display: inline-flex; align-items: center; gap: 6px; max-width: 100%;
            padding: 5px 12px; border-radius: 999px;
            background: var(--bg); border: 1px solid var(--line);
            font-size: 12px; font-weight: 600; color: var(--ink);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .place-tag i { color: var(--pink-deep); font-size: 11px; }
        .place-none { font-size: 12.5px; color: var(--text-faint); font-style: italic; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 900px) {
            .res-table { min-width: 700px; font-size: 12.5px; }
            .res-table th, .res-table td { padding: 10px 12px; }
        }

        @media (max-width: 520px) {
            .filter-card { padding: 12px 14px; }
            .filter-card select { flex: 1; }
            .toolbar-actions, .toolbar-actions select { width: 100%; }
            .panel-body { padding: 14px; }
        }

        /* card view on very small screens */
        @media (max-width: 480px) {
            .table-box.res-box.table-card { background: transparent; border: none; box-shadow: none; overflow: visible; }
            .table-card .panel-head { background: var(--card); border: 1px solid var(--line); border-radius: var(--r-lg); margin-bottom: 12px; }

            .table-card .res-table,
            .table-card .res-table thead,
            .table-card .res-table tbody,
            .table-card .res-table th,
            .table-card .res-table td,
            .table-card .res-table tr { display: block; }
            .table-card .res-table colgroup,
            .table-card .res-table thead { display: none; }
            .table-card .res-table { min-width: unset; }

            .table-card .res-table tbody tr {
                border: 1px solid var(--line);
                border-radius: var(--r-lg);
                margin-bottom: 12px;
                padding: 12px 14px;
                background: var(--card);
                box-shadow: var(--shadow-sm);
            }
            .table-card .res-table tbody tr.reservation-panel-row { padding: 0; margin-top: -12px; border-top: none; border-top-left-radius: 0; border-top-right-radius: 0; }
            .table-card .res-table tbody tr.reservation-panel-row:not(.is-open) { border: none; margin: 0; box-shadow: none; }

            .table-card .res-table td { border: none; padding: 5px 0; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; overflow: visible; }
            .table-card .res-table td > div { min-width: 0; }
            .table-card .cell-main, .table-card .cell-sub { white-space: normal; }

            .table-card .res-table td[data-label]::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-sub);
                min-width: 80px;
                font-size: 10.5px;
                text-transform: uppercase;
                letter-spacing: .04em;
                padding-top: 2px;
            }

            .reservation-panel-content { padding: 14px; box-shadow: none; }
            .pagination { background: var(--card); border: 1px solid var(--line); border-radius: var(--r-lg); }
        }
    </style>
@endsection

@section('content')

    @php
        // Display names (the data may still use the old names; this renames them on screen).
        $serviceRename = [
            'Dino Adventure' => 'Dino Lakbay',
            'Roller Fever'   => 'Skataya',
            'RollerFever'    => 'Skataya',
            'Field of Rides' => 'Piyesta Rides',
        ];
        $rename     = fn ($n) => $serviceRename[$n] ?? $n;
        $renameText = fn ($t) => strtr((string) $t, $serviceRename);
    @endphp

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Visitors</div>
                 <h2 style="font-family: Arial, Helvetica, sans-serif">Visitor Login &amp; Booking Summary</h2>

            <p class="sub">Online bookings, visitor activity and promo insights</p>
        </div>
        <div class="toolbar-actions">
            <select id="periodFilter" aria-label="Period">
                <option value="7" {{ ($days ?? 30) == 7 ? 'selected' : '' }}>Last 7 days</option>
                <option value="30" {{ ($days ?? 30) == 30 ? 'selected' : '' }}>Last 30 days</option>
                <option value="90" {{ ($days ?? 30) == 90 ? 'selected' : '' }}>Last 3 months</option>
                <option value="365" {{ ($days ?? 30) == 365 ? 'selected' : '' }}>This year</option>
            </select>
        </div>
    </div>

    <!-- ONLINE PERFORMANCE -->
    @php
        $perfTotal = (int) ($totalOnlineBookings ?? 0);
        $packageRanking = $packageRanking ?? [];
        $attractionRanking = $attractionRanking ?? [];
        $originsByService = $originsByService ?? [];   // ['Dino Adventure' => [['place' => 'Lipa City', 'count' => 5], ...]]

        $norm = fn ($v) => preg_replace('/[^a-z0-9]/', '', strtolower((string) $v));
        $originsNorm = collect($originsByService)->mapWithKeys(fn ($v, $k) => [$norm($k) => $v]);

        $perfCards = collect($serviceDistribution ?? [])->sortByDesc('count')->values()->map(function ($s) use ($packageRanking, $attractionRanking, $originsNorm, $norm, $rename) {
            $pkgs = collect($packageRanking ?? [])->where('service', $s['name'])->sortByDesc('count')->values();
            $attr = collect($attractionRanking ?? [])->firstWhere('name', $s['name']);
            $origins = collect($originsNorm->get($norm($s['name'])) ?? $originsNorm->get($norm($rename($s['name']))) ?? [])->sortByDesc('count')->values();
            return [
                'name'         => $s['name'],
                'label'        => $rename($s['name']),
                'count'        => $s['count'],
                'pct'          => $s['percentage'],
                'color'        => $s['color'],
                'icon'         => $pkgs->first()['icon'] ?? 'fa-ticket',
                'age'          => $attr['top_age_group'] ?? '—',
                'packages'     => $pkgs,
                'origins'      => $origins,
                'origin_total' => $origins->sum('count'),
            ];
        });
        $perfLeader = $perfCards->first();
        $perfOthers = $perfCards->slice(1);
        $perfOtherNames = $perfOthers->pluck('label')->map(fn ($n) => '<strong>' . e($n) . '</strong>')->values();
        $perfOthersText = $perfOtherNames->count() > 1
            ? $perfOtherNames->slice(0, -1)->implode(', ') . ' and ' . $perfOtherNames->last()
            : ($perfOtherNames->first() ?? '');
    @endphp

    <div class="table-box res-box">
        <div class="panel-head">
            <div>
                <h3>Online Performance</h3>
                <p>How each attraction is doing online &middot; select one to see details</p>
            </div>
            <span class="perf-total"><i class="fa-solid fa-globe"></i> {{ $perfTotal }} online bookings</span>
        </div>

        <div class="perf-summary">
            <i class="fa-solid fa-chart-line"></i>
            <div>
                @if ($perfLeader)
                    <strong>{{ $perfLeader['label'] }}</strong> is the best seller online this period{!! $perfOthersText ? ', followed by ' . $perfOthersText : '' !!}.
                @else
                    No online bookings yet this period.
                @endif
            </div>
        </div>

        @if ($perfCards->count())
            <div class="perf-layout">
                <!-- LEFT: attractions -->
                <div class="perf-list" id="perfList" role="tablist">
                    @foreach ($perfCards as $c)
                        <button type="button" role="tab" class="perf-item {{ $loop->first ? 'is-active' : '' }}" data-target="perfPane{{ $loop->index }}">
                            <span class="rank-badge {{ $loop->first ? 'rank-1' : '' }}">{{ $loop->iteration }}</span>
                            <span class="perf-item-icon" style="background: {{ $c['color'] }};"><i class="fa-solid {{ $c['icon'] }}"></i></span>
                            <span class="perf-item-body">
                                <span class="perf-item-name">{{ $c['label'] }}</span>
                                <span class="bar-track"><span class="bar-fill" style="width: {{ $c['pct'] }}%; background: {{ $c['color'] }};"></span></span>
                            </span>
                            <span class="perf-item-num">{{ $c['count'] }}<small>{{ $c['pct'] }}%</small></span>
                        </button>
                    @endforeach
                </div>

                <!-- RIGHT: selected attraction -->
                <div class="perf-panes">
                    @foreach ($perfCards as $c)
                        <div class="perf-pane {{ $loop->first ? 'is-active' : '' }}" id="perfPane{{ $loop->index }}" role="tabpanel">
                            <div class="perf-pane-head">
                                <span class="perf-pane-icon" style="background: {{ $c['color'] }};"><i class="fa-solid {{ $c['icon'] }}"></i></span>
                                <h4 class="perf-pane-title">{{ $c['label'] }}</h4>
                                @if ($loop->first)
                                    <span class="perf-best"><i class="fa-solid fa-crown"></i> Best seller</span>
                                @endif
                            </div>

                            <div class="perf-kpis">
                                <div class="perf-kpi"><span class="k">Bookings</span><span class="v">{{ $c['count'] }}</span></div>
                                <div class="perf-kpi"><span class="k">All Bookings</span><span class="v">{{ $c['pct'] }}%</span></div>
                                <div class="perf-kpi"><span class="k">Top package</span><span class="v" title="{{ $c['packages']->first()['package'] ?? '' }}">{{ $c['packages']->first()['package'] ?? '—' }}</span></div>
                                <div class="perf-kpi"><span class="k">Top age group</span><span class="v">{{ $c['age'] }}</span></div>
                            </div>

                            <div class="perf-pane-grid">
                                <div>
                                    <div class="perf-block-title"><i class="fa-solid fa-location-dot"></i> Where visitors are from</div>
                                    @forelse ($c['origins'] as $o)
                                        @php $oPct = $c['origin_total'] > 0 ? round(($o['count'] / $c['origin_total']) * 100) : 0; @endphp
                                        <div class="origin-row">
                                            <span class="rank-badge {{ $loop->first ? 'rank-1' : '' }}">{{ $loop->iteration }}</span>
                                            <div>
                                                <div class="origin-place has-bar">{{ $o['place'] }}</div>
                                                <div class="bar-track"><div class="bar-fill" style="width: {{ $oPct }}%; background: {{ $c['color'] }};"></div></div>
                                            </div>
                                            <div class="origin-count">{{ $o['count'] }}<span>{{ $oPct }}%</span></div>
                                        </div>
                                    @empty
                                        <div class="perf-empty"><i class="fa-solid fa-map-location-dot"></i> No location data yet for this attraction.</div>
                                    @endforelse
                                </div>

                                <div>
                                    <div class="perf-block-title"><i class="fa-solid fa-ranking-star"></i> Best-selling packages</div>
                                    @forelse ($c['packages'] as $pkg)
                                        <div class="origin-row">
                                            <span class="rank-badge {{ $loop->first ? 'rank-1' : '' }}">{{ $loop->iteration }}</span>
                                            <div class="origin-place">{{ $pkg['package'] }}</div>
                                            <div class="origin-count">{{ $pkg['count'] }}</div>
                                        </div>
                                    @empty
                                        <div class="perf-empty"><i class="fa-solid fa-box-open"></i> No package data yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    <!-- TOP ONLINE BOOKERS -->
    @php
        $topBookers = collect($topBookers ?? [])->values();
        $topBooker  = $topBookers->first();

        // Location comes from the visitor's saved address (falls back to 'place' if the controller sends that).
        $placeOf = fn ($b) => trim((string) ($b['place'] ?? $b['address'] ?? ''));
        $topBookerPlace = $topBooker ? $placeOf($topBooker) : '';
    @endphp

    <div class="table-box res-box table-card">
        <div class="panel-head">
            <div>
                <h3>Top Online Bookers</h3>
                <p>Who booked online the most, and where they're from</p>
            </div>
        </div>

        @if ($topBooker)
            <div class="perf-summary">
                <i class="fa-solid fa-trophy"></i>
                <div>
                    <strong>{{ $topBooker['name'] }}</strong>
                    @if ($topBookerPlace !== '')
                        from <strong>{{ $topBookerPlace }}</strong>
                    @endif
                    has the most online bookings this period ({{ $topBooker['bookings'] }}).
                </div>
            </div>
        @endif

        <div class="table-scroll" style="overflow-x:auto;">
            <table class="res-table">
                <colgroup>
                    <col style="width:64px">
                    <col style="width:42%">
                    <col>
                    <col style="width:150px">
                </colgroup>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Visitor</th>
                        <th>Location</th>
                        <th>Last Booking</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($topBookers as $b)
                        @php
                            $bPlace = $placeOf($b);
                            $bInitials = collect(preg_split('/\s+/', trim($b['name'] ?? '')))->filter()->take(2)
                                ->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: '?';
                        @endphp
                        <tr>
                            <td data-label="Rank">
                                <span class="rank-badge {{ $loop->first ? 'rank-1' : '' }}">{{ $loop->iteration }}</span>
                            </td>
                            <td data-label="Visitor">
                                <div class="icon-name">
                                    <span class="visitor-avatar">{{ $bInitials }}</span>
                                    <div>
                                        <div class="cell-main">{{ $b['name'] ?? '—' }}</div>
                                        <div class="cell-sub">{{ $b['email'] ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Location">
                                @if ($bPlace !== '')
                                    <span class="place-tag" title="{{ $bPlace }}"><i class="fa-solid fa-location-dot"></i> {{ $bPlace }}</span>
                                @else
                                    <span class="place-none">Not provided</span>
                                @endif
                            </td>
                            <td data-label="Last Booking">
                                <div class="cell-main">{{ !empty($b['last_booking']) ? \Carbon\Carbon::parse($b['last_booking'])->format('M d, Y') : '—' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="4"><i class="fa-solid fa-circle-info"></i> No online bookers yet this period.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- FILTER BAR (visitor logins) -->
    <div class="filter-bar filter-card">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="visitorSearch" placeholder="Search visitor by name or email...">
        </div>

        <select id="visitorStatusFilter">
            <option value="">All Status</option>
            <option value="online">Online</option>
            <option value="offline">Offline</option>
        </select>

        <button type="button" class="btn-ghost" id="visitorClearBtn">
            <i class="fa-solid fa-arrow-rotate-left"></i> Clear
        </button>

        <span class="filter-count" id="visitorCount"></span>
    </div>

    <!-- RECENT VISITOR LOGINS -->
    <div class="table-box res-box table-card">
        <div class="panel-head">
            <div>
                <h3>Recent Visitor Logins</h3>
                <p>Click a row to preview that visitor's linked reservation</p>
            </div>
        </div>

        <div class="table-scroll" style="overflow-x:auto;">
            <table class="res-table">
                <colgroup>
                    <col style="width:34%">
                    <col style="width:180px">
                    <col style="width:140px">
                    <col>
                    <col style="width:64px">
                </colgroup>
                <thead>
                    <tr>
                        <th>Visitor</th>
                        <th>Last Login</th>
                        <th>Status</th>
                        <th>Reservation</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="visitorBody">
                    @forelse(($visitorLogins ?? []) as $v)
                        @php $isOnline = (bool) ($v['online'] ?? false); @endphp
                        <tr class="visitor-row"
                            data-reservation-id="{{ $v['reservation_id'] ?? '' }}"
                            data-name="{{ $v['name'] }}"
                            data-email="{{ $v['email'] ?? '' }}"
                            data-online="{{ $isOnline ? 'online' : 'offline' }}">
                            <td data-label="Visitor">
                                <div class="icon-name">
                                    <span class="visitor-avatar">{{ $v['initials'] ?? '?' }}</span>
                                    <div>
                                        <div class="cell-main">{{ $v['name'] }}</div>
                                        <div class="cell-sub">{{ $v['email'] ?? 'No email' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td data-label="Last Login">
                                <div class="cell-main">{{ $v['last_login'] ?? '—' }}</div>
                            </td>
                            <td data-label="Status">
                                @if ($isOnline)
                                    <span class="visitor-status"><i class="fa-solid fa-circle"></i> Online</span>
                                @else
                                    <span class="visitor-status is-offline"><i class="fa-solid fa-circle"></i> Offline</span>
                                @endif
                            </td>
                            <td data-label="Reservation">
                                @if (!empty($v['reservation_id']))
                                    <div>
                                        <div class="cell-main">Reservation linked</div>
                                        <div class="cell-sub sub-ok">Click to view details</div>
                                    </div>
                                @else
                                    <span class="dash">&mdash;</span>
                                @endif
                            </td>
                            <td data-label="">
                                <span class="expand-chevron"><i class="fa-solid fa-chevron-down"></i></span>
                            </td>
                        </tr>
                        <tr class="reservation-panel-row">
                            <td colspan="5">
                                <div class="reservation-panel">
                                    <div class="reservation-panel-inner">
                                        <div class="reservation-panel-content"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="5"><i class="fa-solid fa-circle-info"></i> No visitor logins recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination no-print" id="visitorPagination"></div>
    </div>

    <!-- AI PROMO GENERATOR -->
    @php
        $fallbackParks = [
            ['name' => 'Dino Lakbay',   'color' => '#22a06b'],
            ['name' => 'Skataya',       'color' => '#ff7a45'],
            ['name' => 'Piyesta Rides', 'color' => '#4f6bff'],
        ];

        // Attractions come from your real booking data; falls back to the 3 parks if there is no data yet.
        $parkList = $perfCards->count()
            ? $perfCards->map(fn ($c) => [
                'name'    => $c['label'],
                'color'   => $c['color'],
                'icon'    => $c['icon'],
                'count'   => $c['count'],
                'origins' => $c['origins']->take(5)->pluck('place')->values(),
            ])->values()
            : collect($fallbackParks)->map(fn ($p) => $p + ['icon' => 'fa-ticket', 'count' => 0, 'origins' => []]);

        // Data the AI can reason about (sent along with the request)
        $aiContext = [
            'branch'       => 'Lipa Branch (theme park)',
            'period_days'  => (int) ($days ?? 30),
            'total_online' => $perfTotal,
            'attractions'  => $perfCards->map(fn ($c) => [
                'name'         => $c['label'],
                'bookings'     => $c['count'],
                'share_pct'    => $c['pct'],
                'top_age'      => $c['age'],
                'top_packages' => $c['packages']->take(3)->map(fn ($p) => ['package' => $p['package'], 'count' => $p['count']])->values(),
                'top_origins'  => $c['origins']->take(3)->map(fn ($o) => ['place' => $o['place'], 'count' => $o['count']])->values(),
            ])->values(),
        ];
    @endphp

    <div class="table-box res-box ai-panel">
        <div class="panel-head ai-head">
            <div class="ai-head-title">
                <span class="fun-mascot" aria-hidden="true"><i></i></span>
                <div>
                    <h3>Promo Generator <span class="ai-chip"><i class="fa-solid fa-sparkles"></i> AI-powered</span></h3>
                    <p>Pick an attraction and audience, and the AI suggests promos and new ride ideas from your booking data</p>
                </div>
            </div>
            <span class="ai-status"><span class="ai-dot"></span> Ready</span>
        </div>

        <div class="ai-chat" id="promoGen"
             data-url="{{ route('ai.promo-suggest', [], false) }}"
             data-context="{{ json_encode($aiContext) }}"
             data-parks="{{ json_encode($parkList) }}">

            <div class="pg-step"><b>1</b> Pick an attraction</div>
            <div class="pg-parks" id="pgParks"></div>

            <div class="pg-step"><b>2</b> Who is the promo for?</div>
            <div class="pg-chips" id="pgAges"></div>
            <input type="text" class="pg-field" id="pgAgeCustom" maxlength="40" placeholder="Or type your own, e.g. 18-25 or office workers" autocomplete="off">

            <div class="pg-step"><b>3</b> Where are the visitors from? <small>(optional)</small></div>
            <div class="pg-chips" id="pgOrigins"></div>
            <input type="text" class="pg-field" id="pgOriginCustom" maxlength="60" placeholder="Or type a town or city" autocomplete="off">
            <p class="pg-hint">Top towns from your records show up after you pick an attraction. Leave it on “Any location” to get promos for everyone.</p>

            <div class="pg-step"><b>4</b> What kind of ideas?</div>
            <div class="pg-chips" id="pgKinds"></div>

            <div class="pg-step"><b>5</b> How many ideas?</div>
            <div class="pg-chips" id="pgCounts"></div>

            <div class="pg-step"><b>6</b> Anything else? <small>(optional)</small></div>
            <input type="text" class="pg-field" id="pgNotes" maxlength="200" placeholder="e.g. slow weekdays, push group bookings" autocomplete="off" style="margin-top:0">

            <button type="button" class="ai-send" id="pgGo" disabled style="margin-top:20px">
                <i class="fa-solid fa-wand-magic-sparkles"></i> <span>Generate ideas</span>
            </button>

            <div class="pg-out" id="pgOut" aria-live="polite"></div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        // Period filter — reloads with a ?days= query param (handled by the controller).
        document.getElementById('periodFilter')?.addEventListener('change', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('days', this.value);
            window.location.href = url.toString();
        });

        function escapeHtml(str) {
            return String(str ?? '—').replace(/[&<>"']/g, c => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[c]));
        }

        // first non-empty value from a list of possible field names
        function pick(obj, keys) {
            for (const k of keys) {
                const v = obj?.[k];
                if (v !== undefined && v !== null && String(v).trim() !== '') return v;
            }
            return null;
        }

        // "rides_60" -> "Rides 60"
        function prettyPackage(s) {
            if (!s) return '—';
            return String(s).replace(/[_-]+/g, ' ').replace(/\s+/g, ' ').trim()
                .replace(/\b\w/g, c => c.toUpperCase());
        }

        // "2026-10-05" -> "Oct 05, 2026"
        function prettyDate(s) {
            if (!s) return '—';
            if (/^\d{4}-\d{2}-\d{2}/.test(String(s))) {
                const d = new Date(String(s).substring(0, 10) + 'T00:00:00');
                if (!isNaN(d)) return d.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' });
            }
            return String(s);
        }

        // "13:30:00" -> "1:30 PM"
        function prettyTime(s) {
            if (!s) return '—';
            const m = String(s).match(/^(\d{1,2}):(\d{2})/);
            if (!m) return String(s);
            let h = parseInt(m[1], 10);
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return `${h}:${m[2]} ${ampm}`;
        }

        // ── Visitor logins: expandable rows + search/filter + pagination ──
        (function () {
            const tbody = document.getElementById('visitorBody');
            if (!tbody) return;

            const pagination = document.getElementById('visitorPagination');
            const searchInput = document.getElementById('visitorSearch');
            const statusFilter = document.getElementById('visitorStatusFilter');
            const clearBtn = document.getElementById('visitorClearBtn');
            const countEl = document.getElementById('visitorCount');
            const COL_COUNT = 5;
            const PAGE_SIZE = 10;
            let currentPage = 1;

            const pairs = Array.from(tbody.querySelectorAll('tr.visitor-row')).map(row => ({
                row,
                panel: row.nextElementSibling
            }));

            if (!pairs.length) {
                if (pagination) pagination.style.display = 'none';
                return;
            }

            // expand / collapse + lazy-load the reservation
            pairs.forEach(({ row, panel }) => {
                row.addEventListener('click', () => {
                    const content = panel.querySelector('.reservation-panel-content');
                    const isOpen = row.classList.contains('is-open');

                    pairs.forEach(p => {
                        if (p.row !== row) {
                            p.row.classList.remove('is-open');
                            p.panel.classList.remove('is-open');
                        }
                    });

                    row.classList.toggle('is-open', !isOpen);
                    panel.classList.toggle('is-open', !isOpen);

                    if (isOpen || content.dataset.loaded) return;

                    const reservationId = row.dataset.reservationId;
                    if (!reservationId) {
                        content.innerHTML = '<p class="reservation-error">No linked reservation for this visitor.</p>';
                        content.dataset.loaded = '1';
                        return;
                    }

                    content.innerHTML = '<div class="reservation-loading"><span class="spinner"></span> Loading reservation details…</div>';

                    fetch(`/reservations/${reservationId}`, { headers: { 'Accept': 'application/json' } })
                        .then(res => {
                            if (!res.ok) throw new Error('Request failed');
                            return res.json();
                        })
                        .then(data => {
                            // tolerant of different field names coming from the controller
                            const pkg    = prettyPackage(pick(data, ['package_label', 'package', 'package_name']));
                            const pax    = pick(data, ['pax', 'display_pax', 'guests', 'number_of_pax']) ?? '—';
                            const date   = prettyDate(pick(data, ['reservation_date', 'display_date', 'date']));
                            const time   = prettyTime(pick(data, ['reservation_time', 'display_time', 'time']));
                            const status = String(pick(data, ['status', 'approval_status']) ?? '—').toLowerCase();

                            content.innerHTML = `
                                <div class="reservation-summary">
                                    <div class="reservation-field"><span class="k">Package</span><span class="v">${escapeHtml(pkg)}</span></div>
                                    <div class="reservation-field"><span class="k">Pax</span><span class="v">${escapeHtml(pax)}</span></div>
                                    <div class="reservation-field"><span class="k">Date</span><span class="v">${escapeHtml(date)}</span></div>
                                    <div class="reservation-field"><span class="k">Time</span><span class="v">${escapeHtml(time)}</span></div>
                                    <div class="reservation-field"><span class="k">Status</span><span class="v"><span class="res-badge" data-s="${escapeHtml(status)}">${escapeHtml(status)}</span></span></div>
                                </div>
                                <a class="reservation-link-btn" href="/reservations#${encodeURIComponent(reservationId)}">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Open in Reservations
                                </a>
                            `;
                            content.dataset.loaded = '1';
                        })
                        .catch(() => {
                            content.innerHTML = '<p class="reservation-error">Couldn\'t load this reservation. Try again.</p>';
                        });
                });
            });

            // "no results" row
            const noMatchRow = document.createElement('tr');
            noMatchRow.className = 'empty-row';
            noMatchRow.style.display = 'none';
            noMatchRow.innerHTML = `<td colspan="${COL_COUNT}"><i class="fa-solid fa-circle-info"></i> No visitors match your search or filters.</td>`;
            tbody.appendChild(noMatchRow);

            function getFiltered() {
                const q = (searchInput?.value || '').toLowerCase().trim();
                const st = statusFilter?.value || '';
                return pairs.filter(({ row }) => {
                    const name = (row.dataset.name || '').toLowerCase();
                    const email = (row.dataset.email || '').toLowerCase();
                    const okSearch = !q || name.includes(q) || email.includes(q);
                    const okStatus = !st || row.dataset.online === st;
                    return okSearch && okStatus;
                });
            }

            function renderPagination(totalPages) {
                if (totalPages <= 1) {
                    pagination.style.display = 'none';
                    pagination.innerHTML = '';
                    return;
                }
                pagination.style.display = 'flex';
                let html = `<span class="pagination-info">Page ${currentPage} of ${totalPages}</span>`;
                html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">&laquo; Prev</button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button class="${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
                html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">Next &raquo;</button>`;
                pagination.innerHTML = html;
                pagination.querySelectorAll('button[data-page]').forEach(b => {
                    b.addEventListener('click', () => {
                        currentPage = parseInt(b.dataset.page, 10);
                        render();
                    });
                });
            }

            function render() {
                const filtered = getFiltered();
                const totalPages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const start = (currentPage - 1) * PAGE_SIZE;
                const visible = new Set(filtered.slice(start, start + PAGE_SIZE));

                pairs.forEach(p => {
                    const show = visible.has(p);
                    p.row.style.display = show ? '' : 'none';
                    p.panel.style.display = show ? '' : 'none';
                    if (!show) {
                        p.row.classList.remove('is-open');
                        p.panel.classList.remove('is-open');
                    }
                });

                noMatchRow.style.display = filtered.length === 0 ? '' : 'none';

                const hasFilter = (searchInput?.value || '') || statusFilter?.value;
                if (countEl) countEl.textContent = hasFilter ? `${filtered.length} of ${pairs.length} visitors shown` : '';

                renderPagination(totalPages);
            }

            searchInput?.addEventListener('input', () => { currentPage = 1; render(); });
            statusFilter?.addEventListener('change', () => { currentPage = 1; render(); });
            clearBtn?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (statusFilter) statusFilter.value = '';
                currentPage = 1;
                render();
            });

            render();
        })();

        // ── Online performance: select an attraction to see its details ──
        (function () {
            const list = document.getElementById('perfList');
            if (!list) return;
            const items = list.querySelectorAll('.perf-item');
            items.forEach(item => {
                item.addEventListener('click', () => {
                    items.forEach(i => i.classList.toggle('is-active', i === item));
                    document.querySelectorAll('.perf-pane').forEach(p => {
                        p.classList.toggle('is-active', p.id === item.dataset.target);
                    });
                });
            });
        })();

        // ── AI promo generator: attraction + audience + optional location ──
        (function () {
            const root = document.getElementById('promoGen');
            if (!root) return;
            const $ = id => document.getElementById(id);
            const url = root.dataset.url;
            let context = {}, parks = [];
            try { context = JSON.parse(root.dataset.context || '{}'); } catch (e) {}
            try { parks = JSON.parse(root.dataset.parks || '[]'); } catch (e) {}

            const AGES = ['18-24', '25-34', '35-44', '45-54', '55+'];
            const sel = { park: null, age: null, origin: null, kind: 'mix', count: 5 };
            const goBtn = $('pgGo'), out = $('pgOut');
            let busy = false;

            function refresh() { goBtn.disabled = busy || !(sel.park && sel.age); }

            function makeChip(label, on, onClick) {
                const b = document.createElement('button');
                b.type = 'button'; b.className = 'pg-chip'; b.textContent = label;
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
                b.addEventListener('click', onClick);
                return b;
            }
            function setPressed(wrap, btn) {
                wrap.querySelectorAll('.pg-chip').forEach(c => c.setAttribute('aria-pressed', c === btn ? 'true' : 'false'));
            }

            // 1. attractions
            const parkWrap = $('pgParks');
            parks.forEach(p => {
                const b = document.createElement('button');
                b.type = 'button'; b.className = 'pg-park'; b.setAttribute('aria-pressed', 'false');
                b.innerHTML =
                    '<span class="pg-park-icon" style="background:' + escapeHtml(p.color) + '"><i class="fa-solid ' + escapeHtml(p.icon) + '"></i></span>' +
                    '<span><span class="pg-park-name">' + escapeHtml(p.name) + '</span>' +
                    '<span class="pg-park-sub">' + escapeHtml(p.count) + ' online bookings</span></span>';
                b.addEventListener('click', () => {
                    parkWrap.querySelectorAll('.pg-park').forEach(x => x.setAttribute('aria-pressed', x === b ? 'true' : 'false'));
                    sel.park = p; sel.origin = null; $('pgOriginCustom').value = '';
                    renderOrigins(); refresh();
                });
                parkWrap.appendChild(b);
            });

            // 2. audience
            const ageWrap = $('pgAges');
            AGES.forEach(a => {
                const c = makeChip(a, false, () => {
                    const on = sel.age !== a;
                    setPressed(ageWrap, on ? c : null);
                    sel.age = on ? a : null; $('pgAgeCustom').value = ''; refresh();
                });
                ageWrap.appendChild(c);
            });
            $('pgAgeCustom').addEventListener('input', e => {
                setPressed(ageWrap, null);
                sel.age = e.target.value.trim() || null; refresh();
            });

            // 3. location (optional)
            const originWrap = $('pgOrigins');
            function renderOrigins() {
                originWrap.innerHTML = '';
                const any = makeChip('Any location', true, () => {
                    setPressed(originWrap, any); sel.origin = null; $('pgOriginCustom').value = '';
                });
                originWrap.appendChild(any);
                ((sel.park && sel.park.origins) || []).forEach(place => {
                    const c = makeChip(place, false, () => {
                        setPressed(originWrap, c); sel.origin = place; $('pgOriginCustom').value = '';
                    });
                    originWrap.appendChild(c);
                });
            }
            $('pgOriginCustom').addEventListener('input', e => {
                setPressed(originWrap, null);
                sel.origin = e.target.value.trim() || null;
                if (!sel.origin && originWrap.firstElementChild) originWrap.firstElementChild.setAttribute('aria-pressed', 'true');
            });
            renderOrigins();

            // 4. kind of ideas + 5. how many
            const KINDS = [
                ['mix', 'Mix of everything'],
                ['promo', 'Promos & discounts'],
                ['newride', 'New rides / upgrades'],
                ['marketing', 'Marketing & events']
            ];
            const COUNTS = [3, 5, 7, 10];
            const kindWrap = $('pgKinds'), countWrap = $('pgCounts');
            KINDS.forEach(([val, label]) => {
                const c = makeChip(label, val === sel.kind, () => { setPressed(kindWrap, c); sel.kind = val; });
                kindWrap.appendChild(c);
            });
            COUNTS.forEach(n => {
                const c = makeChip(String(n), n === sel.count, () => { setPressed(countWrap, c); sel.count = n; });
                countWrap.appendChild(c);
            });

            // tiny formatter: **bold**, "- " bullets, paragraphs (input is escaped first)
            function format(text) {
                const lines = escapeHtml(text).split(/\n/);
                let html = '', inList = false;
                lines.forEach(l => {
                    l = l.trim().replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
                    if (/^[-•*]\s+/.test(l)) {
                        if (!inList) { html += '<ul>'; inList = true; }
                        html += '<li>' + l.replace(/^[-•*]\s+/, '') + '</li>';
                    } else {
                        if (inList) { html += '</ul>'; inList = false; }
                        if (l) html += '<p>' + l + '</p>';
                    }
                });
                if (inList) html += '</ul>';
                return html;
            }

            // confetti burst from the button
            function confetti() {
                if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                const r = goBtn.getBoundingClientRect(), cols = ['#FF3D81', '#FFC914', '#12C4D6', '#8B5CF6'];
                for (let i = 0; i < 24; i++) {
                    const p = document.createElement('div');
                    p.className = 'fun-confetti';
                    p.style.left = (r.left + r.width / 2) + 'px'; p.style.top = r.top + 'px';
                    p.style.background = cols[i % 4];
                    p.style.setProperty('--x', (Math.random() * 260 - 130) + 'px');
                    p.style.setProperty('--y', (-70 - Math.random() * 170) + 'px');
                    p.style.setProperty('--r', (Math.random() * 720) + 'deg');
                    document.body.appendChild(p);
                    setTimeout(() => p.remove(), 1000);
                }
            }

            // generate
            goBtn.addEventListener('click', async () => {
                if (busy || !(sel.park && sel.age)) return;
                const notes = $('pgNotes').value.trim();
                const kindLabel = (KINDS.find(k => k[0] === sel.kind) || KINDS[0])[1];
                const question =
                    sel.count + ' ideas (' + kindLabel + ') for ' + sel.park.name + ', ages ' + sel.age +
                    (sel.origin ? ', visitors from ' + sel.origin : ', any location') +
                    (notes ? '. Note: ' + notes.slice(0, 120) : '') +
                    '. Use my booking data.';

                busy = true; refresh(); confetti();
                out.innerHTML =
                    '<div class="pg-out-title"></div>' +
                    '<div class="ai-msg is-ai"><span class="ai-avatar"><i class="fa-solid fa-wand-magic-sparkles"></i></span>' +
                    '<div class="ai-bubble"><span class="ai-typing"><span></span><span></span><span></span></span></div></div>';
                out.firstElementChild.textContent = sel.count + ' ideas for ' + [sel.park.name, sel.age, sel.origin || 'All locations'].join(' · ');
                const bubble = out.querySelector('.ai-bubble');

                try {
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            question,
                            context: Object.assign({}, context, {
                                selection: { attraction: sel.park.name, age_group: sel.age, location: sel.origin || 'any', notes, kind: sel.kind, count: sel.count }
                            })
                        })
                    });
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.answer) {
                        const firstErr = data.errors ? Object.values(data.errors)[0] : null;
                        const why = (Array.isArray(firstErr) ? firstErr[0] : null) || data.message || 'no answer returned';
                        throw new Error('Error ' + res.status + ': ' + why);
                    }

                    bubble.innerHTML = format(data.answer);
                    const copy = document.createElement('button');
                    copy.type = 'button'; copy.className = 'pg-copy';
                    copy.innerHTML = '<i class="fa-regular fa-copy"></i> Copy text';
                    copy.addEventListener('click', async () => {
                        try { await navigator.clipboard.writeText(data.answer); copy.textContent = 'Copied ✓'; }
                        catch (e) { copy.textContent = 'Copy failed'; }
                    });
                    out.appendChild(copy);
                } catch (err) {
                    bubble.classList.add('is-error');
                    const msg = (err && err.message) ? err.message : 'unknown error';
                    const net = /failed to fetch|networkerror|load failed/i.test(msg)
                        ? ' Hindi naabot ang server. Tingnan ang Network tab at storage/logs/laravel.log.'
                        : '';
                    bubble.textContent = 'Couldn\'t get promo ideas right now. Try again. (' + msg + ')' + net;
                } finally {
                    busy = false; refresh();
                    out.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                }
            });
        })();
    </script>
@endpush