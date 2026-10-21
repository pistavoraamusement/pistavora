@extends('layouts.sidebar')

@section('title', 'Account Management')

@push('head')
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,600;8..60,700&display=swap"
        rel="stylesheet">
@endpush

@section('styles')
    <style>
        :root {
            --blue-bg: #E6F1FB;
            --blue-tx: #185FA5;
            --green-bg: #E7F5E1;
            --green-tx: #3B6D11;
            --amber-bg: #FCEEDA;
            --amber-tx: #854F0B;
            --gold-bg: #FFF3D6;
            --gold-tx: #946600;
            --red-bg: #FFE1E6;
            --red-tx: #A32D2D;
        }

        .serif {
            font-family: 'Source Serif 4', serif;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--ink-soft);
            font-weight: 600;
            font-size: .82rem;
            margin-bottom: 14px;
            transition: .15s;
        }

        .back-link:hover {
            color: var(--pink-dark);
        }

        /* ===== TOOLBAR — title, live stat strip, and primary action in one row ===== */
        .toolbar {
            background: var(--card);
            padding: 18px 26px;
            margin-bottom: 18px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .toolbar .eyebrow {
            font-size: .68rem;
            font-weight: 700;
            color: var(--pink-deep);
            text-transform: uppercase;
            letter-spacing: .09em;
            margin-bottom: 5px;
        }

        .toolbar h2 {
            font-family: 'Source Serif 4', serif;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--ink);
            white-space: nowrap;
        }

        .stat-strip {
            display: flex;
            align-items: center;
            flex: 1;
            justify-content: center;
            flex-wrap: wrap;
        }

        .stat-item {
            padding: 2px 24px;
            text-align: center;
        }

        .stat-item + .stat-item {
            border-left: 1px solid var(--line);
        }

        .stat-item .value {
            font-family: 'Source Serif 4', serif;
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--ink);
            line-height: 1;
        }

        .stat-item .label {
            font-size: .62rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .07em;
            margin-top: 5px;
            white-space: nowrap;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px 22px;
        }

        .form-label {
            font-size: .68rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .08em;
        }

        .form-input,
        .form-select {
            padding: 11px 14px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: .9rem;
            outline: none;
            width: 100%;
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--ink);
            transition: .15s;
        }

        .form-input:focus,
        .form-select:focus {
            border-color: var(--pink);
            box-shadow: 0 0 0 3px var(--pink-light);
            background: #fff;
        }

        .form-actions {
            display: flex;
            gap: 12px;
            margin-top: 18px;
        }

        .print-btn {
            padding: 11px 20px;
            background: var(--pink-deep);
            color: #fff;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, box-shadow .15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .print-btn:hover {
            background: var(--pink-dark);
            box-shadow: 0 0 0 3px var(--pink-light);
        }

        .btn-outline {
            padding: 11px 18px;
            background: #fff;
            color: var(--ink);
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 13px;
            font-family: 'Inter', sans-serif;
            white-space: nowrap;
            transition: background .15s ease, border-color .15s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-outline:hover {
            background: var(--bg);
            border-color: var(--pink);
        }

        .btn-danger {
            padding: 11px 22px;
            background: #E0524F;
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Inter', sans-serif;
            transition: .15s;
        }

        .btn-danger:hover {
            background: #C43F3C;
        }

        /* ===== TABLE CARD — section title and filters share one header row ===== */
        .table-box {
            background: var(--card);
            padding: 22px 26px 26px;
            border-radius: 16px;
            box-shadow: var(--shadow-sm);
        }

        .table-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 14px;
            padding-bottom: 16px;
            margin-bottom: 4px;
            border-bottom: 2px solid var(--pink-light);
        }

        .section-title {
            font-family: 'Source Serif 4', serif;
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--ink);
            display: flex;
            align-items: center;
            gap: 9px;
            white-space: nowrap;
        }

        .section-title i {
            color: var(--pink-dark);
        }

        .filter-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 9px;
        }

        .filter-bar input[type="text"],
        .filter-bar select {
            padding: 9px 12px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 12.5px;
            font-family: 'Inter', sans-serif;
            color: var(--ink-soft);
            background: var(--bg);
        }

        .filter-bar input[type="text"] {
            width: 210px;
        }

        .filter-bar select {
            cursor: pointer;
        }

        .filter-clear {
            background: none;
            border: none;
            color: var(--pink-deep);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: underline;
            font-family: 'Inter', sans-serif;
            padding: 9px 2px;
            white-space: nowrap;
        }

        .table-scroll {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        thead th {
            padding: 11px 14px;
            text-align: left;
            font-size: .72rem;
            font-weight: 600;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: .05em;
            background: var(--ink);
            border-bottom: none;
        }

        thead th:first-child {
            border-radius: 8px 0 0 8px;
        }

        thead th:last-child {
            border-radius: 0 8px 8px 0;
        }

        tbody td {
            padding: 12px 14px;
            font-size: .9rem;
            border-bottom: 1px solid var(--line);
            color: var(--ink);
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        tbody tr:hover td {
            background: var(--pink-pale);
        }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--pink) 0%, var(--pink-deep) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: .76rem;
            font-weight: 700;
            color: #fff;
            flex-shrink: 0;
        }

        .user-name {
            font-size: .9rem;
            font-weight: 600;
            color: var(--ink);
        }

        .user-username {
            font-size: .78rem;
            color: var(--muted);
        }

        .badge {
            display: inline-block;
            padding: 4px 13px;
            border-radius: 20px;
            font-size: .76rem;
            font-weight: 700;
        }

        .badge-admin {
            background: var(--gold-bg);
            color: var(--gold-tx);
        }

        .badge-tl {
            background: var(--blue-bg);
            color: var(--blue-tx);
        }

        .badge-cashier {
            background: var(--green-bg);
            color: var(--green-tx);
        }

        .badge-manager {
            background: var(--amber-bg);
            color: var(--amber-tx);
        }

        .action-btns {
            display: flex;
            gap: 8px;
            justify-content: flex-end;
        }

        .btn-edit {
            padding: 7px 14px;
            background: var(--blue-bg);
            border: none;
            border-radius: 8px;
            color: var(--blue-tx);
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: .15s;
        }

        .btn-edit:hover {
            filter: brightness(0.96);
        }

        .btn-delete {
            padding: 7px 14px;
            background: var(--red-bg);
            border: none;
            border-radius: 8px;
            color: var(--red-tx);
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            transition: .15s;
        }

        .btn-delete:hover {
            filter: brightness(0.96);
        }

        .table-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 14px;
            font-size: .78rem;
            color: var(--muted);
        }

        #noMatchRow td {
            padding: 26px !important;
            color: var(--muted) !important;
            text-align: center;
        }

        .alert-success {
            background: var(--green-bg);
            border: 1px solid #C0DD97;
            color: var(--green-tx);
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: .84rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-error {
            background: var(--red-bg);
            border: 1px solid #F7C1C1;
            color: var(--red-tx);
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 16px;
            font-size: .84rem;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(26, 21, 35, .55);
            backdrop-filter: blur(2px);
            z-index: 100;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal {
            background: #fff;
            border-radius: 18px;
            padding: 26px;
            width: 100%;
            max-width: 400px;
            box-shadow: var(--shadow-md);
        }

        .modal-wide {
            max-width: 520px;
        }

        .modal-title {
            font-family: 'Source Serif 4', serif;
            font-size: 1.02rem;
            font-weight: 700;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 9px;
            color: var(--ink);
        }

        .modal-title i {
            color: var(--pink-dark);
        }

        .modal-actions {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }

        .modal-confirm {
            max-width: 360px;
            text-align: center;
        }

        .modal-confirm .modal-icon {
            width: 54px;
            height: 54px;
            border-radius: 50%;
            background: var(--red-bg);
            color: #E0524F;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            margin: 0 auto 14px;
        }

        .modal-confirm .modal-title {
            justify-content: center;
            font-size: 1rem;
        }

        .modal-confirm .modal-text {
            font-size: .82rem;
            color: var(--ink-soft);
            line-height: 1.5;
            margin-bottom: 4px;
        }

        .modal-confirm .modal-text strong {
            color: var(--ink);
        }

        .modal-confirm .modal-actions {
            justify-content: center;
        }

        @media(max-width:900px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .toolbar {
                justify-content: flex-start;
            }

            .stat-strip {
                justify-content: flex-start;
                width: 100%;
                order: 3;
                padding-top: 14px;
                border-top: 1px solid var(--line);
            }

            .stat-item {
                padding: 2px 18px 2px 0;
            }

            .stat-item + .stat-item {
                border-left: none;
                padding-left: 18px;
            }

            .toolbar-actions {
                width: 100%;
            }
        }

        @media(max-width:520px) {
            .action-btns {
                flex-direction: column;
            }

            .table-box-header {
                align-items: flex-start;
            }

            .filter-bar {
                width: 100%;
            }

            .filter-bar input[type="text"] {
                width: 100%;
                flex: 1;
            }

            .filter-bar select {
                width: 100%;
            }

            .table-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 6px;
            }
        }
    </style>
@endsection

@section('content')

    @php
        $totalAccounts = isset($users) ? $users->count() : 0;
        $byRole = isset($users) ? $users->groupBy('role') : collect();
        $totalAdmins = $byRole->get('admin', collect())->count();
        $totalManagers = $byRole->get('manager', collect())->count();
        $totalCashiers = $byRole->get('cashier', collect())->count() + $byRole->get('tl', collect())->count();
        $hasAddErrors = $errors->any() && (old('fullname') || old('username'));
    @endphp

    {{-- TOOLBAR: title, live counts, and the one primary action, all in one row --}}
    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Staff Accounts</div>
            <h2 style="font-family: Arial, Helvetica, sans-serif">Account Management</h2>
        </div>

        <div class="stat-strip">
            <div class="stat-item">
                <div class="value">{{ $totalAccounts }}</div>
                <div class="label">Total accounts</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ $totalAdmins }}</div>
                <div class="label">Admins</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ $totalManagers }}</div>
                <div class="label">Managers</div>
            </div>
            <div class="stat-item">
                <div class="value">{{ $totalCashiers }}</div>
                <div class="label">Cashiers &amp; TLs</div>
            </div>
        </div>

        <div class="toolbar-actions">
            <button type="button" class="print-btn" onclick="openAdd()"><i class="fas fa-user-plus"></i> Add
                account</button>
        </div>
    </div>

    @if (session('success'))
        <div class="alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
    @endif
    @if ($errors->any() && !$hasAddErrors)
        <div class="alert-error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    {{-- ACCOUNTS TABLE — now the first and largest thing on the page --}}
    <div class="table-box">
        <div class="table-box-header">
            <div class="section-title"></i> Staff Accounts</div>

            <div class="filter-bar">
                <input type="text" id="accountSearch" placeholder="Search name or username&hellip;">
                <select id="roleFilter">
                    <option value="">All roles</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                    <option value="tl">Team Leader</option>
                    <option value="cashier">Cashier</option>
                </select>
                <button type="button" class="filter-clear" id="filterClear">Clear</button>
            </div>
        </div>

        <div class="table-scroll">
            <table id="accountsTable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr data-name="{{ strtolower($user->fullname . ' ' . $user->username) }}"
                            data-role="{{ $user->role }}">
                            <td>
                                <div class="user-cell">
                                    <div class="avatar">{{ strtoupper(substr($user->fullname, 0, 2)) }}</div>
                                    <div>
                                        <div class="user-name">{{ $user->fullname }}</div>
                                        <div class="user-username">&#64;{{ $user->username }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-edit" data-id="{{ $user->user_id }}"
                                        data-fullname="{{ $user->fullname }}" data-username="{{ $user->username }}"
                                        data-role="{{ $user->role }}"
                                        onclick="openEdit(this.dataset.id, this.dataset.fullname, this.dataset.username, this.dataset.role)">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn-delete" data-id="{{ $user->user_id }}"
                                        data-fullname="{{ $user->fullname }}"
                                        onclick="openDelete(this.dataset.id, this.dataset.fullname)">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" style="text-align:center; color:var(--muted); padding:24px;">No accounts
                                found.</td>
                        </tr>
                    @endforelse
                    <tr id="noMatchRow" style="display:none;">
                        <td colspan="3">No accounts match your search or filter.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="table-footer">
            <span id="resultCount">Showing {{ $totalAccounts }} of {{ $totalAccounts }} accounts</span>
        </div>
    </div>

    {{-- ADD ACCOUNT MODAL — reopens automatically with your input if it was submitted with an error --}}
    <div class="modal-overlay @if($hasAddErrors) active @endif" id="addModal">
        <div class="modal modal-wide">
            <div class="modal-title"><i class="fas fa-user-plus"></i> Add new account</div>
            <form action="{{ route('accounts.store') }}" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full name</label>
                        <input class="form-input" type="text" name="fullname" value="{{ old('fullname') }}"
                            placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input class="form-input" type="text" name="username" value="{{ old('username') }}"
                            placeholder="e.g. jdelacruz" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input class="form-input" type="password" name="password" placeholder="Minimum 6 characters"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role">
                            <option value="cashier">Cashier</option>
                            <option value="tl">Team Leader</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                @if ($hasAddErrors)
                    <div class="alert-error" style="margin-top:16px; margin-bottom:0;">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif
                <div class="modal-actions">
                    <button type="submit" class="print-btn"><i class="fas fa-user-check"></i> Create account</button>
                    <button type="button" class="btn-outline" onclick="closeAdd()"><i class="fas fa-times"></i>
                        Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT MODAL --}}
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-title"><i class="fas fa-user-edit"></i> Edit account</div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label">Full name</label>
                    <input class="form-input" type="text" name="fullname" id="edit_fullname" required>
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label">Username</label>
                    <input class="form-input" type="text" name="username" id="edit_username" required>
                </div>
                <div class="form-group" style="margin-bottom:12px">
                    <label class="form-label">New password <span
                            style="color:var(--muted);font-weight:400;text-transform:none">(leave blank to
                            keep)</span></label>
                    <input class="form-input" type="password" name="password" placeholder="Leave blank to keep current">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role" id="edit_role">
                        <option value="cashier">Cashier</option>
                        <option value="tl">Team Leader</option>
                        <option value="manager">Manager</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="print-btn"><i class="fas fa-save"></i> Save changes</button>
                    <button type="button" class="btn-outline" onclick="closeEdit()"><i class="fas fa-times"></i>
                        Cancel</button>
                </div>
            </form>
        </div>
    </div>

    {{-- DELETE CONFIRMATION MODAL --}}
    <div class="modal-overlay" id="deleteModal">
        <div class="modal modal-confirm">
            <div class="modal-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="modal-title">Delete this account?</div>
            <p class="modal-text">
                You are about to delete <strong id="delete_fullname"></strong>'s account.
                This action cannot be undone.
            </p>
            <form id="deleteForm" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-actions">
                    <button type="submit" class="btn-danger"><i class="fas fa-trash"></i> Yes, delete</button>
                    <button type="button" class="btn-outline" onclick="closeDelete()"><i class="fas fa-times"></i>
                        Cancel</button>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        function openAdd() {
            document.getElementById('addModal').classList.add('active');
        }

        function closeAdd() {
            document.getElementById('addModal').classList.remove('active');
        }

        function openEdit(id, fullname, username, role) {
            document.getElementById('editForm').action = '/accounts/update/' + id;
            document.getElementById('edit_fullname').value = fullname;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            document.getElementById('editModal').classList.add('active');
        }

        function closeEdit() {
            document.getElementById('editModal').classList.remove('active');
        }

        function openDelete(id, fullname) {
            document.getElementById('deleteForm').action = '/accounts/delete/' + id;
            document.getElementById('delete_fullname').textContent = fullname;
            document.getElementById('deleteModal').classList.add('active');
        }

        function closeDelete() {
            document.getElementById('deleteModal').classList.remove('active');
        }

        // ===== SEARCH & ROLE FILTER, with a live result count =====
        (function() {
            const searchInput = document.getElementById('accountSearch');
            const roleFilter = document.getElementById('roleFilter');
            const clearBtn = document.getElementById('filterClear');
            const noMatchRow = document.getElementById('noMatchRow');
            const resultCount = document.getElementById('resultCount');
            const rows = Array.from(document.querySelectorAll('#accountsTable tbody tr[data-name]'));
            const total = rows.length;
            if (!searchInput || !rows.length) return;

            function applyFilters() {
                const q = (searchInput.value || '').trim().toLowerCase();
                const role = roleFilter.value || '';
                let visibleCount = 0;
                rows.forEach(row => {
                    const ok = (!q || row.dataset.name.indexOf(q) !== -1) && (!role || row.dataset.role ===
                        role);
                    row.style.display = ok ? '' : 'none';
                    if (ok) visibleCount++;
                });
                if (noMatchRow) noMatchRow.style.display = visibleCount ? 'none' : '';
                if (resultCount) resultCount.textContent = 'Showing ' + visibleCount + ' of ' + total + ' accounts';
            }

            searchInput.addEventListener('input', applyFilters);
            roleFilter.addEventListener('change', applyFilters);
            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                roleFilter.value = '';
                applyFilters();
            });
        })();
    </script>
@endpush