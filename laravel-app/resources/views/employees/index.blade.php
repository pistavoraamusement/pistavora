@extends('layouts.sidebar')

@section('title', 'Employees')

@section('styles')
    @include('partials.ui-theme')
    <style>
        table { min-width: 640px; }
        .form-group input#addPinCode, .form-group input#editPinCode { letter-spacing: 6px; font-weight: 700; }

        .row-actions { display: flex; gap: 8px; }
        .row-actions form { display: inline; }
        .btn-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .8rem;
            transition: .15s;
        }
        .btn-icon.edit { background: var(--bg); color: var(--ink-soft); }
        .btn-icon.edit:hover { background: var(--pink-light); color: var(--pink-deep); }
        .btn-icon.deactivate { background: var(--danger-soft); color: var(--danger); }
        .btn-icon.deactivate:hover { background: var(--danger); color: #fff; }
        .btn-icon.activate { background: var(--present-soft); color: var(--present); }
        .btn-icon.activate:hover { background: var(--present); color: #fff; }

        .clear-confirm-input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 13px;
            text-align: center;
            font-weight: 700;
            letter-spacing: .05em;
        }

        @media (max-width: 900px) {
            .toolbar-actions { width: 100%; }
            .toolbar-actions button { width: 100%; }
        }
    </style>
@endsection

@section('content')

    @php
        // Areas for Team Leader and Staff. The Manager has no area.
        $areaOptions = ['Piyestarides', 'Dino Lakbay', 'Skataya'];
    @endphp

    <div class="toolbar no-print">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Employee Records</div>
            <h2 style="font-family: Arial, Helvetica, sans-serif">Employee Management</h2>
            <p class="sub">Manage your team's records, categories, and status.</p>
        </div>

        <div class="toolbar-actions">
            <button type="button" class="btn-ghost" id="openImportBtn">
                <i class="fa-solid fa-file-import"></i> Import CSV
            </button>
            <button type="button" class="btn-ghost is-danger" id="openClearAllBtn">
                <i class="fa-solid fa-trash"></i> Clear All
            </button>
            <button type="button" class="btn-primary" id="openAddBtn">
                <i class="fa-solid fa-user-plus"></i> Add Employee
            </button>
        </div>
    </div>

    <div class="stats-row no-print" style="--cols:3;">
        <div class="stat-card">
            <div class="label">Total Employees</div>
            <div class="value">{{ $totalCount }}</div>
            <div class="sub">Currently on record</div>
        </div>
        <div class="stat-card">
            <div class="label">Active</div>
            <div class="value present">{{ $activeCount }}</div>
            <div class="sub">Currently working</div>
        </div>
        <div class="stat-card">
            <div class="label">Inactive</div>
            <div class="value deduct">{{ $inactiveCount }}</div>
            <div class="sub">Deactivated records</div>
        </div>
    </div>

    <div class="table-box">

        <div class="filter-bar no-print">
            <span class="filter-label">Filter</span>
            <input type="text" id="empSearch" placeholder="Search employee name&hellip;">
            <select id="empCategoryFilter">
                <option value="">All Categories</option>
                <option value="Manager">Manager</option>
                <option value="Team Leader">Team Leader</option>
                <option value="Staff">Staff</option>
            </select>
            <select id="empAreaFilter">
                <option value="">All Areas</option>
                @foreach ($areaOptions as $a)
                    <option value="{{ $a }}">{{ $a }}</option>
                @endforeach
            </select>
            <select id="empMonthFilter">
                <option value="">All Months (Hired)</option>
                <option value="01">January</option>
                <option value="02">February</option>
                <option value="03">March</option>
                <option value="04">April</option>
                <option value="05">May</option>
                <option value="06">June</option>
                <option value="07">July</option>
                <option value="08">August</option>
                <option value="09">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <select id="empSortBy">
                <option value="name">Sort: Name (A–Z)</option>
                <option value="tenure_desc">Sort: Longest Tenure First</option>
                <option value="tenure_asc">Sort: Most Recently Hired</option>
            </select>
            <button type="button" class="filter-toggle" data-type="active" id="filterActiveOnly">Active</button>
            <button type="button" class="filter-toggle" data-type="inactive" id="filterInactiveOnly">Inactive</button>
            <button type="button" class="filter-clear" id="filterClear">Clear filters</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:44px;">No</th>
                    <th>Name of Employee</th>
                    <th>Category</th>
                    <th>Area</th>
                    <th>Status</th>
                    <th>Date Hired</th>
                    <th style="width:90px;">Actions</th>
                </tr>
            </thead>
            <tbody id="empTableBody">
                @forelse ($employees as $index => $employee)
                    <tr data-name="{{ strtolower($employee->employee_name) }}"
                        data-category="{{ $employee->category }}"
                        data-area="{{ $employee->area ?? '' }}"
                        data-status="{{ $employee->status }}"
                        data-date-hired="{{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('Y-m-d') : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td class="emp-name-cell">{{ $employee->employee_name }}</td>
                        <td>
                            <span class="badge badge--{{ Str::slug($employee->category) }}">{{ $employee->category }}</span>
                        </td>
                        <td>
                            @if (!empty($employee->area))
                                <span class="badge badge--{{ Str::slug($employee->area) }}">{{ $employee->area }}</span>
                            @elseif ($employee->category === 'Manager')
                                <span class="badge badge--none" title="The Manager has no area">&mdash;</span>
                            @else
                                <span class="badge badge--missing">No area yet</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge--{{ $employee->status }}">{{ ucfirst($employee->status) }}</span>
                        </td>
                        <td>{{ $employee->date_hired ? \Carbon\Carbon::parse($employee->date_hired)->format('M d, Y') : '—' }}</td>
                        <td>
                            <div class="row-actions">
                                <button type="button" class="btn-icon edit" onclick='openEditModal(@json($employee))'>
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                @if ($employee->status === 'active')
                                    <form action="{{ route('employees.deactivate', $employee->id) }}" method="POST" id="deactivateForm{{ $employee->id }}">
                                        @csrf
                                    </form>
                                    <button type="button" class="btn-icon deactivate"
                                        onclick="openConfirmModal({
                                            title: 'Deactivate employee?',
                                            message: 'This will remove {{ addslashes($employee->employee_name) }} from the Manual Entry and QR Check-in employee lists. Their attendance history will remain on record.',
                                            confirmLabel: 'Deactivate',
                                            variant: 'warn',
                                            formId: 'deactivateForm{{ $employee->id }}'
                                        })">
                                        <i class="fa-solid fa-user-slash"></i>
                                    </button>
                                @else
                                    <form action="{{ route('employees.activate', $employee->id) }}" method="POST" id="activateForm{{ $employee->id }}">
                                        @csrf
                                    </form>
                                    <button type="button" class="btn-icon activate"
                                        onclick="openConfirmModal({
                                            title: 'Reactivate employee?',
                                            message: 'This will restore {{ addslashes($employee->employee_name) }} to the Manual Entry and QR Check-in employee lists.',
                                            confirmLabel: 'Reactivate',
                                            variant: 'info',
                                            formId: 'activateForm{{ $employee->id }}'
                                        })">
                                        <i class="fa-solid fa-user-check"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:24px;color:var(--muted);text-align:center;">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div id="noMatchRow" style="display:none;">No employees match your search or filters.</div>

        <div class="pagination no-print" id="pagination"></div>

    </div>

    {{-- Add Employee Modal --}}
    <div id="addEmployeeOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <form method="POST" action="{{ route('employees.store') }}">
                @csrf
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">New Record</div>
                        <h3>Add Employee</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeAddModal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Employee Name</label>
                            <input type="text" name="employee_name" required maxlength="255">
                        </div>
                        <div class="form-group full">
                            <label>Category</label>
                            <select name="category" id="addCategory" required>
                                <option value="">Select category</option>
                                <option value="Manager">Manager</option>
                                <option value="Team Leader">Team Leader</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                        <div class="form-group full" id="addAreaGroup">
                            <label>Area</label>
                            <select name="area" id="addArea">
                                <option value="">Select area</option>
                                @foreach ($areaOptions as $a)
                                    <option value="{{ $a }}">{{ $a }}</option>
                                @endforeach
                            </select>
                            <p class="form-hint" id="addAreaHint">Required for Team Leader and Staff.</p>
                        </div>
                        <div class="form-group full">
                            <label>Date Hired</label>
                            <input type="date" name="date_hired">
                        </div>
                        <div class="form-group full">
                            <label>PIN Code (optional)</label>
                            <input type="password" inputmode="numeric" pattern="[0-9]*" name="pin_code" id="addPinCode"
                                maxlength="4" autocomplete="off" placeholder="4-digit PIN, e.g. 1234">
                            <p class="form-hint">Used for QR self check-in. You can set this later instead.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelAddModal">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Add Employee</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Employee Modal --}}
    <div id="editEmployeeOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <form id="editEmployeeForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Update Record</div>
                        <h3>Edit Employee</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeEditModal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group full">
                            <label>Employee Name</label>
                            <input type="text" name="employee_name" id="editName" required maxlength="255">
                        </div>
                        <div class="form-group full">
                            <label>Category</label>
                            <select name="category" id="editCategory" required>
                                <option value="Manager">Manager</option>
                                <option value="Team Leader">Team Leader</option>
                                <option value="Staff">Staff</option>
                            </select>
                        </div>
                        <div class="form-group full" id="editAreaGroup">
                            <label>Area</label>
                            <select name="area" id="editArea">
                                <option value="">— No area yet —</option>
                                @foreach ($areaOptions as $a)
                                    <option value="{{ $a }}">{{ $a }}</option>
                                @endforeach
                            </select>
                            <p class="form-hint" id="editAreaHint">For Team Leader and Staff.</p>
                        </div>
                        <div class="form-group full">
                            <label>Date Hired</label>
                            <input type="date" name="date_hired" id="editDateHired">
                        </div>
                        <div class="form-group full">
                            <label>PIN Code</label>
                            <input type="password" inputmode="numeric" pattern="[0-9]*" name="pin_code" id="editPinCode"
                                maxlength="4" autocomplete="off" placeholder="Leave blank to keep current PIN">
                            <p class="form-hint">The current PIN is hashed and can't be shown here. Leave this blank to keep it unchanged, or enter 4 digits to replace it.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelEditModal">Cancel</button>
                    <button type="submit" class="btn-primary"><i class="fa-solid fa-check"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Import CSV Modal --}}
    <div id="importOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card" role="dialog" aria-modal="true">
            <form method="POST" action="{{ route('employees.import') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="modal-head">
                    <div>
                        <div class="eyebrow">Bulk Add</div>
                        <h3>Import Employees (CSV)</h3>
                    </div>
                    <button type="button" class="modal-close" id="closeImportModal" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <div class="modal-body">
                    <div class="form-group full">
                        <label>CSV File</label>
                        <input type="file" name="import_file" accept=".csv,.txt" required>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-ghost" id="cancelImportModal">Cancel</button>
                    <button type="submit" class="btn-primary" id="importSubmitBtn"><i class="fa-solid fa-file-import"></i> Import</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Custom Confirm Modal (replaces native browser confirm()) --}}
    <div id="confirmOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card confirm-card" role="alertdialog" aria-modal="true">
            <div class="confirm-icon warn" id="confirmIcon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="confirm-body">
                <div class="confirm-title" id="confirmTitle">Are you sure?</div>
                <div class="confirm-message" id="confirmMessage"></div>
            </div>
            <div class="confirm-foot">
                <button type="button" class="btn-ghost" id="confirmCancelBtn">Cancel</button>
                <button type="button" class="btn-danger" id="confirmOkBtn">Confirm</button>
            </div>
        </div>
    </div>

    {{-- Clear All Employees Modal: type-to-confirm because this is permanent --}}
    <div id="clearAllOverlay" class="modal-overlay no-print" aria-hidden="true">
        <div class="modal-card confirm-card" role="alertdialog" aria-modal="true">
            <div class="confirm-icon warn"><i class="fa-solid fa-trash"></i></div>
            <div class="confirm-body">
                <div class="confirm-title">Clear all employees?</div>
                <div class="confirm-message">
                    This will permanently delete ALL {{ $totalCount }} employee record(s) from the system.
                    This does not affect existing Attendance or Payroll history.
                    Use this before importing a fresh CSV with your complete employee list.
                    <br><br>
                    Type <strong>DELETE</strong> below to confirm.
                </div>
                <form action="{{ route('employees.clear-all') }}" method="POST" id="clearAllForm" style="margin-top:14px;">
                    @csrf
                    @method('DELETE')
                    <input type="text" name="confirm_text" id="clearAllConfirmInput" class="clear-confirm-input"
                        placeholder="Type DELETE" autocomplete="off">
                </form>
            </div>
            <div class="confirm-foot">
                <button type="button" class="btn-ghost" id="clearAllCancelBtn">Cancel</button>
                <button type="button" class="btn-danger" id="clearAllOkBtn" disabled>Delete All</button>
            </div>
        </div>
    </div>

    @include('partials.ui-toast')

@endsection

@push('scripts')
<script>
    // Digits only, capped at 4 characters, for both PIN fields.
    function digitsOnly4(e) {
        e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
    }
    document.getElementById('addPinCode')?.addEventListener('input', digitsOnly4);
    document.getElementById('editPinCode')?.addEventListener('input', digitsOnly4);

    // The Area field is always visible. For a Manager it is disabled and empty (not sent to the server).
    // For Team Leader and Staff it is enabled, and required on the Add form.
    function syncAreaField(categorySelect, areaSelect, hintEl, requireArea) {
        const cat = categorySelect.value;
        const isManager = cat === 'Manager';
        const needsArea = cat === 'Team Leader' || cat === 'Staff';

        areaSelect.disabled = isManager;
        if (isManager) areaSelect.value = '';
        areaSelect.required = requireArea && needsArea;

        if (hintEl) {
            hintEl.textContent = isManager
                ? 'The Manager has no area.'
                : (requireArea ? 'Required for Team Leader and Staff.' : 'For Team Leader and Staff.');
        }
    }

    const addCategory = document.getElementById('addCategory');
    const addArea = document.getElementById('addArea');
    const addAreaHint = document.getElementById('addAreaHint');
    addCategory?.addEventListener('change', () => syncAreaField(addCategory, addArea, addAreaHint, true));
    syncAreaField(addCategory, addArea, addAreaHint, true);

    const editCategory = document.getElementById('editCategory');
    const editArea = document.getElementById('editArea');
    const editAreaHint = document.getElementById('editAreaHint');
    editCategory?.addEventListener('change', () => syncAreaField(editCategory, editArea, editAreaHint, false));

    // ── Add Employee modal ──
    const addOverlay = document.getElementById('addEmployeeOverlay');
    document.getElementById('openAddBtn')?.addEventListener('click', () => {
        addOverlay.classList.add('active');
        addOverlay.setAttribute('aria-hidden', 'false');
    });
    function closeAdd() {
        addOverlay.classList.remove('active');
        addOverlay.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('closeAddModal')?.addEventListener('click', closeAdd);
    document.getElementById('cancelAddModal')?.addEventListener('click', closeAdd);
    addOverlay?.addEventListener('click', (e) => { if (e.target === addOverlay) closeAdd(); });

    // ── Edit Employee modal ──
    const editOverlay = document.getElementById('editEmployeeOverlay');
    function openEditModal(employee) {
        document.getElementById('editName').value = employee.employee_name;
        document.getElementById('editCategory').value = employee.category;
        editArea.disabled = false;
        editArea.value = employee.area || '';
        syncAreaField(editCategory, editArea, editAreaHint, false);
        document.getElementById('editDateHired').value = employee.date_hired ? employee.date_hired.substring(0, 10) : '';
        // Always start blank: the stored value is a hash and can never be shown back.
        // Leaving this blank on submit means "keep the current PIN unchanged".
        document.getElementById('editPinCode').value = '';
        document.getElementById('editEmployeeForm').action = '/employees/' + employee.id;
        editOverlay.classList.add('active');
        editOverlay.setAttribute('aria-hidden', 'false');
    }
    function closeEdit() {
        editOverlay.classList.remove('active');
        editOverlay.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('closeEditModal')?.addEventListener('click', closeEdit);
    document.getElementById('cancelEditModal')?.addEventListener('click', closeEdit);
    editOverlay?.addEventListener('click', (e) => { if (e.target === editOverlay) closeEdit(); });

    // ── Import CSV modal ──
    const importOverlay = document.getElementById('importOverlay');
    document.getElementById('openImportBtn')?.addEventListener('click', () => {
        importOverlay.classList.add('active');
        importOverlay.setAttribute('aria-hidden', 'false');
    });
    function closeImport() {
        importOverlay.classList.remove('active');
        importOverlay.setAttribute('aria-hidden', 'true');
    }
    document.getElementById('closeImportModal')?.addEventListener('click', closeImport);
    document.getElementById('cancelImportModal')?.addEventListener('click', closeImport);
    importOverlay?.addEventListener('click', (e) => { if (e.target === importOverlay) closeImport(); });

    document.getElementById('importForm')?.addEventListener('submit', function() {
        const btn = document.getElementById('importSubmitBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';
    });

    // ── Custom Confirm modal (replaces native browser confirm()) ──
    const confirmOverlay = document.getElementById('confirmOverlay');
    const confirmIcon = document.getElementById('confirmIcon');
    const confirmTitle = document.getElementById('confirmTitle');
    const confirmMessage = document.getElementById('confirmMessage');
    const confirmOkBtn = document.getElementById('confirmOkBtn');
    const confirmCancelBtn = document.getElementById('confirmCancelBtn');
    let pendingFormId = null;

    function openConfirmModal({ title, message, confirmLabel, variant, formId }) {
        confirmTitle.textContent = title || 'Are you sure?';
        confirmMessage.textContent = message || '';
        confirmOkBtn.textContent = confirmLabel || 'Confirm';
        pendingFormId = formId;

        confirmIcon.classList.remove('warn', 'info');
        if (variant === 'info') {
            confirmIcon.classList.add('info');
            confirmIcon.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
            confirmOkBtn.className = 'btn-primary';
        } else {
            confirmIcon.classList.add('warn');
            confirmIcon.innerHTML = '<i class="fa-solid fa-triangle-exclamation"></i>';
            confirmOkBtn.className = 'btn-danger';
        }

        confirmOverlay.classList.add('active');
        confirmOverlay.setAttribute('aria-hidden', 'false');
    }

    function closeConfirmModal() {
        confirmOverlay.classList.remove('active');
        confirmOverlay.setAttribute('aria-hidden', 'true');
        pendingFormId = null;
    }

    confirmOkBtn?.addEventListener('click', () => {
        if (pendingFormId) {
            const form = document.getElementById(pendingFormId);
            if (form) form.submit();
        }
        closeConfirmModal();
    });
    confirmCancelBtn?.addEventListener('click', closeConfirmModal);
    confirmOverlay?.addEventListener('click', (e) => { if (e.target === confirmOverlay) closeConfirmModal(); });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            if (addOverlay.classList.contains('active')) closeAdd();
            if (editOverlay.classList.contains('active')) closeEdit();
            if (importOverlay.classList.contains('active')) closeImport();
            if (confirmOverlay.classList.contains('active')) closeConfirmModal();
            if (clearAllOverlay.classList.contains('active')) closeClearAll();
        }
    });

    // ── Clear All Employees modal (type-to-confirm) ──
    const clearAllOverlay = document.getElementById('clearAllOverlay');
    const clearAllConfirmInput = document.getElementById('clearAllConfirmInput');
    const clearAllOkBtn = document.getElementById('clearAllOkBtn');
    const clearAllForm = document.getElementById('clearAllForm');

    document.getElementById('openClearAllBtn')?.addEventListener('click', () => {
        clearAllConfirmInput.value = '';
        clearAllOkBtn.disabled = true;
        clearAllOverlay.classList.add('active');
        clearAllOverlay.setAttribute('aria-hidden', 'false');
        setTimeout(() => clearAllConfirmInput.focus(), 100);
    });

    function closeClearAll() {
        clearAllOverlay.classList.remove('active');
        clearAllOverlay.setAttribute('aria-hidden', 'true');
    }

    document.getElementById('clearAllCancelBtn')?.addEventListener('click', closeClearAll);
    clearAllOverlay?.addEventListener('click', (e) => { if (e.target === clearAllOverlay) closeClearAll(); });

    clearAllConfirmInput?.addEventListener('input', function() {
        clearAllOkBtn.disabled = this.value.trim().toUpperCase() !== 'DELETE';
    });

    clearAllOkBtn?.addEventListener('click', () => {
        if (clearAllConfirmInput.value.trim().toUpperCase() === 'DELETE') {
            clearAllForm.submit();
        }
    });

    // ===== SEARCH, FILTER, SORT & PAGINATION =====
    (function() {
        const searchInput = document.getElementById('empSearch');
        const categoryFilter = document.getElementById('empCategoryFilter');
        const areaFilter = document.getElementById('empAreaFilter');
        const monthFilter = document.getElementById('empMonthFilter');
        const sortBy = document.getElementById('empSortBy');
        const activeToggle = document.getElementById('filterActiveOnly');
        const inactiveToggle = document.getElementById('filterInactiveOnly');
        const clearBtn = document.getElementById('filterClear');
        const noMatchRow = document.getElementById('noMatchRow');
        const paginationEl = document.getElementById('pagination');
        const tbody = document.getElementById('empTableBody');
        if (!tbody) return;

        const PAGE_SIZE = 10;
        let currentPage = 1;

        const rows = Array.from(tbody.querySelectorAll('tr[data-name]'));

        function getMatches() {
            const q = (searchInput.value || '').trim().toLowerCase();
            const cat = categoryFilter.value || '';
            const area = areaFilter.value || '';
            const month = monthFilter.value || '';
            const wantActive = activeToggle.classList.contains('active');
            const wantInactive = inactiveToggle.classList.contains('active');

            return rows.filter(row => {
                const matchesName = !q || row.dataset.name.indexOf(q) !== -1;
                const matchesCategory = !cat || row.dataset.category === cat;
                const matchesArea = !area || row.dataset.area === area;
                const matchesMonth = !month || (row.dataset.dateHired && row.dataset.dateHired.substring(5, 7) === month);
                const matchesStatus = (!wantActive && !wantInactive) ||
                    (wantActive && row.dataset.status === 'active') ||
                    (wantInactive && row.dataset.status === 'inactive');
                return matchesName && matchesCategory && matchesArea && matchesMonth && matchesStatus;
            });
        }

        function sortMatches(matches) {
            const val = sortBy.value;
            if (val === 'tenure_desc' || val === 'tenure_asc') {
                // Longest tenure = the oldest date_hired (empty dates always sort last).
                return matches.slice().sort((a, b) => {
                    const da = a.dataset.dateHired || '';
                    const db = b.dataset.dateHired || '';
                    if (!da && !db) return 0;
                    if (!da) return 1;
                    if (!db) return -1;
                    return val === 'tenure_desc' ? da.localeCompare(db) : db.localeCompare(da);
                });
            }
            return matches; // 'name' = keep server order (already alphabetical)
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

        function render() {
            const matches = sortMatches(getMatches());
            const totalPages = Math.max(1, Math.ceil(matches.length / PAGE_SIZE));
            currentPage = Math.min(Math.max(currentPage, 1), totalPages);
            const visible = matches.slice((currentPage - 1) * PAGE_SIZE, currentPage * PAGE_SIZE);
            renderPagination(totalPages);

            // Sorting reorders the actual DOM rows so the order feels natural.
            visible.forEach(row => tbody.appendChild(row));

            const visibleSet = new Set(visible);
            rows.forEach(row => {
                row.style.display = visibleSet.has(row) ? '' : 'none';
            });

            if (noMatchRow) noMatchRow.style.display = matches.length ? 'none' : '';
        }

        function applyFilters() {
            currentPage = 1;
            render();
        }

        searchInput.addEventListener('input', applyFilters);
        categoryFilter.addEventListener('change', applyFilters);
        areaFilter.addEventListener('change', applyFilters);
        monthFilter.addEventListener('change', applyFilters);
        sortBy.addEventListener('change', applyFilters);
        activeToggle.addEventListener('click', () => {
            activeToggle.classList.toggle('active');
            inactiveToggle.classList.remove('active');
            applyFilters();
        });
        inactiveToggle.addEventListener('click', () => {
            inactiveToggle.classList.toggle('active');
            activeToggle.classList.remove('active');
            applyFilters();
        });
        clearBtn.addEventListener('click', () => {
            searchInput.value = '';
            categoryFilter.value = '';
            areaFilter.value = '';
            monthFilter.value = '';
            sortBy.value = 'name';
            activeToggle.classList.remove('active');
            inactiveToggle.classList.remove('active');
            applyFilters();
        });

        if (rows.length > 0) render();
    })();
</script>
@endpush