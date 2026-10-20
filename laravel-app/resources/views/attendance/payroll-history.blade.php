@extends('layouts.sidebar')

@section('title', 'Payroll History')

@section('styles')
    @include('partials.ui-theme')
    <style>
        .batch-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--pink-pale);
            color: var(--pink-deep);
            font-family: var(--font-sans);
            font-weight: 700;
            font-size: 11px;
            padding: 3px 10px;
            border-radius: 999px;
            margin-left: 10px;
        }

        /* ---- Filters ---- */
        .filters { display: flex; gap: 10px; align-items: flex-end; padding-bottom: 4px; flex-wrap: wrap; }
        .filters label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .8px;
            display: block;
            margin-bottom: 4px;
        }
        .filter-group { display: flex; flex-direction: column; }
        .divider-v { width: 1px; height: 36px; background: var(--line); align-self: flex-end; margin-bottom: 2px; }
        .filters .filter-clear { align-self: flex-end; margin-bottom: 2px; }

        /* ---- Payroll Period dropdown with checklist ---- */
        .cdd { position: relative; }
        .cdd-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            padding: 9px 12px;
            font-size: 13px;
            color: var(--ink-soft);
            background: var(--bg);
            cursor: pointer;
            text-align: left;
        }
        .cdd-toggle:hover, .cdd.open .cdd-toggle { border-color: var(--pink); background: #fff; }
        .cdd-toggle i { font-size: 11px; color: var(--muted); transition: transform .15s ease; }
        .cdd.open .cdd-toggle i { transform: rotate(180deg); }
        .cdd-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            z-index: 120;
            min-width: 100%;
            width: max-content;
            max-width: min(620px, 92vw);
            background: #fff;
            border: 1px solid var(--line-strong);
            border-radius: 12px;
            box-shadow: var(--shadow-md);
        }
        .cdd.open .cdd-menu { display: block; }
        .cdd-head { padding: 8px 14px; border-bottom: 1px solid var(--line); }
        .filters label.cdd-selectall {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 700;
            color: var(--ink);
            cursor: pointer;
            text-transform: none;
            letter-spacing: 0;
            margin: 0;
        }
        .cdd-list { max-height: 300px; overflow-y: auto; padding: 4px 0; }
        .cdd-item { display: flex; align-items: center; gap: 10px; padding: 7px 14px; cursor: pointer; font-size: 13px; color: var(--ink-soft); }
        .cdd-item:hover { background: var(--pink-pale); }
        .cdd-item.active .cdd-text { font-weight: 700; color: var(--pink-deep); }
        .cdd-item:has(.cdd-cb:checked) { background: var(--danger-soft); }
        .cdd-item input, .cdd-selectall input { width: 16px; height: 16px; accent-color: var(--danger); cursor: pointer; flex-shrink: 0; }
        .cdd-text { flex: 1; }
        .cdd-foot { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 14px; border-top: 1px solid var(--line); }
        .cdd-count { font-size: 12px; color: var(--muted); }
        .cdd-delete { padding: 7px 14px; font-size: 12.5px; }

        .empty-state { text-align: center; padding: 32px 20px; color: var(--muted); font-size: 13px; }

        /* ---- Loading overlay (shown while switching Payroll Period) ---- */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 35, .55);
            backdrop-filter: blur(2px);
            z-index: 10001;
            align-items: center;
            justify-content: center;
        }
        .loading-overlay.active { display: flex; }
        .loading-box {
            background: var(--card);
            border-radius: 20px;
            padding: 28px 40px;
            box-shadow: var(--shadow-md);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            min-width: 260px;
        }
        .loading-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--pink-pale);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            position: relative;
        }
        .loading-icon-wrap i { font-size: 22px; color: var(--pink-deep); }
        .loading-ring {
            position: absolute;
            inset: -4px;
            border-radius: 50%;
            border: 3px solid var(--pink-light);
            border-top-color: var(--pink-deep);
            animation: spin .9s linear infinite;
        }
        .loading-title {
            font-size: 13px;
            font-weight: 800;
            color: var(--ink);
            text-transform: uppercase;
            letter-spacing: 1.6px;
            margin-bottom: 6px;
        }
        .loading-dots { display: flex; gap: 5px; }
        .loading-dots span {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: var(--pink);
            animation: dotPulse 1.2s ease-in-out infinite;
        }
        .loading-dots span:nth-child(2) { animation-delay: .15s; }
        .loading-dots span:nth-child(3) { animation-delay: .3s; }
        @keyframes dotPulse {
            0%, 80%, 100% { opacity: .25; transform: scale(.8); }
            40% { opacity: 1; transform: scale(1); }
        }

        @media (max-width: 900px) {
            .filters { flex-direction: column; align-items: stretch; gap: 10px; }
            .filter-group { width: 100%; }
            .filter-group select, .filter-group input[type=text] { width: 100%; }
            .divider-v { display: none; }
            .filters .btn-danger { width: 100%; margin-left: 0 !important; }
        }
    </style>
@endsection

@section('content')

    @php $grouped = $salaries->groupBy('category'); @endphp

    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Payroll and Payslip</div>
             <h2 style="font-family: Arial, Helvetica, sans-serif">Payroll History</h2>
        </div>
    </div>

    <div class="stats-row" style="--cols:4;">
        <div class="stat-card">
            <div class="label">Total employees</div>
            <div class="value pink">{{ $totalEmployees }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total basic pay</div>
            <div class="value">&#8369;{{ number_format($totalBasic, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total deductions</div>
            <div class="value deduct">&#8369;{{ number_format($totalDeductions, 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total net salary</div>
            <div class="value present">&#8369;{{ number_format($totalNet, 2) }}</div>
        </div>
    </div>

    <div class="box" style="overflow:visible;">
        <h3>
            Filter Records
            @if ($selectedBatch && $isLatestBatch)
                <span class="batch-tag"><i class="fa-solid fa-star"></i> Latest Batch</span>
            @endif
        </h3>

        <form method="GET" action="{{ route('payroll-history') }}" id="filterForm">
            <div class="filters">
                <div class="filter-group" style="min-width:280px;">
                    <label>Payroll Period</label>
                    <select name="batch" id="batchSelect" style="display:none;">
                        @forelse($batches as $b)
                            <option value="{{ $b->batch_id }}" {{ $selectedBatch === $b->batch_id ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::parse($b->pay_date)->format('M j, Y') }}
                                &mdash; {{ $b->cutoff_type }} Cutoff
                                &middot; Generated {{ \Carbon\Carbon::parse($b->generated_at)->format('M j, Y g:i A') }}
                                {{ $b->batch_id === ($batches->first()->batch_id ?? null) ? '(Latest)' : '' }}
                            </option>
                        @empty
                            <option value="">No payroll batches yet</option>
                        @endforelse
                    </select>

                    @php
                        $batchLabel = fn($b, $isFirst) => \Carbon\Carbon::parse($b->pay_date)->format('M j, Y')
                            . ' — ' . $b->cutoff_type . ' Cutoff · Generated '
                            . \Carbon\Carbon::parse($b->generated_at)->format('M j, Y g:i A')
                            . ($isFirst ? ' (Latest)' : '');
                        $currentLabel = 'No payroll batches yet';
                        foreach ($batches as $i => $b) {
                            if ((string) $selectedBatch === (string) $b->batch_id) {
                                $currentLabel = $batchLabel($b, $i === 0);
                            }
                        }
                    @endphp

                    <div class="cdd" id="batchDropdown">
                        <button type="button" class="cdd-toggle" id="cddToggle">
                            <span>{{ $currentLabel }}</span>
                            <i class="fa-solid fa-chevron-down"></i>
                        </button>

                        @if ($batches->isNotEmpty())
                            <div class="cdd-menu" id="cddMenu">
                                <div class="cdd-head">
                                    <label class="cdd-selectall">
                                        <input type="checkbox" id="cddSelectAll"> Select All
                                    </label>
                                </div>
                                <div class="cdd-list">
                                    @foreach ($batches as $i => $b)
                                        <div class="cdd-item {{ (string) $selectedBatch === (string) $b->batch_id ? 'active' : '' }}"
                                            data-value="{{ $b->batch_id }}">
                                            <input type="checkbox" class="cdd-cb" form="bulkDeleteForm" name="batches[]"
                                                value="{{ \Carbon\Carbon::parse($b->batch_id)->format('Y-m-d H:i:s') }}">
                                            <span class="cdd-text">{{ $batchLabel($b, $i === 0) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="cdd-foot">
                                    <span class="cdd-count" id="cddCount">0 selected</span>
                                    <button type="button" class="btn-danger cdd-delete" id="cddDelete" disabled>
                                        <i class="fa-regular fa-trash-can"></i> Delete Selected
                                    </button>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="divider-v"></div>

                <div class="filter-group">
                    <label>Category</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        @foreach ($grouped as $cat => $items)
                            <option value="{{ strtolower($cat ?: 'uncategorized') }}">{{ $cat ?: 'Uncategorized' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <label>Employee</label>
                    <input type="text" id="employeeSearch" placeholder="Search employee&hellip;" style="width:190px;"
                        autocomplete="off">
                </div>
                <button type="button" class="filter-clear" id="filterClear">Clear filters</button>

                @if ($selectedBatch)
                    <button type="button" class="btn-danger" style="margin-left:auto;" onclick="confirmDeleteBatch()">
                        <i class="fa-regular fa-trash-can"></i> Delete This Batch
                    </button>
                @endif
            </div>
        </form>
    </div>

    <div class="box">
        <h3>Payroll Records</h3>

        {{-- Desktop / tablet table --}}
        <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Days Worked</th>
                        <th>Basic Pay</th>
                        <th>SSS</th>
                        <th>PhilHealth</th>
                        <th>Pag-IBIG</th>
                        <th>W/Tax</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($grouped as $category => $group)
                        <tr class="category-row" data-cat-row="{{ strtolower($category ?: 'uncategorized') }}">
                            <td colspan="9">{{ $category ?: 'Uncategorized' }}</td>
                        </tr>
                        @foreach ($group as $sal)
                            @php
                                // Same formula as the Salary page: Late + Undertime + government deductions + tax.
                                $totalDeductions =
                                    $sal->deduction +
                                    ($sal->undertime_deduction ?? 0) +
                                    $sal->sss_deduction +
                                    $sal->philhealth_deduction +
                                    $sal->pagibig_deduction +
                                    $sal->withholding_tax;
                            @endphp
                            <tr data-name="{{ strtolower($sal->name) }}"
                                data-category="{{ strtolower($sal->category ?: 'uncategorized') }}">
                                <td class="td-name">{{ $sal->name }}</td>
                                <td>{{ $sal->present_days ?? ($sal->days_worked ?? '-') }}</td>
                                <td>&#8369;{{ number_format($sal->basic_salary, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->sss_deduction, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->philhealth_deduction, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->pagibig_deduction, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($sal->withholding_tax, 2) }}</td>
                                <td class="deduction">&#8369;{{ number_format($totalDeductions, 2) }}</td>
                                <td class="money">&#8369;{{ number_format($sal->net_salary, 2) }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <p>No payroll records found.</p>
                                    <p style="margin-top:4px;">Generate salary first from the Salary &amp; Deductions page.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noMatchRow" style="display:none;">
                        <td colspan="9">No employees match your search or filters.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Mobile card list --}}
        <div class="card-list">
            @forelse($grouped as $category => $group)
                <div class="card-category-label" data-cat-row="{{ strtolower($category ?: 'uncategorized') }}">
                    {{ $category ?: 'Uncategorized' }}</div>
                @foreach ($group as $sal)
                    @php
                        $totalDeductions =
                            $sal->deduction +
                            ($sal->undertime_deduction ?? 0) +
                            $sal->sss_deduction +
                            $sal->philhealth_deduction +
                            $sal->pagibig_deduction +
                            $sal->withholding_tax;
                    @endphp
                    <div class="salary-card" data-name="{{ strtolower($sal->name) }}"
                        data-category="{{ strtolower($sal->category ?: 'uncategorized') }}">
                        <div class="salary-card-top">
                            <span class="salary-card-name">{{ $sal->name }}</span>
                            @if (strtolower($sal->category ?? '') === 'manager')
                                <span class="badge badge--manager">Manager</span>
                            @elseif (str_contains(strtolower($sal->category ?? ''), 'leader'))
                                <span class="badge badge--team-leader">Team Leader</span>
                            @else
                                <span class="badge badge--staff">Staff</span>
                            @endif
                        </div>
                        <div class="salary-card-grid">
                            <div>
                                <div class="scg-label">Days worked</div>
                                <div class="scg-val">{{ $sal->present_days ?? ($sal->days_worked ?? '-') }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Basic pay</div>
                                <div class="scg-val">&#8369;{{ number_format($sal->basic_salary, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">SSS</div>
                                <div class="scg-val is-deduct">&#8369;{{ number_format($sal->sss_deduction, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">PhilHealth</div>
                                <div class="scg-val is-deduct">&#8369;{{ number_format($sal->philhealth_deduction, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Pag-IBIG</div>
                                <div class="scg-val is-deduct">&#8369;{{ number_format($sal->pagibig_deduction, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Withholding tax</div>
                                <div class="scg-val is-deduct">&#8369;{{ number_format($sal->withholding_tax, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Total deductions</div>
                                <div class="scg-val is-deduct">&#8369;{{ number_format($totalDeductions, 2) }}</div>
                            </div>
                        </div>
                        <div class="salary-card-footer">
                            <span class="net-tag">&#8369;{{ number_format($sal->net_salary, 2) }}</span>
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="empty-state">
                    <p>No payroll records found.</p>
                    <p style="margin-top:4px;">Generate salary first from the Salary &amp; Deductions page.</p>
                </div>
            @endforelse
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    {{-- Hidden form used to submit the Delete Batch action --}}
    @if ($selectedBatch)
        <form id="deleteBatchForm" method="POST" action="{{ route('payroll-history.delete-batch', $selectedBatch) }}"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
    @endif

    {{-- Bulk delete form (the checkboxes in the dropdown are linked to it via form="bulkDeleteForm") --}}
    <form id="bulkDeleteForm" method="POST" action="{{ route('payroll-history.bulkDelete') }}" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- Loading overlay: shown while switching Payroll Period --}}
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="loading-icon-wrap">
                <i class="fa-solid fa-file-invoice-dollar"></i>
                <span class="loading-ring"></span>
            </div>
            <div class="loading-title">Records Processing</div>
            <div class="loading-dots"><span></span><span></span><span></span></div>
        </div>
    </div>

    {{-- Delete single batch --}}
    <div class="modal-overlay" id="deleteModalOverlay">
        <div class="modal-card confirm-card" role="alertdialog" aria-modal="true">
            <div class="confirm-icon warn"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="confirm-body">
                <div class="confirm-title">Delete this batch?</div>
                <div class="confirm-message">This batch will be permanently deleted.</div>
            </div>
            <div class="confirm-foot">
                <button type="button" class="btn-ghost" id="cancelDeleteModal">Cancel</button>
                <button type="button" class="btn-danger" id="confirmDeleteModal">
                    <i class="fa-regular fa-trash-can"></i> Delete Batch
                </button>
            </div>
        </div>
    </div>

    {{-- Delete selected batches --}}
    <div class="modal-overlay" id="bulkModalOverlay">
        <div class="modal-card confirm-card" role="alertdialog" aria-modal="true">
            <div class="confirm-icon warn"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="confirm-body">
                <div class="confirm-title">Delete selected batches?</div>
                <div class="confirm-message" id="bulkModalSub">The selected batches will be permanently deleted.</div>
            </div>
            <div class="confirm-foot">
                <button type="button" class="btn-ghost" id="cancelBulkModal">Cancel</button>
                <button type="button" class="btn-danger" id="confirmBulkModal">
                    <i class="fa-regular fa-trash-can"></i> Delete
                </button>
            </div>
        </div>
    </div>

    @include('partials.ui-toast')

@endsection

@push('scripts')
    <script>
        /* ---- Payroll Period dropdown with checklist ---- */
        (function() {
            const dd = document.getElementById('batchDropdown');
            if (!dd) return;
            const toggle = document.getElementById('cddToggle');
            const menu = document.getElementById('cddMenu');
            const select = document.getElementById('batchSelect');
            if (!menu) return;
            const selectAll = document.getElementById('cddSelectAll');
            const countEl = document.getElementById('cddCount');
            const delBtn = document.getElementById('cddDelete');
            const checks = () => Array.from(menu.querySelectorAll('.cdd-cb'));

            function refresh() {
                const n = checks().filter(c => c.checked).length;
                countEl.textContent = n + ' selected';
                delBtn.disabled = n === 0;
                selectAll.checked = n > 0 && n === checks().length;
            }

            toggle.addEventListener('click', (e) => {
                e.stopPropagation();
                dd.classList.toggle('open');
            });
            document.addEventListener('click', (e) => {
                if (!dd.contains(e.target)) dd.classList.remove('open');
            });

            // Clicking a checkbox selects the batch for deletion. Clicking the label opens that batch.
            menu.addEventListener('click', (e) => {
                e.stopPropagation();
                if (e.target.classList.contains('cdd-cb')) { refresh(); return; }
                const item = e.target.closest('.cdd-item');
                if (!item) return;
                select.value = item.dataset.value;
                select.dispatchEvent(new Event('change'));
                dd.classList.remove('open');
            });

            selectAll.addEventListener('change', () => {
                checks().forEach(c => c.checked = selectAll.checked);
                refresh();
            });

            const overlay = document.getElementById('bulkModalOverlay');
            const sub = document.getElementById('bulkModalSub');
            const cancel = document.getElementById('cancelBulkModal');
            const confirmBtn = document.getElementById('confirmBulkModal');

            delBtn.addEventListener('click', () => {
                const n = checks().filter(c => c.checked).length;
                if (!n) return;
                sub.textContent = n + ' payroll batch(es) will be permanently deleted. This cannot be undone.';
                dd.classList.remove('open');
                overlay.classList.add('active');
            });
            cancel.addEventListener('click', () => overlay.classList.remove('active'));
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) overlay.classList.remove('active');
            });
            confirmBtn.addEventListener('click', () => {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner"></span> Deleting...';
                document.getElementById('bulkDeleteForm').submit();
            });
        })();

        function confirmDeleteBatch() {
            document.getElementById('deleteModalOverlay').classList.add('active');
        }

        (function() {
            const modalOverlay = document.getElementById('deleteModalOverlay');
            const cancelBtn = document.getElementById('cancelDeleteModal');
            const confirmBtn = document.getElementById('confirmDeleteModal');
            if (!modalOverlay) return;

            cancelBtn.addEventListener('click', () => modalOverlay.classList.remove('active'));
            modalOverlay.addEventListener('click', (e) => {
                if (e.target === modalOverlay) modalOverlay.classList.remove('active');
            });
            confirmBtn.addEventListener('click', () => {
                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner"></span> Deleting...';
                document.getElementById('deleteBatchForm').submit();
            });
        })();

        /* ---- Loading overlay: shown when the Payroll Period (batch) changes ---- */
        (function() {
            const batchSelect = document.getElementById('batchSelect');
            const filterForm = document.getElementById('filterForm');
            const loadingOverlay = document.getElementById('loadingOverlay');
            if (!batchSelect || !filterForm || !loadingOverlay) return;

            batchSelect.addEventListener('change', () => {
                loadingOverlay.classList.add('active');
                // Double requestAnimationFrame makes sure the overlay is painted before navigating.
                requestAnimationFrame(() => {
                    requestAnimationFrame(() => filterForm.submit());
                });
            });

            filterForm.addEventListener('submit', () => loadingOverlay.classList.add('active'));
        })();

        /* ---- Instant client-side search, category filter, and pagination ---- */
        (function() {
            const searchInput = document.getElementById('employeeSearch');
            const categoryFilter = document.getElementById('categoryFilter');
            const clearBtn = document.getElementById('filterClear');
            const noMatchRow = document.getElementById('noMatchRow');
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-scroll table tbody');
            const cardList = document.querySelector('.card-list');
            if (!searchInput || !tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            function groupRows(container, catSelector, itemSelector) {
                const groups = [];
                let current = null;
                Array.from(container.children).forEach(el => {
                    if (el.matches(catSelector)) {
                        current = { catEl: el, rows: [] };
                        groups.push(current);
                    } else if (el.matches(itemSelector) && current) {
                        current.rows.push(el);
                    }
                });
                return groups;
            }

            const tableGroups = groupRows(tbody, '.category-row', 'tr[data-name]');
            const cardGroups = cardList ? groupRows(cardList, '.card-category-label', '.salary-card[data-name]') : [];

            function getMatches() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const cat = categoryFilter ? (categoryFilter.value || '') : '';
                const matches = [];
                tableGroups.forEach(g => g.rows.forEach(row => {
                    const ok = (!q || row.dataset.name.indexOf(q) !== -1) && (!cat || row.dataset.category === cat);
                    if (ok) matches.push(row);
                }));
                return matches;
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

            function applyVisibility(groups, visibleKeys) {
                groups.forEach(g => {
                    let groupVisible = false;
                    g.rows.forEach(row => {
                        const show = visibleKeys.has(row.dataset.name + '|' + row.dataset.category);
                        row.style.display = show ? '' : 'none';
                        if (show) groupVisible = true;
                    });
                    g.catEl.style.display = groupVisible ? '' : 'none';
                });
            }

            function render() {
                const matches = getMatches();
                const totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const visible = matches.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);
                const visibleKeys = new Set(visible.map(r => r.dataset.name + '|' + r.dataset.category));

                applyVisibility(tableGroups, visibleKeys);
                applyVisibility(cardGroups, visibleKeys);

                if (noMatchRow) noMatchRow.style.display = matches.length ? 'none' : '';
                renderPagination(totalPages);
            }

            function applyFilters() {
                currentPage = 1;
                render();
            }

            searchInput.addEventListener('input', applyFilters);
            if (categoryFilter) categoryFilter.addEventListener('change', applyFilters);
            if (clearBtn) clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                if (categoryFilter) categoryFilter.value = '';
                applyFilters();
            });

            if (tableGroups.some(g => g.rows.length)) render();
        })();
    </script>
@endpush