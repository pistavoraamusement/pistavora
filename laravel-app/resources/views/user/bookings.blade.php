{{--
    resources/views/user/bookings.blade.php
    Route: GET  /user/bookings                        -> user.bookings
           POST /user/bookings/{booking}/cancel        -> user.bookings.cancel
           POST /user/bookings/{booking}/reschedule    -> user.bookings.reschedule
    Controller: App\Http\Controllers\User\BookingController@index / cancel / reschedule

    Expects from the controller:
      $bookings -> LengthAwarePaginator of
        ['id', 'reference', 'service', 'category', 'package', 'date', 'time_label', 'pax',
         'price', 'status', 'status_label', 'voucher_code', 'rejection_reason',
         'lines' (name, qty, unit_price, free, total), 'extras' (name, qty, unit_price, total), 'discount']

      Optional (admin rejection popup):
        'approval_status' => 'rejected' | 'pending' | 'approved'
        'reject_reason'   => string|null
        'reject_note'     => string|null

      $stats (optional) -> total_visits, upcoming_reservations, completed_reservations,
        total_spending, favorite_package, favorite_package_count,
        history_labels, history_values, category_labels, category_values
--}}
@extends('layouts.user')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')
@section('page-subtitle', 'Manage your reservations')
@section('hide-page-title', '1')
@section('body-class', 'page-uniform')

@push('styles')
<style>
/* =====================================================================
   MY BOOKINGS — theme tokens (light + dark)
   Everything lives inside .mt-wrap so dark mode works for the hero,
   tabs, analytics, tickets, pagination and the popups.
   To soften or change the overall color, edit --mt-accent below.
   To change the page background tint, edit --mt-page / --mt-notch.
   ===================================================================== */
.mt-wrap, .mt-wrap *{ box-sizing:border-box; }
.mt-wrap{
  --mt-surface:#fff; --mt-soft:#FBF8FB; --mt-text:var(--ink); --mt-sub:var(--ink-soft); --mt-line:var(--line);
  --mt-shadow:var(--shadow-card); --mt-accent:var(--pink-deep); --mt-focus:var(--pink-light); --mt-on:#fff;
  --mt-ok-bg:var(--teal-pale);   --mt-ok-fg:var(--teal-deep);
  --mt-warn-bg:var(--amber-pale); --mt-warn-fg:var(--amber-deep);
  --mt-bad-bg:var(--rose-pale);  --mt-bad-fg:var(--rose-deep);
  /* hero gradient */
  --mt-hero-a:var(--pink-deep); --mt-hero-b:#F2762E;
  /* page background: soft warm tint (NOT plain white) + faint dots + glows */
  --mt-notch:#FFF4F3;
  --mt-page:
    radial-gradient(rgba(226,75,110,.11) 1.3px, transparent 1.5px) 0 0 / 22px 22px,
    radial-gradient(620px 320px at 100% 0%, rgba(242,118,46,.14), transparent 70%),
    radial-gradient(520px 320px at 0% 100%, rgba(226,75,110,.12), transparent 70%),
    linear-gradient(180deg,#FFF1F4 0%,#FFF6EE 55%,#FFF3F6 100%);
  width:100%;
  padding:16px;
  border-radius:28px;
  background:var(--mt-page);
}
[data-theme="dark"] .mt-wrap{
  --mt-surface:var(--d-surface); --mt-soft:var(--d-surface-soft); --mt-text:var(--d-text); --mt-sub:var(--d-text-soft); --mt-line:var(--d-border);
  --mt-shadow:0 10px 30px -18px rgba(0,0,0,.7); --mt-accent:#FF7A9E; --mt-focus:rgba(255,122,158,.28); --mt-on:#2a0a14;
  --mt-ok-bg:rgba(45,212,191,.14);  --mt-ok-fg:#5EEAD4;
  --mt-warn-bg:rgba(255,193,7,.14); --mt-warn-fg:#FFD98A;
  --mt-bad-bg:rgba(251,113,133,.15); --mt-bad-fg:#FDA4AF;
  --mt-hero-a:#8E2347; --mt-hero-b:#B5501C;
  --mt-notch:var(--d-bg);
  --mt-page:
    radial-gradient(rgba(255,122,158,.10) 1.3px, transparent 1.5px) 0 0 / 22px 22px,
    radial-gradient(620px 320px at 100% 0%, rgba(181,80,28,.18), transparent 70%),
    radial-gradient(520px 320px at 0% 100%, rgba(142,35,71,.22), transparent 70%),
    var(--d-bg);
}
.mt-panel[hidden]{ display:none !important; }

/* =====================================================================
   Hide the layout's own dark-mode toggle on THIS page only
   (the toggle now lives inside the hero, next to "+ New Booking").
   If your layout's toggle uses a different id/class, add it here.
   ===================================================================== */
#themeToggle, #theme-toggle, #darkModeToggle, #dark-mode-toggle, #darkToggle,
.theme-toggle, .dark-toggle, .dark-mode-toggle, .darkmode-toggle,
[data-theme-toggle], [data-toggle-theme], [data-action="toggle-theme"]{
  display:none !important;
}

/* ===== Buttons (all same height/border so rows line up) ===== */
.mt-btn{ display:inline-flex; align-items:center; justify-content:center; gap:6px; border:2px solid transparent; cursor:pointer; border-radius:14px; padding:11px 18px;
  font-weight:800; font-size:13px; line-height:1.2; font-family:inherit; text-decoration:none; text-align:center; white-space:nowrap; transition:all .18s var(--ease); }
.mt-btn:hover{ transform:translateY(-2px); }
.mt-btn:focus-visible{ outline:2px solid var(--mt-accent); outline-offset:2px; }
.mt-btn.go{ background:var(--mt-accent); color:var(--mt-on); box-shadow:none; }
.mt-btn.go:hover{ filter:brightness(1.06); }
.mt-btn.white{ background:var(--mt-accent); color:var(--mt-on); box-shadow:none; }
.mt-btn.ghost{ background:var(--mt-soft); color:var(--mt-text); border-color:var(--mt-line); }
.mt-btn.ghost:hover{ border-color:var(--mt-accent); color:var(--mt-accent); }
.mt-btn.danger{ background:transparent; color:var(--mt-bad-fg); border-color:var(--mt-bad-fg); }
.mt-btn.danger:hover{ background:var(--mt-bad-bg); }

/* ===== Hero (gradient + pattern + floating decorations) ===== */
.mt-hero{ position:relative; overflow:hidden; margin-top:0; display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap;
  padding:28px 30px; border-radius:26px; color:#fff;
  background:linear-gradient(135deg,var(--mt-hero-a) 0%,var(--mt-hero-b) 100%);
  border:1px solid rgba(255,255,255,.18);
  box-shadow:0 18px 40px -22px var(--mt-hero-a); }
/* dot pattern */
.mt-hero::before{ content:""; position:absolute; inset:0; pointer-events:none; opacity:.55;
  background-image:radial-gradient(rgba(255,255,255,.22) 1.4px, transparent 1.6px);
  background-size:18px 18px;
  -webkit-mask-image:linear-gradient(115deg, transparent 20%, #000 100%);
          mask-image:linear-gradient(115deg, transparent 20%, #000 100%); }
/* glow circles */
.mt-hero::after{ content:""; position:absolute; right:-70px; top:-90px; width:260px; height:260px; border-radius:50%;
  background:radial-gradient(circle at 30% 30%, rgba(255,255,255,.35), rgba(255,255,255,0) 70%); pointer-events:none; }
.mt-hero-glow{ position:absolute; left:-60px; bottom:-110px; width:240px; height:240px; border-radius:50%;
  background:radial-gradient(circle, rgba(255,255,255,.18), rgba(255,255,255,0) 70%); pointer-events:none; }
.mt-deco{ position:absolute; pointer-events:none; user-select:none; line-height:1; filter:drop-shadow(0 6px 10px rgba(0,0,0,.18)); animation:mtFloat 6s ease-in-out infinite; }
.mt-deco.d1{ right:32%; top:14px;  font-size:26px; opacity:.45; animation-delay:0s; }
.mt-deco.d2{ right:20%; bottom:10px; font-size:34px; opacity:.4;  animation-delay:1.2s; }
.mt-deco.d3{ right:44%; bottom:14px; font-size:22px; opacity:.35; animation-delay:2.4s; }
@keyframes mtFloat{ 0%,100%{ transform:translateY(0) rotate(-6deg);} 50%{ transform:translateY(-8px) rotate(6deg);} }
.mt-hero > *{ position:relative; z-index:1; }
.mt-hero > .mt-deco, .mt-hero > .mt-hero-glow{ position:absolute; z-index:0; }
.mt-hero small{ display:inline-flex; align-items:center; gap:6px; padding:5px 12px; border-radius:999px; background:rgba(255,255,255,.18); color:#fff;
  font-weight:800; letter-spacing:.1em; font-size:11px; backdrop-filter:blur(4px); }
.mt-hero h2{ margin:10px 0 4px; font-size:30px; font-weight:800; letter-spacing:-.01em; color:#fff; text-shadow:0 2px 12px rgba(0,0,0,.18); }
.mt-hero p{ margin:0; font-size:14px; color:rgba(255,255,255,.9); }

.mt-hero-actions{ display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
/* New Booking button on the gradient */
.mt-hero .mt-btn.go{ background:#fff; color:var(--mt-hero-a); border-color:#fff; box-shadow:0 8px 20px -10px rgba(0,0,0,.4); }
[data-theme="dark"] .mt-hero .mt-btn.go{ background:#fff; color:#8E2347; }
.mt-hero .mt-btn.go:hover{ filter:none; box-shadow:0 12px 24px -10px rgba(0,0,0,.5); }
/* Dark-mode toggle (icon-only glass button) */
.mt-btn.mt-theme{ width:46px; height:46px; padding:0; border-radius:50%; flex:0 0 46px;
  background:rgba(255,255,255,.16); color:#fff; border-color:rgba(255,255,255,.5); backdrop-filter:blur(6px); }
.mt-btn.mt-theme:hover{ background:rgba(255,255,255,.28); }
.mt-btn.mt-theme:focus-visible{ outline-color:#fff; }
.mt-btn.mt-theme .mt-theme-ico{ font-size:19px; line-height:1; }

/* ===== Tabs (segmented control) ===== */
.mt-tabs{ display:inline-flex; gap:4px; margin-top:16px; padding:5px; border-radius:999px; background:var(--mt-surface); border:1px solid var(--mt-line); }
.mt-tab{ border:none; cursor:pointer; font-family:inherit; font-weight:800; font-size:13px; padding:9px 22px; border-radius:999px; background:transparent; color:var(--mt-sub); transition:all .18s var(--ease); }
.mt-tab:hover{ color:var(--mt-text); }
.mt-tab:focus-visible{ outline:2px solid var(--mt-accent); outline-offset:2px; }
.mt-tab.active{ background:var(--mt-accent); color:var(--mt-on); box-shadow:none; }

/* ===== Search ===== */
.mt-search{ margin-top:18px; display:flex; gap:8px; flex-wrap:wrap; align-items:center; }
.mt-search input{ flex:1 1 240px; min-width:0; height:46px; padding:0 20px; font-size:13.5px; font-family:inherit; border-radius:999px; border:2px solid var(--mt-line);
  background:var(--mt-surface); color:var(--mt-text); outline:none; transition:border-color .18s, box-shadow .18s; }
.mt-search input::placeholder{ color:var(--mt-sub); opacity:.85; }
.mt-search input:focus{ border-color:var(--mt-accent); box-shadow:0 0 0 3px var(--mt-focus); }
.mt-search .mt-btn{ height:46px; }
.mt-meta{ margin:10px 4px 0; font-size:12.5px; color:var(--mt-sub); }

/* ===== Ticket list ===== */
.mt-list{ margin-top:16px; display:flex; flex-direction:column; gap:18px; }
.mt-ticket{ position:relative; background:var(--mt-surface); color:var(--mt-text); border-radius:24px; border:1px solid var(--mt-line); box-shadow:var(--mt-shadow); overflow:hidden;
  transition:transform .25s var(--ease), box-shadow .25s var(--ease); }
.mt-ticket:hover{ transform:translateY(-3px); box-shadow:var(--shadow-pop); }
.mt-top{ padding:18px 22px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;
  background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; }
.mt-top > div:first-child{ min-width:0; }
.mt-top small{ opacity:.9; font-weight:800; letter-spacing:.08em; font-size:11px; text-transform:uppercase; }
.mt-top h3{ margin:4px 0 0; font-size:21px; color:#fff; letter-spacing:.01em; text-transform:uppercase; }
.mt-when{ opacity:.92; font-size:13px; margin-top:6px; }

/* Clickable ticket area */
.js-open-detail{ cursor:pointer; }
.mt-top:focus-visible, .mt-mid:focus-visible{ outline:2px solid var(--mt-accent); outline-offset:-2px; }
.mt-top:focus-visible{ outline-color:#fff; }
.mt-hint{ display:block; margin-top:10px; font-size:11.5px; font-weight:800; color:#fff; opacity:.8; transition:opacity .15s; }
.mt-ticket:hover .mt-hint{ opacity:1; }

.mt-pill{ display:inline-flex; align-items:center; align-self:flex-start; gap:8px; padding:7px 16px; border-radius:999px; font-weight:800; font-size:12.5px; white-space:nowrap;
  background:var(--mt-warn-bg); color:var(--mt-warn-fg); }
.mt-pill::before{ content:""; width:9px; height:9px; border-radius:50%; background:currentColor; animation:mtPulse 1.4s ease-in-out infinite; }
.mt-pill.ok{ background:var(--mt-ok-bg); color:var(--mt-ok-fg); }
.mt-pill.bad{ background:var(--mt-bad-bg); color:var(--mt-bad-fg); }
.mt-pill.ok::before, .mt-pill.bad::before{ animation:none; }
@keyframes mtPulse{ 0%,100%{ opacity:1; transform:scale(1);} 50%{ opacity:.45; transform:scale(.7);} }

/* Itemized receipt inside the ticket */
.mt-mid{ padding:14px 22px 16px; font-size:13.5px; border-top:0; }
.mt-row{ display:flex; justify-content:space-between; align-items:flex-start; gap:12px; padding:5px 0; }
.mt-row > span:first-child{ min-width:0; word-break:break-word; font-weight:700; }
.mt-row small{ display:block; color:var(--mt-sub); font-size:11.5px; font-weight:500; margin-top:2px; }
.mt-row .mt-amt{ white-space:nowrap; font-weight:700; }
.mt-row.disc{ color:var(--mt-ok-fg); font-weight:700; }
.mt-row.sum{ align-items:center; border-top:2px dashed var(--mt-line); margin-top:8px; padding-top:10px; font-size:17px; font-weight:800; }
.mt-row.sum span:last-child{ color:var(--mt-accent); }

/* Ticket notches — color matches the page background */
.mt-perf{ position:relative; border-top:3px dotted var(--mt-line); margin:0 18px; }
.mt-perf::before,.mt-perf::after{ content:""; position:absolute; top:-14px; width:26px; height:26px; border-radius:50%; background:var(--mt-notch); border:1px solid var(--mt-line); }
.mt-perf::before{ left:-32px; clip-path:inset(0 0 0 50%); }
.mt-perf::after{ right:-32px; clip-path:inset(0 50% 0 0); }

.mt-bot{ padding:16px 22px 20px; display:flex; gap:20px; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; }
.mt-voucher{ min-width:220px; }
.mt-vlabel{ font-size:11px; font-weight:800; letter-spacing:.12em; color:var(--mt-sub); }
.mt-vwrap{ position:relative; display:inline-flex; align-items:center; gap:10px; flex-wrap:wrap; margin-top:6px; }
.mt-vcode{ font-family:'Courier New',monospace; font-size:22px; font-weight:800; letter-spacing:.18em; padding:8px 14px; border-radius:12px; background:var(--gold-pale); color:#6b3b00; user-select:all; }
[data-theme="dark"] .mt-vcode{ background:rgba(255,201,72,.12); color:#FFD98A; }
.mt-vcode.lock{ filter:blur(6px); user-select:none; pointer-events:none; }
.mt-lock{ position:absolute; inset:0; display:grid; place-items:center; font-size:22px; pointer-events:none; }
.mt-copy{ border:none; cursor:pointer; border-radius:999px; padding:8px 14px; font-weight:800; font-size:12px; background:var(--mt-ok-bg); color:var(--mt-ok-fg); font-family:inherit; transition:filter .15s; }
.mt-copy:hover{ filter:brightness(.95); }
.mt-vnote{ display:block; margin-top:8px; font-size:11.5px; color:var(--mt-sub); }

.mt-line{ display:flex; align-items:flex-start; justify-content:center; margin:16px 0 2px; }
.mt-line div{ flex:1; text-align:center; font-size:11.5px; font-weight:700; color:var(--mt-sub); position:relative; }
.mt-line div::before{ content:""; display:block; width:16px; height:16px; margin:0 auto 6px; border-radius:50%; background:var(--mt-line); position:relative; z-index:1; }
.mt-line div::after{ content:""; position:absolute; top:7px; left:-50%; width:100%; height:3px; background:var(--mt-line); }
.mt-line div:first-child::after{ display:none; }
.mt-line div.on{ color:var(--mt-text); }
.mt-line div.on::before,.mt-line div.on::after{ background:var(--teal); }
.mt-line div.cur::before{ background:var(--gold); animation:mtPulse 1.4s ease-in-out infinite; }

.mt-actions{ display:flex; flex-direction:column; gap:8px; min-width:230px; flex:0 1 320px; margin-left:auto; }
.mt-actions p{ margin:0; font-size:12px; line-height:1.5; color:var(--mt-sub); }
.mt-alert{ margin:0; padding:10px 12px; border-radius:12px; font-size:12px; line-height:1.5; background:var(--mt-bad-bg); color:var(--mt-bad-fg); }
.mt-pair{ display:flex; gap:8px; }
.mt-pair > *{ flex:1; min-width:0; }
.mt-pair form{ display:block; }
.mt-pair form .mt-btn{ width:100%; }
.mt-actions > .mt-btn{ width:100%; }

.mt-empty{ text-align:center; padding:34px 20px; border-radius:24px; border:2px dashed var(--mt-line); background:var(--mt-surface); color:var(--mt-sub); font-size:14px; }
.mt-empty b{ display:block; font-size:40px; margin-bottom:6px; }

/* ===== Pagination ===== */
.mt-pager{ margin-top:18px; display:flex; flex-wrap:wrap; justify-content:center; align-items:center; gap:6px; }
.mt-pg{ min-width:40px; height:40px; padding:0 12px; display:inline-flex; align-items:center; justify-content:center; border-radius:12px; border:2px solid var(--mt-line);
  background:var(--mt-surface); color:var(--mt-text); font-weight:800; font-size:13px; font-family:inherit; text-decoration:none; transition:all .15s var(--ease); }
a.mt-pg:hover{ border-color:var(--mt-accent); color:var(--mt-accent); transform:translateY(-1px); }
.mt-pg.active{ background:var(--mt-accent); border-color:transparent; color:var(--mt-on); }
.mt-pg.disabled{ opacity:.45; pointer-events:none; }
.mt-pg-gap{ color:var(--mt-sub); padding:0 2px; }
.mt-pager-info{ margin:8px 0 0; text-align:center; font-size:12px; color:var(--mt-sub); }

/* ===== Analytics ===== */
.an-kpis{ margin-top:16px; display:grid; grid-template-columns:repeat(12,minmax(0,1fr)); gap:12px; }
.an-kpi{ grid-column:span 4; display:flex; align-items:center; gap:14px; padding:16px 18px; border-radius:20px; background:var(--mt-surface); border:1px solid var(--mt-line); box-shadow:var(--mt-shadow); min-width:0; }
.an-kpi.half{ grid-column:span 6; }
.an-ico{ flex-shrink:0; width:46px; height:46px; border-radius:14px; display:grid; place-items:center; font-size:21px; }
.an-ico.pink{ background:rgba(226,75,110,.14); } .an-ico.teal{ background:rgba(13,148,136,.16); }
.an-ico.amber{ background:rgba(245,158,11,.18); } .an-ico.violet{ background:rgba(124,58,237,.15); } .an-ico.orange{ background:rgba(242,118,46,.17); }
.an-body{ min-width:0; }
.an-val{ font-size:24px; font-weight:800; line-height:1.1; letter-spacing:-.01em; color:var(--mt-text); }
.an-val.sm{ font-size:16px; line-height:1.25; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; word-break:break-word; }
.an-lbl{ margin-top:4px; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--mt-sub); }

.an-charts{ margin-top:12px; display:grid; grid-template-columns:repeat(auto-fit,minmax(min(100%,320px),1fr)); gap:12px; }
.an-card{ display:flex; flex-direction:column; padding:18px 20px 20px; border-radius:22px; background:var(--mt-surface); border:1px solid var(--mt-line); box-shadow:var(--mt-shadow); min-width:0; }
.an-card h4{ margin:0; font-size:15px; font-weight:800; color:var(--mt-text); }
.an-sub{ margin:2px 0 14px; font-size:12px; color:var(--mt-sub); }
.an-canvas{ position:relative; flex:1 1 0; min-height:260px; overflow:hidden; }
.an-donut{ position:relative; height:210px; }
.an-donut-center{ position:absolute; inset:0; display:flex; flex-direction:column; align-items:center; justify-content:center; pointer-events:none; }
.an-donut-center b{ font-size:30px; font-weight:800; line-height:1; color:var(--mt-text); }
.an-donut-center span{ margin-top:4px; font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--mt-sub); }
.an-legend{ margin-top:16px; display:flex; flex-direction:column; gap:12px; }
.an-leg{ font-size:13px; color:var(--mt-text); }
.an-leg-top{ display:flex; align-items:center; gap:8px; }
.an-dot{ width:10px; height:10px; border-radius:50%; flex-shrink:0; }
.an-leg-name{ flex:1; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.an-leg-val{ font-weight:800; }
.an-leg-pct{ min-width:40px; text-align:right; font-size:11.5px; color:var(--mt-sub); }
.an-bar{ margin-top:6px; height:5px; border-radius:99px; background:var(--mt-soft); overflow:hidden; }
.an-bar i{ display:block; height:100%; border-radius:99px; }

/* ===== Popups (shared overlay + card) ===== */
.rr-overlay{ display:none; position:fixed; inset:0; z-index:10000; align-items:center; justify-content:center; padding:20px; background:rgba(20,16,30,.55); backdrop-filter:blur(3px); }
.rr-overlay.active{ display:flex; }
.rr-card{ width:100%; max-width:420px; padding:24px 22px 20px; border-radius:20px; background:var(--mt-surface); color:var(--mt-text); border:1px solid var(--mt-line); box-shadow:0 24px 60px rgba(0,0,0,.35); }
.rr-icon{ width:48px; height:48px; margin:0 auto 12px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800; background:var(--mt-bad-bg); color:var(--mt-bad-fg); }
.rr-card h3{ margin:0 0 4px; text-align:center; font-size:18px; color:var(--mt-text); }
.rr-sub{ margin:0 0 16px; text-align:center; font-size:12.5px; color:var(--mt-sub); }
.rr-box{ margin-bottom:10px; padding:12px 14px; border-radius:12px; background:var(--mt-bad-bg); }
.rr-label{ margin-bottom:4px; font-size:10px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--mt-bad-fg); }
.rr-text{ font-size:14px; font-weight:600; line-height:1.45; color:var(--mt-text); word-break:break-word; }
.rr-note{ font-size:13px; font-weight:500; }
.rr-help{ margin:12px 0 16px; text-align:center; font-size:12px; line-height:1.5; color:var(--mt-sub); }
.rr-actions{ display:flex; gap:8px; }
.rr-actions > *{ flex:1; }

/* ===== Booking details popup ===== */
.dt-card{ max-width:460px; max-height:90vh; overflow:auto; }
.dt-head{ display:flex; justify-content:space-between; align-items:flex-start; gap:10px; margin-bottom:14px; }
.dt-head small{ font-size:11px; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:var(--mt-sub); }
.dt-head h3{ margin:4px 0 0; text-align:left; font-size:20px; }
.dt-rows{ border-top:1px solid var(--mt-line); }
.dt-row{ display:flex; justify-content:space-between; gap:14px; padding:10px 0; border-bottom:1px solid var(--mt-line); font-size:13.5px; }
.dt-row span:first-child{ color:var(--mt-sub); }
.dt-row span:last-child{ font-weight:700; text-align:right; word-break:break-word; }
.dt-row.total span:last-child{ color:var(--mt-accent); font-size:16px; font-weight:800; }
.dt-row.code span:last-child{ font-family:'Courier New',monospace; letter-spacing:.14em; }
.dt-actions{ display:flex; gap:8px; margin-top:16px; }
.dt-actions > *{ flex:1; }

/* ===== Mobile ===== */
@media (max-width:720px){
  .mt-wrap{ padding:10px; border-radius:22px; }
  .an-kpi, .an-kpi.half{ grid-column:span 6; }
  .an-kpi.fav{ grid-column:1 / -1; }
  .an-kpi{ padding:14px; gap:12px; }
  .an-val{ font-size:21px; }
  .mt-hero{ padding:22px 20px; }
  .mt-hero h2{ font-size:26px; }
  .mt-hero-actions{ width:100%; }
  .mt-hero-actions .mt-btn:not(.mt-theme){ flex:1 1 0; padding-left:10px; padding-right:10px; }
  .mt-deco.d1{ right:8%; }
  .mt-deco.d2{ right:4%; }
  .mt-deco.d3{ display:none; }
  .mt-tabs{ display:flex; width:100%; }
  .mt-tab{ flex:1; padding:9px 10px; }
  .mt-top{ padding:16px 18px; }
  .mt-mid{ padding:12px 18px 14px; }
  .mt-bot{ padding:14px 18px 18px; flex-direction:column; align-items:stretch; gap:16px; }
  .mt-voucher{ min-width:0; }
  .mt-actions{ min-width:0; width:100%; flex:1 1 auto; margin-left:0; }
  .mt-vcode{ font-size:18px; letter-spacing:.12em; }
  .mt-search .mt-btn{ flex:1; }
}

@media (prefers-reduced-motion:reduce){
  .mt-deco{ animation:none; }
}
</style>
@endpush

@section('content')
<div class="mt-wrap">

    <div class="mt-hero">
        <span class="mt-hero-glow" aria-hidden="true"></span>
        <span class="mt-deco d1" aria-hidden="true">🎟️</span>
        <span class="mt-deco d2" aria-hidden="true">🎡</span>
        <span class="mt-deco d3" aria-hidden="true">🦖</span>

        <div>
            <small>🎫 MANAGE YOUR VISITS</small>
            <h2>My Bookings</h2>
            <p>Upcoming &amp; past visits</p>
        </div>

        <div class="mt-hero-actions">
            <button type="button" class="mt-btn mt-theme" id="mtThemeBtn" aria-label="Toggle dark mode" title="Toggle dark mode">
                <span class="mt-theme-ico" id="mtThemeIcon" aria-hidden="true">🌙</span>
            </button>
            <a href="{{ route('user.booking') }}" class="mt-btn go">+ New Booking</a>
        </div>
    </div>

    @if (isset($stats) && $stats['total_visits'] > 0)
        <div class="mt-tabs" role="tablist" aria-label="Bookings sections">
            <button type="button" class="mt-tab active" role="tab" aria-selected="true"  data-target="bookingsPanel">My Bookings</button>
            <button type="button" class="mt-tab"        role="tab" aria-selected="false" data-target="analyticsPanel">Analytics</button>
        </div>

        {{-- ================= ANALYTICS ================= --}}
        <div id="analyticsPanel" class="mt-panel" hidden>
            <div class="an-kpis">
                <div class="an-kpi">
                    <div class="an-body">
                        <div class="an-val">{{ $stats['total_visits'] }}</div>
                        <div class="an-lbl">Total Visits</div>
                    </div>
                </div>
                <div class="an-kpi">
                    <div class="an-body">
                        <div class="an-val">{{ $stats['upcoming_reservations'] }}</div>
                        <div class="an-lbl">Upcoming</div>
                    </div>
                </div>
                <div class="an-kpi">
                    <div class="an-body">
                        <div class="an-val">{{ $stats['completed_reservations'] }}</div>
                        <div class="an-lbl">Completed</div>
                    </div>
                </div>
                <div class="an-kpi half">
                    <div class="an-body">
                        <div class="an-val">₱{{ number_format($stats['total_spending'], 0) }}</div>
                        <div class="an-lbl">Total Spending</div>
                    </div>
                </div>
                <div class="an-kpi half fav">
                    <div class="an-body">
                        <div class="an-val sm">{{ $stats['favorite_package'] }}</div>
                        <div class="an-lbl">Most Booked ({{ $stats['favorite_package_count'] }}x)</div>
                    </div>
                </div>
            </div>

            <div class="an-charts">
                <div class="an-card">
                    <h4>Visit History</h4>
                    <p class="an-sub">Bookings over the last 6 months</p>
                    <div class="an-canvas"><canvas id="historyChart"></canvas></div>
                </div>

                <div class="an-card">
                    <h4>Bookings by Attraction</h4>
                    <p class="an-sub">Where you spend most of your visits</p>
                    <div class="an-donut">
                        <canvas id="categoryChart"></canvas>
                        <div class="an-donut-center"><b id="categoryTotal">0</b><span>Bookings</span></div>
                    </div>
                    <div class="an-legend" id="categoryLegend"></div>
                </div>
            </div>
        </div>
    @endif

    {{-- ================= BOOKINGS ================= --}}
    <div id="bookingsPanel" class="mt-panel">

        <form method="GET" action="{{ route('user.bookings') }}" role="search" class="mt-search">
            <input type="search" name="q" value="{{ $q ?? '' }}"
                   placeholder="&#128269; Search bookings — package, date, status, WP-000027, voucher code…"
                   aria-label="Search bookings" autocomplete="off">
            <button type="submit" class="mt-btn go">Search</button>
            @if (!empty($q))
                <a href="{{ route('user.bookings') }}" class="mt-btn ghost">Clear</a>
            @endif
        </form>
        @if (!empty($q))
            <p class="mt-meta">
                {{ $bookings->total() }} result{{ $bookings->total() === 1 ? '' : 's' }} for &ldquo;{{ $q }}&rdquo;
            </p>
        @endif

        <div class="mt-list">
            @forelse ($bookings as $booking)
                @php
                    $status        = $booking['status'] ?? '';
                    $approved      = in_array($status, ['confirmed', 'done'], true);
                    $inReview      = $status === 'awaiting_verification';
                    $pending       = $status === 'pending_payment';
                    $payRejected   = $pending && !empty($booking['rejection_reason']);
                    $adminRejected = $status === 'rejected' || ($booking['approval_status'] ?? '') === 'rejected';
                    $cancelled     = $status === 'cancelled';
                    $noVoucher     = $adminRejected || $cancelled;
                    $pillCls       = $approved ? 'ok' : (($payRejected || $adminRejected || $cancelled) ? 'bad' : '');
                    $emoji         = ['dino_adventure' => '🦖', 'rollerfever' => '🛼', 'field_of_rides' => '🎡'][$booking['service'] ?? ''] ?? '🎟️';
                    $guests        = $booking['pax'] . ' ' . ((int) $booking['pax'] === 1 ? 'guest' : 'guests');
                    $hasCode       = $approved && !empty($booking['voucher_code']);
                    $timeLabel     = $booking['time_label'] ?? null;
                    $lines         = $booking['lines'] ?? [];
                    $extras        = $booking['extras'] ?? [];
                    $discount      = (float) ($booking['discount'] ?? 0);

                    // Data for the "full details" popup.
                    // The voucher code is only included once the booking is approved.
                    $detail = [
                        'reference' => $booking['reference'],
                        'category'  => $booking['category'],
                        'package'   => collect($lines)->pluck('name')->filter()->implode(', ') ?: $booking['package'],
                        'date'      => $booking['date'],
                        'time'      => $timeLabel,
                        'guests'    => $guests,
                        'price'     => $booking['price'],
                        'status'    => $booking['status_label'],
                        'voucher'   => $hasCode ? $booking['voucher_code'] : null,
                        'reason'    => ($payRejected || $adminRejected)
                                        ? ($booking['reject_reason'] ?? $booking['rejection_reason'] ?? null)
                                        : null,
                        'receipt'   => $approved ? route('user.bookings.receipt', $booking['id']) : null,
                    ];
                @endphp
                <article class="mt-ticket" data-detail="{{ json_encode($detail) }}">
                    <div class="mt-top js-open-detail" role="button" tabindex="0" aria-label="View booking details">
                        <div>
                            <small>{{ $emoji }} {{ $booking['category'] }}</small>
                            <h3>{{ $booking['reference'] }}</h3>
                            <div class="mt-when">📅 {{ $booking['date'] }}{{ $timeLabel ? ' · ' . $timeLabel : '' }} · {{ $guests }}</div>
                            <span class="mt-hint">Tap to view full details →</span>
                        </div>
                        <span class="mt-pill {{ $pillCls }}">{{ $booking['status_label'] }}</span>
                    </div>

                    <div class="mt-mid js-open-detail">
                        @foreach ($lines as $line)
                            <div class="mt-row">
                                <span>
                                    {{ $line['name'] }}
                                    <small>
                                        {{ $line['qty'] }} × ₱{{ number_format($line['unit_price'], 0) }}
                                        @if (!empty($line['free'])) · 🎁 {{ $line['free'] }} free @endif
                                    </small>
                                </span>
                                <span class="mt-amt">₱{{ number_format($line['total'], 2) }}</span>
                            </div>
                        @endforeach

                        @foreach ($extras as $extra)
                            <div class="mt-row">
                                <span>{{ $extra['name'] }}<small>{{ $extra['qty'] }} × ₱{{ number_format($extra['unit_price'], 0) }}</small></span>
                                <span class="mt-amt">₱{{ number_format($extra['total'], 2) }}</span>
                            </div>
                        @endforeach

                        @if ($discount > 0)
                            <div class="mt-row disc">
                                <span>🎉 Deals applied</span>
                                <span class="mt-amt">−₱{{ number_format($discount, 2) }}</span>
                            </div>
                        @endif

                        <div class="mt-row sum"><span>Total</span><span>{{ $booking['price'] }}</span></div>

                        @if ($inReview || $approved)
                            <div class="mt-line">
                                <div class="on">Submitted</div>
                                <div class="{{ $approved ? 'on' : 'on cur' }}">In review</div>
                                <div class="{{ $approved ? 'on' : '' }}">Approved</div>
                            </div>
                        @endif
                    </div>

                    <div class="mt-perf"></div>

                    <div class="mt-bot">
                        <div class="mt-voucher">
                            <div class="mt-vlabel">YOUR VOUCHER CODE</div>
                            @if ($hasCode)
                                <div class="mt-vwrap">
                                    <span class="mt-vcode">{{ $booking['voucher_code'] }}</span>
                                    <button type="button" class="mt-copy" data-code="{{ $booking['voucher_code'] }}">Copy</button>
                                </div>
                            @else
                                {{-- decorative placeholder only — the real code is never sent until approved --}}
                                <div class="mt-vwrap">
                                    <span class="mt-vcode lock" aria-hidden="true">{{ strtoupper(\Illuminate\Support\Str::random(8)) }}</span>
                                    <span class="mt-lock">🔒</span>
                                </div>
                                <span class="mt-vnote">
                                    {{ $noVoucher ? 'No voucher is available for this booking' : 'Unlocks once your payment is approved' }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-actions">
                            @if ($approved)
                                <p>
                                    @if ($status === 'confirmed')
                                        Approved &middot; rescheduling is no longer available
                                    @else
                                        Voucher already used
                                    @endif
                                </p>
                                <a href="{{ route('user.bookings.receipt', $booking['id']) }}" class="mt-btn ghost">View receipt</a>

                            @elseif ($inReview)
                                <p>We're checking your payment. You can still reschedule until it's approved.</p>
                                <div class="mt-pair">
                                    <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}" class="mt-btn ghost">Reschedule</a>
                                    <form method="POST" action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                          onsubmit="return confirm('Cancel this booking?');">
                                        @csrf
                                        <button type="submit" class="mt-btn danger">Cancel</button>
                                    </form>
                                </div>

                            @elseif ($pending)
                                @if ($payRejected)
                                    <p class="mt-alert">Your payment proof was not accepted: {{ $booking['rejection_reason'] }}. Please upload a new proof.</p>
                                @endif
                                <a href="{{ route('user.bookings.review', $booking['id']) }}" class="mt-btn go">
                                    {{ $payRejected ? 'Upload New Proof' : 'Review Booking' }}
                                </a>
                                <div class="mt-pair">
                                    <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}" class="mt-btn ghost">Reschedule</a>
                                    <form method="POST" action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                          onsubmit="return confirm('Cancel this booking?');">
                                        @csrf
                                        <button type="submit" class="mt-btn danger">Cancel</button>
                                    </form>
                                </div>

                            @elseif ($adminRejected)
                                <p class="mt-alert">This booking was rejected. Tap "View reason" to see why.</p>
                                <div class="mt-pair">
                                    <button type="button" class="mt-btn ghost js-show-reject"
                                            data-package="{{ $booking['package'] }}"
                                            data-date="{{ $booking['date'] }}"
                                            data-reason="{{ $booking['reject_reason'] ?? $booking['rejection_reason'] ?? '' }}"
                                            data-note="{{ $booking['reject_note'] ?? '' }}">View reason</button>
                                    <a href="{{ route('user.booking') }}" class="mt-btn go">Book again</a>
                                </div>

                            @elseif ($cancelled)
                                <p>This booking was cancelled.</p>
                                <a href="{{ route('user.booking') }}" class="mt-btn ghost">Book again</a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="mt-empty">
                    @if (!empty($q))
                        <b>&#128269;</b>
                        No bookings match &ldquo;{{ $q }}&rdquo;. Try a package name, date or status.
                    @else
                        <b>&#127905;</b>
                        You don't have any bookings yet.
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if ($bookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $bookings->hasPages())
            @php
                $start = max(1, $bookings->currentPage() - 2);
                $end   = min($bookings->lastPage(), $bookings->currentPage() + 2);
            @endphp
            <nav class="mt-pager" aria-label="Bookings pages">
                @if ($bookings->onFirstPage())
                    <span class="mt-pg disabled" aria-disabled="true">&larr;</span>
                @else
                    <a class="mt-pg" href="{{ $bookings->previousPageUrl() }}" aria-label="Previous page">&larr;</a>
                @endif

                @if ($start > 1)
                    <a class="mt-pg" href="{{ $bookings->url(1) }}">1</a>
                    @if ($start > 2)<span class="mt-pg-gap">&hellip;</span>@endif
                @endif

                @for ($page = $start; $page <= $end; $page++)
                    <a class="mt-pg {{ $page === $bookings->currentPage() ? 'active' : '' }}"
                       href="{{ $bookings->url($page) }}"
                       @if ($page === $bookings->currentPage()) aria-current="page" @endif>{{ $page }}</a>
                @endfor

                @if ($end < $bookings->lastPage())
                    @if ($end < $bookings->lastPage() - 1)<span class="mt-pg-gap">&hellip;</span>@endif
                    <a class="mt-pg" href="{{ $bookings->url($bookings->lastPage()) }}">{{ $bookings->lastPage() }}</a>
                @endif

                @if ($bookings->hasMorePages())
                    <a class="mt-pg" href="{{ $bookings->nextPageUrl() }}" aria-label="Next page">&rarr;</a>
                @else
                    <span class="mt-pg disabled" aria-disabled="true">&rarr;</span>
                @endif
            </nav>
            <p class="mt-pager-info">Showing {{ $bookings->firstItem() }}–{{ $bookings->lastItem() }} of {{ $bookings->total() }}</p>
        @endif
    </div>

    {{-- ================= BOOKING DETAILS POPUP ================= --}}
    <div id="detailOverlay" class="rr-overlay" aria-hidden="true">
        <div class="rr-card dt-card" role="dialog" aria-modal="true" aria-labelledby="dtTitle">
            <div class="dt-head">
                <div>
                    <small id="dtCategory"></small>
                    <h3 id="dtTitle"></h3>
                </div>
                <span class="mt-pill" id="dtStatus"></span>
            </div>
            <div class="dt-rows" id="dtRows"></div>
            <div class="dt-actions">
                <button type="button" class="mt-btn ghost" id="dtClose">Close</button>
                <a href="#" class="mt-btn go" id="dtReceipt" style="display:none;">View receipt</a>
            </div>
        </div>
    </div>

    {{-- ================= REJECT REASON POPUP ================= --}}
    <div id="rejectReasonOverlay" class="rr-overlay" aria-hidden="true">
        <div class="rr-card" role="dialog" aria-modal="true" aria-labelledby="rrTitle">
            <div class="rr-icon">!</div>
            <h3 id="rrTitle">Booking Rejected</h3>
            <p class="rr-sub"><span id="rrPackage"></span> &middot; <span id="rrDate"></span></p>

            <div class="rr-box">
                <div class="rr-label">Reason</div>
                <div class="rr-text" id="rrReason"></div>
            </div>

            <div class="rr-box" id="rrNoteBox" style="display:none;">
                <div class="rr-label">Note from admin</div>
                <div class="rr-text rr-note" id="rrNote"></div>
            </div>

            <p class="rr-help">You may submit a new booking. Please make sure your payment and booking details are accurate.</p>

            <div class="rr-actions">
                <button type="button" class="mt-btn ghost" id="rrClose">Close</button>
                <a href="{{ route('user.booking') }}" class="mt-btn go">Book Again</a>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    /* Dark mode toggle — icon only (lives in the hero, next to "+ New Booking") */
    (function () {
        var btn  = document.getElementById('mtThemeBtn');
        if (!btn) return;
        var icon = document.getElementById('mtThemeIcon');

        /* Selectors of the layout's own toggle (hidden by CSS on this page).
           We click it so the layout's normal theme logic / saving still runs.
           If yours uses a different id/class, add it to this list. */
        var NATIVE_TOGGLES = '#themeToggle, #theme-toggle, #darkModeToggle, #dark-mode-toggle, #darkToggle, ' +
                             '.theme-toggle, .dark-toggle, .dark-mode-toggle, .darkmode-toggle, ' +
                             '[data-theme-toggle], [data-toggle-theme], [data-action="toggle-theme"]';

        function isDark() {
            return document.documentElement.getAttribute('data-theme') === 'dark' ||
                   document.body.getAttribute('data-theme') === 'dark';
        }

        function sync() {
            var dark = isDark();
            icon.textContent = dark ? '\u2600\uFE0F' : '\uD83C\uDF19';
            var label = dark ? 'Switch to light mode' : 'Switch to dark mode';
            btn.setAttribute('aria-label', label);
            btn.setAttribute('title', label);
            btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
        }

        function fallbackToggle() {
            var next = isDark() ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            if (document.body.hasAttribute('data-theme')) document.body.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        }

        btn.addEventListener('click', function () {
            var before = isDark();
            var native = document.querySelector(NATIVE_TOGGLES);
            if (native && native !== btn) { native.click(); }
            else { fallbackToggle(); }

            // If the layout's toggle didn't change anything, do it ourselves.
            setTimeout(function () {
                if (isDark() === before) fallbackToggle();
                sync();
            }, 60);
        });

        var obs = new MutationObserver(sync);
        [document.documentElement, document.body].forEach(function (n) {
            obs.observe(n, { attributes: true, attributeFilter: ['data-theme', 'class'] });
        });
        sync();
    })();

    /* Reject reason popup */
    (function () {
        var overlay = document.getElementById('rejectReasonOverlay');
        if (!overlay) return;
        var closeBtn = document.getElementById('rrClose');

        function openReject(btn) {
            var reason = (btn.dataset.reason || '').trim();
            var note   = (btn.dataset.note || '').trim();

            document.getElementById('rrPackage').textContent = btn.dataset.package || '';
            document.getElementById('rrDate').textContent    = btn.dataset.date || '';
            document.getElementById('rrReason').textContent  = reason || 'No reason was provided by the admin.';
            document.getElementById('rrNote').textContent    = note;
            document.getElementById('rrNoteBox').style.display = note ? '' : 'none';

            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeReject() {
            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (e) {
            var btn = e.target.closest('.js-show-reject');
            if (btn) openReject(btn);
        });
        closeBtn.addEventListener('click', closeReject);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeReject(); });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('active')) closeReject();
        });
    })();

    /* Copy voucher code */
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.mt-copy');
        if (!b) return;
        var code = b.getAttribute('data-code');

        function done() {
            b.textContent = 'Copied \u2713';
            setTimeout(function () { b.textContent = 'Copy'; }, 1600);
        }
        function fallback() {
            var t = document.createElement('textarea');
            t.value = code; t.style.position = 'fixed'; t.style.opacity = '0';
            document.body.appendChild(t); t.select();
            try { document.execCommand('copy'); } catch (err) {}
            document.body.removeChild(t);
            done();
        }

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(code).then(done, fallback);
        } else {
            fallback();
        }
    });

    /* Booking details popup (click the top or middle part of a ticket) */
    (function () {
        var ov = document.getElementById('detailOverlay');
        if (!ov) return;
        var rows = document.getElementById('dtRows');
        var closeBtn = document.getElementById('dtClose');

        function addRow(label, value, cls) {
            if (!value) return;
            var r = document.createElement('div');
            r.className = 'dt-row' + (cls ? ' ' + cls : '');
            var a = document.createElement('span'); a.textContent = label;
            var b = document.createElement('span'); b.textContent = value;
            r.appendChild(a); r.appendChild(b);
            rows.appendChild(r);
        }

        function openDetail(card) {
            if (!card) return;
            var d;
            try { d = JSON.parse(card.dataset.detail); } catch (e) { return; }

            document.getElementById('dtCategory').textContent = d.category || '';
            document.getElementById('dtTitle').textContent = d.reference || '';

            var pill = document.getElementById('dtStatus');
            var src = card.querySelector('.mt-top .mt-pill');
            pill.textContent = d.status || '';
            pill.className = src ? src.className : 'mt-pill';

            rows.textContent = '';
            addRow('Package', d.package);
            addRow('Date', d.date);
            addRow('Time', d.time);
            addRow('Guests', d.guests);
            addRow('Voucher code', d.voucher, 'code');
            addRow('Reason', d.reason);
            addRow('Total', d.price, 'total');

            var rc = document.getElementById('dtReceipt');
            if (d.receipt) { rc.href = d.receipt; rc.style.display = ''; }
            else { rc.style.display = 'none'; }

            ov.classList.add('active');
            ov.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            closeBtn.focus();
        }

        function closeDetail() {
            ov.classList.remove('active');
            ov.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('a, button, form, input')) return;   // don't trigger from real buttons/links
            var hit = e.target.closest('.js-open-detail');
            if (hit) openDetail(hit.closest('.mt-ticket'));
        });
        document.addEventListener('keydown', function (e) {
            if ((e.key === 'Enter' || e.key === ' ') && e.target.classList && e.target.classList.contains('js-open-detail')) {
                e.preventDefault();
                openDetail(e.target.closest('.mt-ticket'));
            }
            if (e.key === 'Escape' && ov.classList.contains('active')) closeDetail();
        });
        closeBtn.addEventListener('click', closeDetail);
        ov.addEventListener('click', function (e) { if (e.target === ov) closeDetail(); });
    })();
</script>
@endpush

@if (isset($stats) && $stats['total_visits'] > 0)
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    (function () {
        var wrap  = document.querySelector('.mt-wrap');
        var tabs  = document.querySelectorAll('.mt-tab');
        var panel = document.getElementById('analyticsPanel');
        var charts = [];
        var built = false;
        var lastDark = null;

        var DATA = {
            historyLabels: @json($stats['history_labels']),
            historyValues: @json($stats['history_values']),
            catLabels:     @json($stats['category_labels']),
            catValues:     @json($stats['category_values'])
        };

        function isDark() { return !!document.querySelector('[data-theme="dark"]'); }

        function cssVar(name, fallback) {
            var v = getComputedStyle(wrap).getPropertyValue(name).trim();
            return v || fallback;
        }

        function palette(dark) {
            return [
                dark ? '#FF7A9E' : cssVar('--pink-deep', '#B82850'),
                '#F2762E',
                dark ? '#2DD4BF' : '#0D9488',
                '#F5B301',
                '#8B5CF6',
                '#3B82F6'
            ];
        }

        function el(tag, cls, text) {
            var n = document.createElement(tag);
            if (cls) n.className = cls;
            if (text !== undefined) n.textContent = text;
            return n;
        }

        function destroy() {
            charts.forEach(function (c) { c.destroy(); });
            charts = [];
        }

        function renderLegend(colors) {
            var box = document.getElementById('categoryLegend');
            box.textContent = '';
            var total = DATA.catValues.reduce(function (a, b) { return a + (Number(b) || 0); }, 0);
            document.getElementById('categoryTotal').textContent = total;

            DATA.catLabels.forEach(function (label, i) {
                var val   = Number(DATA.catValues[i]) || 0;
                var pct   = total ? Math.round((val / total) * 100) : 0;
                var color = colors[i % colors.length];

                var row = el('div', 'an-leg');
                var top = el('div', 'an-leg-top');
                var dot = el('span', 'an-dot'); dot.style.background = color;
                top.appendChild(dot);
                top.appendChild(el('span', 'an-leg-name', label));
                top.appendChild(el('b', 'an-leg-val', String(val)));
                top.appendChild(el('span', 'an-leg-pct', pct + '%'));

                var bar  = el('div', 'an-bar');
                var fill = el('i'); fill.style.width = pct + '%'; fill.style.background = color;
                bar.appendChild(fill);

                row.appendChild(top);
                row.appendChild(bar);
                box.appendChild(row);
            });
        }

        function build() {
            if (typeof Chart === 'undefined') return;
            destroy();

            var dark    = isDark();
            lastDark    = dark;
            var colors  = palette(dark);
            var tick    = dark ? '#B9B2C6' : '#6B6478';
            var grid    = dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)';
            var surface = cssVar('--mt-surface', dark ? '#1b1626' : '#ffffff');

            Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
            Chart.defaults.color = tick;

            /* Visit history (bar) */
            var hCanvas = document.getElementById('historyChart');
            var hCtx    = hCanvas.getContext('2d');
            var height  = Math.max(hCanvas.parentNode.clientHeight, 260);
            var grad    = hCtx.createLinearGradient(0, 0, 0, height);
            grad.addColorStop(0, colors[0]);
            grad.addColorStop(1, colors[1]);

            charts.push(new Chart(hCanvas, {
                type: 'bar',
                data: {
                    labels: DATA.historyLabels,
                    datasets: [{
                        label: 'Bookings',
                        data: DATA.historyValues,
                        backgroundColor: grad,
                        borderRadius: 8,
                        borderSkipped: false,
                        maxBarThickness: 38
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false }, ticks: { color: tick } },
                        y: { beginAtZero: true, ticks: { precision: 0, color: tick }, grid: { color: grid }, border: { display: false } }
                    }
                }
            }));

            /* Bookings by attraction (doughnut) */
            charts.push(new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: DATA.catLabels,
                    datasets: [{
                        data: DATA.catValues,
                        backgroundColor: DATA.catLabels.map(function (_, i) { return colors[i % colors.length]; }),
                        borderColor: surface,
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '68%',
                    plugins: { legend: { display: false } }
                }
            }));

            renderLegend(colors);
        }

        /* Tab switching (charts are built the first time Analytics is opened,
           so they never render inside a hidden, zero-size container). */
        tabs.forEach(function (btn) {
            btn.addEventListener('click', function () {
                tabs.forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                    b.setAttribute('aria-selected', b === btn ? 'true' : 'false');
                });
                document.querySelectorAll('.mt-panel').forEach(function (p) {
                    p.hidden = (p.id !== btn.dataset.target);
                });
                if (btn.dataset.target === 'analyticsPanel') {
                    if (!built) { build(); built = true; }
                    else { charts.forEach(function (c) { c.resize(); }); }
                }
            });
        });

        /* Re-colour the charts when the user toggles dark mode */
        var observer = new MutationObserver(function () {
            if (!built || isDark() === lastDark) return;
            if (panel.hidden) { destroy(); built = false; }
            else { build(); }
        });
        [document.documentElement, document.body].forEach(function (node) {
            observer.observe(node, { attributes: true, attributeFilter: ['data-theme', 'class'] });
        });
    })();
    </script>
    @endpush
@endif