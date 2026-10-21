@extends('layouts.sidebar')

@php
    $zoneIcons = [
        'Skataya'      => 'fa-bolt',
        'Snackbar'     => 'fa-burger',
        'Piyestarides' => 'fa-flag-checkered',
        'Dinolakbay'   => 'fa-dragon',
    ];

    // Same URLs as the sidebar submenu
    $zoneUrls = [
        'Skataya'      => route('zone.rollerFever'),
        'Snackbar'     => route('zone.snackbar'),
        'Piyestarides' => route('zone.fieldOfRides'),
        'Dinolakbay'   => route('zone.dinoAdventure'),
    ];
@endphp

@section('title', 'Sales Forecast')

@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>
  :root{
    --fc-bg:#F8FAFC; --fc-card:#fff; --fc-line:#E5E7EF;
    --fc-ink:#1B1D28; --fc-ink-soft:#33344A; --fc-muted:#5B5D72;
    --fc-primary:#4F46E5; --fc-primary-dark:#4338CA; --fc-primary-light:#EEF0FF;
    --fc-teal:#0F766E; --fc-teal-bg:#DDF7F2;
    --fc-amber:#92400E; --fc-amber-bg:#FEF3C7;
    --fc-red:#BE123C; --fc-red-bg:#FFE4EA;
    --fc-radius:16px; --fc-shadow:0 1px 2px rgba(15,23,42,.04), 0 4px 14px rgba(15,23,42,.04);
  }

  /* ---------- page wrapper: everything is centered and capped in width ---------- */
  .fc-page{ max-width:1180px; margin:0 auto; }

  /* ---------- header (left-aligned) ---------- */
  .fc-head{ text-align:left; padding:6px 0 18px; }
  .fc-head h1{ font-family:'Plus Jakarta Sans',sans-serif; font-size:1.75rem; font-weight:800; color:var(--fc-ink); margin:0 0 6px; letter-spacing:-.02em; }
  .fc-head p{ margin:0; font-size:.86rem; color:var(--fc-muted); line-height:1.55; }
  .fc-live{ display:inline-flex; align-items:center; gap:7px; margin-top:10px; font-size:.74rem; font-weight:600; color:var(--fc-muted); }
  .fc-live::before{ content:''; width:7px; height:7px; border-radius:50%; background:#14B8A6; box-shadow:0 0 0 3px var(--fc-teal-bg); }

  /* ---------- info note (left-aligned, full width) ---------- */
  .info-note{ margin:0 0 22px; background:var(--fc-primary-light); border-radius:12px; padding:13px 18px; font-size:.8rem; color:var(--fc-ink-soft); line-height:1.6; text-align:left; }
  .info-note strong{ color:var(--fc-primary-dark); }

  /* ---------- zone cards: equal-size, evenly spaced grid ---------- */
  .zone-grid{ display:grid; grid-template-columns:repeat(4, minmax(0,1fr)); gap:18px; margin-bottom:22px; align-items:stretch; }
  @media(max-width:1180px){ .zone-grid{ grid-template-columns:repeat(2, minmax(0,1fr)); } }
  @media(max-width:640px){ .zone-grid{ grid-template-columns:1fr; } }

  .zone-card{
    display:flex; flex-direction:column; align-items:center; text-align:center;
    text-decoration:none; color:inherit; height:100%;
    background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius);
    padding:22px 18px 16px; box-shadow:var(--fc-shadow);
    transition:transform .15s, border-color .15s, box-shadow .15s;
  }
  .zone-card:hover{ transform:translateY(-3px); border-color:var(--fc-primary); box-shadow:0 10px 26px rgba(79,70,229,.14); }
  .zone-card:focus-visible{ outline:2px solid var(--fc-primary); outline-offset:3px; }

  /* top: icon above the zone name */
  .zone-top{ display:flex; flex-direction:column; align-items:center; gap:10px; width:100%; padding-bottom:16px; margin-bottom:16px; border-bottom:1px solid var(--fc-line); }
  .zone-icon{ width:46px; height:46px; border-radius:14px; background:var(--fc-primary-light); color:var(--fc-primary-dark); display:inline-flex; align-items:center; justify-content:center; font-size:1.1rem; }
  .zone-name{ font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:1.05rem; color:var(--fc-ink); }

  .zone-body{ display:flex; flex-direction:column; align-items:center; width:100%; flex:1 1 auto; }
  .zone-label{ font-size:.66rem; font-weight:700; color:var(--fc-muted); text-transform:uppercase; letter-spacing:.08em; }
  .zone-gross{ font-family:'Plus Jakarta Sans',sans-serif; font-size:1.6rem; font-weight:800; color:var(--fc-ink); line-height:1.2; margin:4px 0 10px; white-space:nowrap; }
  .zone-vs{ font-size:.74rem; color:var(--fc-muted); margin-top:8px; min-height:2.2em; line-height:1.4; }
  .zone-txn{ margin-top:10px; padding:5px 12px; background:var(--fc-bg); border:1px solid var(--fc-line); border-radius:999px; font-size:.72rem; font-weight:600; color:var(--fc-ink-soft); }

  .trend-up, .trend-down, .trend-stable{ display:inline-block; padding:4px 12px; border-radius:20px; font-size:.74rem; font-weight:700; white-space:nowrap; }
  .trend-up{ background:var(--fc-teal-bg); color:var(--fc-teal); }
  .trend-down{ background:var(--fc-red-bg); color:var(--fc-red); }
  .trend-stable{ background:var(--fc-amber-bg); color:var(--fc-amber); }

  /* sparkline always sits at the bottom so all cards line up */
  .spark-wrap{ position:relative; width:100%; height:64px; margin-top:auto; padding-top:14px; box-sizing:content-box; }

  .zone-go{ margin-top:12px; padding-top:12px; border-top:1px solid var(--fc-line); width:100%; font-size:.74rem; font-weight:700; color:var(--fc-primary-dark); display:inline-flex; align-items:center; justify-content:center; gap:6px; }
  .zone-card:hover .zone-go i{ transform:translateX(3px); }
  .zone-go i{ transition:transform .15s; }

  /* ---------- comparison panel ---------- */
  .content-panel{ background:var(--fc-card); border:1px solid var(--fc-line); border-radius:var(--fc-radius); box-shadow:var(--fc-shadow); padding:24px; }
  .pane-title{ margin:0 0 14px; font-family:'Plus Jakarta Sans',sans-serif; font-size:1rem; font-weight:700; color:var(--fc-ink); text-align:center; }
  .chart-wrap{ position:relative; height:320px; }

  .loading{ text-align:center; padding:28px; color:var(--fc-muted); font-size:.8rem; width:100%; }
  .error-msg{ background:var(--fc-red-bg); color:var(--fc-red); padding:12px; border-radius:9px; text-align:center; font-size:.8rem; }

  @media(max-width:640px){ .fc-head h1{ font-size:1.45rem; } .chart-wrap{ height:260px; } .content-panel{ padding:16px; } }
</style>
@endsection

@section('content')

<div class="fc-page">

  <div class="fc-head">
    <h1>Sales Forecast</h1>
    <p>Gross sales summary per zone for PISTAVORA Lipa. Click a zone to open its full forecast dashboard.</p>
    <span class="fc-live">Live · <span id="generatedAt">Loading…</span></span>
  </div>

  <div class="info-note">
    <strong>How this works:</strong> Gross is the total sales of the zone from the 1st of this month until today (completed transactions only).
    The % change compares it against the <em>same number of days</em> of last month, so the comparison is fair mid-month.
  </div>

  {{-- Zone cards (rendered by JS; skeleton placeholders keep the layout stable) --}}
  <div class="zone-grid" id="zoneGrid">
    @foreach ($zones as $z)
      <a class="zone-card" href="{{ $zoneUrls[$z] ?? '#' }}">
        <div class="zone-top">
          <span class="zone-icon"><i class="fas {{ $zoneIcons[$z] ?? 'fa-chart-line' }}"></i></span>
          <span class="zone-name">{{ $z }}</span>
        </div>
        <div class="loading">Loading…</div>
      </a>
    @endforeach
  </div>

  <div class="content-panel">
    <h3 class="pane-title">Gross Trend per Zone (Last 6 Months)</h3>
    <div class="chart-wrap"><canvas id="chartZones"></canvas></div>
  </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const ML_API    = '{{ url("/api/ml") }}';
  const ZONE_URLS = @json($zoneUrls);
  const ICONS     = @json($zoneIcons);
  const COLORS    = { 'Skataya': '#4F46E5', 'Snackbar': '#F59E0B', 'Piyestarides': '#FF7A59', 'Dinolakbay': '#14B8A6' };

  Chart.defaults.font.family = 'Inter, sans-serif';
  Chart.defaults.color = '#33344A';

  function peso(n) {
      return '₱' + Number(n || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function trendBadge(z) {
      const pct = Math.abs(z.change_pct) + '%';
      if (z.trend === 'up')   return `<span class="trend-up">▲ ${pct}</span>`;
      if (z.trend === 'down') return `<span class="trend-down">▼ ${pct}</span>`;
      return `<span class="trend-stable">→ Stable</span>`;
  }

  function sparkId(zone) {
      return 'spark-' + zone.replace(/\s+/g, '-');
  }

  function renderCards(data) {
      const grid = document.getElementById('zoneGrid');

      grid.innerHTML = data.zones.map(z => `
        <a class="zone-card" href="${ZONE_URLS[z.zone] || '#'}" aria-label="Open ${z.zone} dashboard">
          <div class="zone-top">
            <span class="zone-icon"><i class="fas ${ICONS[z.zone] || 'fa-chart-line'}"></i></span>
            <span class="zone-name">${z.zone}</span>
          </div>

          <div class="zone-body">
            <div class="zone-label">Gross This Month</div>
            <div class="zone-gross">${peso(z.this_gross)}</div>
            ${trendBadge(z)}
            <div class="zone-vs">vs ${peso(z.last_gross)}<br>same period last month</div>
            <div class="zone-txn">${Number(z.transactions).toLocaleString()} transactions</div>
            <div class="spark-wrap"><canvas id="${sparkId(z.zone)}"></canvas></div>
          </div>

          <div class="zone-go">Open dashboard <i class="fas fa-arrow-right"></i></div>
        </a>`).join('');

      // Sparklines
      data.zones.forEach(z => {
          const ctx = document.getElementById(sparkId(z.zone));
          if (!ctx) return;
          const color = COLORS[z.zone] || '#4F46E5';
          new Chart(ctx, {
              type: 'line',
              data: {
                  labels: data.months,
                  datasets: [{ data: z.series, borderColor: color, backgroundColor: color + '22', fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 }]
              },
              options: {
                  responsive: true, maintainAspectRatio: false,
                  plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => peso(c.parsed.y) } } },
                  scales: { x: { display: false }, y: { display: false, beginAtZero: true } }
              }
          });
      });
  }

  function renderComparison(data) {
      const ctx = document.getElementById('chartZones');
      const existing = Chart.getChart(ctx);
      if (existing) existing.destroy();

      new Chart(ctx, {
          type: 'line',
          data: {
              labels: data.months,
              datasets: data.zones.map(z => ({
                  label: z.zone,
                  data: z.series,
                  borderColor: COLORS[z.zone] || '#4F46E5',
                  backgroundColor: (COLORS[z.zone] || '#4F46E5') + '22',
                  tension: 0.4, pointRadius: 4, pointHoverRadius: 6, borderWidth: 2
              }))
          },
          options: {
              responsive: true, maintainAspectRatio: false,
              plugins: {
                  legend: { position: 'bottom', labels: { usePointStyle: true, boxWidth: 8, padding: 18 } },
                  tooltip: { callbacks: { label: c => c.dataset.label + ': ' + peso(c.parsed.y) } }
              },
              scales: {
                  y: { beginAtZero: true, grid: { color: '#ECEDF6' }, ticks: { callback: v => '₱' + Number(v).toLocaleString() } },
                  x: { grid: { display: false } }
              }
          }
      });
  }

  async function boot() {
      try {
          const r = await fetch(ML_API + '/zone-summary', { headers: { 'Accept': 'application/json' } });
          if (!r.ok) throw new Error('HTTP ' + r.status);
          const data = await r.json();

          document.getElementById('generatedAt').textContent = data.generated_at || '';
          renderCards(data);
          renderComparison(data);
      } catch (e) {
          console.error(e);
          document.getElementById('zoneGrid').innerHTML =
              `<div class="error-msg" style="grid-column:1/-1">Could not load zone summary.<br><small>${e}</small></div>`;
      }
  }

  boot();
</script>
@endpush