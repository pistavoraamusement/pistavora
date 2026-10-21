@extends('layouts.sidebar')

@section('title', 'Inventory')

@push('head')
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap" rel="stylesheet">
@endpush

@section('styles')
<style>
  .serif{font-family:'Source Serif 4', serif;}

  /* ── TOOLBAR ─────────────────────────────────────────────── */
  .toolbar{background:var(--card);padding:22px 26px;margin-bottom:20px;border-radius:16px;box-shadow:var(--shadow-sm);display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:18px;}
  .toolbar .eyebrow{font-size:.68rem;font-weight:700;color:var(--pink-deep);text-transform:uppercase;letter-spacing:.09em;margin-bottom:6px;}
  .toolbar h2{font-family:'Source Serif 4', serif;font-size:1.4rem;font-weight:700;color:var(--ink);}
  .toolbar-actions{display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
  .toolbar-actions input[type="text"]{padding:9px 12px;border:1px solid var(--line-strong);border-radius:10px;font-size:12.5px;font-family:'Inter',sans-serif;color:var(--ink-soft);background:var(--bg);min-width:180px;max-width:220px;}
  .toolbar-actions select{padding:9px 12px;border:1px solid var(--line-strong);border-radius:10px;font-size:12.5px;font-family:'Inter',sans-serif;color:var(--ink-soft);background:var(--bg);cursor:pointer;}
  .toolbar-actions input[type="text"]:focus, .toolbar-actions select:focus{outline:none;border-color:var(--pink);box-shadow:0 0 0 3px var(--pink-light);}

  .view-only-chip{display:inline-flex;align-items:center;gap:6px;background:var(--amber-light);color:var(--amber);padding:8px 14px;border-radius:999px;font-size:.74rem;font-weight:700;white-space:nowrap;}

  #uploadBtn{padding:11px 18px!important;background:#fff!important;color:var(--ink)!important;border:1px solid var(--line-strong)!important;border-radius:10px!important;cursor:pointer;font-weight:600;font-size:13px;font-family:'Inter',sans-serif;white-space:nowrap;transition:background .15s ease, border-color .15s ease;}
  #uploadBtn:hover{background:var(--bg)!important;border-color:var(--pink)!important;}
  #openImportBtn{padding:11px 18px;background:#fff;color:var(--ink);border:1px solid var(--line-strong);border-radius:10px;cursor:pointer;font-weight:600;font-size:13px;font-family:'Inter',sans-serif;white-space:nowrap;transition:background .15s ease, border-color .15s ease;}
  #openImportBtn:hover{background:var(--bg);border-color:var(--pink);}
  .print-btn{padding:11px 20px;background:var(--pink-deep);color:#fff;border:none;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;font-family:'Inter',sans-serif;white-space:nowrap;transition:background .15s ease, box-shadow .15s ease;}
  .print-btn:hover{background:var(--pink-dark);box-shadow:0 0 0 3px var(--pink-light);}

  /* ── STATS ROW ───────────────────────────────────────────── */
  .stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:20px;}
  .stat-card{background:var(--card);border-radius:16px;padding:18px 20px;box-shadow:var(--shadow-sm);transition:.15s;}
  .stat-card .label{font-size:.68rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:9px;}
  .stat-card .value{font-family:'Source Serif 4', serif;font-size:1.7rem;font-weight:700;color:var(--ink);line-height:1;}
  .stat-card .sub{font-size:11px;color:var(--muted);margin-top:7px;}
  .stat-card.low{cursor:pointer;user-select:none;}
  .stat-card.low:hover{box-shadow:var(--shadow-md);transform:translateY(-2px);}
  .stat-card.low.open{box-shadow:var(--shadow-md);}
  .stat-card.low .value{color:var(--amber);}
  .stat-card.low .sub{color:var(--amber);}
  .stat-card.low .sub i{margin-left:4px;transition:transform .15s;}
  .stat-card.low.open .sub i{transform:rotate(180deg);}

  /* ── LOW STOCK PREVIEW PANEL ─────────────────────────────── */
  .low-stock-preview{
    display:none;background:var(--card);border:1px solid var(--amber);border-left:4px solid var(--amber);
    border-radius:14px;padding:14px 18px;margin:-10px 0 20px;box-shadow:var(--shadow-sm);
    animation:slideDown .18s ease;
  }
  .low-stock-preview.active{display:block;}
  @keyframes slideDown{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}
  .low-stock-preview-header{
    display:flex;justify-content:space-between;align-items:center;font-weight:700;
    color:var(--amber);font-size:.85rem;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--line);
  }
  .low-stock-preview-header .close-btn{font-size:20px;}
  .low-stock-preview-list{display:flex;flex-direction:column;gap:6px;max-height:240px;overflow-y:auto;}
  .low-stock-preview-item{
    display:flex;justify-content:space-between;align-items:center;padding:9px 12px;
    background:var(--pink-pale);border-radius:9px;font-size:.83rem;cursor:pointer;transition:.15s;
  }
  .low-stock-preview-item:hover{background:var(--pink-light);}
  .low-stock-preview-item .lsp-name{font-weight:600;color:var(--ink);display:flex;flex-direction:column;}
  .low-stock-preview-item .lsp-cat{font-weight:400;color:var(--muted);font-size:.72rem;}
  .low-stock-preview-item .lsp-qty{font-weight:700;color:#ef4444;font-size:.78rem;white-space:nowrap;margin-left:10px;}
  .low-stock-preview-empty{color:var(--muted);font-size:.82rem;text-align:center;padding:12px;}

  /* ── TABLE SECTIONS (Stock Items + Tickets) ──────────────── */
  .report-section{background:var(--card);padding:20px;border-radius:16px;margin-bottom:20px;box-shadow:var(--shadow-sm);border:1px solid var(--line);display:flex;flex-direction:column;}
  .report-title{font-family:'Source Serif 4', serif;font-size:1.1rem;font-weight:700;margin-bottom:14px;padding-bottom:12px;border-bottom:2px solid var(--pink-light);color:var(--ink);display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
  .report-title .title-icon{color:var(--pink-deep);font-size:.95rem;}
  .count-pill{display:inline-flex;align-items:center;justify-content:center;min-width:26px;padding:2px 9px;border-radius:999px;background:var(--pink-light);color:var(--pink-dark);font-family:'Inter',sans-serif;font-size:.72rem;font-weight:700;}
  .report-hint{margin-left:auto;font-family:'Inter',sans-serif;font-size:.74rem;font-weight:500;color:var(--muted);}
  .table-wrapper{overflow:auto;max-height:470px;border:1px solid var(--line);border-radius:12px;background:#fff;}
  .empty-state{display:none;text-align:center;padding:26px 12px;color:var(--muted);font-size:.84rem;}

  .pagination-bar{display:flex;align-items:center;justify-content:space-between;padding-top:14px;gap:10px;flex-wrap:wrap;}
  .pagination-info{font-size:.78rem;color:var(--muted);font-weight:600;}
  .pagination-nav{display:flex;align-items:center;gap:4px;}
  .page-btn{min-width:32px;height:32px;padding:0 8px;border:1px solid var(--line);background:var(--card);border-radius:8px;cursor:pointer;font-size:.8rem;font-weight:700;color:var(--ink-soft);transition:.15s;}
  .page-btn:hover:not(:disabled){border-color:var(--pink);color:var(--pink-dark);}
  .page-btn.active{background:var(--pink-deep);color:#fff;border-color:transparent;}
  .page-btn:disabled{opacity:.4;cursor:not-allowed;}
  .page-ellipsis{padding:0 4px;color:var(--muted);font-size:.8rem;}

  /* ── DATA TABLES (shared by Stock Items + Tickets) ───────── */
  .report-table{width:100%;border-collapse:separate;border-spacing:0;font-family:'Inter',sans-serif;}
  #stockTable{min-width:1020px;}
  #ticketTable{min-width:520px;}

  /* header: soft tint + dark text, readable and not heavy */
  .report-table thead{position:sticky;top:0;z-index:5;}
  .report-table thead th{
    background:var(--pink-pale,#FAF5FB);color:var(--ink-soft);
    padding:12px 14px;text-align:left;vertical-align:middle;
    font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;
    border-bottom:2px solid var(--pink-light);white-space:nowrap;
  }

  /* body cells: one font-size, one vertical rhythm */
  .report-table td{
    padding:11px 14px;vertical-align:middle;text-align:left;
    border-bottom:1px solid var(--line);
    font-size:.85rem;line-height:1.35;color:var(--ink-soft);
  }
  .report-table tbody tr:last-child td{border-bottom:none;}
  .report-table tbody tr:nth-child(even){background:rgba(26,21,35,.018);}
  .report-table tbody tr:hover{background:var(--pink-pale);}
  .report-table tr.row-highlight{background:var(--pink-light)!important;animation:pulseHighlight 1.2s ease;}
  @keyframes pulseHighlight{0%,100%{background:var(--pink-light);}50%{background:var(--pink);}}

  /* column 1 = product / ticket name */
  .report-table tbody td:first-child{font-weight:600;color:var(--ink);font-size:.88rem;}

  /* column 2 = category (+ zone tag) */
  .report-table tbody td:nth-child(2){white-space:nowrap;color:var(--ink-soft);}

  /* STOCK TABLE alignment: 3-7 numeric centered · 8 price right · 9 status centered · 10 actions centered */
  #stockTable th:nth-child(n+3):nth-child(-n+7),
  #stockTable td:nth-child(n+3):nth-child(-n+7){text-align:center;}
  #stockTable th:nth-child(8),  #stockTable td:nth-child(8){text-align:right;}
  #stockTable th:nth-child(9),  #stockTable td:nth-child(9){text-align:center;}
  #stockTable th:nth-child(10), #stockTable td:nth-child(10){text-align:center;}

  /* TICKET TABLE alignment: 3 price right · 4 actions centered */
  #ticketTable th:nth-child(3), #ticketTable td:nth-child(3){text-align:right;}
  #ticketTable th:nth-child(4), #ticketTable td:nth-child(4){text-align:center;}

  /* the header of "Short / Over" etc. should not wrap awkwardly */
  #stockTable th:nth-child(7){min-width:110px;}
  #stockTable th:nth-child(6){min-width:96px;}

  .zone-tag{display:inline-block;font-size:.66rem;font-weight:700;line-height:1;padding:4px 8px;border-radius:999px;background:var(--pink-light);color:var(--pink-dark);margin-left:6px;vertical-align:middle;}

  /* numbers */
  .cell-num{font-variant-numeric:tabular-nums;font-weight:500;color:var(--ink);}
  .cell-stock{font-weight:700;}
  .cell-sold{color:var(--muted);}
  .cell-beginning{color:var(--muted);}
  .cell-price{font-variant-numeric:tabular-nums;font-weight:600;color:var(--ink);white-space:nowrap;}

  /* status badge: soft tint + dark text + dot (better contrast than white-on-color) */
  .status-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 11px;border-radius:999px;font-size:.7rem;font-weight:700;line-height:1;white-space:nowrap;}
  .status-badge::before{content:"";width:6px;height:6px;border-radius:50%;background:currentColor;flex-shrink:0;}
  .status-good{background:#DCFCE7;color:#166534;}
  .status-low{background:#FEE2E2;color:#B91C1C;}

  /* actual count input */
  .count-input{width:76px;height:34px;padding:0 8px;border:1px solid var(--line-strong);border-radius:8px;font-size:.84rem;font-weight:600;font-family:'Inter',sans-serif;font-variant-numeric:tabular-nums;color:var(--ink);background:#fff;text-align:center;transition:border-color .15s, box-shadow .15s;}
  .count-input::placeholder{color:var(--muted);font-weight:400;}
  .count-input:hover:not([readonly]){border-color:var(--pink);}
  .count-input:focus{outline:none;border-color:var(--pink);box-shadow:0 0 0 3px var(--pink-light);}
  .count-input[readonly]{background:var(--bg);color:var(--ink-soft);cursor:not-allowed;}

  /* variance badges */
  .var-badge{display:inline-block;padding:5px 11px;border-radius:999px;font-size:.7rem;font-weight:700;line-height:1;white-space:nowrap;}
  .var-ok{background:#D9F5EF;color:#0F766E;}
  .var-short{background:#FEE2E2;color:#B91C1C;}
  .var-over{background:#FEF3C7;color:#B45309;}
  .var-none{color:var(--muted);font-size:.85rem;}

  /* ── ROW ACTIONS ─────────────────────────────────────────── */
  .row-actions{display:flex;align-items:center;justify-content:center;gap:6px;}
  .action-btn{width:30px;height:30px;padding:0;margin:0;border:1px solid transparent;border-radius:8px;display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:.78rem;transition:background .15s, color .15s, transform .12s, box-shadow .15s;}
  .action-btn i{pointer-events:none;}
  .action-btn:hover{transform:translateY(-1px);}
  .action-btn:active{transform:scale(.94);}
  .action-btn:focus-visible{outline:3px solid rgba(99,102,241,.45);outline-offset:2px;}
  .restock-btn{background:var(--amber-light)!important;color:var(--amber)!important;}
  .restock-btn:hover{background:var(--amber)!important;color:#fff!important;box-shadow:0 4px 10px rgba(180,83,9,.28);}
  .edit-btn{background:#EDE9FE;color:#6D28D9;}
  .edit-btn:hover{background:#7C5CE0;color:#fff;box-shadow:0 4px 10px rgba(124,92,224,.3);}
  .delete-btn{background:var(--danger-soft);color:var(--danger);}
  .delete-btn:hover{background:#ef4444;color:#fff;box-shadow:0 4px 10px rgba(239,68,68,.3);}

  .act-tooltip{position:fixed;z-index:10050;background:#1F2937;color:#fff;font-size:.74rem;font-weight:500;line-height:1;padding:7px 10px;border-radius:7px;white-space:nowrap;box-shadow:0 6px 18px rgba(0,0,0,.25);opacity:0;pointer-events:none;transition:opacity .12s;}
  .act-tooltip.show{opacity:1;}

  /* ── TOAST ───────────────────────────────────────────────── */
  #toastStack{position:fixed;top:22px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;align-items:center;gap:10px;width:100%;max-width:420px;padding:0 16px;pointer-events:none;}
  .toast{pointer-events:auto;width:100%;display:flex;align-items:flex-start;gap:12px;background:#fff;color:var(--ink);padding:14px 16px;border-radius:14px;border-left:4px solid var(--ink);box-shadow:var(--shadow-md);font-size:13px;font-weight:500;line-height:1.4;opacity:0;transform:translateY(-16px) scale(.97);animation:toastIn .35s cubic-bezier(.34,1.56,.64,1) forwards;position:relative;overflow:hidden;}
  .toast.hide{animation:toastOut .28s ease forwards;}
  .toast .toast-icon{flex-shrink:0;width:26px;height:26px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:13px;color:#fff;background:var(--ink-soft);}
  .toast.toast-success{border-left-color:var(--present);}
  .toast.toast-success .toast-icon{background:var(--present);}
  .toast.toast-error{border-left-color:#ef4444;}
  .toast.toast-error .toast-icon{background:#ef4444;}
  .toast .toast-body{flex:1;min-width:0;padding-top:2px;}
  .toast .toast-title{display:block;font-weight:700;font-size:12.5px;margin-bottom:2px;color:var(--ink);}
  .toast .toast-msg{display:block;color:var(--ink-soft);font-size:12.5px;word-break:break-word;}
  .toast .toast-close{flex-shrink:0;background:none;border:none;cursor:pointer;color:var(--muted);font-size:13px;padding:2px;line-height:1;margin-top:1px;}
  .toast .toast-close:hover{color:var(--ink);}
  .toast .toast-bar{position:absolute;left:0;bottom:0;height:3px;border-radius:0 0 0 14px;background:currentColor;opacity:.35;animation:toastShrink 3s linear forwards;}
  @keyframes toastIn{to{opacity:1;transform:translateY(0) scale(1);}}
  @keyframes toastOut{to{opacity:0;transform:translateY(-12px) scale(.96);}}
  @keyframes toastShrink{from{width:100%;}to{width:0%;}}

  /* ── MODALS ──────────────────────────────────────────────── */
  .modal{display:none;position:fixed;z-index:2000;inset:0;background:rgba(26,21,35,.55);align-items:center;justify-content:center;}
  .modal.active{display:flex;}
  .modal-content{background:var(--card);padding:26px;border-radius:18px;box-shadow:var(--shadow-md);width:90%;max-width:460px;animation:slideIn .25s ease;}
  @keyframes slideIn{from{transform:translateY(-30px);opacity:0}to{transform:translateY(0);opacity:1}}
  .modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;border-bottom:2px solid var(--pink-light);padding-bottom:14px;}
  .modal-header h2{margin:0;color:var(--ink);font-size:1.05rem;font-weight:700;display:flex;align-items:center;gap:8px;font-family:'Source Serif 4', serif;}
  .close-btn{background:none;border:none;font-size:24px;cursor:pointer;color:var(--muted);line-height:1;}
  .close-btn:hover{color:var(--ink);}
  .modal-body{margin-bottom:18px;}
  .form-group{margin-bottom:14px;}
  .form-group label{display:block;margin-bottom:6px;font-weight:600;color:var(--ink);font-size:.82rem;}
  .form-group input, .form-group select{width:100%;padding:10px 12px;border:1px solid var(--line);border-radius:9px;font-size:.85rem;font-family:'Inter',sans-serif;box-sizing:border-box;color:var(--ink);}
  .form-group input:focus, .form-group select:focus{outline:none;border-color:var(--pink);box-shadow:0 0 0 3px var(--pink-light);}
  .form-group .field-hint{display:block;margin-top:6px;font-size:.74rem;color:var(--muted);}
  .form-row{display:flex;gap:10px;}
  .form-row .form-group{flex:1;}
  .checkbox-row{display:flex;align-items:center;gap:8px;margin-bottom:14px;}
  .checkbox-row input{width:auto;}
  .checkbox-row label{margin:0;}
  .modal-footer{display:flex;gap:10px;justify-content:flex-end;}
  .modal-footer button{padding:10px 20px;border:none;border-radius:9px;cursor:pointer;font-weight:700;font-size:.83rem;transition:.15s;}
  .btn-submit{background:var(--pink-deep);color:#fff;}
  .btn-submit:hover{filter:brightness(1.05);transform:translateY(-1px);}
  .btn-cancel{background:var(--pink-pale);color:var(--ink-soft);}
  .btn-cancel:hover{background:var(--pink-light);}
  .btn-danger{background:#ef4444;color:#fff;}
  .btn-danger:hover{background:#dc2626;}
  .add-cat-link{display:inline-block;margin-top:6px;font-size:.76rem;font-weight:700;color:var(--pink-dark);cursor:pointer;}
  .add-cat-link:hover{text-decoration:underline;}

  .loading-overlay{
    position:fixed;inset:0;background:rgba(243,239,243,.85);backdrop-filter:blur(2px);
    z-index:5000;display:flex;flex-direction:column;align-items:center;justify-content:center;
    gap:14px;opacity:0;visibility:hidden;transition:opacity .2s ease;
  }
  .loading-overlay.active{opacity:1;visibility:visible;}
  .loading-spinner{
    width:44px;height:44px;border-radius:50%;
    border:4px solid var(--pink-light);border-top-color:var(--pink);
    animation:spin .7s linear infinite;
  }
  .loading-text{font-size:.85rem;font-weight:700;color:var(--pink-dark);letter-spacing:.02em;}
  @keyframes spin{to{transform:rotate(360deg);}}

  @media(max-width:1000px){
    .stats-row{grid-template-columns:1fr 1fr;}
  }
  @media(max-width:900px){
    .toolbar h2{font-size:1.05rem;}
    .toolbar-actions{width:100%;}
    .toolbar-actions input[type="text"]{max-width:none;width:100%;}
    .toolbar-actions select{width:100%;}
    .report-hint{display:none;}
  }
  @media(max-width:520px){
    .stats-row{grid-template-columns:1fr;}
  }
</style>

{{-- VIEW-ONLY MODE (cashier): edit controls are hidden but stay in the DOM
     so js/inventory.js never throws on missing elements. --}}
@php
  $canManage = in_array(strtolower((string) session('role')), ['admin', 'tl', 'manager']);
@endphp
@unless($canManage)
<style>
  .manage-only{display:none!important;}
</style>
@endunless
@endsection

@section('content')

  @php
    // Cashier = view only.
    $canManage = in_array(strtolower((string) session('role')), ['admin', 'tl', 'manager']);

    // Split the product list:
    //   Stock Items = categories that track stock
    //   Tickets     = categories that do NOT track stock (rides, passes, services)
    $stockItems  = $products->filter(function ($p) { return (int) $p->is_stock_tracked === 1; })->values();
    $ticketItems = $products->filter(function ($p) { return (int) $p->is_stock_tracked !== 1; })->values();
  @endphp

  <!-- ══ TOOLBAR ════════════════════════════════════════════ -->
  <div class="toolbar no-print">
    <div>
      <div class="eyebrow">Lipa Branch &middot; Inventory</div>
      <h2 style="font-family: Arial, Helvetica, sans-serif">Inventory Management</h2>
    </div>

    <div class="toolbar-actions">
      @unless($canManage)
        <span class="view-only-chip"><i class="fas fa-eye"></i> View only</span>
      @endunless

      <input type="text" id="searchInput" placeholder="Search products or tickets…">

      <select id="zoneFilter">
        <option value="all">All Zones</option>
        @foreach($zones as $zone)
          <option value="{{ $zone }}">{{ $zone }}</option>
        @endforeach
      </select>

      <select id="categoryFilter">
        <option value="all">All Categories</option>
        @foreach($categoriesByZone as $zone => $cats)
          <optgroup label="{{ $zone }}">
            @foreach($cats as $cat)
              <option value="{{ $cat->category_id }}" data-zone="{{ $cat->zone }}">{{ $cat->category_name }}</option>
            @endforeach
          </optgroup>
        @endforeach
      </select>

      <form id="exportForm" class="manage-only" method="post" action="{{ route('inventory.export') }}" style="margin:0;">
        @csrf
        <input type="hidden" name="action" value="export_products">
        <button type="submit" id="uploadBtn"><i class="fas fa-file-export"></i> Export</button>
      </form>

      <input type="file" id="importFileInput" accept=".csv" style="display:none">
      <button id="openImportBtn" type="button" class="manage-only"><i class="fas fa-file-import"></i> Import CSV</button>

      <button id="openAddBtn" type="button" class="print-btn manage-only"><i class="fas fa-plus"></i> Add Item</button>
    </div>
  </div>

  <!-- ══ STATS ══════════════════════════════════════════════ -->
  <div class="stats-row no-print">
    <div class="stat-card">
      <div class="label">Stock Items</div>
      <div class="value" id="statItems">0</div>
      <div class="sub">Products with tracked stock</div>
    </div>
    <div class="stat-card">
      <div class="label">Tickets</div>
      <div class="value" id="statTickets">0</div>
      <div class="sub">Rides, passes &amp; services</div>
    </div>
    <div class="stat-card low" id="lowStockCard" title="Click to preview low stock items">
      <div class="label">Low Stock Items</div>
      <div class="value" id="lowStocks">0</div>
      <div class="sub">Need restocking <i class="fas fa-chevron-down"></i></div>
    </div>
    <div class="stat-card">
      <div class="label">Total Stock</div>
      <div class="value" id="totalStocks">0</div>
      <div class="sub">Units available</div>
    </div>
  </div>

  <!-- ══ LOW STOCK PREVIEW PANEL ════════════════════════════ -->
  <div class="low-stock-preview" id="lowStockPreview">
    <div class="low-stock-preview-header">
      <span><i class="fas fa-triangle-exclamation"></i> Low Stock Items — click one to jump to it</span>
      <button type="button" class="close-btn" id="closeLowStockPreview">&times;</button>
    </div>
    <div class="low-stock-preview-list" id="lowStockPreviewList"></div>
  </div>

  <!-- ══ TABLE 1: STOCK ITEMS ═══════════════════════════════ -->
  <div class="report-section" id="stockSection">
    <div class="report-title">Stock Items
      <span class="count-pill" id="stockCount">0</span>
      <span class="report-hint">Products with tracked stock</span>
    </div>

    <div class="table-wrapper">
      <table class="report-table" id="stockTable">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Beginning</th>
            <th>Sold</th>
            <th>Stock</th>
            <th>Actual Count</th>
            <th>Short / Over</th>
            <th>Price</th>
            <th>Status</th>
            @if($canManage)
              <th>Actions</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($stockItems as $p)
            @php
              $threshold  = intval($p->low_stock_threshold ?? 20);
              $stock      = intval($p->stock);
              $isLow      = $stock <= $threshold;
              $priceRaw   = number_format(floatval($p->price ?? 0), 2, '.', '');
              $priceShown = number_format(floatval($p->price ?? 0), 2);

              // Beginning / Sold / Actual Count / Variance
              $beginning  = $p->beginning_stock !== null ? intval($p->beginning_stock) : null;
              $soldQty    = intval($sold[$p->product_id] ?? 0);
              $actual     = $p->actual_count !== null ? intval($p->actual_count) : null;
              $variance   = $actual !== null ? $actual - $stock : null;
            @endphp
            <tr data-id="{{ $p->product_id }}"
                data-name="{{ $p->product_name }}"
                data-category-id="{{ $p->category_id }}"
                data-cat="{{ $p->category_name }}"
                data-zone="{{ $p->zone }}"
                data-tracked="1"
                data-stock="{{ $stock }}"
                data-price="{{ $priceRaw }}"
                data-threshold="{{ $threshold }}"
                data-beginning="{{ $beginning }}"
                data-sold="{{ $soldQty }}"
                data-actual="{{ $actual }}">
              <td>{{ $p->product_name }}</td>
              <td>{{ $p->category_name }} <span class="zone-tag">{{ $p->zone }}</span></td>
              <td class="cell-num cell-beginning">{{ $beginning ?? '—' }}</td>
              <td class="cell-num cell-sold">{{ $soldQty }}</td>
              <td class="cell-num cell-stock">{{ $stock }}</td>
              <td class="cell-num cell-actual">
                <input type="number" min="0" class="count-input"
                       data-id="{{ $p->product_id }}"
                       value="{{ $actual }}"
                       placeholder="—"
                       @unless($canManage) readonly @endunless>
              </td>
              <td class="cell-num cell-variance" id="var-{{ $p->product_id }}">
                @if($variance === null)
                  <span class="var-none">—</span>
                @elseif($variance === 0)
                  <span class="var-badge var-ok">Balanced</span>
                @elseif($variance < 0)
                  <span class="var-badge var-short">Short {{ abs($variance) }}</span>
                @else
                  <span class="var-badge var-over">Over {{ $variance }}</span>
                @endif
              </td>
              <td class="cell-price">₱{{ $priceShown }}</td>
              <td class="cell-status">
                <span class="status-badge {{ $isLow ? 'status-low' : 'status-good' }}">{{ $isLow ? 'Low Stock' : 'In Stock' }}</span>
              </td>
              @if($canManage)
                <td>
                  <div class="row-actions">
                    <button type="button" class="action-btn restock-btn" data-action="restock" data-tip="Restock" aria-label="Restock {{ $p->product_name }}"><i class="fas fa-plus-circle"></i></button>
                    <button type="button" class="action-btn edit-btn" data-action="edit" data-tip="Edit product" aria-label="Edit {{ $p->product_name }}"><i class="fas fa-pen"></i></button>
                    <button type="button" class="action-btn delete-btn" data-action="delete" data-tip="Delete product" aria-label="Delete {{ $p->product_name }}"><i class="fas fa-trash-can"></i></button>
                  </div>
                </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="empty-state" id="stockEmpty">No stock items match your filters.</div>
    </div>

    <div class="pagination-bar" id="stockPager"></div>
  </div>

  <!-- ══ TABLE 2: TICKETS ═══════════════════════════════════ -->
  <div class="report-section" id="ticketSection">
    <div class="report-title">Tickets
      <span class="count-pill" id="ticketCount">0</span>
      <span class="report-hint">Rides, passes &amp; services (no stock tracking)</span>
    </div>

    <div class="table-wrapper">
      <table class="report-table" id="ticketTable">
        <thead>
          <tr>
            <th>Ticket</th>
            <th>Category</th>
            <th>Price</th>
            @if($canManage)
              <th>Actions</th>
            @endif
          </tr>
        </thead>
        <tbody>
          @foreach($ticketItems as $p)
            @php
              $priceRaw   = number_format(floatval($p->price ?? 0), 2, '.', '');
              $priceShown = number_format(floatval($p->price ?? 0), 2);
            @endphp
            <tr data-id="{{ $p->product_id }}"
                data-name="{{ $p->product_name }}"
                data-category-id="{{ $p->category_id }}"
                data-cat="{{ $p->category_name }}"
                data-zone="{{ $p->zone }}"
                data-tracked="0"
                data-stock=""
                data-price="{{ $priceRaw }}"
                data-threshold="{{ intval($p->low_stock_threshold ?? 20) }}">
              <td>{{ $p->product_name }}</td>
              <td>{{ $p->category_name }} <span class="zone-tag">{{ $p->zone }}</span></td>
              <td class="cell-price">₱{{ $priceShown }}</td>
              @if($canManage)
                <td>
                  <div class="row-actions">
                    <button type="button" class="action-btn edit-btn" data-action="edit" data-tip="Edit ticket" aria-label="Edit {{ $p->product_name }}"><i class="fas fa-pen"></i></button>
                    <button type="button" class="action-btn delete-btn" data-action="delete" data-tip="Delete ticket" aria-label="Delete {{ $p->product_name }}"><i class="fas fa-trash-can"></i></button>
                  </div>
                </td>
              @endif
            </tr>
          @endforeach
        </tbody>
      </table>
      <div class="empty-state" id="ticketEmpty">No tickets match your filters.</div>
    </div>

    <div class="pagination-bar" id="ticketPager"></div>
  </div>

  <!-- ══ ADD MODAL ══════════════════════════════════════════ -->
  <div id="addProductModal" class="modal manage-only">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-plus-circle" style="color:var(--pink)"></i> Add New Item</h2>
        <button class="close-btn" id="closeAddBtn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Name <span style="color:#ef4444">*</span></label>
          <input type="text" id="addName" placeholder="e.g. Demi Apple or Skataya Ticket">
        </div>
        <div class="form-group">
          <label>Category <span style="color:#ef4444">*</span></label>
          <select id="addCategory">
            <option value="">Select Category</option>
            @foreach($categoriesByZone as $zone => $cats)
              <optgroup label="{{ $zone }}">
                @foreach($cats as $cat)
                  <option value="{{ $cat->category_id }}" data-tracked="{{ $cat->is_stock_tracked }}" data-zone="{{ $cat->zone }}">{{ $cat->category_name }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <span class="field-hint">Categories that track stock go to <strong>Stock Items</strong>; the rest go to <strong>Tickets</strong>.</span>
          <span class="add-cat-link" id="openAddCatFromAdd">+ Add new category</span>
        </div>
        <div class="form-group">
          <label>Stock Quantity <span style="color:#ef4444">*</span></label>
          <input type="number" id="addStock" placeholder="e.g. 100" min="0">
          <small id="addStockNote" style="color:var(--muted);display:none">⚠️ This category does not track stock (it will be listed as a ticket).</small>
        </div>
        <div class="form-group">
          <label>Price <span style="color:var(--muted);font-weight:400">(optional)</span></label>
          <input type="number" id="addPrice" placeholder="e.g. 85.00" step="0.01" min="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelAddBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitAddBtn">Add Item</button>
      </div>
    </div>
  </div>

  <!-- ══ EDIT MODAL ═════════════════════════════════════════ -->
  <div id="editProductModal" class="modal manage-only">
    <div class="modal-content">
      <div class="modal-header">
        <h2><i class="fas fa-edit" style="color:#7C5CE0"></i> Edit Item</h2>
        <button class="close-btn" id="closeEditBtn">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="editProductId">
        <div class="form-group">
          <label>Name</label>
          <input type="text" id="editProductName">
        </div>
        <div class="form-group">
          <label>Category</label>
          <select id="editProductCategory">
            <option value="">Select Category</option>
            @foreach($categoriesByZone as $zone => $cats)
              <optgroup label="{{ $zone }}">
                @foreach($cats as $cat)
                  <option value="{{ $cat->category_id }}" data-tracked="{{ $cat->is_stock_tracked }}" data-zone="{{ $cat->zone }}">{{ $cat->category_name }}</option>
                @endforeach
              </optgroup>
            @endforeach
          </select>
          <span class="add-cat-link" id="openAddCatFromEdit">+ Add new category</span>
        </div>
        <div class="form-group" id="editStockGroup">
          <label>Stock Quantity</label>
          <input type="number" id="editProductStock" placeholder="e.g. 100" min="0">
          <small id="editStockNote" style="color:var(--muted);display:none">⚠️ This category does not track stock (it will be listed as a ticket).</small>
        </div>
        <div class="form-group">
          <label>Price</label>
          <input type="number" id="editProductPrice" step="0.01" min="0">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelEditBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitEditBtn">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- ══ ADD CATEGORY MODAL ═════════════════════════════════ -->
  <div id="addCategoryModal" class="modal manage-only">
    <div class="modal-content" style="max-width:400px">
      <div class="modal-header">
        <h2><i class="fas fa-tags" style="color:var(--pink)"></i> Add New Category</h2>
        <button class="close-btn" id="closeAddCatBtn">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Category Name <span style="color:#ef4444">*</span></label>
          <input type="text" id="newCatName" placeholder="e.g. Souvenirs">
        </div>
        <div class="form-group">
          <label>Zone <span style="color:#ef4444">*</span></label>
          <input type="text" id="newCatZone" list="zoneDatalist" placeholder="e.g. DinoLakbay">
          <datalist id="zoneDatalist">
            @foreach($zones as $zone)
              <option value="{{ $zone }}">
            @endforeach
          </datalist>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Type <span style="color:#ef4444">*</span></label>
            <select id="newCatType">
              <option value="merchandise">Merchandise</option>
              <option value="ride">Ride / Pass</option>
              <option value="service">Service</option>
            </select>
          </div>
        </div>
        <div class="checkbox-row">
          <input type="checkbox" id="newCatTracked" checked>
          <label for="newCatTracked">Track stock for this category (untick for tickets)</label>
        </div>
      </div>
      <div id="addCatError" style="color:#ef4444;font-size:.8rem;margin-bottom:8px"></div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelAddCatBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitAddCatBtn">Save Category</button>
      </div>
    </div>
  </div>

  <!-- ══ DELETE CONFIRM MODAL ═══════════════════════════════ -->
  <div id="deleteConfirmModal" class="modal manage-only">
    <div class="modal-content" style="max-width:380px">
      <div class="modal-header">
        <h2>🗑️ Confirm Delete</h2>
        <button class="close-btn" id="closeDeleteBtn">&times;</button>
      </div>
      <div class="modal-body" style="text-align:center">
        <p style="font-size:.9rem;color:var(--ink-soft);margin-bottom:6px">
          Are you sure you want to delete<br>
          <strong id="deleteProductName"></strong>?
        </p>
        <p style="color:#ef4444;font-size:.8rem">This cannot be undone.</p>
      </div>
      <div class="modal-footer" style="justify-content:center">
        <button type="button" class="btn-cancel" id="cancelDeleteBtn">Cancel</button>
        <button type="button" class="btn-danger" id="confirmDeleteBtn">Yes, Delete</button>
      </div>
    </div>
  </div>

  <!-- ══ RESTOCK MODAL ══════════════════════════════════════ -->
  <div id="restockModal" class="modal manage-only">
    <div class="modal-content" style="max-width:380px">
      <div class="modal-header">
        <h2><i class="fas fa-plus-circle" style="color:var(--amber)"></i> Restock Product</h2>
        <button class="close-btn" id="closeRestockBtn">&times;</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="restockProductId">
        <p style="margin-bottom:12px;color:var(--ink-soft);font-size:.87rem">
          Restocking: <strong id="restockProductName"></strong><br>
          <span style="font-size:.78rem;color:var(--muted)">Current stock: <strong id="restockCurrentStock"></strong></span>
        </p>
        <div class="form-group">
          <label>Quantity to Add <span style="color:#ef4444">*</span></label>
          <input type="number" id="restockQty" placeholder="e.g. 50" min="1">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn-cancel" id="cancelRestockBtn">Cancel</button>
        <button type="button" class="btn-submit" id="submitRestockBtn" style="background:var(--amber)">Add Stock</button>
      </div>
    </div>
  </div>

  <div id="toastStack" aria-live="polite"></div>

  <!-- ══ LOADING OVERLAY ════════════════════════════════════ -->
  <div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner"></div>
    <div class="loading-text" id="loadingText">Processing…</div>
  </div>

@endsection

@push('scripts')
<script>
  // Passed to js/inventory.js so rows created in JS match the server-rendered ones
  window.INVENTORY_CAN_MANAGE = @json($canManage);
  window.INVENTORY_CSRF = @json(csrf_token());
</script>

<script>
  // Hover labels for the row action icons
  (function () {
    const tip = document.createElement('div');
    tip.className = 'act-tooltip';
    tip.setAttribute('role', 'tooltip');
    document.body.appendChild(tip);

    const SEL = '.row-actions .action-btn';

    function show(el) {
      const text = el.dataset.tip;
      if (!text) return;
      tip.textContent = text;
      tip.classList.add('show');

      const r = el.getBoundingClientRect();
      const t = tip.getBoundingClientRect();
      let top = r.top - t.height - 8;
      if (top < 8) top = r.bottom + 8;
      let left = r.left + r.width / 2 - t.width / 2;
      left = Math.max(8, Math.min(left, window.innerWidth - t.width - 8));
      tip.style.top = top + 'px';
      tip.style.left = left + 'px';
    }
    function hide() { tip.classList.remove('show'); }

    document.addEventListener('mouseover', (e) => { const el = e.target.closest(SEL); if (el) show(el); });
    document.addEventListener('mouseout',  (e) => { const el = e.target.closest(SEL); if (el && !el.contains(e.relatedTarget)) hide(); });
    document.addEventListener('focusin',   (e) => { const el = e.target.closest(SEL); if (el) show(el); });
    document.addEventListener('focusout',  hide);
    document.addEventListener('click',     hide);
    window.addEventListener('scroll', hide, true);
  })();
</script>

<script>
  // ─── SAVE PHYSICAL COUNT ───
  // Event delegation: works for rows rendered by Blade and rows added by JS.
  document.addEventListener('change', async (e) => {
    const inp = e.target.closest('.count-input');
    if (!inp || inp.readOnly || inp.value === '') return;

    const cell = document.getElementById('var-' + inp.dataset.id);
    const csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || window.INVENTORY_CSRF;

    const badge = (cls, text) => `<span class="var-badge ${cls}">${text}</span>`;

    try {
      const res = await fetch('/inventory/count', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({ id: inp.dataset.id, actual_count: inp.value })
      });
      const d = await res.json();

      if (d.status !== 'success') {
        cell.innerHTML = `<span class="var-none">${d.message || 'Error'}</span>`;
        return;
      }

      const v = d.variance;
      const tr = inp.closest('tr');
      if (tr) tr.dataset.actual = inp.value;

      cell.innerHTML = v === 0
        ? badge('var-ok', 'Balanced')
        : v < 0
          ? badge('var-short', 'Short ' + Math.abs(v))
          : badge('var-over', 'Over ' + v);
    } catch (err) {
      cell.innerHTML = '<span class="var-none">Failed to save</span>';
    }
  });
</script>
<script src="{{ asset('js/inventory.js') }}"></script>
@endpush