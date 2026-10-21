<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Pistavora System | Analytics</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<style>
  :root{
    --pink:#FF5C85;
    --pink-dark:#D63E63;
    --pink-deep:#B82850;
    --pink-light:#FFE3EB;
    --pink-pale:#FFF2F6;
    --ink:#1A1523;
    --ink-soft:#635C72;
    --muted:#9C94AB;
    --line:#EEE8F0;
    --bg:#F3EFF3;
    --card:#FFFFFF;
    --teal:#1FAE9E;
    --amber:#F2932A;
    --shadow-sm:0 1px 3px rgba(26,21,35,.06), 0 1px 2px rgba(26,21,35,.04);
    --shadow-md:0 10px 28px rgba(26,21,35,.09);
    --shadow-pink:0 16px 36px rgba(214,62,99,.28);
  }
  *{box-sizing:border-box;margin:0;padding:0;}
  body{background:var(--bg);font-family:'Inter',sans-serif;color:var(--ink);}
  a{text-decoration:none;color:inherit;}

  .app-shell{display:grid;grid-template-columns:236px 1fr;min-height:100vh;}

  /* ===== MOBILE TOPBAR ===== */
  .mobile-topbar{
    display:none;align-items:center;justify-content:space-between;
    background:var(--ink);padding:14px 18px;position:sticky;top:0;z-index:40;
  }
  .mobile-topbar img{height:32px;}
  .hamburger-btn{
    width:38px;height:38px;border-radius:10px;background:rgba(255,255,255,.08);border:none;
    color:#fff;font-size:1.05rem;display:flex;align-items:center;justify-content:center;cursor:pointer;
  }
  .sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(26,21,35,.5);z-index:45;}
  .sidebar-overlay.active{display:block;}

  /* ===== SIDEBAR ===== */
  .sidebar{background:var(--ink);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;}
  .sidebar-brand{padding:20px 10px;display:flex;align-items:center;justify-content:center;}
  .sidebar-brand .mark{width:210px;height:84px;border-radius:12px;display:flex;align-items:center;justify-content:center;padding:2px;flex-shrink:0;}
  .sidebar-brand .mark img{width:100%;height:100%;object-fit:contain;}

  .sidebar-nav{flex:1;overflow-y:auto;padding:10px 14px;display:flex;flex-direction:column;gap:1px;scrollbar-width:none;-ms-overflow-style:none;}
  .sidebar-nav::-webkit-scrollbar{width:0;height:0;display:none;}
  .nav-label{font-size:.65rem;font-weight:700;letter-spacing:.09em;text-transform:uppercase;color:#6E6680;padding:16px 12px 6px;}
  .sidebar-nav a{display:flex;align-items:center;gap:11px;padding:10px 12px;border-radius:10px;color:#C3BDD0;font-weight:500;font-size:.86rem;transition:.15s;}
  .sidebar-nav a i{width:16px;text-align:center;font-size:.82rem;color:#7A7290;transition:.15s;}
  .sidebar-nav a:hover{background:rgba(255,92,133,.12);color:#FFB3C6;}
  .sidebar-nav a:hover i{color:var(--pink);}
  .sidebar-nav a.active{background:rgba(255,92,133,.16);color:#fff;}
  .sidebar-nav a.active i{color:var(--pink);}

  .sidebar-footer{padding:16px;border-top:1px solid rgba(255,255,255,.08);}
  .sidebar-user{display:flex;align-items:center;gap:11px;padding:8px 6px 14px;}
  .sidebar-user .avatar{width:36px;height:36px;border-radius:11px;background:linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:.88rem;flex-shrink:0;}
  .sidebar-user .name{color:#fff;font-weight:600;font-size:.82rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:130px;}
  .sidebar-user .role{color:#8A8299;font-size:.72rem;text-transform:capitalize;}
  .sidebar-logout{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#C3BDD0;font-weight:500;font-size:.85rem;transition:.15s;}
  .sidebar-logout i{width:16px;text-align:center;color:#7A7290;}
  .sidebar-logout:hover{background:rgba(255,92,133,.12);color:#FFB3C6;}
  .sidebar-logout:hover i{color:var(--pink);}

  /* ===== MAIN ===== */
  .content-body{padding:clamp(20px, 3vw, 40px);max-width:1240px;}

  .page-header{
    position:relative;overflow:hidden;
    background:linear-gradient(120deg, #241A2E 0%, var(--pink-deep) 78%, var(--pink) 100%);
    border-radius:24px;padding:clamp(20px, 3vw, 32px);margin-bottom:22px;box-shadow:var(--shadow-pink);
  }
  .page-header h1{color:#fff;font-size:clamp(1.1rem, 3vw, 1.4rem);font-weight:700;margin-bottom:4px;}
  .page-header p{color:rgba(255,255,255,.75);font-size:.85rem;}

  /* ===== Card / grid / table primitives used throughout the analytics content ===== */
  .card{background:var(--card);border:1px solid var(--line);border-radius:16px;padding:18px;box-shadow:var(--shadow-sm);}
  .grid.cols-2{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
  .pill-btn{background:var(--pink-deep);color:#fff;border:none;padding:9px 18px;border-radius:999px;font-size:12.5px;font-weight:600;cursor:pointer;transition:.15s;}
  .pill-btn:hover{background:var(--pink-dark);}
  .table-scroll{overflow-x:auto;}
  table{width:100%;border-collapse:collapse;font-size:12.5px;}
  th{background:var(--pink-pale);color:var(--ink);text-align:left;padding:10px 12px;font-weight:700;font-size:10.5px;text-transform:uppercase;letter-spacing:.03em;white-space:nowrap;}
  td{padding:10px 12px;border-bottom:1px solid var(--line);color:var(--ink-soft);white-space:nowrap;}
  tr:hover td{background:var(--pink-pale);}
  .kpi{font-weight:800;color:var(--ink);margin:6px 0 4px;}
  .muted{color:var(--muted);}
  .delta{font-size:11.5px;font-weight:600;}
  .delta.up{color:var(--teal);}
  .delta.down{color:var(--pink-dark);}
  .tag{padding:3px 10px;border-radius:999px;font-size:10.5px;font-weight:700;color:#fff;display:inline-block;}
  .tag.green{background:var(--teal);}
  .tag.amber{background:var(--amber);}
  .tag.rose{background:var(--pink-dark);}

  /* ===== RESPONSIVE ===== */
  @media(max-width:900px){
    .app-shell{display:block;}
    .mobile-topbar{display:flex;}
    .sidebar{position:fixed;top:0;left:0;height:100vh;width:260px;transform:translateX(-100%);transition:transform .25s ease;z-index:50;box-shadow:0 0 40px rgba(0,0,0,.3);}
    .sidebar.open{transform:translateX(0);}
    .content-body{padding:18px;}
    .grid.cols-2{grid-template-columns:1fr;}
  }
  @media(max-width:520px){
    .sidebar-brand .mark{width:170px;height:68px;}
  }
</style>
</head>
<body>

<div class="mobile-topbar">
  <img src="{{ asset('images/pistavora-mark-white.svg') }}" alt="Pistavora">
  <button class="hamburger-btn" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>
</div>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<div class="app-shell">

  <aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
      <div class="mark"><img src="{{ asset('images/pistavora-mark-white.svg') }}" alt="Pistavora"></div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-label">Operations</div>
      @if(session('role') === 'cashier')
        {{ route('pos.index') }}<i class="fa-solid fa-cash-register"></i> Point of Sale</a>
        <a href="{{ route('inventory.index') }}"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
      @else
        <a href="{{ route('pos.index') }}"><i class="fa-solid fa-cash-register"></i> Point of Sale</a>
        <a href="{{ route('inventory.index') }}"><i class="fa-solid fa-boxes-stacked"></i> Inventory</a>
        <a href="{{ route('calculation.index') }}"><i class="fa-solid fa-calculator"></i> Calculation</a>
        <div class="nav-label">People</div>
        <a href="{{ route('customer.index') }}"><i class="fa-solid fa-users"></i> Customer Management</a>
        <a href="{{ route('attendance.index') }}"><i class="fa-solid fa-clipboard-check"></i> Attendance</a>
        <a href="{{ route('accounts.create') }}"><i class="fa-solid fa-user-plus"></i> Account Management</a>

        <div class="nav-label">Insights</div>
        <a href="{{ route('analytics.index') }}" class="active"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        @if(session('role') === 'admin')
          <a href="{{ route('ml-forecast.index') }}"><i class="fa-solid fa-brain"></i> Inventory Forecast</a>
        @endif
      @endif
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar">{{ strtoupper(substr(session('fullname', 'U'), 0, 1)) }}</div>
        <div>
          <div class="name">{{ session('fullname') }}</div>
          <div class="role">{{ session('role') }}</div>
        </div>
      </div>
      <a href="/logout" class="sidebar-logout">
        <i class="fas fa-sign-out-alt"></i> Log out
      </a>
    </div>
  </aside>

  <div class="main-content">
    <div class="content-body">

      <div class="page-header">
        <h1>Dashboard</h1>
        <p>Lipa Branch — real-time business insights</p>
      </div>

      @php
        $kpis = $kpis ?? [
            ['label' => 'Total Sales', 'value' => '₱20,308.00', 'delta' => '↑ 12.5% from last period', 'trend' => 'up', 'color' => 'pink-deep'],
            ['label' => 'Total Orders', 'value' => '23', 'delta' => '↑ 8.2% from last period', 'trend' => 'up', 'color' => 'teal'],
            ['label' => 'Avg Order Value', 'value' => '₱882.96', 'delta' => '↓ 2.1% from last period', 'trend' => 'down', 'color' => 'amber'],
            ['label' => 'Attendance Rate', 'value' => '91.4%', 'delta' => '↑ 5.3% from last period', 'trend' => 'up', 'color' => 'pink'],
        ];

        $salesTrend = $salesTrend ?? [
            'labels' => ['Jun 24', 'Jun 25', 'Jun 26', 'Jun 27', 'Jun 28', 'Jun 29', 'Jun 30'],
            'data' => [2400, 3100, 1980, 4200, 3600, 5100, 20308],
        ];

        $salesByCategory = $salesByCategory ?? [
            ['label' => 'Walk-In', 'pct' => 65, 'color' => '#FF5C85'],
            ['label' => 'Booking', 'pct' => 25, 'color' => '#1FAE9E'],
            ['label' => 'Group Event', 'pct' => 10, 'color' => '#F2932A'],
        ];

        $dailyAttendance = $dailyAttendance ?? [
            'labels' => ['Jun 24', 'Jun 25', 'Jun 26', 'Jun 27', 'Jun 28', 'Jun 29', 'Jun 30'],
            'present' => [14, 15, 13, 16, 14, 18, 17],
            'absent' => [4, 3, 5, 2, 4, 0, 1],
        ];

        $paymentMethods = $paymentMethods ?? [
            ['label' => 'Cash', 'pct' => 40, 'color' => '#1A1523'],
            ['label' => 'Card', 'pct' => 45, 'color' => '#FF5C85'],
            ['label' => 'GCash', 'pct' => 15, 'color' => '#FFB3C6'],
        ];

        $sales = $sales ?? [
            ['date' => '2026-05-16', 'id' => '#ORD0001', 'cat' => 'Walk-In', 'items' => '5 items', 'amt' => '₱825.00', 'pay' => 'cash'],
            ['date' => '2026-05-16', 'id' => '#ORD0002', 'cat' => 'Walk-In', 'items' => '5 items', 'amt' => '₱825.00', 'pay' => 'cash'],
            ['date' => '2026-05-16', 'id' => '#ORD0003', 'cat' => 'Walk-In', 'items' => '1 item', 'amt' => '₱999.00', 'pay' => 'card'],
            ['date' => '2026-05-17', 'id' => '#ORD0006', 'cat' => 'Walk-In', 'items' => '3 items', 'amt' => '₱1,200.00', 'pay' => 'card'],
            ['date' => '2026-05-17', 'id' => '#ORD0007', 'cat' => 'Walk-In', 'items' => '2 items', 'amt' => '₱650.00', 'pay' => 'cash'],
            ['date' => '2026-05-18', 'id' => '#ORD0008', 'cat' => 'Booking', 'items' => '8 items', 'amt' => '₱4,500.00', 'pay' => 'card'],
            ['date' => '2026-05-18', 'id' => '#ORD0009', 'cat' => 'Walk-In', 'items' => '1 item', 'amt' => '₱450.00', 'pay' => 'cash'],
            ['date' => '2026-05-19', 'id' => '#ORD0010', 'cat' => 'Walk-In', 'items' => '4 items', 'amt' => '₱1,800.00', 'pay' => 'card'],
            ['date' => '2026-05-20', 'id' => '#ORD0011', 'cat' => 'Group', 'items' => '15 items', 'amt' => '₱6,750.00', 'pay' => 'card'],
            ['date' => '2026-05-21', 'id' => '#ORD0012', 'cat' => 'Walk-In', 'items' => '2 items', 'amt' => '₱900.00', 'pay' => 'cash'],
        ];

        $attendance = $attendance ?? [
            ['date' => '2026-06-05', 'emp' => 'Mark Flores', 'tin' => '08:00', 'tout' => '17:00', 'status' => 'Present'],
            ['date' => '2026-06-05', 'emp' => 'Ana Garcia', 'tin' => '08:00', 'tout' => '17:00', 'status' => 'Present'],
            ['date' => '2026-06-05', 'emp' => 'Pedro Reyes', 'tin' => '08:00', 'tout' => '17:00', 'status' => 'Present'],
            ['date' => '2026-06-06', 'emp' => 'Mark Flores', 'tin' => '08:05', 'tout' => '17:00', 'status' => 'Present'],
            ['date' => '2026-06-06', 'emp' => 'Pedro Reyes', 'tin' => '—', 'tout' => '—', 'status' => 'Absent'],
            ['date' => '2026-06-07', 'emp' => 'Maria Santos', 'tin' => '09:10', 'tout' => '17:00', 'status' => 'Late'],
        ];

        $predictiveKpis = $predictiveKpis ?? [
            ['label' => '14-Day Sales Forecast', 'value' => '₱110,574', 'color' => 'pink-deep'],
            ['label' => 'Projected Growth', 'value' => '-51.55%', 'color' => 'teal', 'negative' => true],
            ['label' => 'Critical Stock Items', 'value' => '4', 'color' => 'amber'],
            ['label' => 'Anomalies Detected', 'value' => '19', 'color' => 'pink'],
        ];

        $forecast = $forecast ?? [
            'labels' => ['Jun 1', 'Jun 5', 'Jun 9', 'Jun 13', 'Jun 17', 'Jun 21', 'Jun 25', 'Jun 29', 'Jul 3', 'Jul 7', 'Jul 11'],
            'historical' => [4200, 5100, 3900, 5300, 4700, 4300, 4900, 4600, null, null, null],
            'predicted' => [null, null, null, null, null, null, null, 4600, 4800, 5000, 5200],
        ];

        $revExp = $revExp ?? [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep'],
            'revenue' => [180000, 195000, 172000, 210000, 228218, null, 240000, 252000, 248000],
            'expenses' => [120000, 115000, 130000, 118000, 140000, null, 145000, 138000, 142000],
        ];

        $inventory = $inventory ?? [
            ['item' => 'Burger', 'cat' => 'Food', 'rem' => 9, 'use' => '8.5/day', 'days' => '1.1 days', 'score' => 100, 'level' => 'Critical'],
            ['item' => 'Chips', 'cat' => 'Snacks', 'rem' => 13, 'use' => '4.1/day', 'days' => '3.2 days', 'score' => 100, 'level' => 'Critical'],
            ['item' => 'Juice', 'cat' => 'Beverages', 'rem' => 7, 'use' => '4.1/day', 'days' => '1.7 days', 'score' => 100, 'level' => 'Critical'],
            ['item' => 'Soda', 'cat' => 'Beverages', 'rem' => 50, 'use' => '8.4/day', 'days' => '6 days', 'score' => 91.4, 'level' => 'Critical'],
            ['item' => 'Fries', 'cat' => 'Food', 'rem' => 86, 'use' => '7.9/day', 'days' => '10.9 days', 'score' => 73.3, 'level' => 'High'],
            ['item' => 'Water', 'cat' => 'Beverages', 'rem' => 92, 'use' => '4.4/day', 'days' => '20.9 days', 'score' => 58.7, 'level' => 'Medium'],
        ];

        $insights = $insights ?? [
            ['icon' => '📈', 'color' => 'teal', 'title' => 'Revenue Grew 3.0%', 'body' => 'Last 30-day sales totaled ₱228,218 vs ₱221,508 the prior period.', 'action' => 'Maintain momentum through staff incentives.'],
            ['icon' => '🎯', 'color' => '#FFB3C6', 'title' => 'Snacks Leads Revenue', 'body' => 'Snacks generated ₱77,108 (30 days). Food is underperforming at ₱38,074.', 'action' => 'Consider promoting Food products with bundle deals or discounts.'],
            ['icon' => '🏆', 'color' => 'teal', 'title' => 'Jane Smith is Top Performer', 'body' => 'Jane Smith contributed ₱92,925 in the last 30 days.', 'action' => 'Reward top performers to sustain motivation.'],
            ['icon' => '⚠️', 'color' => 'pink', 'title' => '4 Products at Critical Stock Level', 'body' => 'Reorder immediately: Burger, Chips, Juice. Stockout risk is elevated.', 'action' => 'Place purchase orders within 48 hours to prevent lost sales.'],
            ['icon' => '💳', 'color' => '#FFB3C6', 'title' => 'Card is Most-Used Payment Method', 'body' => '12 of last 30 transactions used card payment.', 'action' => 'Ensure card payment infrastructure is reliable.'],
            ['icon' => '📅', 'color' => '#FFB3C6', 'title' => 'Weekend Sales are Higher', 'body' => 'Weekend avg: ₱8,968/day vs weekday avg: ₱7,024/day.', 'action' => 'Staff up on weekends. Run Friday-to-Sunday promotions.'],
        ];

        $anomalies = $anomalies ?? [
            ['date' => '2026-05-17', 'cat' => 'Snacks', 'amt' => '₱8,577', 'type' => 'Drop'],
            ['date' => '2026-05-21', 'cat' => 'Others', 'amt' => '₱15,685', 'type' => 'Spike'],
            ['date' => '2026-05-22', 'cat' => 'Food', 'amt' => '₱8,911', 'type' => 'Drop'],
            ['date' => '2026-05-27', 'cat' => 'Others', 'amt' => '₱15,282', 'type' => 'Spike'],
            ['date' => '2026-05-28', 'cat' => 'Food', 'amt' => '₱15,603', 'type' => 'Spike'],
            ['date' => '2026-05-29', 'cat' => 'Beverages', 'amt' => '₱9,399', 'type' => 'Drop'],
        ];
      @endphp

      <!-- Tab bar: Historical / Predictive -->
      <div style="display:flex;border:1px solid var(--line);border-radius:10px;overflow:hidden;margin-bottom:18px;">
        <div id="anTab-hist" style="flex:1;padding:11px;text-align:center;background:var(--pink-deep);color:#fff;font-weight:600;font-size:13.5px;cursor:pointer;" onclick="switchAnTab('hist')">📈 Historical Reports</div>
        <div id="anTab-pred" style="flex:1;padding:11px;text-align:center;background:#fff;color:var(--muted);font-weight:600;font-size:13.5px;cursor:pointer;" onclick="switchAnTab('pred')">🤖 Predictive Analytics (AI)</div>
      </div>

      <!-- Date filter (decorative for now) -->
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;font-size:13px;flex-wrap:wrap;">
        <span style="color:var(--muted);">From:</span>
        <input type="date" value="2026-06-23" style="border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:12.5px;">
        <span style="color:var(--muted);">To:</span>
        <input type="date" value="2026-06-30" style="border:1px solid var(--line);border-radius:8px;padding:7px 10px;font-size:12.5px;">
        <button class="pill-btn">Apply Filter</button>
      </div>

      <!-- ===================== HISTORICAL ===================== -->
      <div id="anContent-hist">

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;">
          @foreach ($kpis as $k)
            <div class="card" style="border-left:4px solid var(--{{ $k['color'] }});">
              <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">{{ $k['label'] }}</div>
              <div class="kpi" style="font-size:22px;">{{ $k['value'] }}</div>
              <div class="delta {{ $k['trend'] }}">{{ $k['delta'] }}</div>
            </div>
          @endforeach
        </div>

        <div class="grid cols-2" style="margin-bottom:16px;">
          <div class="card">
            <h3 style="font-size:13.5px;margin-bottom:14px;">Sales Trend (Last 7 Days)</h3>
            <canvas id="salesTrendChart" height="160"></canvas>
          </div>
          <div class="card">
            <h3 style="font-size:13.5px;margin-bottom:14px;">Sales by Category</h3>
            <div style="display:flex;align-items:center;justify-content:center;gap:24px;height:240px;">
              <canvas id="categoryChart" style="max-width:200px;max-height:200px;"></canvas>
              <div style="font-size:13px;flex:1;max-width:140px;">
                @foreach ($salesByCategory as $c)
                  <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;"><span style="width:10px;height:10px;border-radius:50%;background:{{ $c['color'] }};flex-shrink:0;"></span>{{ $c['label'] }} <b style="margin-left:auto;">{{ $c['pct'] }}%</b></div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <div class="grid cols-2" style="margin-bottom:20px;">
          <div class="card">
            <h3 style="font-size:13.5px;margin-bottom:14px;">Daily Attendance</h3>
            <canvas id="attendanceChart" height="150"></canvas>
          </div>
          <div class="card">
            <h3 style="font-size:13.5px;margin-bottom:14px;">Payment Methods</h3>
            <div style="display:flex;align-items:center;justify-content:center;gap:24px;height:230px;">
              <canvas id="paymentChart" style="max-width:190px;max-height:190px;"></canvas>
              <div style="font-size:13px;flex:1;max-width:140px;">
                @foreach ($paymentMethods as $p)
                  <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;"><span style="width:10px;height:10px;border-radius:50%;background:{{ $p['color'] }};flex-shrink:0;"></span>{{ $p['label'] }} <b style="margin-left:auto;">{{ $p['pct'] }}%</b></div>
                @endforeach
              </div>
            </div>
          </div>
        </div>

        <!-- Sales Report -->
        <div class="card" style="margin-bottom:16px;">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-size:14px;margin:0;display:flex;align-items:center;gap:8px;">📋 Sales Report</h3>
            <button onclick="toggleDrop('salesDrop')" style="background:var(--pink-deep);color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">⬇ Export ▾</button>
          </div>
          <div style="border-top:2px solid var(--pink-dark);margin-bottom:12px;"></div>
          <div class="table-scroll">
            <table>
              <tr><th>Date</th><th>Order ID</th><th>Category</th><th>Items</th><th>Amount</th><th>Payment</th></tr>
              @foreach ($sales as $row)
                <tr>
                  <td>{{ $row['date'] }}</td>
                  <td style="color:var(--pink-deep);font-weight:600;">{{ $row['id'] }}</td>
                  <td>{{ $row['cat'] }}</td>
                  <td>{{ $row['items'] }}</td>
                  <td>{{ $row['amt'] }}</td>
                  <td>{{ $row['pay'] }}</td>
                </tr>
              @endforeach
            </table>
          </div>
          <p class="muted" style="font-size:11px;margin-top:10px;">Showing {{ count($sales) }} most recent orders. Pagination comes with real DB-backed data.</p>
        </div>

        <!-- Attendance Report -->
        <div class="card">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
            <h3 style="font-size:14px;margin:0;display:flex;align-items:center;gap:8px;">👥 Attendance Report</h3>
            <button onclick="toggleDrop('attDrop')" style="background:var(--pink-deep);color:#fff;border:none;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;">⬇ Export ▾</button>
          </div>
          <div style="border-top:2px solid var(--pink-dark);margin-bottom:12px;"></div>
          <div class="table-scroll">
            <table>
              <tr><th>Date</th><th>Employee</th><th>Time In</th><th>Time Out</th><th>Status</th></tr>
              @foreach ($attendance as $row)
                @php $cls = $row['status']==='Present' ? 'green' : ($row['status']==='Late' ? 'amber' : 'rose'); @endphp
                <tr>
                  <td>{{ $row['date'] }}</td>
                  <td>{{ $row['emp'] }}</td>
                  <td>{{ $row['tin'] }}</td>
                  <td>{{ $row['tout'] }}</td>
                  <td><span class="tag {{ $cls }}">{{ $row['status'] }}</span></td>
                </tr>
              @endforeach
            </table>
          </div>
        </div>

      </div><!-- end hist -->

      <!-- ===================== PREDICTIVE ===================== -->
      <div id="anContent-pred" style="display:none;">

        <div style="border:1px solid var(--line);border-radius:10px;padding:10px 16px;font-size:12px;color:#2a7a5a;background:#f0fbf6;margin-bottom:18px;display:flex;align-items:center;gap:8px;">
          <span style="width:9px;height:9px;border-radius:50%;background:var(--teal);display:inline-block;flex-shrink:0;"></span>
          Connected to ML backend — scikit-learn: active, 365 sales records loaded, models: lr_sales, rf_sales, iso_sales.
        </div>

        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:18px;">
          @foreach ($predictiveKpis as $k)
            <div class="card" style="border-left:4px solid var(--{{ $k['color'] }});">
              <div class="muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.5px;font-weight:700;">{{ $k['label'] }}</div>
              <div class="kpi" style="font-size:22px;{{ !empty($k['negative']) ? 'color:var(--pink);' : '' }}">{{ $k['value'] }}</div>
            </div>
          @endforeach
        </div>

        <div class="card" style="margin-bottom:16px;">
          <h3 style="font-size:13.5px;margin-bottom:4px;">Sales Forecast</h3>
          <p class="muted" style="font-size:11.5px;margin:0 0 14px;">Historical sales vs. predicted demand</p>
          <canvas id="forecastChart" height="120"></canvas>
          <div style="display:flex;gap:18px;margin-top:10px;font-size:11.5px;">
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:24px;height:3px;background:#FF5C85;display:inline-block;border-radius:2px;"></span>Historical</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:24px;height:2px;border-top:2px dashed #F2932A;display:inline-block;"></span>Predicted</span>
          </div>
        </div>

        <div class="card" style="margin-bottom:16px;">
          <h3 style="font-size:13.5px;margin-bottom:4px;">Revenue & Expense Projection</h3>
          <p class="muted" style="font-size:11.5px;margin:0 0 14px;">Monthly historical and forecasted figures</p>
          <canvas id="revExpChart" height="110"></canvas>
        </div>

        <div class="card" style="margin-bottom:16px;">
          <h3 style="font-size:14px;margin:0;display:flex;align-items:center;gap:8px;">📦 Inventory Risk & Reorder Forecast</h3>
          <p class="muted" style="font-size:11.5px;margin:4px 0 0;">Stockout risk scoring based on sell-through velocity</p>
          <div style="border-top:2px solid var(--pink-dark);margin:10px 0 12px;"></div>
          <div class="table-scroll">
            <table>
              <tr><th>Item</th><th>Category</th><th>Remaining</th><th>Daily use</th><th>Days left</th><th>Risk score</th><th>Level</th></tr>
              @foreach ($inventory as $row)
                @php $lvlBg = ['Critical'=>'#D63E63','High'=>'#F2932A','Medium'=>'#F2932A'][$row['level']]; @endphp
                <tr>
                  <td style="font-weight:600;">{{ $row['item'] }}</td>
                  <td>{{ $row['cat'] }}</td>
                  <td>{{ $row['rem'] }}</td>
                  <td>{{ $row['use'] }}</td>
                  <td>{{ $row['days'] }}</td>
                  <td>{{ $row['score'] }}</td>
                  <td><span style="background:{{ $lvlBg }};color:#fff;padding:3px 9px;border-radius:999px;font-size:10.5px;font-weight:700;">{{ $row['level'] }}</span></td>
                </tr>
              @endforeach
            </table>
          </div>
        </div>

        <div class="card" style="margin-bottom:16px;">
          <h3 style="font-size:14px;margin-bottom:4px;display:flex;align-items:center;gap:8px;">💡 Business Insights</h3>
          <p class="muted" style="font-size:11.5px;margin:0 0 12px;">AI-generated observations from the last 30 days</p>
          <div style="border-top:2px solid var(--pink-dark);margin-bottom:14px;"></div>
          @foreach ($insights as $i)
            <div style="display:flex;border-left:4px solid {{ str_starts_with($i['color'], '#') ? $i['color'] : 'var(--'.$i['color'].')' }};padding:12px 16px;background:var(--pink-pale);border-radius:0 8px 8px 0;margin-bottom:8px;">
              <div style="flex:1;">
                <div style="font-size:13px;font-weight:700;">{{ $i['icon'] }} {{ $i['title'] }}</div>
                <div style="font-size:11.5px;color:var(--muted);margin:4px 0;">{{ $i['body'] }}</div>
                <div style="font-size:11px;color:var(--pink-deep);font-style:italic;">→ {{ $i['action'] }}</div>
              </div>
            </div>
          @endforeach
        </div>

        <div class="card">
          <h3 style="font-size:14px;margin-bottom:4px;display:flex;align-items:center;gap:8px;">🔍 Anomaly Detection</h3>
          <p class="muted" style="font-size:11.5px;margin:0 0 12px;">Unusual sales days flagged by the model</p>
          <div style="border-top:2px solid var(--pink-dark);margin-bottom:10px;"></div>
          <div class="table-scroll">
            <table>
              <tr><th>Date</th><th>Category</th><th>Amount</th><th>Type</th></tr>
              @foreach ($anomalies as $row)
                <tr>
                  <td>{{ $row['date'] }}</td>
                  <td>{{ $row['cat'] }}</td>
                  <td>{{ $row['amt'] }}</td>
                  <td>{{ $row['type']==='Drop' ? '📉' : '📈' }} {{ $row['type'] }}</td>
                </tr>
              @endforeach
            </table>
          </div>
        </div>

      </div><!-- end pred -->

    </div>
  </div>

</div>

<script>
  function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebarOverlay').classList.toggle('active');
  }

  function switchAnTab(tab){
    document.getElementById('anContent-hist').style.display = tab==='hist' ? 'block' : 'none';
    document.getElementById('anContent-pred').style.display = tab==='pred' ? 'block' : 'none';
    document.getElementById('anTab-hist').style.background = tab==='hist' ? 'var(--pink-deep)' : '#fff';
    document.getElementById('anTab-hist').style.color = tab==='hist' ? '#fff' : 'var(--muted)';
    document.getElementById('anTab-pred').style.background = tab==='pred' ? 'var(--pink-deep)' : '#fff';
    document.getElementById('anTab-pred').style.color = tab==='pred' ? '#fff' : 'var(--muted)';
  }
  function toggleDrop(){ /* placeholder — real export wiring comes with the reporting module */ alert('Export — kakabit pa lang sa susunod na pass.'); }

  document.addEventListener('DOMContentLoaded', () => {
    new Chart(document.getElementById('salesTrendChart'), {
      type: 'line',
      data: {
        labels: @json($salesTrend['labels']),
        datasets: [{
          label: 'Sales (₱)', data: @json($salesTrend['data']),
          borderColor: '#FF5C85', backgroundColor: 'rgba(255,92,133,.12)',
          borderWidth: 2, pointBackgroundColor: '#FF5C85', fill: true, tension: .4
        }]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => '₱'+v.toLocaleString() }, grid: { color: '#EEE8F0' } }, x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('categoryChart'), {
      type: 'doughnut',
      data: {
        labels: @json(collect($salesByCategory)->pluck('label')),
        datasets: [{ data: @json(collect($salesByCategory)->pluck('pct')), backgroundColor: @json(collect($salesByCategory)->pluck('color')), borderWidth: 2 }]
      },
      options: { plugins: { legend: { display: false } }, cutout: '70%' }
    });

    new Chart(document.getElementById('attendanceChart'), {
      type: 'bar',
      data: {
        labels: @json($dailyAttendance['labels']),
        datasets: [
          { label: 'Present', data: @json($dailyAttendance['present']), backgroundColor: '#1FAE9E', borderRadius: 5 },
          { label: 'Absent', data: @json($dailyAttendance['absent']), backgroundColor: '#D63E63', borderRadius: 5 }
        ]
      },
      options: { plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: '#EEE8F0' } } } }
    });

    new Chart(document.getElementById('paymentChart'), {
      type: 'doughnut',
      data: {
        labels: @json(collect($paymentMethods)->pluck('label')),
        datasets: [{ data: @json(collect($paymentMethods)->pluck('pct')), backgroundColor: @json(collect($paymentMethods)->pluck('color')), borderWidth: 2 }]
      },
      options: { plugins: { legend: { display: false } }, cutout: '70%' }
    });

    new Chart(document.getElementById('forecastChart'), {
      type: 'line',
      data: {
        labels: @json($forecast['labels']),
        datasets: [
          { label: 'Historical', data: @json($forecast['historical']), borderColor: '#FF5C85', backgroundColor: 'rgba(255,92,133,.08)', borderWidth: 2, pointRadius: 3, fill: true, tension: .4, spanGaps: false },
          { label: 'Predicted', data: @json($forecast['predicted']), borderColor: '#F2932A', backgroundColor: 'rgba(242,147,42,.06)', borderWidth: 2, borderDash: [6,4], pointRadius: 4, pointBackgroundColor: '#F2932A', fill: true, tension: .4, spanGaps: false }
        ]
      },
      options: { plugins: { legend: { display: false } }, scales: { y: { ticks: { callback: v => '₱'+v.toLocaleString() }, grid: { color: '#EEE8F0' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } }
    });

    new Chart(document.getElementById('revExpChart'), {
      type: 'line',
      data: {
        labels: @json($revExp['labels']),
        datasets: [
          { label: 'Revenue', data: @json($revExp['revenue']), borderColor: '#FF5C85', borderWidth: 2, tension: .4, pointRadius: 3, spanGaps: false },
          { label: 'Expenses', data: @json($revExp['expenses']), borderColor: '#D63E63', borderWidth: 2, borderDash: [5,4], tension: .4, pointRadius: 3, spanGaps: false }
        ]
      },
      options: { plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 14 } } }, scales: { y: { ticks: { callback: v => '₱'+v.toLocaleString() }, grid: { color: '#EEE8F0' } }, x: { grid: { display: false } } } }
    });
  });
</script>

</body>
</html>