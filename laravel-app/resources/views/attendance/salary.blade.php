@extends('layouts.sidebar')

@section('title', 'Salary and Deductions')

@section('styles')
    @include('partials.ui-theme')
    <style>
        html { scroll-behavior: auto; }
        #salary-summary { scroll-margin-top: 90px; }

        /* ---- Daily rate settings ---- */
        .rate-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 14px; }
        .rate-box {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px 14px;
            min-width: 0;
            background: var(--bg);
        }
        .rate-box .badge { margin-bottom: 8px; }
        .rate-box label {
            font-size: 10.5px;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: .8px;
            display: block;
            margin-bottom: 4px;
        }
        .rate-input-row { display: flex; align-items: center; gap: 6px; }
        .rate-input-row span { font-family: var(--font-display); font-size: 15px; color: var(--ink-soft); }
        .rate-input-row input {
            padding: 6px 10px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            font-family: var(--font-display);
            color: var(--ink);
            background: var(--card);
            width: 100%;
            min-width: 0;
        }
        .rate-note { font-size: 11px; color: var(--muted); margin-top: 10px; }
        .btn-row { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .btn-divider { width: 1px; height: 36px; background: var(--line); flex-shrink: 0; }

        table { min-width: 960px; }

        /* ---- Generate Salary modal ---- */
        .modal-card.wide { max-width: 560px; }
        .modal-body .form-group { margin-bottom: 14px; }

        .govt-status {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 10px 14px;
            margin-bottom: 14px;
            border: 1px solid var(--line-strong);
            border-radius: 10px;
            background: var(--bg);
            font-size: 13px;
            font-weight: 600;
            color: var(--ink);
            line-height: 1.4;
        }
        .govt-status small { display: block; font-size: 11.5px; font-weight: 500; color: var(--muted); margin-top: 2px; }
        .govt-status.is-on { background: var(--danger-soft); border-color: var(--danger); }
        .govt-status.is-on .govt-icon { color: var(--danger); }
        .govt-status.is-off .govt-icon { color: var(--muted); }

        .holiday-list { display: flex; flex-direction: column; gap: 6px; margin: 8px 0 14px; }
        .holiday-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 9px 12px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: var(--bg);
        }
        .holiday-row .hol-info { display: flex; flex-direction: column; min-width: 0; }
        .holiday-row .day-label { font-size: 13px; font-weight: 600; color: var(--ink); }
        .holiday-row .hol-name { font-size: 11.5px; font-weight: 500; color: var(--muted); }
        .holiday-empty {
            padding: 12px;
            border: 1px dashed var(--line-strong);
            border-radius: 10px;
            background: var(--bg);
            color: var(--muted);
            font-size: 12.5px;
            text-align: center;
        }
        .hl-chip { display: inline-block; padding: 4px 11px; border-radius: 999px; font-size: 10.5px; font-weight: 700; white-space: nowrap; }
        .hl-regular { background: var(--danger-soft); color: var(--danger); }
        .hl-special { background: var(--pink-light); color: var(--pink-deep); }
        .holiday-legend { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px; }
        .modal-note { font-size: 11px; color: var(--muted); line-height: 1.6; }

        @media (max-width: 900px) {
            .rate-grid { grid-template-columns: 1fr; }
            .btn-row { flex-direction: column; align-items: stretch; }
            .btn-divider { width: 100%; height: 1px; }
            .btn-row .btn-primary, .btn-row .btn-ghost { width: 100%; }
            .holiday-row { flex-wrap: wrap; }
        }
    </style>
@endsection

@section('content')

    @php
        // Area filter, read from the query string.
        $area = request('area', '');

        // The full list of areas comes from the employees table (not the filtered attendance),
        // so the other areas stay available once one is selected.
        $areas = collect();
        try {
            $areas = \Illuminate\Support\Facades\DB::table('employees')
                ->whereNotNull('area')
                ->where('area', '!=', '')
                ->distinct()
                ->pluck('area');
        } catch (\Throwable $e) {
            $areas = collect();
        }
        if ($areas->isEmpty()) {
            $areas = collect($attendance->pluck('area'));
        }
        $areas = $areas
            ->filter(fn($a) => $a && $a !== 'All areas')
            ->unique()
            ->sort()
            ->values();
        $shown = $area ? $attendance->where('area', $area) : $attendance;
        $grouped = $shown->groupBy(fn($a) => $a->category ?? 'Staff');

        // Cutoff type is automatic, based on today's date (day 1-15 = 1st, 16-31 = 2nd).
        $autoCutoff = now()->day <= 15 ? '1st' : '2nd';

        // SSS / PhilHealth / Pag-IBIG are deducted in one lump sum on the 2nd cutoff only.
        $applyGovt = $autoCutoff === '2nd';

        // Holidays from the `holidays` table (month of the payroll period).
        $payrollDate = $periodStart ?? now();
        $daysInMonth = $payrollDate->daysInMonth;

        $holidayData = [];
        try {
            $holidayData = \App\Models\Holiday::whereYear('date', $payrollDate->year)
                ->whereMonth('date', $payrollDate->month)
                ->get()
                ->mapWithKeys(fn($h) => [(int) $h->date->day => ['name' => $h->name, 'type' => $h->type]])
                ->all();
        } catch (\Throwable $e) {
            // Table does not exist yet (not migrated) - no holidays will be shown.
            $holidayData = [];
        }
    @endphp

    <div class="toolbar">
        <div>
            <div class="eyebrow">Lipa Branch &middot; Salary and Deductions &middot; {{ $periodStart->format('M j') }}&ndash;{{ $periodEnd->format('M j, Y') }}@if ($area) &middot; {{ $area }}@endif</div>
 <h2 style="font-family: Arial, Helvetica, sans-serif">Salary and Deductions</h2>
        </div>
    </div>

    <div class="box">
        <h3>Daily Rate Settings</h3>
        <form action="{{ route('salary.rates') }}" method="POST" id="ratesForm">
            @csrf
            <div class="rate-grid">
                <div class="rate-box">
                    <span class="badge badge--manager">Manager</span>
                    <label>Daily Rate</label>
                    <div class="rate-input-row">
                        <span>&#8369;</span>
                        <input type="number" name="rates[manager]" value="{{ $rates->get('manager')?->daily_rate ?? 0 }}"
                            min="0" step="50" required>
                    </div>
                </div>
                <div class="rate-box">
                    <span class="badge badge--team-leader">Team Leader</span>
                    <label>Daily Rate</label>
                    <div class="rate-input-row">
                        <span>&#8369;</span>
                        <input type="number" name="rates[team_leader]"
                            value="{{ $rates->get('team_leader')?->daily_rate ?? 0 }}" min="0" step="50" required>
                    </div>
                </div>
                <div class="rate-box">
                    <span class="badge badge--staff">Staff</span>
                    <label>Daily Rate</label>
                    <div class="rate-input-row">
                        <span>&#8369;</span>
                        <input type="number" name="rates[staff]" value="{{ $rates->get('staff')?->daily_rate ?? 0 }}"
                            min="0" step="50" required>
                    </div>
                </div>
            </div>

            <div class="btn-row">
                <button type="submit" class="btn-primary" id="btnSaveRates">Save Rates</button>
                <div class="btn-divider"></div>
                <button type="button" class="btn-ghost" id="openHolidayModal">
                    &#9654; Generate Salary
                </button>
            </div>
            <p class="rate-note">&#9888;&#65039; Changes apply to future payrolls only. Past records are not affected.</p>
        </form>

        <form id="generateForm" action="{{ route('salary.store') }}" method="POST" style="display:none;">
            @csrf
        </form>
    </div>

    <div class="box">
        <h3 id="salary-summary">Salary Summary</h3>

        <div class="area-chips" role="tablist" aria-label="Filter by area">
            <a href="{{ request()->fullUrlWithQuery(['area' => null, 'page' => null]) }}#salary-summary"
                class="area-chip {{ $area === '' ? 'active' : '' }}">All areas</a>
            @foreach ($areas as $a)
                <a href="{{ request()->fullUrlWithQuery(['area' => $a, 'page' => null]) }}#salary-summary"
                    class="area-chip {{ $area === $a ? 'active' : '' }}">{{ $a }}</a>
            @endforeach
        </div>

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
                        <th>Late</th>
                        <th>Undertime</th>
                        <th>Deductions</th>
                        <th>Net Salary</th>
                        <th>Payslip</th>
                    </tr>
                </thead>
                <tbody>
                    @php $idx = 0; @endphp
                    @forelse($grouped as $category => $group)
                        <tr class="category-row" @if ($idx >= 10) style="display:none" @endif>
                            <td colspan="12">{{ $category }}</td>
                        </tr>
                        @foreach ($group as $item)
                            @php
                                $sal = $salaryMap->get($item->name);
                                $daysWorked = $item->days_worked ?? 0;
                                $dailyRate = $rates->get(strtolower(str_replace([' ', '-'], '_', $category)))?->daily_rate ?? 0;
                                $basicPay = $sal ? $sal->basic_salary : $daysWorked * $dailyRate;
                                // Deductions = everything that reduces net pay, including the two
                                // attendance-related ones shown in their own columns (Late and Undertime).
                                $totalDeductions = $sal
                                    ? $sal->deduction +
                                        ($sal->undertime_deduction ?? 0) +
                                        $sal->sss_deduction +
                                        $sal->philhealth_deduction +
                                        $sal->pagibig_deduction +
                                        $sal->withholding_tax
                                    : 0;
                            @endphp
                            <tr data-idx="{{ $idx }}" @if ($idx >= 10) style="display:none" @endif>
                                @php $idx++; @endphp
                                <td><strong>{{ $item->name }}</strong></td>
                                <td>{{ $daysWorked }}</td>
                                <td>{{ '₱' . number_format($basicPay, 2) }}</td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">{{ '₱' . number_format($sal?->sss_deduction ?? 0, 2) }}</td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">{{ '₱' . number_format($sal?->philhealth_deduction ?? 0, 2) }}</td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">{{ '₱' . number_format($sal?->pagibig_deduction ?? 0, 2) }}</td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">{{ '₱' . number_format($sal?->withholding_tax ?? 0, 2) }}</td>
                                <td class="{{ $sal && $sal->deduction > 0 ? 'deduction' : 'muted' }}"
                                    title="Scheduled shift, arrived after the grace period">
                                    {{ '₱' . number_format($sal?->deduction ?? 0, 2) }}
                                </td>
                                <td class="{{ $sal && ($sal->undertime_deduction ?? 0) > 0 ? 'deduction' : 'muted' }}"
                                    title="Incomplete shift (e.g., no shift assigned yet)">
                                    {{ '₱' . number_format($sal?->undertime_deduction ?? 0, 2) }}
                                </td>
                                <td class="{{ $sal ? 'deduction' : 'muted' }}">{{ '₱' . number_format($totalDeductions, 2) }}</td>
                                <td class="{{ $sal ? 'money' : 'muted' }}">{{ '₱' . number_format($sal?->net_salary ?? 0, 2) }}</td>
                                <td>
                                    @if ($sal)
                                        <a href="{{ route('payslip.show', $sal->id) }}" class="btn-ghost btn-sm">
                                            <i class="fa-regular fa-file-lines"></i> View
                                        </a>
                                    @else
                                        <span class="muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="12" style="text-align:center;padding:24px;color:var(--muted);">
                                @if ($area)
                                    No records found for {{ $area }}.
                                @else
                                    No records yet. Please import a Bio file first.
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-list">
            @php $cidx = 0; @endphp
            @forelse($grouped as $category => $group)
                <div class="card-category-label" @if ($cidx >= 10) style="display:none" @endif>{{ $category }}</div>
                @foreach ($group as $item)
                    @php
                        $sal = $salaryMap->get($item->name);
                        $daysWorked = $item->days_worked ?? 0;
                        $dailyRate = $rates->get(strtolower(str_replace([' ', '-'], '_', $category)))?->daily_rate ?? 0;
                        $basicPay = $sal ? $sal->basic_salary : $daysWorked * $dailyRate;
                        $totalDeductions = $sal
                            ? $sal->deduction +
                                ($sal->undertime_deduction ?? 0) +
                                $sal->sss_deduction +
                                $sal->philhealth_deduction +
                                $sal->pagibig_deduction +
                                $sal->withholding_tax
                            : 0;
                    @endphp
                    <div class="salary-card" data-idx="{{ $cidx }}" @if ($cidx >= 10) style="display:none" @endif>
                        @php $cidx++; @endphp
                        <div class="salary-card-name">{{ $item->name }}</div>
                        <div class="salary-card-grid">
                            <div>
                                <div class="scg-label">Days worked</div>
                                <div class="scg-val">{{ $daysWorked }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Basic pay</div>
                                <div class="scg-val">{{ '₱' . number_format($basicPay, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">SSS</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($sal?->sss_deduction ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">PhilHealth</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($sal?->philhealth_deduction ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Pag-IBIG</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($sal?->pagibig_deduction ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Withholding tax</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($sal?->withholding_tax ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Late</div>
                                <div class="scg-val {{ $sal && $sal->deduction > 0 ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($sal?->deduction ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Undertime</div>
                                <div class="scg-val {{ $sal && ($sal->undertime_deduction ?? 0) > 0 ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($sal?->undertime_deduction ?? 0, 2) }}</div>
                            </div>
                            <div>
                                <div class="scg-label">Deductions</div>
                                <div class="scg-val {{ $sal ? 'is-deduct' : 'is-muted' }}">{{ '₱' . number_format($totalDeductions, 2) }}</div>
                            </div>
                            <div style="grid-column:1 / -1;">
                                <div class="scg-label">Net salary</div>
                                <div class="scg-val {{ $sal ? 'is-present' : 'is-muted' }}">{{ '₱' . number_format($sal?->net_salary ?? 0, 2) }}</div>
                            </div>
                        </div>
                        @if ($sal)
                            <div class="salary-card-footer">
                                <a href="{{ route('payslip.show', $sal->id) }}" class="btn-ghost btn-sm">
                                    <i class="fa-regular fa-file-lines"></i> View payslip
                                </a>
                            </div>
                        @endif
                    </div>
                @endforeach
            @empty
                <p style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">
                    @if ($area)
                        No records found for {{ $area }}.
                    @else
                        No records yet. Please import a Bio file first.
                    @endif
                </p>
            @endforelse
        </div>

        <div class="pagination" id="pagination"></div>
    </div>

    {{-- Generate Salary modal --}}
    <div class="modal-overlay" id="holidayModalOverlay">
        <div class="modal-card wide" role="dialog" aria-modal="true">
            <div class="modal-head">
                <div>
                    <div class="eyebrow">Payroll</div>
                    <h3>Generate Salary</h3>
                    <p class="modal-sub" id="holidaySub"></p>
                </div>
                <button type="button" class="modal-close" id="closeHolidayModal" aria-label="Close">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="areaSelect">Area to calculate</label>
                    <select id="areaSelect">
                        <option value="">All areas (includes Manager)</option>
                        @foreach ($areas as $a)
                            <option value="{{ $a }}" {{ $area === $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Automatic: SSS / PhilHealth / Pag-IBIG are deducted on the 2nd cutoff only --}}
                <div class="govt-status {{ $applyGovt ? 'is-on' : 'is-off' }}" id="govtStatus">
                    <span class="govt-icon">{{ $applyGovt ? '●' : '○' }}</span>
                    <div>
                        @if ($applyGovt)
                            SSS / PhilHealth / Pag-IBIG will be deducted this cutoff
                            <small>Deducted in one lump sum on the 2nd cutoff.</small>
                        @else
                            No SSS / PhilHealth / Pag-IBIG deductions this cutoff
                            <small>These are deducted in one lump sum on the 2nd cutoff.</small>
                        @endif
                    </div>
                </div>

                <div class="scg-label">Holidays in this cutoff</div>
                <div class="holiday-list" id="holidayList"></div>

                <div class="holiday-legend">
                    <span class="hl-chip hl-regular">Regular &middot; 200%</span>
                    <span class="hl-chip hl-special">Special Non-Working &middot; 130%</span>
                </div>

                <p class="modal-note">
                    &#8226; Holidays are pulled automatically from the holidays calendar.<br>
                    &#8226; Holiday rates apply only to employees who were present on that day.<br>
                    &#8226; If a single area is selected, only that area is calculated. The Manager is included when "All areas" is selected.
                </p>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn-ghost" id="cancelHolidayModal">Cancel</button>
                <button type="button" class="btn-primary" id="confirmGenerate">Generate Salary</button>
            </div>
        </div>
    </div>

    @include('partials.ui-toast')

@endsection

@push('scripts')
    <script>
        (function() {
            const form = document.getElementById('ratesForm');
            const btn = document.getElementById('btnSaveRates');
            if (!form || !btn) return;

            form.addEventListener('submit', function() {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner"></span> Saving rates...';
            });
        })();

        (function() {
            const openBtn = document.getElementById('openHolidayModal');
            const cancelBtn = document.getElementById('cancelHolidayModal');
            const closeBtn = document.getElementById('closeHolidayModal');
            const confirmBtn = document.getElementById('confirmGenerate');
            const overlayEl = document.getElementById('holidayModalOverlay');
            const listEl = document.getElementById('holidayList');
            const subEl = document.getElementById('holidaySub');
            const generateForm = document.getElementById('generateForm');
            const areaSelect = document.getElementById('areaSelect');
            if (!openBtn || !generateForm) return;

            // From the holidays table: {"17": {name, type}, ...}
            const HOLIDAYS = @json($holidayData);
            const DAYS_IN_MONTH = {{ $daysInMonth }};
            const MONTH_LABEL = @json($payrollDate->format('F'));

            // Decided automatically by the server based on today's date.
            const CUTOFF = @json($autoCutoff);      // '1st' or '2nd'
            const APPLY_GOVT = @json($applyGovt);   // true only on the 2nd cutoff

            const TYPE_LABEL = {
                regular: 'Regular · 200%',
                special: 'Special Non-Working · 130%'
            };

            function esc(s) {
                const d = document.createElement('div');
                d.textContent = s;
                return d.innerHTML;
            }

            // Only the holidays that fall inside the automatic cutoff.
            function holidaysInCutoff() {
                const is1st = CUTOFF === '1st';
                const start = is1st ? 1 : 16;
                const end = is1st ? 15 : DAYS_IN_MONTH;
                const list = [];
                for (let d = start; d <= end; d++) {
                    if (HOLIDAYS[d]) list.push({ day: d, name: HOLIDAYS[d].name, type: HOLIDAYS[d].type });
                }
                return { start, end, list };
            }

            function renderHolidays() {
                const { start, end, list } = holidaysInCutoff();

                subEl.textContent = `Cutoff period: ${MONTH_LABEL} ${start}–${end}`;

                if (!list.length) {
                    listEl.innerHTML = '<div class="holiday-empty">No holidays in this cutoff.</div>';
                    return;
                }

                listEl.innerHTML = list.map(h => `
                    <div class="holiday-row">
                        <div class="hol-info">
                            <span class="day-label">${MONTH_LABEL} ${h.day}</span>
                            <span class="hol-name">${esc(h.name)}</span>
                        </div>
                        <span class="hl-chip hl-${h.type}">${TYPE_LABEL[h.type] ?? h.type}</span>
                    </div>
                `).join('');
            }

            renderHolidays();

            const closeModal = () => overlayEl.classList.remove('active');
            openBtn.addEventListener('click', () => overlayEl.classList.add('active'));
            cancelBtn.addEventListener('click', closeModal);
            closeBtn?.addEventListener('click', closeModal);
            overlayEl.addEventListener('click', (e) => {
                if (e.target === overlayEl) closeModal();
            });

            confirmBtn.addEventListener('click', () => {
                generateForm.querySelectorAll(
                    'input[name^="holidays"], input[name="cutoff_type"], input[name="apply_govt_deductions"], input[name="area"]'
                ).forEach(el => el.remove());

                const addHidden = (name, value) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = name;
                    input.value = value;
                    generateForm.appendChild(input);
                };

                addHidden('cutoff_type', CUTOFF);
                addHidden('apply_govt_deductions', APPLY_GOVT ? '1' : '0');
                addHidden('area', areaSelect ? areaSelect.value : '');

                // Holidays of the current cutoff are submitted automatically, no manual selection.
                holidaysInCutoff().list.forEach(h => addHidden(`holidays[${h.day}]`, h.type));

                confirmBtn.disabled = true;
                confirmBtn.innerHTML = '<span class="spinner"></span> Generating...';

                generateForm.submit();
            });
        })();

        (function() {
            const paginationEl = document.getElementById('pagination');
            const tbody = document.querySelector('.table-scroll table tbody');
            const cardList = document.querySelector('.card-list');
            if (!tbody) return;

            const PAGE_SIZE = 10;
            let currentPage = 1;

            function groupRows(container, catSelector, itemSelector) {
                const groups = [];
                let current = null;
                Array.from(container.children).forEach(el => {
                    if (el.matches(catSelector)) {
                        current = { catEl: el, items: [] };
                        groups.push(current);
                    } else if (el.matches(itemSelector) && current) {
                        current.items.push(el);
                    }
                });
                return groups;
            }

            const tableGroups = groupRows(tbody, '.category-row', 'tr[data-idx]');
            const cardGroups = cardList ? groupRows(cardList, '.card-category-label', '.salary-card[data-idx]') : [];
            const total = tableGroups.reduce((n, g) => n + g.items.length, 0);

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

            function applyToGroups(groups, start, end) {
                groups.forEach(g => {
                    let groupVisible = false;
                    g.items.forEach(el => {
                        const idx = parseInt(el.dataset.idx, 10);
                        const show = idx >= start && idx < end;
                        el.style.display = show ? '' : 'none';
                        if (show) groupVisible = true;
                    });
                    g.catEl.style.display = groupVisible ? '' : 'none';
                });
            }

            function render() {
                const totalPages = Math.max(1, Math.ceil(total / PAGE_SIZE));
                currentPage = Math.min(Math.max(currentPage, 1), totalPages);
                const start = (currentPage - 1) * PAGE_SIZE;
                const end = start + PAGE_SIZE;

                applyToGroups(tableGroups, start, end);
                applyToGroups(cardGroups, start, end);
                renderPagination(totalPages);
            }

            if (total > 0) render();
        })();
    </script>
@endpush