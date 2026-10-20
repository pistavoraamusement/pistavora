@extends('layouts.sidebar')

@section('title', 'Reservation and Bookings')

@push('head')
    <link
        href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        :root {
            /* status colors (page only) */
            --confirmed: #0F9D8A;
            --confirmed-text: #0B7C6D;
            --confirmed-soft: #E6F6F3;
            --pending: #B7791F;
            --pending-soft: #FEF4E0;
            --paid: #2563EB;
            --paid-soft: #E8F0FE;
            --cancelled: #DC2650;
            --cancelled-soft: #FDE8ED;
            --text-sub: #625C70;
            --text-faint: #9A94A8;

            --r-sm: 8px;
            --r-md: 10px;
            --r-lg: 14px;
        }

        .serif { font-family: 'Source Serif 4', serif; }

        /* ===== TOOLBAR ===== */
        .toolbar {
            background: var(--card);
            border: 1px solid var(--line);
            padding: 20px 24px;
            margin-bottom: 16px;
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .toolbar .eyebrow { font-size: .68rem; font-weight: 700; color: var(--pink-deep); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px; }
        .toolbar h2 { font-family: 'Source Serif 4', serif; font-size: 1.45rem; font-weight: 700; color: var(--ink); line-height: 1.2; }
        .toolbar p.sub { font-size: .8rem; color: var(--text-sub); margin-top: 4px; }
        .toolbar-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

        /* ===== FILTER BAR ===== */
        .filter-bar {
            background: var(--card);
            border: 1px solid var(--line);
            padding: 14px 20px;
            margin-bottom: 16px;
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm);
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-bar .search-wrap { position: relative; flex: 1; min-width: 220px; }
        .filter-bar .search-wrap i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 12.5px; pointer-events: none; }

        .filter-bar input[type="text"],
        .filter-bar select {
            height: 40px;
            border: 1px solid var(--line-strong);
            border-radius: var(--r-md);
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }

        .filter-bar input[type="text"] { width: 100%; padding: 0 12px 0 36px; }
        .filter-bar select { padding: 0 12px; cursor: pointer; }

        .filter-bar input[type="text"]:focus,
        .filter-bar select:focus { outline: none; border-color: var(--pink); box-shadow: 0 0 0 3px var(--pink-light); }

        .filter-clear {
            height: 40px;
            padding: 0 16px;
            background: #fff;
            color: var(--ink-soft);
            border: 1px solid var(--line-strong);
            border-radius: var(--r-md);
            cursor: pointer;
            font-weight: 600;
            font-size: 12.5px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: .15s;
        }

        .filter-clear:hover { background: var(--pink-pale); border-color: var(--pink); color: var(--pink-deep); }
        .filter-count { font-size: 12px; color: var(--text-sub); white-space: nowrap; }

        /* ===== TABLE BOX ===== */
        .table-box {
            background: var(--card);
            border: 1px solid var(--line);
            padding: 0;
            border-radius: var(--r-lg);
            box-shadow: var(--shadow-sm);
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .sample-note {
            font-size: .78rem;
            color: var(--pink-deep);
            background: var(--pink-pale);
            border-bottom: 1px solid var(--pink-light);
            padding: 11px 20px;
            font-weight: 600;
        }

        /* Fixed layout + colgroup widths = every row lines up, nothing wraps */
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            min-width: 1000px;
            font-size: 13px;
        }

        th, td {
            padding: 12px 16px;
            text-align: left;
            border-bottom: 1px solid var(--line);
            vertical-align: middle;
        }

        thead tr th {
            background: #F7F7FA;
            color: var(--text-sub);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .06em;
            padding: 12px 16px;
            border-bottom: 1px solid var(--line-strong);
            white-space: nowrap;
        }

        thead tr th:first-child { border-top-left-radius: var(--r-lg); }
        thead tr th:last-child { border-top-right-radius: var(--r-lg); }

        tbody tr { transition: background .12s; }
        tbody tr:hover { background: #FAFAFC; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr.clickable-row { cursor: pointer; }

        td { color: var(--ink-soft); overflow: hidden; }

        /* two-line cell pattern: main value on top, supporting detail below */
        .cell-main { font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cell-sub { font-size: 12px; color: var(--text-sub); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .dash { color: var(--text-faint); }

        .sub-wait, .sub-ok, .sub-bad { font-weight: 600; }
        .sub-wait { color: var(--pending); }
        .sub-ok { color: var(--confirmed-text); }
        .sub-bad { color: var(--cancelled); }

        /* ===== BADGES ===== */
        .badge {
            display: inline-flex;
            align-items: center;
            height: 24px;
            padding: 0 11px;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: .02em;
            white-space: nowrap;
        }

        .badge-paid { background: var(--paid-soft); color: var(--paid); }
        .badge-done { background: var(--paid-soft); color: var(--paid); gap: 6px; }

        /* Done badge shown in the Status column (replaces the dropdown once voucher is punched) */
        .badge-done-status { height: 30px; padding: 0 14px; font-size: 12px; }

        /* ===== STATUS DROPDOWN ===== */
        .status-select {
            appearance: none;
            -webkit-appearance: none;
            height: 30px;
            padding: 0 28px 0 12px;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 12px;
            font-weight: 700;
            text-transform: capitalize;
            letter-spacing: .02em;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='6' viewBox='0 0 10 6'%3E%3Cpath d='M1 1l4 4 4-4' stroke='%23635C72' stroke-width='1.5' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 11px center;
            transition: .15s;
        }

        .status-select:focus { outline: none; box-shadow: 0 0 0 3px var(--pink-light); }
        .status-select:disabled { opacity: .6; cursor: not-allowed; }

        .status-select[data-status="pending"] { background-color: var(--pending-soft); color: var(--pending); }
        .status-select[data-status="approved"],
        .status-select[data-status="confirmed"] { background-color: var(--confirmed-soft); color: var(--confirmed-text); }
        .status-select[data-status="paid"] { background-color: var(--paid-soft); color: var(--paid); }
        .status-select[data-status="rejected"],
        .status-select[data-status="cancelled"] { background-color: var(--cancelled-soft); color: var(--cancelled); }

        .reject-reason { color: var(--cancelled); font-weight: 500; }

        /* ===== ACTION BUTTONS ===== */
        .action-buttons { display: flex; align-items: center; gap: 6px; }

        .btn-icon-edit,
        .btn-icon-delete {
            width: 32px;
            height: 32px;
            border-radius: var(--r-sm);
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--text-sub);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            transition: .15s;
        }

        .btn-icon-edit:hover { background: var(--paid-soft); border-color: var(--paid); color: var(--paid); }
        .btn-icon-delete:hover { background: var(--cancelled-soft); border-color: var(--cancelled); color: var(--cancelled); }

        /* edit is locked once the reservation is Done (voucher already used) */
        .btn-icon-edit:disabled,
        .btn-icon-edit:disabled:hover { opacity: .45; cursor: not-allowed; background: #fff; border-color: var(--line-strong); color: var(--muted); }

        .delete-form { display: inline-block; margin: 0; }

        /* ===== PAGINATION ===== */
        .pagination {
            display: none;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 6px;
            padding: 16px 20px;
            border-top: 1px solid var(--line);
        }

        .pagination-info { font-size: 12px; color: var(--text-sub); margin-right: 8px; }

        .pagination button {
            min-width: 32px;
            height: 32px;
            padding: 0 10px;
            border: 1px solid var(--line-strong);
            background: #fff;
            color: var(--ink-soft);
            border-radius: var(--r-sm);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: .15s;
        }

        .pagination button:hover:not(:disabled):not(.active) { background: var(--pink-pale); border-color: var(--pink); color: var(--pink-deep); }
        .pagination button.active { background: var(--pink-deep); border-color: var(--pink-deep); color: #fff; }
        .pagination button:disabled { opacity: .4; cursor: not-allowed; }

        #noMatchRow td {
            padding: 36px !important;
            color: var(--text-sub) !important;
            background: var(--card) !important;
            text-align: center !important;
            font-weight: 500 !important;
        }

        /* ===== GROUPED CUSTOMER ROWS ===== */
        tr.group-header { cursor: pointer; background: #FAFAFC; }
        tr.group-header:hover { background: var(--pink-pale); }
        tr.group-header.expanded td { border-bottom-color: var(--pink-light); background: var(--pink-pale); }

        .group-name { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .group-name > div { min-width: 0; }
        .group-chevron { width: 10px; flex-shrink: 0; color: var(--pink-deep); font-size: 11px; transition: transform .15s ease; }

        .group-summary-cell { color: var(--text-sub) !important; font-weight: 500; }
        .group-hint { color: var(--text-sub) !important; font-size: 12px; }

        .badge-pending-mini {
            display: inline-flex;
            align-items: center;
            height: 24px;
            background: var(--pending-soft);
            color: var(--pending);
            font-size: 11.5px;
            font-weight: 700;
            padding: 0 11px;
            border-radius: 999px;
            white-space: nowrap;
        }

        /* bookings inside an expanded group: indented, with an accent bar */
        tr.detail-row td { background: #fff; }
        tr.detail-row td:first-child { padding-left: 36px; box-shadow: inset 3px 0 0 var(--pink-light); }

        /* ===== BUTTONS ===== */
        .btn-primary,
        .btn-danger,
        .btn-ghost {
            height: 40px;
            padding: 0 18px;
            border-radius: var(--r-md);
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease, border-color .15s ease;
        }

        .btn-primary { background: var(--pink-deep); color: #fff; border: none; }
        .btn-primary:hover { background: var(--pink-dark); box-shadow: 0 0 0 3px var(--pink-light); }
        .btn-danger { background: var(--cancelled); color: #fff; border: none; }
        .btn-danger:hover { filter: brightness(.92); box-shadow: 0 0 0 3px var(--cancelled-soft); }
        .btn-ghost { background: #fff; color: var(--ink); border: 1px solid var(--line-strong); }
        .btn-ghost:hover { background: var(--bg); border-color: var(--pink); }

        /* ===== MODALS ===== */
        #reservationOverlay,
        #editReservationOverlay,
        #confirmOverlay,
        #receiptOverlay,
        #rejectOverlay,
        #voucherOverlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 10000;
            align-items: center;
            justify-content: center;
            background: rgba(20, 16, 30, .5);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            padding: 20px;
            animation: overlayFade .2s ease;
        }

        #reservationOverlay.active,
        #editReservationOverlay.active,
        #confirmOverlay.active,
        #receiptOverlay.active,
        #rejectOverlay.active,
        #voucherOverlay.active { display: flex; }

        .modal-card {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 520px;
            max-height: calc(100vh - 40px);
            overflow-y: auto;
            box-shadow: var(--shadow-md);
            animation: cardPop .22s ease;
        }

        .modal-head { display: flex; justify-content: space-between; align-items: flex-start; padding: 20px 24px 16px; border-bottom: 1px solid var(--line); }
        .modal-head .eyebrow { font-size: .66rem; font-weight: 700; color: var(--pink-deep); text-transform: uppercase; letter-spacing: .08em; margin-bottom: 4px; }
        .modal-head h3 { font-family: 'Source Serif 4', serif; font-size: 1.2rem; font-weight: 700; color: var(--ink); }

        .modal-close {
            width: 32px;
            height: 32px;
            border-radius: var(--r-sm);
            border: none;
            background: var(--bg);
            color: var(--ink-soft);
            font-size: 14px;
            cursor: pointer;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: .15s;
        }

        .modal-close:hover { background: var(--pink-light); color: var(--pink-deep); }
        .modal-body { padding: 20px 24px; }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label { font-size: 11.5px; font-weight: 700; color: var(--ink-soft); text-transform: uppercase; letter-spacing: .04em; }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 10px 12px;
            border: 1px solid var(--line-strong);
            border-radius: var(--r-md);
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: #fff;
            transition: border-color .15s, box-shadow .15s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus { outline: none; border-color: var(--pink); box-shadow: 0 0 0 3px var(--pink-light); }

        .form-group textarea { resize: vertical; min-height: 80px; }
        .form-hint { font-size: 12px; color: var(--text-sub); }

        .modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 24px 22px; border-top: 1px solid var(--line); }

        @keyframes overlayFade { from { opacity: 0; } to { opacity: 1; } }
        @keyframes cardPop { from { opacity: 0; transform: translateY(8px) scale(.98); } to { opacity: 1; transform: translateY(0) scale(1); } }

        /* ===== REJECT REASON MODAL ===== */
        .reason-list { display: flex; flex-direction: column; gap: 8px; }

        .reason-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 11px 14px;
            border: 1px solid var(--line-strong);
            border-radius: var(--r-md);
            background: #fff;
            font-size: 13px;
            color: var(--ink);
            font-weight: 500;
            cursor: pointer;
            transition: .15s;
        }

        .reason-option:hover { border-color: var(--cancelled); background: var(--cancelled-soft); }
        .reason-option input { accent-color: var(--cancelled); width: 16px; height: 16px; flex-shrink: 0; }
        .reason-option:has(input:checked) { border-color: var(--cancelled); background: var(--cancelled-soft); color: var(--cancelled); font-weight: 600; }

        #rejectNoteWrap { display: none; margin-top: 12px; }
        #rejectNoteWrap.show { display: block; }

        #rejectNoteWrap textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line-strong);
            border-radius: var(--r-md);
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink);
            background: #fff;
            resize: vertical;
            min-height: 80px;
        }

        #rejectNoteWrap textarea:focus { outline: none; border-color: var(--pink); box-shadow: 0 0 0 3px var(--pink-light); }

        .reject-error { display: none; margin-top: 10px; font-size: 12px; color: var(--cancelled); font-weight: 600; }
        .reject-error.show { display: block; }

        /* ===== VOUCHER MODAL ===== */
        .voucher-meta { font-size: 12.5px; color: var(--text-sub); margin-bottom: 16px; line-height: 1.6; }
        .voucher-meta strong { color: var(--ink); font-size: 14px; }

        .voucher-code-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            background: var(--pink-pale);
            border: 2px dashed var(--pink);
            border-radius: var(--r-lg);
            padding: 16px 18px;
        }

        /* Done = already punched by the cashier, can't be used again */
        .voucher-code-box.used { background: #F4F4F7; border-color: var(--line-strong); }

        .voucher-code { font-family: 'Courier New', monospace; font-size: 1.35rem; font-weight: 700; letter-spacing: .12em; color: var(--pink-deep); word-break: break-all; }
        .voucher-code.empty { font-family: 'Inter', sans-serif; font-size: 13px; letter-spacing: 0; color: var(--text-sub); font-weight: 500; }
        .voucher-code.used { color: var(--text-sub); text-decoration: line-through; }

        .voucher-note { display: none; margin-top: 12px; padding: 10px 14px; border-radius: var(--r-md); font-size: 12.5px; font-weight: 600; line-height: 1.5; }
        .voucher-note.show { display: block; }
        .voucher-note.note-used { background: var(--paid-soft); color: var(--paid); }
        .voucher-note.note-wait { background: var(--pending-soft); color: var(--pending); }
        .voucher-note.note-rejected { background: var(--cancelled-soft); color: var(--cancelled); }

        /* ===== CONFIRM DELETE MODAL ===== */
        .confirm-card {
            background: #fff;
            border-radius: 16px;
            width: 100%;
            max-width: 390px;
            padding: 28px 26px 22px;
            text-align: center;
            box-shadow: var(--shadow-md);
            animation: cardPop .22s ease;
        }

        .confirm-icon {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            margin: 0 auto 16px;
            background: var(--cancelled-soft);
            color: var(--cancelled);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .confirm-card h3 { font-family: 'Source Serif 4', serif; font-size: 1.15rem; font-weight: 700; color: var(--ink); margin-bottom: 8px; }
        .confirm-card p { font-size: 13.5px; color: var(--ink-soft); line-height: 1.55; margin-bottom: 22px; }
        .confirm-actions { display: flex; gap: 10px; justify-content: center; }

        .confirm-actions button {
            flex: 1;
            height: 42px;
            padding: 0 18px;
            border-radius: var(--r-md);
            font-size: 13.5px;
            font-weight: 700;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: .15s;
            border: none;
        }

        .btn-confirm-cancel { background: #fff; color: var(--ink-soft); border: 1px solid var(--line-strong) !important; }
        .btn-confirm-cancel:hover { background: var(--bg); }
        .btn-confirm-delete { background: var(--cancelled); color: #fff; }
        .btn-confirm-delete:hover { filter: brightness(.92); box-shadow: 0 0 0 3px var(--cancelled-soft); }

        @media(max-width:420px) { .confirm-actions { flex-direction: column-reverse; } }

        /* ===== TOAST ===== */
        #toastStack {
            position: fixed;
            top: 22px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 10001;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 100%;
            max-width: 420px;
            padding: 0 16px;
            pointer-events: none;
        }

        .toast {
            pointer-events: auto;
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 12px;
            background: #fff;
            color: var(--ink);
            padding: 13px 15px;
            border-radius: var(--r-lg);
            border: 1px solid var(--line);
            border-left: 4px solid var(--ink);
            box-shadow: var(--shadow-md);
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            opacity: 0;
            transform: translateY(-16px) scale(.97);
            animation: toastIn .3s ease forwards;
            position: relative;
            overflow: hidden;
        }

        .toast.hide { animation: toastOut .28s ease forwards; }

        .toast .toast-icon {
            flex-shrink: 0;
            width: 26px;
            height: 26px;
            border-radius: var(--r-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            color: #fff;
            background: var(--ink-soft);
        }

        .toast.toast-success { border-left-color: var(--confirmed); }
        .toast.toast-success .toast-icon { background: var(--confirmed); }
        .toast.toast-error { border-left-color: var(--cancelled); }
        .toast.toast-error .toast-icon { background: var(--cancelled); }

        .toast .toast-body { flex: 1; min-width: 0; padding-top: 2px; }
        .toast .toast-title { font-weight: 700; font-size: 12.5px; margin-bottom: 2px; color: var(--ink); }
        .toast .toast-msg { color: var(--ink-soft); font-size: 12.5px; word-break: break-word; }
        .toast .toast-close { flex-shrink: 0; background: none; border: none; cursor: pointer; color: var(--muted); font-size: 13px; padding: 2px; }
        .toast .toast-close:hover { color: var(--ink); }
        .toast .toast-bar { position: absolute; left: 0; bottom: 0; height: 3px; background: currentColor; opacity: .3; animation: toastShrink 3s linear forwards; }

        @keyframes toastIn { to { opacity: 1; transform: translateY(0) scale(1); } }
        @keyframes toastOut { to { opacity: 0; transform: translateY(-12px) scale(.96); } }
        @keyframes toastShrink { from { width: 100%; } to { width: 0%; } }

        /* ===== RESPONSIVE ===== */
        @media(max-width:900px) {
            .toolbar h2 { font-size: 1.15rem; }
            table { min-width: 900px; font-size: 12.5px; }
            th, td { padding: 10px 12px; }
        }

        @media(max-width:520px) {
            #toastStack { top: 14px; max-width: calc(100% - 24px); padding: 0; }
            .form-grid { grid-template-columns: 1fr; }
            .modal-foot { flex-direction: column-reverse; }
            .modal-foot button { width: 100%; }
            .filter-bar { padding: 12px 14px; }
            .filter-bar select { flex: 1; }
            .toolbar { padding: 16px 18px; }
            .toolbar-actions, .toolbar-actions .btn-primary { width: 100%; }
        }

        /* card view on very small screens */
        @media(max-width:480px) {
            .table-box { background: transparent; border: none; box-shadow: none; overflow: visible; }

            table, thead, tbody, th, td, tr { display: block; }
            colgroup, thead { display: none; }

            tbody tr {
                border: 1px solid var(--line);
                border-radius: var(--r-lg);
                margin-bottom: 12px;
                padding: 12px 14px;
                background: var(--card);
                box-shadow: var(--shadow-sm);
            }

            tbody tr:hover { background: var(--card); }

            td { border: none; padding: 5px 0; font-size: 13px; display: flex; align-items: flex-start; gap: 8px; overflow: visible; }
            td > div { min-width: 0; }
            .cell-main, .cell-sub { white-space: normal; }

            td::before {
                content: attr(data-label);
                font-weight: 700;
                color: var(--text-sub);
                min-width: 80px;
                font-size: 10.5px;
                text-transform: uppercase;
                letter-spacing: .04em;
                padding-top: 2px;
            }

            table { min-width: unset; }
            tr.detail-row td:first-child { padding-left: 0; box-shadow: none; }
            .pagination { background: var(--card); border: 1px solid var(--line); border-radius: var(--r-lg); }
        }
    </style>
@endsection

@section('content')

    @php
        $dummyRows = [
            ['customer' => 'Juan Dela Cruz', 'email' => 'juan.delacruz@example.com', 'date' => 'Jul 02, 2026', 'time' => '10:00 AM', 'category' => 'DinoLakbay', 'package' => 'Whole Day Pass', 'pax' => 4],
            ['customer' => 'Maria Santos', 'email' => 'maria.santos@example.com', 'date' => 'Jul 03, 2026', 'time' => '1:00 PM', 'category' => 'Skataya', 'package' => 'Birthday Package', 'pax' => 12],
            ['customer' => 'Ana Reyes', 'email' => 'ana.reyes@example.com', 'date' => 'Jul 05, 2026', 'time' => '9:00 AM', 'category' => 'DinoLakbay', 'package' => 'Half Day Pass', 'pax' => 2],
            ['customer' => 'Mark Villanueva', 'email' => 'mark.villanueva@example.com', 'date' => 'Jul 06, 2026', 'time' => '2:30 PM', 'category' => 'PiyestaRides', 'package' => 'Group Package', 'pax' => 20],
        ];
        $usingDummyData = !isset($bookings) || $bookings->count() === 0;
        $packageOptions = $packages ?? ['Whole Day Pass', 'Half Day Pass', 'Birthday Package', 'Group Package'];

        // Maps the `service` slug saved by the customer booking flow to its label.
        // Reservations made via the admin "New Reservation" form don't collect a service yet.
        $serviceLabels = [
            'dino_adventure' => 'DinoLakbay',
            'rollerfever'    => 'Skataya',
            'field_of_rides' => 'PiyestaRides',
        ];

        // Rejection reasons. These are also shown to the customer.
        $rejectReasons = [
            'Invalid or unclear payment receipt',
            'Incorrect payment amount',
            'Selected date/time is fully booked',
            'Selected package is not available',
            'Incomplete or incorrect booking details',
            'Others',
        ];

        $methodLabels = ['qrph' => 'QR Ph', 'gcash' => 'GCash', 'maya' => 'Maya', 'cash' => 'Cash'];
    @endphp

    <!-- TOOLBAR -->
    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Customer Management</div>
            <h2 style="font-family: Arial, Helvetica, sans-serif">Reservations</h2>
            <p class="sub">Upcoming reservations and schedules</p>
        </div>
        <div class="toolbar-actions">
            <button type="button" class="btn-primary" id="openReservationModalBtn">
                <i class="fa-solid fa-plus"></i> New Reservation
            </button>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
        <div class="search-wrap">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="searchInput" placeholder="Search by customer name or email...">
        </div>

        <select id="categoryFilter">
            <option value="">All Categories</option>
            <option value="Dino Adventure">DinoLakbay</option>
            <option value="RollerFever">Skataya</option>
            <option value="Field of Rides">PiyestaRides</option>
        </select>

        <select id="statusFilter">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
            <option value="done">Done</option>
        </select>

        <button type="button" class="filter-clear" id="clearFiltersBtn">
            <i class="fa-solid fa-arrow-rotate-left"></i> Clear
        </button>

        <span class="filter-count" id="filterCount"></span>
    </div>

    <!-- TABLE -->
    <div class="table-box">

        @if ($usingDummyData)
            <div class="sample-note"><i class="fa-solid fa-circle-info"></i> Showing sample data &mdash; no
                reservations recorded yet.</div>
        @endif

        <table>
            <colgroup>
                <col style="width:25%">   {{-- Customer (name + email) --}}
                <col style="width:130px"> {{-- Schedule (date + time) --}}
                <col style="width:20%">   {{-- Package (package + category) --}}
                <col style="width:64px">  {{-- Pax --}}
                <col style="width:150px"> {{-- Payment --}}
                <col style="width:150px"> {{-- Status --}}
                <col style="width:140px"> {{-- Actions --}}
            </colgroup>
            <thead>
                <tr>
                    <th>Customer</th>
                    <th>Schedule</th>
                    <th>Package</th>
                    <th>Pax</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @if ($usingDummyData)
                    @foreach ($dummyRows as $row)
                        <tr data-customer="{{ $row['customer'] }}" data-email="{{ $row['email'] }}"
                            data-category="{{ $row['category'] }}" data-pax="{{ $row['pax'] }}" data-status-key="">
                            <td data-label="Customer">
                                <div class="cell-main">{{ $row['customer'] }}</div>
                                <div class="cell-sub">{{ $row['email'] }}</div>
                            </td>
                            <td data-label="Schedule">
                                <div class="cell-main">{{ $row['date'] }}</div>
                                <div class="cell-sub">{{ $row['time'] }}</div>
                            </td>
                            <td data-label="Package">
                                <div class="cell-main">{{ $row['package'] }}</div>
                                <div class="cell-sub">{{ $row['category'] }}</div>
                            </td>
                            <td data-label="Pax">{{ $row['pax'] }}</td>
                            <td data-label="Payment"><span class="dash">&mdash;</span></td>
                            <td data-label="Status"><span class="dash">&mdash;</span></td>
                            <td data-label="Actions"><span class="dash">&mdash;</span></td>
                        </tr>
                    @endforeach
                @else
                    @foreach ($bookings as $booking)
                        @php
                            // FLOW:
                            //  1. Customer books      -> approval_status = 'pending'
                            //  2. Admin verifies      -> 'approved' or 'rejected'
                            //  3. Only approved reservations can be punched by the cashier
                            //  4. Voucher punched     -> DONE (cannot be used again)

                            $approval = $booking->approval_status ?? 'pending';
                            $isApproved = $approval === 'approved';

                            // DONE = voucher already punched by the cashier. Add any other
                            // column name your cashier module uses to this check.
                            $isDone = !empty($booking->voucher_used_at)
                                || !empty($booking->used_at)
                                || !empty($booking->redeemed_at)
                                || !empty($booking->is_used)
                                || in_array($booking->voucher_status ?? null, ['used', 'redeemed', 'punched'], true)
                                || in_array($booking->status ?? null, ['used', 'redeemed', 'completed', 'done'], true);

                            // used by the filter and by auto-refresh
                            $statusKey = $isDone ? 'done' : $approval;

                            $custName = $booking->display_customer->fullname ?? ($booking->customer_name ?? 'Walk-in');
                            $custEmail = $booking->display_customer->email ?? null;
                            $catLabel = $serviceLabels[$booking->service] ?? null;
                        @endphp
                        <tr class="clickable-row"
                            data-id="{{ $booking->id }}"
                            data-status-key="{{ $statusKey }}"
                            data-sig="{{ $booking->id }}-{{ $booking->updated_at?->timestamp }}-{{ $statusKey }}"
                            data-voucher="{{ $booking->voucher_code ?? '' }}"
                            data-approval="{{ $approval }}"
                            data-done="{{ $isDone ? '1' : '0' }}"
                            data-customer="{{ $custName }}"
                            data-email="{{ $custEmail ?? '' }}"
                            data-category="{{ $catLabel ?? '' }}"
                            data-pax="{{ $booking->display_pax }}"
                            data-package="{{ $booking->package }}"
                            data-date="{{ $booking->display_date?->format('M d, Y') }}">

                            <td data-label="Customer">
                                <div class="cell-main">{{ $custName }}</div>
                                <div class="cell-sub">{{ $custEmail ?: 'No email' }}</div>
                            </td>

                            <td data-label="Schedule">
                                <div class="cell-main">{{ $booking->display_date?->format('M d, Y') }}</div>
                                <div class="cell-sub">{{ $booking->display_time }}</div>
                            </td>

                            <td data-label="Package">
                                <div class="cell-main" title="{{ $booking->package }}">{{ $booking->package }}</div>
                                <div class="cell-sub">{{ $catLabel ?? '—' }}</div>
                            </td>

                            <td data-label="Pax">{{ $booking->display_pax }}</td>

                            {{-- PAYMENT: always shows the payment method used (GCash, Maya, QR Ph, Cash). --}}
                            <td data-label="Payment">
                                <div>
                                    @if (isset($methodLabels[$booking->payment_method]))
                                        <span class="badge badge-paid">{{ $methodLabels[$booking->payment_method] }}</span>
                                    @else
                                        <span class="dash">&mdash;</span>
                                    @endif

                                    @if (!$isDone)
                                        @if ($booking->status === 'awaiting_verification')
                                            <div class="cell-sub sub-wait">Waiting for approval</div>
                                        @elseif ($isApproved)
                                            <div class="cell-sub sub-ok">Ready for cashier</div>
                                        @elseif ($booking->status === 'pending_payment' && $booking->payment_rejection_reason)
                                            <div class="cell-sub sub-bad" title="{{ $booking->payment_rejection_reason }}">Proof rejected</div>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            {{-- STATUS: shows "Done" once the voucher is punched, otherwise the Approve/Reject dropdown. --}}
                            <td data-label="Status">
                                <div>
                                    @if ($isDone)
                                        <span class="badge badge-done badge-done-status" title="Voucher already punched by the cashier">
                                            <i class="fa-solid fa-check-double"></i> Done
                                        </span>
                                        <div class="cell-sub">Voucher used</div>
                                    @else
                                        <select class="status-select"
                                            data-status="{{ $approval }}"
                                            data-current="{{ $approval }}"
                                            data-id="{{ $booking->id }}"
                                            aria-label="Reservation status">
                                            <option value="pending" disabled @selected($approval === 'pending')>Pending</option>
                                            <option value="approved" @selected($approval === 'approved')>Approved</option>
                                            <option value="rejected" @selected($approval === 'rejected')>Rejected</option>
                                        </select>
                                        @if ($approval === 'rejected' && ($booking->reject_reason ?? null))
                                            @php
                                                $fullReason = $booking->reject_reason . ($booking->reject_note ? ': ' . $booking->reject_note : '');
                                            @endphp
                                            <div class="cell-sub reject-reason" title="{{ $fullReason }}">{{ $fullReason }}</div>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            <td data-label="Actions">
                                <div class="action-buttons">
                                    <button type="button" class="btn-icon-edit"
                                        title="{{ $isDone ? 'Locked — reservation is already done' : 'Edit reservation' }}"
                                        aria-label="Edit reservation"
                                        @disabled($isDone)
                                        onclick="openEditModal({
                                            id: '{{ $booking->id }}',
                                            customer_name: @js($booking->display_customer->fullname ?? $booking->customer_name ?? ''),
                                            customer_contact: @js($booking->customer_contact ?? ''),
                                            package: @js($booking->package),
                                            pax: {{ $booking->display_pax }},
                                            reservation_date: '{{ $booking->display_date?->format('Y-m-d') }}',
                                            reservation_time: '{{ $booking->display_time }}',
                                            notes: @js($booking->notes ?? '')
                                        })">
                                        <i class="fa-solid fa-pen"></i>
                                    </button>
                                    @if ($booking->receipt_path)
                                        <button type="button" class="btn-icon-edit" title="View proof of payment"
                                            aria-label="View proof of payment"
                                            onclick="openReceiptModal('{{ asset('storage/' . $booking->receipt_path) }}')">
                                            <i class="fa-solid fa-receipt"></i>
                                        </button>
                                    @endif
                                    <form action="{{ route('reservations.destroy', $booking) }}" method="POST"
                                        class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-icon-delete" title="Delete reservation"
                                            aria-label="Delete reservation">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="pagination no-print" id="pagination"></div>
    </div>

    <!-- HIDDEN FORM: APPROVE (submitted when "Approved" is selected) -->
    <form id="approveForm" method="POST" style="display:none;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="status" value="approved">
    </form>

    <!-- REJECT REASON MODAL -->
    <div id="rejectOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="rejectModalTitle"
            style="max-width:460px;">
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Reject Reservation</div>
                        <h3 id="rejectModalTitle">Reason for rejection</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeRejectModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-hint" style="margin-bottom:12px;">
                        The customer will be able to see the reason you select.
                    </div>
                    <div class="reason-list">
                        @foreach ($rejectReasons as $reason)
                            <label class="reason-option">
                                <input type="radio" name="reject_reason" value="{{ $reason }}">
                                <span>{{ $reason }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div id="rejectNoteWrap">
                        <textarea name="reject_note" id="rejectNote" placeholder="Type the reason..."></textarea>
                    </div>
                    <div class="reject-error" id="rejectError">Please select a reason.</div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelRejectModal">Cancel</button>
                    <button type="submit" class="btn-danger" id="confirmRejectBtn">
                        <i class="fa-solid fa-ban"></i> Reject Reservation
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- NEW RESERVATION MODAL -->
    <div id="reservationOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="reservationModalTitle">
            <form id="newReservationForm" method="POST" action="{{ route('reservations.store') }}">
                @csrf
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Lipa Branch</div>
                        <h3 id="reservationModalTitle">New Reservation</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeReservationModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="new_customer_name">Customer name</label>
                            <input type="text" name="customer_name" id="new_customer_name" required>
                        </div>
                        <div class="form-group full">
                            <label for="new_customer_contact">Contact number</label>
                            <input type="text" name="customer_contact" id="new_customer_contact">
                        </div>
                        <div class="form-group">
                            <label for="new_package">Package</label>
                            <select name="package" id="new_package" required>
                                @foreach ($packageOptions as $pkg)
                                    <option value="{{ $pkg }}">{{ $pkg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="new_pax">Number of pax</label>
                            <input type="number" name="pax" id="new_pax" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="new_reservation_date">Date</label>
                            <input type="date" name="reservation_date" id="new_reservation_date" required>
                        </div>
                        <div class="form-group">
                            <label for="new_reservation_time">Time</label>
                            <input type="time" name="reservation_time" id="new_reservation_time" required>
                        </div>
                        <div class="form-group full">
                            <label for="new_notes">Notes (optional)</label>
                            <textarea name="notes" id="new_notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelReservationModal">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Reservation</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT RESERVATION MODAL -->
    <div id="editReservationOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="editReservationModalTitle">
            <form id="editReservationForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Lipa Branch</div>
                        <h3 id="editReservationModalTitle">Edit Reservation</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeEditReservationModal" aria-label="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label for="edit_customer_name">Customer name</label>
                            <input type="text" name="customer_name" id="edit_customer_name" required>
                        </div>
                        <div class="form-group full">
                            <label for="edit_customer_contact">Contact number</label>
                            <input type="text" name="customer_contact" id="edit_customer_contact">
                        </div>
                        <div class="form-group">
                            <label for="edit_package">Package</label>
                            <select name="package" id="edit_package" required>
                                @foreach ($packageOptions as $pkg)
                                    <option value="{{ $pkg }}">{{ $pkg }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_pax">Number of pax</label>
                            <input type="number" name="pax" id="edit_pax" min="1" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_reservation_date">Date</label>
                            <input type="date" name="reservation_date" id="edit_reservation_date" required>
                        </div>
                        <div class="form-group">
                            <label for="edit_reservation_time">Time</label>
                            <input type="time" name="reservation_time" id="edit_reservation_time" required>
                        </div>
                        <div class="form-group full">
                            <label for="edit_notes">Notes (optional)</label>
                            <textarea name="notes" id="edit_notes"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelEditReservationModal">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- CONFIRM DELETE MODAL -->
    <div id="confirmOverlay" aria-hidden="true">
        <div class="confirm-card" role="alertdialog" aria-modal="true" aria-labelledby="confirmTitle"
            aria-describedby="confirmMessage">
            <div class="confirm-icon"><i class="fa-solid fa-trash"></i></div>
            <h3 id="confirmTitle">Delete this reservation?</h3>
            <p id="confirmMessage">Are you sure you want to delete this reservation? This action cannot be undone.</p>
            <div class="confirm-actions">
                <button type="button" class="btn-confirm-cancel" id="confirmCancelBtn">Cancel</button>
                <button type="button" class="btn-confirm-delete" id="confirmOkBtn">Yes, Delete</button>
            </div>
        </div>
    </div>

    <!-- VIEW RECEIPT MODAL -->
    <div id="receiptOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="receiptModalTitle"
            style="max-width:480px;">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Payment Proof</div>
                    <h3 id="receiptModalTitle">Payment Receipt</h3>
                </div>
                <button type="button" class="modal-close" id="closeReceiptModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" style="text-align:center;">
                <img id="receiptImage" src="" alt="Payment receipt" style="max-width:100%;border-radius:12px;">
            </div>
        </div>
    </div>

    <!-- VOUCHER CODE MODAL -->
    <div id="voucherOverlay" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="voucherModalTitle"
            style="max-width:440px;">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Reservation</div>
                    <h3 id="voucherModalTitle">Voucher Code</h3>
                </div>
                <button type="button" class="modal-close" id="closeVoucherModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="voucher-meta">
                    <div><strong id="voucherCustomer"></strong></div>
                    <div><span id="voucherPackage"></span> &middot; <span id="voucherDate"></span></div>
                </div>
                <div class="voucher-code-box" id="voucherCodeBox">
                    <span class="voucher-code" id="voucherCode"></span>
                    <button type="button" class="btn-ghost" id="copyVoucherBtn">
                        <i class="fa-regular fa-copy"></i> Copy
                    </button>
                </div>
                <div class="voucher-note" id="voucherNote"></div>
            </div>
        </div>
    </div>

    <!-- TOAST STACK -->
    <div id="toastStack" aria-live="polite"></div>

    @if (session('success'))
        <span id="flash-success" data-msg="{{ session('success') }}" style="display:none;"></span>
    @endif
    @if (session('error'))
        <span id="flash-error" data-msg="{{ session('error') }}" style="display:none;"></span>
    @endif
    @if ($errors->any())
        <span id="flash-error" data-msg="{{ $errors->first() }}" style="display:none;"></span>
    @endif

@endsection

@push('scripts')
    <script>
        // ── New Reservation modal ──
        const reservationOverlay = document.getElementById('reservationOverlay');
        const openReservationModalBtn = document.getElementById('openReservationModalBtn');
        const closeReservationModalBtn = document.getElementById('closeReservationModal');
        const cancelReservationModalBtn = document.getElementById('cancelReservationModal');

        function openReservationModal() {
            reservationOverlay.classList.add('active');
            reservationOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeReservationModal() {
            reservationOverlay.classList.remove('active');
            reservationOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        openReservationModalBtn?.addEventListener('click', openReservationModal);
        closeReservationModalBtn?.addEventListener('click', closeReservationModal);
        cancelReservationModalBtn?.addEventListener('click', closeReservationModal);
        reservationOverlay?.addEventListener('click', (e) => {
            if (e.target === reservationOverlay) closeReservationModal();
        });

        // ── Edit Reservation modal ──
        const editReservationOverlay = document.getElementById('editReservationOverlay');
        const editReservationForm = document.getElementById('editReservationForm');
        const closeEditReservationBtn = document.getElementById('closeEditReservationModal');
        const cancelEditReservationBtn = document.getElementById('cancelEditReservationModal');

        function openEditModal(data) {
            editReservationForm.action = `/reservations/${data.id}`;

            document.getElementById('edit_customer_name').value = data.customer_name || '';
            document.getElementById('edit_customer_contact').value = data.customer_contact || '';
            document.getElementById('edit_package').value = data.package || '';
            document.getElementById('edit_pax').value = data.pax || '';
            document.getElementById('edit_reservation_date').value = data.reservation_date || '';
            document.getElementById('edit_reservation_time').value = data.reservation_time || '';
            document.getElementById('edit_notes').value = data.notes || '';

            editReservationOverlay.classList.add('active');
            editReservationOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeEditModal() {
            editReservationOverlay.classList.remove('active');
            editReservationOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        closeEditReservationBtn?.addEventListener('click', closeEditModal);
        cancelEditReservationBtn?.addEventListener('click', closeEditModal);
        editReservationOverlay?.addEventListener('click', (e) => {
            if (e.target === editReservationOverlay) closeEditModal();
        });

        // If validation failed server-side and the page reloaded with old input,
        // reopen whichever modal was being submitted.
        @if ($errors->any())
            @if (old('_method') === 'PUT')
                // an edit was in flight — nothing to prefill without the original booking id,
                // so just leave the page as-is; the flash error toast below still shows.
            @elseif (old('_method') === 'PATCH')
                // status update failed — the error toast below is enough.
            @else
                openReservationModal();
            @endif
        @endif

        // ── View Receipt modal ──
        const receiptOverlay = document.getElementById('receiptOverlay');
        const receiptImage = document.getElementById('receiptImage');
        const closeReceiptModalBtn = document.getElementById('closeReceiptModal');

        function openReceiptModal(url) {
            if (/\.pdf($|\?)/i.test(url)) { window.open(url, '_blank'); return; }   // PDFs open in a new tab
            receiptImage.src = url;
            receiptOverlay.classList.add('active');
            receiptOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeReceiptModal() {
            receiptOverlay.classList.remove('active');
            receiptOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            receiptImage.src = '';
        }

        closeReceiptModalBtn?.addEventListener('click', closeReceiptModal);
        receiptOverlay?.addEventListener('click', (e) => {
            if (e.target === receiptOverlay) closeReceiptModal();
        });

        // ── Status: Approve / Reject ──
        const approveForm = document.getElementById('approveForm');
        const rejectOverlay = document.getElementById('rejectOverlay');
        const rejectForm = document.getElementById('rejectForm');
        const rejectNoteWrap = document.getElementById('rejectNoteWrap');
        const rejectNote = document.getElementById('rejectNote');
        const rejectError = document.getElementById('rejectError');
        let pendingStatusSelect = null; // the <select> currently being rejected

        function statusUrl(id) {
            return `/reservations/${id}/status`;
        }

        function revertSelect(sel) {
            if (!sel) return;
            sel.value = sel.dataset.current;
            sel.dataset.status = sel.dataset.current;
        }

        function openRejectModal(sel) {
            pendingStatusSelect = sel;
            rejectForm.action = statusUrl(sel.dataset.id);
            rejectForm.querySelectorAll('input[name="reject_reason"]').forEach(r => r.checked = false);
            rejectNote.value = '';
            rejectNoteWrap.classList.remove('show');
            rejectError.classList.remove('show');

            rejectOverlay.classList.add('active');
            rejectOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeRejectModal(revert = true) {
            rejectOverlay.classList.remove('active');
            rejectOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            if (revert) revertSelect(pendingStatusSelect);
            pendingStatusSelect = null;
        }

        document.getElementById('closeRejectModal')?.addEventListener('click', () => closeRejectModal());
        document.getElementById('cancelRejectModal')?.addEventListener('click', () => closeRejectModal());
        rejectOverlay?.addEventListener('click', (e) => {
            if (e.target === rejectOverlay) closeRejectModal();
        });

        // choosing "Others" reveals the text box
        rejectForm?.addEventListener('change', (e) => {
            if (e.target.name !== 'reject_reason') return;
            const isOthers = e.target.value === 'Others';
            rejectNoteWrap.classList.toggle('show', isOthers);
            rejectError.classList.remove('show');
            if (isOthers) rejectNote.focus();
        });

        rejectForm?.addEventListener('submit', (e) => {
            const chosen = rejectForm.querySelector('input[name="reject_reason"]:checked');
            if (!chosen) {
                e.preventDefault();
                rejectError.textContent = 'Please select a reason.';
                rejectError.classList.add('show');
                return;
            }
            if (chosen.value === 'Others' && !rejectNote.value.trim()) {
                e.preventDefault();
                rejectError.textContent = 'Please enter a reason when selecting "Others".';
                rejectError.classList.add('show');
                rejectNote.focus();
                return;
            }
            const btn = document.getElementById('confirmRejectBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            window.__submittingForm = true;
        });

        // status dropdown change (delegated, so it works for hidden/grouped rows too)
        document.querySelector('.table-box table tbody')?.addEventListener('change', (e) => {
            const sel = e.target.closest('select.status-select');
            if (!sel) return;

            // locked once Done (voucher already punched)
            if (sel.closest('tr')?.dataset.done === '1') {
                revertSelect(sel);
                return;
            }

            sel.dataset.status = sel.value; // update the color right away

            if (sel.value === 'approved') {
                sel.disabled = true;
                approveForm.action = statusUrl(sel.dataset.id);
                window.__submittingForm = true;
                approveForm.submit();
            } else if (sel.value === 'rejected') {
                openRejectModal(sel);
            }
        });

        // ── Custom delete confirmation modal ──
        const confirmOverlay = document.getElementById('confirmOverlay');
        const confirmCancelBtn = document.getElementById('confirmCancelBtn');
        const confirmOkBtn = document.getElementById('confirmOkBtn');
        let pendingDeleteForm = null;

        function openConfirmModal(form) {
            pendingDeleteForm = form;
            confirmOverlay.classList.add('active');
            confirmOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeConfirmModal() {
            confirmOverlay.classList.remove('active');
            confirmOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
            pendingDeleteForm = null;
        }

        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                openConfirmModal(form);
            });
        });

        confirmCancelBtn?.addEventListener('click', closeConfirmModal);
        confirmOverlay?.addEventListener('click', (e) => {
            if (e.target === confirmOverlay) closeConfirmModal();
        });
        confirmOkBtn?.addEventListener('click', () => {
            if (pendingDeleteForm) {
                confirmOkBtn.disabled = true;
                confirmOkBtn.textContent = 'Deleting...';
                window.__submittingForm = true;
                pendingDeleteForm.submit();
            }
        });

        // ── Voucher Code modal (click a booking row) ──
        const voucherOverlay = document.getElementById('voucherOverlay');
        const voucherCodeEl = document.getElementById('voucherCode');
        const voucherCodeBox = document.getElementById('voucherCodeBox');
        const voucherNote = document.getElementById('voucherNote');
        const copyVoucherBtn = document.getElementById('copyVoucherBtn');

        function setVoucherNote(type, text) {
            voucherNote.className = 'voucher-note';
            if (!type) { voucherNote.textContent = ''; return; }
            voucherNote.classList.add('show', 'note-' + type);
            voucherNote.textContent = text;
        }

        // Voucher states:
        //  DONE      -> already punched by the cashier, cannot be used again
        //  APPROVED  -> valid, the cashier can punch it
        //  PENDING / REJECTED -> not usable
        function openVoucherModal(d) {
            document.getElementById('voucherCustomer').textContent = d.customer || '';
            document.getElementById('voucherPackage').textContent = d.package || '';
            document.getElementById('voucherDate').textContent = d.date || '';

            const code = (d.voucher || '').trim();
            const isDone = d.done === '1';
            const approval = d.approval || 'pending';

            voucherCodeEl.textContent = code || 'No voucher code';
            voucherCodeEl.classList.toggle('empty', !code);
            voucherCodeEl.classList.toggle('used', isDone && !!code);
            voucherCodeBox.classList.toggle('used', isDone);

            // Copy only makes sense for a valid (approved, not yet punched) code
            copyVoucherBtn.style.display = (code && !isDone && approval === 'approved') ? '' : 'none';

            if (isDone) {
                setVoucherNote('used', 'Done — this voucher has already been punched by the cashier and can no longer be used.');
            } else if (approval === 'pending') {
                setVoucherNote('wait', 'This reservation has not been approved yet, so the cashier cannot punch the voucher.');
            } else if (approval === 'rejected') {
                setVoucherNote('rejected', 'This reservation was rejected, so the voucher cannot be used.');
            } else {
                setVoucherNote('', '');
            }

            voucherOverlay.classList.add('active');
            voucherOverlay.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        }

        function closeVoucherModal() {
            voucherOverlay.classList.remove('active');
            voucherOverlay.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        // Clipboard API, with a fallback for non-secure (http) contexts
        async function copyText(text) {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return;
            }
            const ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            const ok = document.execCommand('copy');
            ta.remove();
            if (!ok) throw new Error('copy failed');
        }

        document.querySelector('.table-box table tbody')?.addEventListener('click', (e) => {
            // ignore clicks on buttons/forms/status select
            if (e.target.closest('button, a, form, select, input, option')) return;
            const row = e.target.closest('tr.clickable-row');
            if (!row) return;
            openVoucherModal(row.dataset);
        });

        document.getElementById('closeVoucherModal')?.addEventListener('click', closeVoucherModal);
        voucherOverlay?.addEventListener('click', (e) => {
            if (e.target === voucherOverlay) closeVoucherModal();
        });

        copyVoucherBtn?.addEventListener('click', async () => {
            try {
                await copyText(voucherCodeEl.textContent.trim());
                showToast('Voucher code copied.', 'success');
            } catch (err) {
                showToast('Unable to copy the code.', 'error');
            }
        });

        // Escape closes whichever modal is open
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            if (confirmOverlay.classList.contains('active')) closeConfirmModal();
            if (editReservationOverlay.classList.contains('active')) closeEditModal();
            if (reservationOverlay.classList.contains('active')) closeReservationModal();
            if (receiptOverlay.classList.contains('active')) closeReceiptModal();
            if (rejectOverlay.classList.contains('active')) closeRejectModal();
            if (voucherOverlay.classList.contains('active')) closeVoucherModal();
        });

        // ── Toast notifications ──
        const toastStack = document.getElementById('toastStack');

        const TOAST_ICONS = {
            success: { icon: 'fa-solid fa-circle-check', title: 'Success' },
            error: { icon: 'fa-solid fa-circle-exclamation', title: 'Error' }
        };

        function showToast(msg, type = "success", opts = {}) {
            const cfg = TOAST_ICONS[type] || TOAST_ICONS.success;
            const title = opts.title || cfg.title;
            const duration = opts.duration ?? 3500;

            const el = document.createElement('div');
            el.className = `toast toast-${type}`;
            el.innerHTML = `
                <div class="toast-icon"><i class="${cfg.icon}"></i></div>
                <div class="toast-body">
                    <div class="toast-title">${title}</div>
                    <div class="toast-msg"></div>
                </div>
                <button type="button" class="toast-close" aria-label="Dismiss"><i class="fa-solid fa-xmark"></i></button>
                <div class="toast-bar" style="animation-duration:${duration}ms;"></div>
            `;
            el.querySelector('.toast-msg').textContent = msg;

            function dismiss() {
                if (!el.isConnected) return;
                el.classList.add('hide');
                setTimeout(() => el.remove(), 280);
            }

            el.querySelector('.toast-close').addEventListener('click', dismiss);
            toastStack.appendChild(el);

            if (duration > 0) setTimeout(dismiss, duration);
        }

        const successMsg = document.getElementById('flash-success')?.dataset.msg;
        const errorMsg = document.getElementById('flash-error')?.dataset.msg;

        if (successMsg) showToast(successMsg, "success");
        if (errorMsg) showToast(errorMsg, "error");

        // ===== AUTO-REFRESH =====
        // Every 8 seconds the current page is fetched in the background and each row's
        // "signature" (id + updated_at + status) is compared. If anything changed (new
        // booking, approve, reject, or "Done" from the cashier) the page reloads.
        // It never reloads while a modal is open or a form is submitting, and the
        // search/filters are restored after the reload.
        (function() {
            const POLL_MS = 8000;
            const STATE_KEY = 'reservations_filter_state';

            function signatureOf(root) {
                return Array.from(root.querySelectorAll('.table-box tbody tr[data-sig]'))
                    .map(r => r.dataset.sig)
                    .join('|');
            }

            const initialSig = signatureOf(document);
            let reloading = false;

            function anyModalOpen() {
                return !!document.querySelector(
                    '#reservationOverlay.active, #editReservationOverlay.active, #confirmOverlay.active, ' +
                    '#receiptOverlay.active, #rejectOverlay.active, #voucherOverlay.active'
                );
            }

            function saveFilterState() {
                try {
                    sessionStorage.setItem(STATE_KEY, JSON.stringify({
                        search: document.getElementById('searchInput')?.value || '',
                        category: document.getElementById('categoryFilter')?.value || '',
                        status: document.getElementById('statusFilter')?.value || ''
                    }));
                } catch (e) { /* storage unavailable — fine */ }
            }

            async function checkForUpdates() {
                if (reloading || document.hidden || window.__submittingForm) return;
                try {
                    const res = await fetch(window.location.href, {
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    if (!res.ok) return;
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newSig = signatureOf(doc);

                    if (newSig !== initialSig) {
                        // wait until any open modal is closed
                        if (anyModalOpen() || window.__submittingForm) return;
                        reloading = true;
                        saveFilterState();
                        showToast('New updates found. Refreshing...', 'success', { title: 'Updated', duration: 1200 });
                        setTimeout(() => window.location.reload(), 1000);
                    }
                } catch (err) {
                    // network hiccup — try again on the next poll
                }
            }

            setInterval(checkForUpdates, POLL_MS);
            document.addEventListener('visibilitychange', () => {
                if (!document.hidden) checkForUpdates();
            });
        })();

        // ===== GROUP BY CUSTOMER + SEARCH + FILTER + CLIENT-SIDE PAGINATION =====
        // Grouping happens purely in the DOM: existing <tr> elements are only shown/hidden,
        // so every edit/delete/receipt/status control keeps working as before.
        // Row data is read from data-* attributes (customer, email, category, pax, status-key).
        (function() {
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-box table tbody');
            const table = document.querySelector('.table-box table');
            const searchInput = document.getElementById('searchInput');
            const categoryFilter = document.getElementById('categoryFilter');
            const statusFilter = document.getElementById('statusFilter');
            const clearFiltersBtn = document.getElementById('clearFiltersBtn');
            const filterCount = document.getElementById('filterCount');
            if (!paginationEl || !tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;
            const COL_COUNT = table.querySelectorAll('thead th').length || 7;

            const originalRows = Array.from(tbody.querySelectorAll('tr'));
            if (!originalRows.length) return;

            function escapeHtml(str) {
                return String(str).replace(/[&<>"']/g, c => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
                }[c]));
            }

            // restore search/filters after an auto-refresh
            try {
                const saved = JSON.parse(sessionStorage.getItem('reservations_filter_state') || 'null');
                if (saved) {
                    if (searchInput) searchInput.value = saved.search || '';
                    if (categoryFilter) categoryFilter.value = saved.category || '';
                    if (statusFilter) statusFilter.value = saved.status || '';
                    sessionStorage.removeItem('reservations_filter_state');
                }
            } catch (e) { /* ignore */ }

            // ---- Group rows by customer (email when available, otherwise name) ----
            const groupsMap = new Map();
            const orderedKeys = [];

            originalRows.forEach(row => {
                const name = (row.dataset.customer || '').trim();
                const email = (row.dataset.email || '').trim();
                const key = (email || name).toLowerCase();
                if (!groupsMap.has(key)) {
                    groupsMap.set(key, { name, email, rows: [] });
                    orderedKeys.push(key);
                }
                groupsMap.get(key).rows.push(row);
            });

            // ---- Pagination "items": a standalone row for customers with one booking,
            // or a collapsible group header + its detail rows for customers with several ----
            const items = [];
            const groupState = new Map(); // groupIndex -> expanded? (default collapsed)

            orderedKeys.forEach((key, idx) => {
                const group = groupsMap.get(key);

                if (group.rows.length === 1) {
                    items.push({ type: 'single', row: group.rows[0] });
                    return;
                }

                let totalPax = 0;
                let pendingCount = 0;
                group.rows.forEach(row => {
                    const paxVal = parseInt(row.dataset.pax, 10);
                    if (!isNaN(paxVal)) totalPax += paxVal;
                    if (row.dataset.statusKey === 'pending') pendingCount++;
                    row.classList.add('detail-row');
                    row.dataset.group = String(idx);
                    row.style.display = 'none';
                });

                const header = document.createElement('tr');
                header.className = 'group-header';
                header.dataset.group = String(idx);
                header.innerHTML = `
                    <td data-label="Customer">
                        <div class="group-name">
                            <i class="fa-solid fa-chevron-right group-chevron"></i>
                            <div>
                                <div class="cell-main">${escapeHtml(group.name)}</div>
                                <div class="cell-sub">${group.email ? escapeHtml(group.email) : 'No email'}</div>
                            </div>
                        </div>
                    </td>
                    <td colspan="2" data-label="Bookings" class="group-summary-cell">${group.rows.length} bookings</td>
                    <td data-label="Pax"><span class="cell-main">${totalPax}</span></td>
                    <td data-label="Payment"></td>
                    <td data-label="Status">${pendingCount > 0 ? `<span class="badge-pending-mini">${pendingCount} pending</span>` : ''}</td>
                    <td data-label="Actions" class="group-hint">Click to view all</td>
                `;
                header.addEventListener('click', () => {
                    groupState.set(idx, !groupState.get(idx));
                    render();
                });

                group.rows[0].parentNode.insertBefore(header, group.rows[0]);
                groupState.set(idx, false);
                items.push({ type: 'group', header, rows: group.rows, groupIndex: idx });
            });

            // "no results" row, inserted once and toggled as needed
            const noMatchRow = document.createElement('tr');
            noMatchRow.id = 'noMatchRow';
            noMatchRow.style.display = 'none';
            noMatchRow.innerHTML = `<td colspan="${COL_COUNT}"><i class="fa-solid fa-circle-info"></i> No reservations match your search or filters.</td>`;
            tbody.appendChild(noMatchRow);

            function rowMatchesFilters(row, search, category, status) {
                const customer = (row.dataset.customer || '').toLowerCase();
                const email = (row.dataset.email || '').toLowerCase();
                const rowCategory = row.dataset.category || '';
                const rowStatus = row.dataset.statusKey || '';

                const matchesSearch = !search || customer.includes(search) || email.includes(search);
                const matchesCategory = !category || rowCategory === category;
                const matchesStatus = !status || rowStatus === status;

                return matchesSearch && matchesCategory && matchesStatus;
            }

            function getFilteredItems() {
                const search = (searchInput?.value || '').toLowerCase().trim();
                const category = categoryFilter?.value || '';
                const status = statusFilter?.value || '';
                const hasActiveFilter = !!(search || category || status);

                const result = [];
                let matchedRowCount = 0;

                items.forEach(item => {
                    if (item.type === 'single') {
                        if (rowMatchesFilters(item.row, search, category, status)) {
                            result.push(item);
                            matchedRowCount++;
                        }
                        return;
                    }

                    const matchingRows = item.rows.filter(r => rowMatchesFilters(r, search, category, status));
                    if (matchingRows.length > 0) {
                        result.push({ ...item, matchingRows });
                        matchedRowCount += matchingRows.length;
                    }
                });

                return { result, hasActiveFilter, matchedRowCount };
            }

            function renderPagination(totalPages) {
                if (totalPages <= 1) {
                    paginationEl.style.display = 'none';
                    paginationEl.innerHTML = '';
                    return;
                }
                paginationEl.style.display = 'flex';
                let html = `<span class="pagination-info">Page ${currentPage} of ${totalPages}</span>`;
                html += `<button ${currentPage === 1 ? 'disabled' : ''} data-page="${currentPage - 1}">&laquo; Prev</button>`;
                for (let p = 1; p <= totalPages; p++) {
                    html += `<button class="${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`;
                }
                html += `<button ${currentPage === totalPages ? 'disabled' : ''} data-page="${currentPage + 1}">Next &raquo;</button>`;
                paginationEl.innerHTML = html;
                paginationEl.querySelectorAll('button[data-page]').forEach(b => {
                    b.addEventListener('click', () => {
                        currentPage = parseInt(b.dataset.page, 10);
                        render();
                    });
                });
            }

            // how many <tr> each item will show on screen right now
            function computeVisibleRowCounts(filteredItems, hasActiveFilter) {
                return filteredItems.map(item => {
                    if (item.type === 'single') return 1;
                    const expanded = hasActiveFilter ? true : groupState.get(item.groupIndex);
                    const rowsCount = hasActiveFilter ? item.matchingRows.length : item.rows.length;
                    return 1 + (expanded ? rowsCount : 0); // +1 for the header row
                });
            }

            // split items into pages of at most PAGE_SIZE visible rows
            function paginateByVisibleRows(filteredItems, counts, pageSize) {
                const pages = [];
                let current = [];
                let currentCount = 0;

                filteredItems.forEach((item, idx) => {
                    const c = counts[idx];
                    if (currentCount > 0 && currentCount + c > pageSize) {
                        pages.push(current);
                        current = [];
                        currentCount = 0;
                    }
                    current.push(item);
                    currentCount += c;
                });

                if (current.length || pages.length === 0) pages.push(current);
                return pages;
            }

            function render() {
                originalRows.forEach(row => row.style.display = 'none');
                items.forEach(item => { if (item.type === 'group') item.header.style.display = 'none'; });

                const { result: filtered, hasActiveFilter, matchedRowCount } = getFilteredItems();
                const counts = computeVisibleRowCounts(filtered, hasActiveFilter);
                const pages = paginateByVisibleRows(filtered, counts, PAGE_SIZE);

                const totalPages = pages.length;
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const pageItems = pages[currentPage - 1] || [];

                pageItems.forEach(item => {
                    if (item.type === 'single') {
                        item.row.style.display = '';
                        return;
                    }

                    item.header.style.display = '';
                    const chevron = item.header.querySelector('.group-chevron');
                    const summaryCell = item.header.querySelector('.group-summary-cell');

                    const showExpanded = hasActiveFilter ? true : groupState.get(item.groupIndex);
                    const rowsToShow = hasActiveFilter ? item.matchingRows : item.rows;

                    item.header.classList.toggle('expanded', showExpanded);
                    if (chevron) chevron.style.transform = showExpanded ? 'rotate(90deg)' : 'rotate(0deg)';
                    if (summaryCell) {
                        summaryCell.textContent = hasActiveFilter
                            ? `${item.matchingRows.length} of ${item.rows.length} bookings match`
                            : `${item.rows.length} bookings`;
                    }

                    item.rows.forEach(r => r.style.display = 'none');
                    if (showExpanded) rowsToShow.forEach(r => r.style.display = '');
                });

                noMatchRow.style.display = filtered.length === 0 ? '' : 'none';

                if (filterCount) {
                    const hasFilterText = (searchInput?.value || '') || categoryFilter?.value || statusFilter?.value;
                    filterCount.textContent = hasFilterText
                        ? `${matchedRowCount} of ${originalRows.length} bookings shown`
                        : '';
                }

                renderPagination(totalPages);
            }

            searchInput?.addEventListener('input', () => { currentPage = 1; render(); });
            categoryFilter?.addEventListener('change', () => { currentPage = 1; render(); });
            statusFilter?.addEventListener('change', () => { currentPage = 1; render(); });

            clearFiltersBtn?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (categoryFilter) categoryFilter.value = '';
                if (statusFilter) statusFilter.value = '';
                currentPage = 1;
                render();
            });

            render();
        })();
    </script>
@endpush