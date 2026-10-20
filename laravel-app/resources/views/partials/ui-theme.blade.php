{{--
    Shared UI theme.
    Save as: resources/views/partials/ui-theme.blade.php
    Usage:   @include('partials.ui-theme')  inside @section('styles'), BEFORE the page's own <style>.
--}}
<style>
    :root {
        /* --danger, --danger-soft, --present, --amber, --sky, --font-display etc. come from layouts/sidebar */
        --info: var(--sky);
        --info-soft: var(--sky-soft);
        --warn: var(--amber);
        --warn-soft: var(--amber-light);
        --font-sans: 'Inter', sans-serif;
    }

    button, input, select, textarea { font-family: var(--font-sans); }
    .display { font-family: var(--font-display); }

    @keyframes spin { to { transform: rotate(360deg); } }
    @keyframes overlayFade { from { opacity: 0; } to { opacity: 1; } }
    @keyframes cardPop { from { opacity: 0; transform: scale(.92); } to { opacity: 1; transform: scale(1); } }

    /* ---------- Toolbar ---------- */
    .toolbar {
        background: var(--card);
        padding: 20px 24px;
        margin-bottom: 16px;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 16px;
    }
    .eyebrow {
        font-size: .68rem;
        font-weight: 700;
        color: var(--pink-deep);
        text-transform: uppercase;
        letter-spacing: .09em;
        margin-bottom: 6px;
    }
    .toolbar h2 {
        font-family: var(--font-display);
        font-size: 1.3rem;
        font-weight: 700;
        color: var(--ink);
        line-height: 1.2;
    }
    .toolbar .sub { font-size: 12px; color: var(--muted); margin-top: 4px; }
    .toolbar-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

    /* ---------- Stat cards ---------- */
    .stats-row {
        display: grid;
        grid-template-columns: repeat(var(--cols, 3), 1fr);
        gap: 14px;
        margin-bottom: 16px;
    }
    .stat-card {
        background: var(--card);
        border-radius: 16px;
        padding: 16px 20px;
        box-shadow: var(--shadow-sm);
        min-width: 0;
    }
    .stat-card .label {
        font-size: .68rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .08em;
        margin-bottom: 8px;
    }
    .stat-card .value {
        font-family: var(--font-display);
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--ink);
        line-height: 1;
        word-break: break-word;
    }
    .stat-card .value.present { color: var(--present); }
    .stat-card .value.deduct { color: var(--danger); }
    .stat-card .value.pink { color: var(--pink-deep); }
    .stat-card .sub { font-size: 11px; color: var(--muted); margin-top: 7px; }

    /* ---------- Content boxes ---------- */
    .box, .table-box {
        background: var(--card);
        padding: 22px 24px;
        border-radius: 16px;
        box-shadow: var(--shadow-sm);
        margin-bottom: 16px;
    }
    .table-box { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .box h3 {
        font-family: var(--font-display);
        font-size: 16px;
        font-weight: 700;
        color: var(--ink);
        margin-bottom: 14px;
        padding-bottom: 12px;
        border-bottom: 1px solid var(--line);
    }

    /* ---------- Buttons ---------- */
    .btn-primary, .btn-ghost, .btn-danger {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        border: 1px solid transparent;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.2;
        cursor: pointer;
        white-space: nowrap;
        text-decoration: none;
        transition: background .15s ease, border-color .15s ease, box-shadow .15s ease;
    }
    .btn-primary { background: var(--pink-deep); color: #fff; }
    .btn-primary:hover { background: var(--pink-dark); box-shadow: 0 0 0 3px var(--pink-light); }
    .btn-ghost { background: #fff; color: var(--ink); border-color: var(--line-strong); }
    .btn-ghost:hover { background: var(--bg); border-color: var(--pink); }
    .btn-ghost.is-danger { color: var(--danger); border-color: var(--danger-soft); }
    .btn-danger { background: var(--danger); color: #fff; }
    .btn-danger:hover { background: #991B1B; box-shadow: 0 0 0 3px var(--danger-soft); }
    .btn-sm { padding: 6px 14px; font-size: 12px; border-radius: 8px; }
    .btn-primary:disabled, .btn-ghost:disabled, .btn-danger:disabled { opacity: .6; cursor: not-allowed; }

    .spinner {
        display: inline-block;
        width: 13px;
        height: 13px;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin .6s linear infinite;
        vertical-align: -2px;
    }

    /* ---------- Form controls ---------- */
    :where(.filter-bar, .filters, .form-group) :where(input:not([type="checkbox"]):not([type="radio"]):not([type="file"]):not([type="hidden"]), select) {
        padding: 9px 12px;
        border: 1px solid var(--line-strong);
        border-radius: 10px;
        font-size: 13px;
        color: var(--ink-soft);
        background: var(--bg);
        outline: none;
    }
    :where(.filter-bar, .filters, .form-group) :where(input, select):focus {
        border-color: var(--pink);
        background: #fff;
    }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-group.full { grid-column: 1 / -1; }
    .form-group label, .field-label {
        font-size: 11.5px;
        font-weight: 700;
        color: var(--ink-soft);
        text-transform: uppercase;
        letter-spacing: .04em;
    }
    .form-hint { font-size: 11px; color: var(--muted); }

    /* ---------- Filter bar ---------- */
    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px;
        padding-bottom: 18px;
        border-bottom: 1px solid var(--line);
    }
    .filter-bar .filter-label {
        font-size: .68rem;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .08em;
    }
    .filter-bar input[type="text"] { flex: 1; min-width: 180px; max-width: 280px; }
    .filter-bar select { cursor: pointer; }
    .filter-toggle {
        padding: 9px 14px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: #fff;
        color: var(--ink-soft);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s ease;
    }
    .filter-toggle:hover { border-color: var(--pink); }
    .filter-toggle.active { background: var(--pink-deep); border-color: var(--pink-deep); color: #fff; }
    .filter-toggle[data-type="late"].active,
    .filter-toggle[data-type="inactive"].active { background: var(--danger); border-color: var(--danger); color: #fff; }
    .filter-toggle[data-type="overtime"].active,
    .filter-toggle[data-type="active"].active { background: var(--present); border-color: var(--present); color: #fff; }
    .filter-clear {
        background: none;
        border: none;
        color: var(--pink-deep);
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: underline;
        padding: 9px 4px;
    }

    .area-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
    .area-chip {
        padding: 6px 12px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: #fff;
        color: var(--ink-soft);
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }
    .area-chip:hover { background: var(--pink-pale); border-color: var(--pink); color: var(--pink-deep); }
    .area-chip.active { background: var(--pink-deep); border-color: var(--pink-deep); color: #fff; }

    /* ---------- Pagination ---------- */
    .pagination {
        display: none;
        flex-wrap: wrap;
        justify-content: center;
        align-items: center;
        gap: 6px;
        padding-top: 16px;
        margin-top: 6px;
        border-top: 1px solid var(--line);
    }
    .pagination-info { font-size: 11.5px; color: var(--muted); margin-right: 6px; }
    .pagination button {
        min-width: 30px;
        padding: 7px 10px;
        border: 1px solid var(--line-strong);
        background: #fff;
        color: var(--ink-soft);
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    .pagination button.active { background: var(--pink-deep); border-color: var(--pink-deep); color: #fff; }
    .pagination button:disabled { opacity: .4; cursor: not-allowed; }

    /* ---------- Tables ---------- */
    .table-scroll { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
    table { width: 100%; border-collapse: collapse; font-variant-numeric: tabular-nums; }
    thead th {
        background: var(--ink);
        color: #fff;
        padding: 11px 12px;
        text-align: left;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: none;
    }
    tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid var(--line);
        font-size: 12.5px;
        color: var(--ink-soft);
        vertical-align: middle;
    }
    tbody tr:hover:not(.category-row):not(.total-row) { background: var(--pink-pale); }
    td strong, .td-name, .emp-name-cell { color: var(--ink); font-weight: 600; }
    .money { color: var(--present); font-weight: 700; }
    .deduction { color: var(--danger); font-weight: 600; }
    .muted { color: var(--muted); }
    .category-row td {
        background: var(--pink-light);
        color: var(--pink-deep);
        font-weight: 700;
        font-size: 10.5px;
        padding: 8px 14px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        border-top: 1px solid var(--line-strong);
        border-bottom: 1px solid var(--line-strong);
    }
    #noMatchRow, #noMatchRow > td {
        padding: 26px;
        color: var(--muted);
        text-align: center;
        font-size: 13px;
    }

    /* ---------- Badges ---------- */
    .badge {
        display: inline-block;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .04em;
        white-space: nowrap;
    }
    .badge--manager { background: var(--ink); color: #fff; }
    .badge--team-leader { background: var(--pink-light); color: var(--pink-deep); }
    .badge--staff { background: var(--card); color: var(--ink-soft); border: 1px solid var(--line-strong); }
    .badge--field-of-rides { background: var(--green-light); color: var(--green); }
    .badge--dino-adventure { background: var(--warn-soft); color: var(--warn); }
    .badge--roller-skates { background: var(--info-soft); color: var(--info); }
    .badge--missing { background: var(--warn-soft); color: var(--warn); }
    .badge--none { color: var(--muted); font-weight: 400; padding: 0; }
    .badge--active { background: var(--present-soft); color: var(--present); }
    .badge--inactive { background: var(--danger-soft); color: var(--danger); }

    /* ---------- Mobile cards (Salary / Payroll History) ---------- */
    .card-list { display: none; }
    .salary-card {
        background: var(--bg);
        border: 1px solid var(--line);
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 10px;
    }
    .salary-card:last-child { margin-bottom: 0; }
    .salary-card-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 10px; }
    .salary-card-name { font-family: var(--font-display); font-size: 14px; font-weight: 700; color: var(--ink); }
    .salary-card-top .salary-card-name { margin-bottom: 0; }
    .salary-card > .salary-card-name { margin-bottom: 10px; }
    .salary-card-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px 12px; }
    .salary-card-footer {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        padding-top: 10px;
        margin-top: 10px;
        border-top: 1px dashed var(--line-strong);
    }
    .scg-label { font-size: 9.5px; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: .6px; }
    .scg-val { font-size: 13px; font-weight: 600; color: var(--ink); margin-top: 2px; }
    .scg-val.is-deduct { color: var(--danger); }
    .scg-val.is-muted { color: var(--muted); }
    .scg-val.is-present { color: var(--present); font-size: 15px; }
    .net-tag { font-family: var(--font-display); font-size: 15px; font-weight: 700; color: var(--present); }
    .card-category-label {
        font-size: 10.5px;
        font-weight: 700;
        color: var(--pink-deep);
        text-transform: uppercase;
        letter-spacing: 1.2px;
        padding: 10px 0 6px;
    }

    /* ---------- Modals ---------- */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10000;
        align-items: center;
        justify-content: center;
        background: rgba(26, 21, 35, .45);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        animation: overlayFade .2s ease;
        padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-card {
        background: #fff;
        border-radius: 18px;
        width: 100%;
        max-width: 480px;
        max-height: calc(100vh - 40px);
        overflow-y: auto;
        box-shadow: var(--shadow-md);
        animation: cardPop .25s cubic-bezier(.34, 1.56, .64, 1);
        scrollbar-width: none;
        -ms-overflow-style: none;
    }
    .modal-card::-webkit-scrollbar { display: none; }
    .modal-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        padding: 20px 24px 16px;
        border-bottom: 1px solid var(--line);
    }
    .modal-head .eyebrow { font-size: .66rem; margin-bottom: 4px; }
    .modal-head h3 { font-family: var(--font-display); font-size: 1.1rem; font-weight: 700; color: var(--ink); }
    .modal-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }
    .modal-close {
        width: 32px;
        height: 32px;
        border-radius: 9px;
        border: none;
        background: var(--bg);
        color: var(--ink-soft);
        font-size: 14px;
        cursor: pointer;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .modal-close:hover { background: var(--pink-light); color: var(--pink-deep); }
    .modal-body { padding: 20px 24px; }
    .modal-foot { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px 22px; }

    /* Confirm dialogs */
    .confirm-card { max-width: 380px; text-align: center; }
    .confirm-icon {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        margin: 26px auto 4px;
    }
    .confirm-icon.warn { background: var(--danger-soft); color: var(--danger); }
    .confirm-icon.info { background: var(--present-soft); color: var(--present); }
    .confirm-body { padding: 4px 24px 22px; }
    .confirm-title { font-family: var(--font-display); font-size: 1.05rem; font-weight: 700; color: var(--ink); margin-bottom: 6px; }
    .confirm-message { font-size: 13px; color: var(--ink-soft); line-height: 1.5; }
    .confirm-foot { display: flex; justify-content: center; gap: 10px; padding: 0 24px 26px; }
    .confirm-foot button { flex: 1; }

    /* ---------- Toasts ---------- */
    #toastStack {
        position: fixed;
        top: 22px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 20000;
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
        padding: 14px 16px;
        border-radius: 14px;
        border-left: 4px solid var(--ink);
        box-shadow: var(--shadow-md);
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
        opacity: 0;
        transform: translateY(-16px) scale(.97);
        animation: toastIn .35s cubic-bezier(.34, 1.56, .64, 1) forwards;
        position: relative;
        overflow: hidden;
    }
    .toast.hide { animation: toastOut .28s ease forwards; }
    .toast .toast-icon {
        flex-shrink: 0;
        width: 26px;
        height: 26px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        color: #fff;
        background: var(--ink-soft);
    }
    .toast.toast-info { border-left-color: var(--pink-deep); }
    .toast.toast-info .toast-icon { background: var(--pink-deep); }
    .toast.toast-success { border-left-color: var(--present); }
    .toast.toast-success .toast-icon { background: var(--present); }
    .toast.toast-error { border-left-color: var(--danger); }
    .toast.toast-error .toast-icon { background: var(--danger); }
    .toast .toast-body { flex: 1; min-width: 0; padding-top: 2px; }
    .toast .toast-title { font-weight: 700; font-size: 12.5px; margin-bottom: 2px; color: var(--ink); }
    .toast .toast-msg { color: var(--ink-soft); font-size: 12.5px; word-break: break-word; }
    .toast .toast-close {
        flex-shrink: 0;
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        font-size: 13px;
        padding: 2px;
        line-height: 1;
        margin-top: 1px;
    }
    .toast .toast-close:hover { color: var(--ink); }
    .toast .toast-bar {
        position: absolute;
        left: 0;
        bottom: 0;
        height: 3px;
        border-radius: 0 0 0 14px;
        background: currentColor;
        opacity: .35;
        animation: toastShrink 3s linear forwards;
    }
    @keyframes toastIn { to { opacity: 1; transform: translateY(0) scale(1); } }
    @keyframes toastOut { to { opacity: 0; transform: translateY(-12px) scale(.96); } }
    @keyframes toastShrink { from { width: 100%; } to { width: 0%; } }

    /* ---------- Responsive ---------- */
    @media (max-width: 900px) {
        .toolbar { padding: 16px 18px; }
        .toolbar h2 { font-size: 1.1rem; }
        .box, .table-box { padding: 16px; }
        .stats-row { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .filter-bar input[type="text"] { max-width: none; width: 100%; }
        .filter-bar select { width: 100%; }
        .form-grid { grid-template-columns: 1fr; }
        .table-scroll { display: none; }
        .card-list { display: block; }
    }
    @media (max-width: 520px) {
        .stats-row { grid-template-columns: 1fr; }
        #toastStack { top: 14px; max-width: calc(100% - 24px); padding: 0; }
    }
</style>
