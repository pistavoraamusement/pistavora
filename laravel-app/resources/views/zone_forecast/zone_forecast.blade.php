@extends('layouts.sidebar')

@php
    $zoneIcons = [
        'Skataya'      => 'fa-bolt',
        'Snackbar'     => 'fa-burger',
        'Piyestarides' => 'fa-flag-checkered',
        'Dinolakbay'   => 'fa-dragon',
    ];
    $zoneIcon    = $zoneIcons[$zone] ?? 'fa-chart-line';
    $hasVouchers = $hasVouchers ?? true;
@endphp

@section('title', $zone . ' Forecast')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>
  /* Tokens — fall back to the same values the Booking & Pricing page uses */
  :root{
    --fc-bg:#F8FAFC; --fc-card:#fff; --fc-line:#E5E7EF;
    --fc-ink:#1B1D28; --fc-ink-soft:#33344A; --fc-muted:#5B5D72;
    --fc-primary:#4F46E5; --fc-primary-dark:#4338CA; --fc-primary-light:#EEF0FF;
    --fc-accent:#FF7A59; --fc-accent-light:#FFEDE6;
    --fc-teal:#0F766E; --fc-teal-bg:#DDF7F2;
    --fc-amber:#92400E; --fc-amber-bg:#FEF3C7;
    --fc-red:#BE123C; --fc-red-bg:#FFE4EA;
    --fc-radius:14px; --fc-radius-sm:9px;
    --fc-shadow:0 1px 2px rgba(15,23,42,.04);
  }
  .jak, .fc-title{ font-family:'Plus Jakarta Sans','Inter',sans-serif; }

  /* ---------- header ---------- */
  .fc-head{ display:flex; align-items:flex-end; justify-content:space-between; gap:16px; flex-wrap:wrap; padding:4px 0 18px; }
  .fc-head h1{ font-family:'Plus Jakarta Sans',sans-serif; font-size:1.5rem; font-weight:800; color:var(--fc-ink); margin:0 0 4px; letter-spacing:-.02em; }
  .fc-head h1{ display:flex; align-items:center; gap:12px; }
  .fc-head h1 i{ width:38px; height:38px; border-radius:11px; background:var(--fc-primary-light); color:var(--fc-primary-dark); display:inline-flex; align-items:center; justify-content:center; font-size:.95rem; }
  .fc-head p{ margin:0; font-size:.82rem; color:var(--fc-muted); }
  .fc-live{ display:inline-flex; align-items:center; gap:7px; margin-top:8px; font-size:.72rem; font-weight:600; color:var(--fc-muted); }
  .fc-live::before{ content:''; width:7px; height:7px; border-radius:50%; background:#14B8A6; box-shadow:0 0 0 3px var(--fc-teal-bg); }

  .fc-btn{ display:inline-flex; align-items:center; gap:7px; font-family:inherit; font-size:.8rem; font-weight:700; padding:9px 16px; border-radius:10px; cursor:pointer; border:1px solid transparent; transition:background .15s, border-color .15s, color .15s; }
  .fc-btn-primary{ background:var(--fc-primary); color:#fff; }
  .fc-btn-primary:hover{ background:var(--fc-primary-dark); }
  .fc-btn-ghost{ background:var(--fc-card); color:var(--fc-ink-soft); border-color:#CBD0DE; }
  .fc-btn-ghost:hover{ border-color:var(--fc-primary); color:var(--fc-primary-dark); }

  /* ---------- filters ---------- */
  .filter-bar{ display:flex; align-items:flex-end; gap:12px; flex-wrap:wrap; background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius); padding:12px 16px; margin-bottom:16px; box-shadow:var(--fc-shadow); }
  .filter-field{ display:flex; flex-direction:column; gap:4px; }
  .filter-field label{ font-size:.66rem; font-weight:700; color:var(--fc-muted); text-transform:uppercase; letter-spacing:.05em; }
  .filter-field input, .filter-field select{ border:1px solid #CBD0DE; border-radius:var(--fc-radius-sm); padding:7px 10px; font-size:.8rem; font-family:inherit; color:var(--fc-ink); background:#fff; min-width:140px; }
  .filter-field input:focus, .filter-field select:focus{ outline:none; border-color:var(--fc-primary); box-shadow:0 0 0 3px rgba(79,70,229,.14); }
  .filter-spacer{ flex:1 1 auto; }

  /* ---------- KPI ---------- */
  .kpi-strip{ display:grid; grid-template-columns:repeat(4,1fr); gap:14px; margin-bottom:16px; }
  @media(max-width:980px){ .kpi-strip{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:520px){ .kpi-strip{ grid-template-columns:1fr; } }
  .kpi-card{ background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius); padding:16px; display:flex; align-items:center; gap:14px; box-shadow:var(--fc-shadow); }
  .kpi-icon{ width:42px; height:42px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1rem; flex-shrink:0; background:var(--fc-primary-light); color:var(--fc-primary-dark); }
  .kpi-card.is-revenue .kpi-icon{ background:var(--fc-accent-light); color:#B93815; }
  .kpi-card.is-items .kpi-icon{ background:var(--fc-teal-bg); color:var(--fc-teal); }
  .kpi-card.is-alert .kpi-icon{ background:var(--fc-red-bg); color:var(--fc-red); }
  .kpi-body{ min-width:0; }
  .kpi-label{ font-size:.68rem; color:var(--fc-muted); font-weight:700; text-transform:uppercase; letter-spacing:.06em; }
  .kpi-value{ font-family:'Plus Jakarta Sans',sans-serif; font-size:1.4rem; font-weight:800; color:var(--fc-ink); line-height:1.2; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .kpi-sub{ font-size:.7rem; color:var(--fc-muted); }

  /* ---------- tabs ---------- */
  .fc-tabs{ display:flex; gap:24px; border-bottom:1px solid var(--fc-line); overflow-x:auto; scrollbar-width:none; padding:0 20px; }
  .fc-tabs::-webkit-scrollbar{ display:none; }
  .tab-btn{ all:unset; box-sizing:border-box; cursor:pointer; display:inline-flex; align-items:center; gap:8px; padding:14px 2px 12px; border-bottom:2px solid transparent; margin-bottom:-1px; font-size:.82rem; font-weight:600; color:var(--fc-muted); font-family:'Inter',sans-serif; white-space:nowrap; }
  .tab-btn i{ font-size:.82rem; }
  .tab-btn:hover{ color:var(--fc-ink); }
  .tab-btn:focus-visible{ outline:2px solid var(--fc-primary); outline-offset:2px; border-radius:4px; }
  .tab-btn.active{ color:var(--fc-primary-dark); border-bottom-color:var(--fc-primary); }

  .content-panel{ background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius); box-shadow:var(--fc-shadow); overflow:hidden; }
  .tab-pane{ display:none; padding:24px; }
  .tab-pane.active{ display:block; animation:fcFade .2s ease; }
  @keyframes fcFade{ from{opacity:0;transform:translateY(3px)} to{opacity:1;transform:none} }

  .pane-title{ margin:0 0 12px; font-family:'Plus Jakarta Sans',sans-serif; font-size:.95rem; font-weight:700; color:var(--fc-ink); }
  .fc-grid-2{ display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-bottom:8px; }
  .fc-grid-peak{ grid-template-columns:1.6fr 1fr; }
  @media(max-width:900px){ .fc-grid-2, .fc-grid-peak{ grid-template-columns:1fr; } }
  .chart-wrap{ position:relative; height:250px; margin-bottom:16px; }

  .info-note{ background:var(--fc-primary-light); border-radius:var(--fc-radius-sm); padding:11px 14px; margin-bottom:18px; font-size:.78rem; color:var(--fc-ink-soft); line-height:1.5; }
  .info-note strong{ color:var(--fc-primary-dark); }

  /* ---------- tables ---------- */
  .table-scroll{ width:100%; overflow-x:auto; }
  .ml-table{ width:100%; border-collapse:collapse; font-size:.8rem; }
  .ml-table th{ background:var(--fc-bg); color:var(--fc-muted); padding:10px 12px; text-align:left; font-weight:700; font-size:.66rem; text-transform:uppercase; letter-spacing:.06em; border-bottom:1px solid var(--fc-line); white-space:nowrap; }
  .ml-table td{ padding:11px 12px; border-bottom:1px solid var(--fc-line); color:var(--fc-ink-soft); }
  .ml-table tr:last-child td{ border-bottom:none; }
  .ml-table tbody tr:hover td{ background:var(--fc-bg); }

  /* ---------- percentage bars ---------- */
  .pct-row{ display:flex; align-items:center; gap:12px; padding:9px 0; border-bottom:1px solid var(--fc-line); flex-wrap:wrap; }
  .pct-row:last-child{ border-bottom:none; }
  .pct-label{ width:150px; flex-shrink:0; font-size:.8rem; font-weight:700; color:var(--fc-ink); }
  .pct-track{ flex:1; min-width:100px; height:18px; background:var(--fc-primary-light); border-radius:20px; overflow:hidden; }
  .pct-fill{ height:100%; border-radius:20px; background:linear-gradient(90deg,#4F46E5,#7C3AED); display:flex; align-items:center; justify-content:flex-end; padding-right:9px; transition:width .5s ease; min-width:4px; }
  .pct-fill span{ color:#fff; font-size:.66rem; font-weight:700; white-space:nowrap; }
  .pct-value{ width:52px; flex-shrink:0; text-align:right; font-size:.8rem; font-weight:800; color:var(--fc-primary-dark); }
  .best-tag, .worst-tag{ padding:2px 9px; border-radius:20px; font-size:.64rem; font-weight:700; margin-left:6px; }
  .best-tag{ background:var(--fc-teal-bg); color:var(--fc-teal); }
  .worst-tag{ background:var(--fc-red-bg); color:var(--fc-red); }

  /* ---------- badges ---------- */
  .trend-up, .trend-down, .trend-stable{ padding:3px 10px; border-radius:20px; font-size:.7rem; font-weight:700; white-space:nowrap; }
  .trend-up{ background:var(--fc-teal-bg); color:var(--fc-teal); }
  .trend-down{ background:var(--fc-red-bg); color:var(--fc-red); }
  .trend-stable{ background:var(--fc-amber-bg); color:var(--fc-amber); }
  .peak-badge{ background:var(--fc-primary-light); color:var(--fc-primary-dark); padding:6px 13px; border-radius:20px; font-size:.74rem; font-weight:700; display:inline-block; margin:0 6px 6px 0; }

  .staff-grid{ display:grid; grid-template-columns:repeat(auto-fill,minmax(230px,1fr)); gap:10px; }
  .staff-card{ background:var(--fc-bg); border:1px solid var(--fc-line); border-left:3px solid var(--fc-primary); padding:10px 13px; border-radius:var(--fc-radius-sm); margin-bottom:8px; }
  .staff-grid .staff-card{ margin-bottom:0; }
  .staff-card .hour{ font-weight:700; color:var(--fc-ink); font-size:.82rem; }
  .staff-card .note{ color:var(--fc-muted); font-size:.74rem; margin-top:3px; line-height:1.4; }

  .loading{ text-align:center; padding:28px; color:var(--fc-muted); font-size:.8rem; }
  .loading i{ font-size:22px; animation:spin 1s linear infinite; color:var(--fc-primary); }
  @keyframes spin{ to{ transform:rotate(360deg) } }
  .error-msg{ background:var(--fc-red-bg); color:var(--fc-red); padding:12px; border-radius:var(--fc-radius-sm); text-align:center; font-size:.8rem; }

  .toast{ position:fixed; bottom:18px; right:18px; background:#1E1B3A; color:#fff; padding:11px 16px; border-radius:var(--fc-radius-sm); display:none; box-shadow:0 10px 28px rgba(30,27,58,.2); z-index:3000; font-weight:700; font-size:.8rem; }
  .toast.error{ background:var(--fc-red); }

  @media(max-width:640px){ .tab-pane{ padding:16px; } .chart-wrap{ height:210px; } .pct-label{ width:110px; } .fc-tabs{ padding:0 14px; gap:18px; } }
</style>
@endsection

@section('content')

<div class="fc-head">
  <div>
    <h1><i class="fas {{ $zoneIcon }}"></i> {{ $zone }} Forecast</h1>
    <p>ML-powered sales trends and demand predictions for {{ $zone }}.</p>
    <span class="fc-live">Live · <span id="generatedAt">Loading…</span></span>
  </div>
  <button class="fc-btn fc-btn-ghost" id="downloadReport"><i class="fas fa-file-pdf"></i> Download Report</button>
</div>

{{-- Filters --}}
<div class="filter-bar">
  <div class="filter-field"><label for="fDateFrom">From</label><input type="date" id="fDateFrom"></div>
  <div class="filter-field"><label for="fDateTo">To</label><input type="date" id="fDateTo"></div>
  <div class="filter-field">
    <label for="fPayment">Payment</label>
    <select id="fPayment"><option value="all">All Methods</option></select>
  </div>
  <div class="filter-spacer"></div>
  <button class="fc-btn fc-btn-ghost" id="clearFilters">Clear</button>
  <button class="fc-btn fc-btn-primary" id="applyFilters">Apply</button>
</div>

{{-- KPIs --}}
<div class="kpi-strip">
  <div class="kpi-card">
    <span class="kpi-icon"><i class="fas fa-receipt"></i></span>
    <div class="kpi-body"><div class="kpi-label">Transactions</div><div class="kpi-value" id="mTxn">—</div><div class="kpi-sub">This month, completed</div></div>
  </div>
  <div class="kpi-card is-revenue">
    <span class="kpi-icon"><i class="fas fa-peso-sign"></i></span>
    <div class="kpi-body"><div class="kpi-label">Gross</div><div class="kpi-value" id="mRevenue">—</div><div class="kpi-sub">This month total</div></div>
  </div>
  <div class="kpi-card is-items">
    <span class="kpi-icon"><i class="fas fa-box"></i></span>
    <div class="kpi-body"><div class="kpi-label">Items Sold</div><div class="kpi-value" id="mItems">—</div><div class="kpi-sub">Units this month</div></div>
  </div>
  <div class="kpi-card is-alert">
    <span class="kpi-icon"><i class="fas fa-triangle-exclamation"></i></span>
    <div class="kpi-body"><div class="kpi-label">Low Stock</div><div class="kpi-value" id="mLowStock">—</div><div class="kpi-sub">Need restocking</div></div>
  </div>
</div>

<div class="content-panel">
  <div class="fc-tabs" role="tablist">
    <button class="tab-btn active" data-tab="top"><i class="fas fa-trophy"></i> Top Products</button>
    <button class="tab-btn" data-tab="dayofweek"><i class="fas fa-calendar-week"></i> Day of Week</button>
    <button class="tab-btn" data-tab="hourly"><i class="fas fa-clock"></i> Peak Hours</button>
    <button class="tab-btn" data-tab="forecast"><i class="fas fa-wand-magic-sparkles"></i> Next Month</button>
    <button class="tab-btn" data-tab="monthly"><i class="fas fa-chart-line"></i> Monthly</button>
    <button class="tab-btn" data-tab="payment"><i class="fas fa-credit-card"></i> Payment</button>
    <button class="tab-btn" data-tab="category"><i class="fas fa-boxes-stacked"></i> Categories</button>
    @if ($hasVouchers)
      <button class="tab-btn" data-tab="voucher"><i class="fas fa-ticket"></i> Vouchers</button>
    @endif
  </div>

  {{-- TOP PRODUCTS --}}
  <div id="tab-top" class="tab-pane active">
    <div class="fc-grid-2">
      <div><h3 class="pane-title">Top Products This Month</h3><div class="chart-wrap"><canvas id="chartTopMonth"></canvas></div></div>
      <div><h3 class="pane-title">All-Time Top Products</h3><div class="chart-wrap"><canvas id="chartTopAll"></canvas></div></div>
    </div>
    <div id="topTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- DAY OF WEEK --}}
  <div id="tab-dayofweek" class="tab-pane">
    <div class="fc-grid-2">
      <div><h3 class="pane-title">Transactions by Day</h3><div class="chart-wrap"><canvas id="chartDow"></canvas></div></div>
      <div><h3 class="pane-title">Gross by Day</h3><div class="chart-wrap"><canvas id="chartDowRev"></canvas></div></div>
    </div>
    <h3 class="pane-title">Weekend Top-Sellers (Sat &amp; Sun)</h3>
    <div id="weekendTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- PEAK HOURS --}}
  <div id="tab-hourly" class="tab-pane">
    <div class="fc-grid-2 fc-grid-peak">
      <div><h3 class="pane-title">Hourly Transactions</h3><div class="chart-wrap"><canvas id="chartHourly"></canvas></div></div>
      <div><h3 class="pane-title">Peak Hours</h3><div id="peakHoursDisplay" class="loading"><i class="fas fa-spinner"></i></div></div>
    </div>
    <h3 class="pane-title">Manpower Recommendations</h3>
    <div id="manpowerAdvice" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- NEXT MONTH --}}
  <div id="tab-forecast" class="tab-pane">
    <div class="info-note"><strong>How this works:</strong> Linear regression on the last 90 days of daily sales per product, scoped to the {{ $zone }} zone only. The trend badge shows whether demand is growing, shrinking, or stable — use the next-month forecast to plan restocking quantities.</div>
    <div id="forecastTable" class="loading"><i class="fas fa-spinner"></i><br>Calculating forecasts…</div>
  </div>

  {{-- MONTHLY --}}
  <div id="tab-monthly" class="tab-pane">
    <h3 class="pane-title">Monthly Gross Trend (Last 12 Months)</h3>
    <div class="chart-wrap"><canvas id="chartMonthly"></canvas></div>
    <div id="monthlyTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- PAYMENT --}}
  <div id="tab-payment" class="tab-pane">
    <div class="fc-grid-2">
      <div><h3 class="pane-title">Gross Share by Payment Method</h3><div class="chart-wrap"><canvas id="chartPayment"></canvas></div></div>
      <div><h3 class="pane-title">Breakdown</h3><div id="paymentBars" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div></div>
    </div>
  </div>

  {{-- CATEGORY --}}
  <div id="tab-category" class="tab-pane">
    <div class="info-note"><strong>How this works:</strong> Sales are grouped by category type (e.g. Rides, Rentals, F&amp;B) and by individual category — never combined into one bucket. Percentages are share of total gross for {{ $zone }}. <strong>Click any category row</strong> to see which products generated its gross.</div>
    <div id="categoryByType" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>

    <div class="fc-grid-2" style="margin-top:24px">
      <div><h3 class="pane-title">Best Sellers (Products)</h3><div id="bestSellers" class="loading"><i class="fas fa-spinner"></i></div></div>
      <div><h3 class="pane-title">Least Sellers (Products)</h3><div id="worstSellers" class="loading"><i class="fas fa-spinner"></i></div></div>
    </div>

    <h3 class="pane-title" style="margin-top:24px">Full Category Breakdown <small style="font-weight:500;color:var(--fc-muted)">&nbsp;(click a row to expand)</small></h3>
    <div id="categoryTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>

  {{-- VOUCHERS (hidden for zones without vouchers, e.g. Snackbar) --}}
  @if ($hasVouchers)
  <div id="tab-voucher" class="tab-pane">
    <div class="info-note"><strong>How this works:</strong> Online booking vouchers redeemed through the POS (completed only; voided vouchers are excluded). Follows the date filter above. “Upcoming” means confirmed vouchers not yet redeemed.</div>
    <div class="kpi-strip" style="grid-template-columns:repeat(2,1fr)">
      <div class="kpi-card"><span class="kpi-icon"><i class="fas fa-ticket"></i></span><div class="kpi-body"><div class="kpi-label">Vouchers Redeemed</div><div class="kpi-value" id="vCount">—</div></div></div>
      <div class="kpi-card is-revenue"><span class="kpi-icon"><i class="fas fa-peso-sign"></i></span><div class="kpi-body"><div class="kpi-label">Voucher Amount</div><div class="kpi-value" id="vAmount">—</div></div></div>
    </div>
    <div class="fc-grid-2">
      <div><h3 class="pane-title">Redemptions per Day</h3><div class="chart-wrap"><canvas id="chartVoucherDay"></canvas></div></div>
      <div><h3 class="pane-title">Upcoming Visits (Not Yet Redeemed)</h3><div id="voucherUpcoming" class="loading"><i class="fas fa-spinner"></i></div></div>
    </div>
    <h3 class="pane-title" style="margin-top:8px">Availed Packages</h3>
    <div id="voucherTable" class="loading"><i class="fas fa-spinner"></i><br>Loading…</div>
  </div>
  @endif
</div>

<div id="toast" class="toast"></div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ZONE         = @json($zone);
  const HAS_VOUCHERS = @json($hasVouchers);
  const ML_API       = '{{ url("/api/zone-forecast") }}/' + encodeURIComponent(ZONE);
  const CSRF         = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.color = '#33344A';

  function showToast(msg, type) {
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.className = 'toast' + (type === 'error' ? ' error' : '');
      t.style.display = 'block';
      setTimeout(() => t.style.display = 'none', 3000);
  }

  function currentFilters() {
      const from = document.getElementById('fDateFrom').value;
      const to   = document.getElementById('fDateTo').value;
      const pay  = document.getElementById('fPayment').value;
      const params = new URLSearchParams();
      if (from) params.set('date_from', from);
      if (to)   params.set('date_to', to);
      if (pay && pay !== 'all') params.set('payment_method', pay);
      const qs = params.toString();
      return qs ? ('?' + qs) : '';
  }

  async function apiFetch(endpoint, withFilters = false) {
      const url = ML_API + endpoint + (withFilters ? currentFilters() : '');
      const r = await fetch(url, { headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' } });
      if (!r.ok) throw new Error('HTTP ' + r.status + ' — ' + endpoint);
      return r.json();
  }

  function peso(n) {
      return '₱' + Number(n || 0).toLocaleString('en-PH', {minimumFractionDigits:2, maximumFractionDigits:2});
  }

  /* Palette: indigo, coral, teal, amber, violet, pink, sky, green, orange, slate */
  const COLORS = ['#4F46E5','#FF7A59','#14B8A6','#F59E0B','#8B5CF6','#EC4899','#0EA5E9','#84CC16','#F97316','#64748B'];
  const PRIMARY = '#4F46E5', PRIMARY_SOFT = 'rgba(79,70,229,.14)', ACCENT = '#FF7A59', ACCENT_SOFT = 'rgba(255,122,89,.14)';

  function makeChart(id, type, labels, datasets, opts = {}) {
      const ctx = document.getElementById(id);
      if (!ctx) return;
      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();
      return new Chart(ctx, {
          type,
          data: { labels, datasets },
          options: {
              responsive: true,
              maintainAspectRatio: false,
              plugins: { legend: { display: opts.legend !== false, labels: { usePointStyle: true, boxWidth: 8 } } },
              scales: type === 'bar' || type === 'line' ? {
                  y: { beginAtZero: true, grid: { color: '#ECEDF6' } },
                  x: { grid: { display: false } }
              } : undefined,
              ...opts.extra
          }
      });
  }

  /* TABS */
  document.querySelectorAll('.tab-btn').forEach(btn => {
      btn.addEventListener('click', () => {
          document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
          document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
          btn.classList.add('active');
          document.getElementById('tab-' + btn.dataset.tab).classList.add('active');
      });
  });

  /* SUMMARY */
  async function loadSummary() {
      try {
          const d = await apiFetch('/summary', true);
          document.getElementById('mTxn').textContent      = Number(d.total_txn || 0).toLocaleString();
          document.getElementById('mRevenue').textContent  = peso(d.total_revenue);
          document.getElementById('mItems').textContent    = Number(d.total_items_sold || 0).toLocaleString();
          document.getElementById('mLowStock').textContent = d.low_stock_count;
      } catch(e) { console.error('Summary error', e); }
  }

  /* TOP PRODUCTS */
  async function loadTopProducts() {
      const div = document.getElementById('topTable');
      try {
          const d = await apiFetch('/top_products', true);
          const tm = d.this_month.slice(0, 8);
          makeChart('chartTopMonth', 'bar', tm.map(r => r.product_name),
              [{ label: 'Units Sold', data: tm.map(r => r.total_qty), backgroundColor: PRIMARY, borderRadius: 6 }], { legend: false });
          const at = d.all_time.slice(0, 8);
          makeChart('chartTopAll', 'bar', at.map(r => r.product_name),
              [{ label: 'Units Sold', data: at.map(r => r.total_qty), backgroundColor: ACCENT, borderRadius: 6 }], { legend: false });
          if (!d.this_month.length) {
              div.innerHTML = '<div class="error-msg">No sales data this month yet.</div>';
              return;
          }
          div.innerHTML = `
          <h3 class="pane-title">This Month Details</h3>
          <div class="table-scroll"><table class="ml-table">
            <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Units Sold</th><th>Revenue</th></tr></thead>
            <tbody>
              ${d.this_month.map((r,i) => `
              <tr><td>${i+1}</td><td><strong>${r.product_name}</strong></td><td>${r.category_name}</td>
                  <td>${Number(r.total_qty).toLocaleString()}</td><td>${peso(r.total_revenue)}</td></tr>`).join('')}
            </tbody>
          </table></div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load data.<br><small>${e}</small></div>`;
      }
  }

  /* DAY OF WEEK */
  async function loadDayOfWeek() {
      const div = document.getElementById('weekendTable');
      try {
          const d = await apiFetch('/day_of_week');
          const days = d.by_day;
          const isWk = r => (r.day_name==='Saturday'||r.day_name==='Sunday');
          makeChart('chartDow', 'bar', days.map(r => r.day_name),
              [{ label: 'Transactions', data: days.map(r => r.num_transactions),
                 backgroundColor: days.map(r => isWk(r) ? ACCENT : PRIMARY), borderRadius: 6 }], { legend: false });
          makeChart('chartDowRev', 'bar', days.map(r => r.day_name),
              [{ label: 'Revenue', data: days.map(r => parseFloat(r.total_revenue || 0)),
                 backgroundColor: days.map(r => isWk(r) ? '#F59E0B' : '#14B8A6'), borderRadius: 6 }], { legend: false });
          if (!d.weekend_top.length) {
              div.innerHTML = '<div class="error-msg">No weekend sales data yet.</div>';
              return;
          }
          div.innerHTML = `
          <div class="table-scroll"><table class="ml-table">
            <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Day</th><th>Units Sold</th></tr></thead>
            <tbody>
              ${d.weekend_top.map((r,i) => `
              <tr><td>${i+1}</td><td><strong>${r.product_name}</strong></td><td>${r.category_name}</td>
                  <td>${r.day_name}</td><td>${Number(r.total_qty).toLocaleString()}</td></tr>`).join('')}
            </tbody>
          </table></div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load data.</div>`;
      }
  }

  /* PEAK HOURS */
  async function loadHourly() {
      const peakDiv = document.getElementById('peakHoursDisplay');
      const manpDiv = document.getElementById('manpowerAdvice');
      try {
          const d = await apiFetch('/hourly_peaks');
          makeChart('chartHourly', 'line',
              d.hourly.map(r => {
                  const h = parseInt(r.hour);
                  const s = h < 12 ? 'AM' : 'PM';
                  const disp = h === 0 ? 12 : h > 12 ? h-12 : h;
                  return disp + ':00 ' + s;
              }),
              [{ label: 'Transactions', data: d.hourly.map(r => r.num_transactions),
                 borderColor: PRIMARY, backgroundColor: PRIMARY_SOFT, fill: true, tension: 0.4,
                 pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: PRIMARY }]
          );
          peakDiv.innerHTML = d.peak_hours.length
              ? '<p style="color:#5B5D72;margin-bottom:10px;font-size:.8rem">Hours with the highest customer traffic:</p>' +
                d.peak_hours.map(h => `<span class="peak-badge">${h}</span>`).join('')
              : '<p style="color:#5B5D72">Not enough data yet.</p>';
          if (!d.manpower_advice.length) {
              manpDiv.innerHTML = '<div class="error-msg">Not enough data for manpower suggestions.</div>';
              return;
          }
          manpDiv.innerHTML = '<div class="staff-grid">' + d.manpower_advice.map(a => `
          <div class="staff-card">
            <div class="hour">${a.hour} &nbsp;·&nbsp; <span style="color:#5B5D72;font-size:.74rem;font-weight:500">${a.transactions} avg txns</span></div>
            <div class="note">${a.note}</div>
          </div>`).join('') + '</div>';
      } catch(e) {
          peakDiv.innerHTML = `<div class="error-msg">Could not load data.</div>`;
          manpDiv.innerHTML = `<div class="error-msg">Could not load data.</div>`;
      }
  }

  /* NEXT MONTH FORECAST */
  async function loadForecast() {
      const div = document.getElementById('forecastTable');
      try {
          const d = await apiFetch('/next_month_forecast');
          document.getElementById('generatedAt').textContent = d.generated_at || '';
          if (!d.forecasts.length) {
              div.innerHTML = '<div class="error-msg">Not enough historical data for forecasting. Need at least 3 days of sales per product.</div>';
              return;
          }
          div.innerHTML = `
          <div class="table-scroll"><table class="ml-table">
            <thead><tr><th>#</th><th>Product</th><th>Category</th><th>Current Avg/Day</th><th>Forecast Avg/Day</th><th>Next Month Total</th><th>Trend</th><th>Data</th></tr></thead>
            <tbody>
              ${d.forecasts.map((r,i) => `
              <tr>
                <td>${i+1}</td>
                <td><strong>${r.product_name}</strong></td>
                <td>${r.category_name}</td>
                <td>${r.current_month_avg} units/day</td>
                <td>${r.forecast_daily_avg} units/day</td>
                <td><strong>${Number(r.forecast_next_month).toLocaleString()} units</strong></td>
                <td>
                  ${r.trend==='up'     ? '<span class="trend-up">▲ Growing</span>'    : ''}
                  ${r.trend==='down'   ? '<span class="trend-down">▼ Declining</span>' : ''}
                  ${r.trend==='stable' ? '<span class="trend-stable">→ Stable</span>'  : ''}
                </td>
                <td style="color:#5B5D72;font-size:.72rem">${r.data_points} days${r.low_data ? ' · <span class="trend-stable">low data</span>' : ''}</td>
              </tr>`).join('')}
            </tbody>
          </table></div>`;
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load forecast.<br><small>${e}</small></div>`;
      }
  }

  /* MONTHLY TREND */
  async function loadMonthly() {
      const div = document.getElementById('monthlyTable');
      try {
          const d = await apiFetch('/monthly_trend');
          const m = d.monthly || [];

          makeChart('chartMonthly', 'line', m.map(r => r.month),
              [{ label: 'Revenue', data: m.map(r => parseFloat(r.total_revenue || 0)),
                 borderColor: PRIMARY, backgroundColor: PRIMARY_SOFT, fill: true, tension: 0.4,
                 pointRadius: 4, pointHoverRadius: 6, pointBackgroundColor: PRIMARY }],
              { legend: false });

          div.innerHTML = m.length ? `
          <div class="table-scroll"><table class="ml-table">
            <thead><tr><th>Month</th><th>Transactions</th><th>Units Sold</th><th>Gross</th></tr></thead>
            <tbody>
              ${m.slice().reverse().map(r => `
              <tr><td><strong>${r.month}</strong></td>
                  <td>${Number(r.num_transactions).toLocaleString()}</td>
                  <td>${Number(r.total_qty).toLocaleString()}</td>
                  <td>${peso(r.total_revenue)}</td></tr>`).join('')}
            </tbody>
          </table></div>`
          : '<div class="error-msg">No monthly data yet.</div>';
      } catch(e) {
          div.innerHTML = `<div class="error-msg">Could not load monthly data.<br><small>${e}</small></div>`;
      }
  }

  /* PAYMENT METHODS */
  async function loadPaymentBreakdown() {
      const barsDiv = document.getElementById('paymentBars');
      try {
          const d = await apiFetch('/payment_breakdown', true);
          const methods = d.methods || [];

          const select = document.getElementById('fPayment');
          if (select.options.length <= 1) {
              methods.forEach(m => {
                  const opt = document.createElement('option');
                  opt.value = m.payment_method;
                  opt.textContent = m.payment_method;
                  select.appendChild(opt);
              });
          }

          if (!methods.length) {
              barsDiv.innerHTML = '<div class="error-msg">No payment data yet.</div>';
              makeChart('chartPayment', 'doughnut', [], []);
              return;
          }

          makeChart('chartPayment', 'doughnut',
              methods.map(m => m.payment_method),
              [{ data: methods.map(m => m.total_revenue), backgroundColor: COLORS, borderWidth: 2, borderColor: '#fff', hoverOffset: 6 }],
              { legend: true, extra: { cutout: '66%' } }
          );

          barsDiv.innerHTML = methods.map(m => `
            <div class="pct-row">
              <div class="pct-label">${m.payment_method}</div>
              <div class="pct-track"><div class="pct-fill" style="width:${m.percentage}%"><span>${peso(m.total_revenue)}</span></div></div>
              <div class="pct-value">${m.percentage}%</div>
            </div>`).join('');
      } catch(e) {
          barsDiv.innerHTML = `<div class="error-msg">Could not load payment data.</div>`;
      }
  }

  /* CATEGORY BREAKDOWN */
  async function loadCategoryBreakdown() {
      const byTypeDiv = document.getElementById('categoryByType');
      const bestDiv   = document.getElementById('bestSellers');
      const worstDiv  = document.getElementById('worstSellers');
      const tableDiv  = document.getElementById('categoryTable');
      try {
          const d = await apiFetch('/category_breakdown', true);
          const byType     = d.by_type || [];
          const byCategory = d.by_category || [];

          if (!byType.length) {
              byTypeDiv.innerHTML = '<div class="error-msg">No category sales data yet.</div>';
              bestDiv.innerHTML = ''; worstDiv.innerHTML = ''; tableDiv.innerHTML = '';
              return;
          }

          byTypeDiv.innerHTML = `
            <h3 class="pane-title">By Category Type</h3>
            ${byType.map(t => `
              <div class="pct-row">
                <div class="pct-label">${t.category_type}</div>
                <div class="pct-track"><div class="pct-fill" style="width:${t.percentage}%"><span>${peso(t.total_revenue)}</span></div></div>
                <div class="pct-value">${t.percentage}%</div>
              </div>`).join('')}`;

          bestDiv.innerHTML = (d.best_sellers || []).map((r,i) => `
            <div class="staff-card" style="border-left-color:#0F766E">
              <div class="hour">#${i+1} · ${r.product_name} <span class="best-tag">${r.category_name}</span></div>
              <div class="note">${Number(r.total_qty).toLocaleString()} units · ${peso(r.total_revenue)} · ${r.percentage}% of total revenue</div>
            </div>`).join('') || '<p style="color:#5B5D72">Not enough data yet.</p>';

          worstDiv.innerHTML = (d.worst_sellers || []).map((r,i) => `
            <div class="staff-card" style="border-left-color:#BE123C">
              <div class="hour">#${i+1} · ${r.product_name} <span class="worst-tag">${r.category_name}</span></div>
              <div class="note">${Number(r.total_qty).toLocaleString()} units · ${peso(r.total_revenue)} · ${r.percentage}% of total revenue</div>
            </div>`).join('') || '<p style="color:#5B5D72">Not enough data yet.</p>';

          tableDiv.innerHTML = `
            <div class="table-scroll">
            <table class="ml-table" id="catBreakdownTable">
              <thead><tr><th></th><th>#</th><th>Category</th><th>Type</th><th>Units Sold</th><th>Gross</th><th>% of Total</th></tr></thead>
              <tbody>
                ${byCategory.map((r,i) => `
                <tr class="cat-row" data-cat="cat-${i}" style="cursor:pointer">
                  <td><i class="fas fa-chevron-right" id="caret-${i}"></i></td>
                  <td>${i+1}</td>
                  <td><strong>${r.category_name}</strong></td>
                  <td>${r.category_type}</td>
                  <td>${Number(r.total_qty).toLocaleString()}</td>
                  <td>${peso(r.total_revenue)}</td>
                  <td><strong>${r.percentage}%</strong></td>
                </tr>
                <tr id="cat-${i}" style="display:none">
                  <td colspan="7" style="background:var(--fc-bg);padding:0">
                    ${(r.products && r.products.length) ? `
                      <table class="ml-table" style="margin:6px 10px;width:calc(100% - 20px)">
                        <thead><tr><th>Product</th><th>Units Sold</th><th>Gross</th><th>% of ${r.category_name} Gross</th></tr></thead>
                        <tbody>
                          ${r.products.map(p => `
                          <tr><td>${p.product_name}</td><td>${Number(p.total_qty).toLocaleString()}</td>
                              <td>${peso(p.total_revenue)}</td><td>${p.percentage_of_category}%</td></tr>`).join('')}
                        </tbody>
                      </table>` : '<p style="color:#5B5D72;padding:10px 14px">No product-level data.</p>'}
                  </td>
                </tr>`).join('')}
              </tbody>
            </table>
            </div>`;

          document.querySelectorAll('.cat-row').forEach(row => {
              row.addEventListener('click', () => {
                  const target = document.getElementById(row.dataset.cat);
                  const caret  = row.querySelector('i');
                  const isOpen = target.style.display !== 'none';
                  target.style.display = isOpen ? 'none' : 'table-row';
                  caret.classList.toggle('fa-chevron-right', isOpen);
                  caret.classList.toggle('fa-chevron-down', !isOpen);
              });
          });
      } catch(e) {
          byTypeDiv.innerHTML = `<div class="error-msg">Could not load category data.</div>`;
      }
  }

  /* VOUCHERS (only runs when the zone has vouchers) */
  async function loadVouchers() {
      const tbl = document.getElementById('voucherTable');
      const up  = document.getElementById('voucherUpcoming');
      if (!tbl || !up) return;
      try {
          const d = await apiFetch('/voucher_summary', true);
          document.getElementById('vCount').textContent  = Number(d.total_vouchers || 0).toLocaleString();
          document.getElementById('vAmount').textContent = peso(d.total_amount);

          const days = d.by_day || [];
          makeChart('chartVoucherDay', 'bar', days.map(r => r.day),
              [{ label: 'Vouchers', data: days.map(r => Number(r.vouchers)), backgroundColor: PRIMARY, borderRadius: 6 }], { legend: false });

          const upcoming = d.upcoming || [];
          up.innerHTML = upcoming.length ? `
            <div class="table-scroll"><table class="ml-table">
              <thead><tr><th>Visit Date</th><th>Vouchers</th><th>Amount</th></tr></thead>
              <tbody>${upcoming.map(r => `
                <tr><td>${r.visit_date}</td><td>${Number(r.vouchers).toLocaleString()}</td><td>${peso(r.total_amount)}</td></tr>`).join('')}
              </tbody></table></div>`
            : '<p style="color:#5B5D72;font-size:.8rem">There are no upcoming unredeemed vouchers.</p>';

          const items = d.by_item || [];
          tbl.innerHTML = items.length ? `
            <div class="table-scroll"><table class="ml-table">
              <thead><tr><th>#</th><th>Package</th><th>Vouchers</th><th>Amount</th></tr></thead>
              <tbody>${items.map((r,i) => `
                <tr><td>${i+1}</td><td><strong>${r.name}</strong></td>
                <td>${Number(r.vouchers).toLocaleString()}</td><td>${peso(r.total_amount)}</td></tr>`).join('')}
              </tbody></table></div>`
            : '<div class="error-msg">No voucher redemptions in this period.</div>';
      } catch(e) {
          tbl.innerHTML = `<div class="error-msg">Could not load voucher data.<br><small>${e}</small></div>`;
          up.innerHTML  = '';
      }
  }

  /* FILTER CONTROLS */
  function reloadFilteredData() {
      loadSummary(); loadTopProducts(); loadPaymentBreakdown(); loadCategoryBreakdown();
      if (HAS_VOUCHERS) loadVouchers();
  }
  document.getElementById('applyFilters').addEventListener('click', reloadFilteredData);
  document.getElementById('clearFilters').addEventListener('click', () => {
      document.getElementById('fDateFrom').value = '';
      document.getElementById('fDateTo').value = '';
      document.getElementById('fPayment').value = 'all';
      reloadFilteredData();
  });
  document.getElementById('downloadReport').addEventListener('click', () => {
      window.location.href = ML_API + '/report' + currentFilters();
  });

  /* BOOT — each loader runs independently so one failure can't block the rest */
  const bootLoaders = [
      loadSummary, loadTopProducts, loadDayOfWeek, loadHourly, loadForecast,
      loadMonthly, loadPaymentBreakdown, loadCategoryBreakdown
  ];
  if (HAS_VOUCHERS) bootLoaders.push(loadVouchers);

  bootLoaders.forEach(fn => {
      try { fn(); } catch (e) { console.error(fn.name + ' failed:', e); }
  });
</script>
@endpush