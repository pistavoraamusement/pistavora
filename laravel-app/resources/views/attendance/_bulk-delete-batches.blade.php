{{-- ====== BULK DELETE PAYROLL PERIODS ======
     Gamit: @include('attendance._bulk-delete-batches')
     Ilagay sa payroll-history.blade.php, sa ilalim ng Payroll Period dropdown.
     Kailangan ng $batches (galing na sa payrollHistory()). --}}

<button type="button" id="openBulkDelete"
    style="margin-top:8px;padding:8px 14px;border:1px solid #B82850;color:#B82850;background:#fff;border-radius:8px;font-weight:600;cursor:pointer;">
    🗑 Delete Multiple Periods
</button>

<form id="bulkDeleteForm" method="POST" action="{{ route('payroll-history.bulkDelete') }}"
    style="display:none;margin-top:10px;border:1px solid #ddd;border-radius:10px;padding:12px;max-width:560px;background:#fff;">
    @csrf
    @method('DELETE')

    <label style="display:flex;align-items:center;gap:8px;font-weight:700;padding:6px 4px;border-bottom:1px solid #eee;cursor:pointer;">
        <input type="checkbox" id="selectAllPeriods"> Select All
    </label>

    <div style="max-height:280px;overflow-y:auto;">
        @foreach ($batches as $b)
            @php
                $bValue = \Carbon\Carbon::parse($b->batch_id)->format('Y-m-d H:i:s');
                $bLabel = \Carbon\Carbon::parse($b->pay_date)->format('M d, Y')
                    . ' — ' . ($b->cutoff_type === '2nd' ? '2nd' : '1st') . ' Cutoff · Generated '
                    . \Carbon\Carbon::parse($b->generated_at)->format('M j, Y g:i A')
                    . ($loop->first ? ' (Latest)' : '');
            @endphp
            <label class="period-row"
                style="display:flex;align-items:center;gap:8px;padding:7px 6px;cursor:pointer;border-radius:6px;font-size:13px;">
                <input type="checkbox" name="batches[]" value="{{ $bValue }}" class="period-check">
                <span>{{ $bLabel }}</span>
            </label>
        @endforeach
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:10px;">
        <span id="selectedCount" style="font-size:12px;color:#555;">0 selected</span>
        <div style="display:flex;gap:8px;">
            <button type="button" id="cancelBulkDelete"
                style="padding:8px 14px;border:1px solid #ccc;background:#fff;border-radius:8px;cursor:pointer;">Cancel</button>
            <button type="submit" id="confirmBulkDelete" disabled
                style="padding:8px 14px;border:none;background:#B82850;color:#fff;border-radius:8px;font-weight:600;cursor:pointer;opacity:.5;">
                Delete Selected
            </button>
        </div>
    </div>
</form>

<style>
    .period-row:hover { background:#f3f6ff; }
    .period-row:has(input:checked) { background:#dbe7ff; } /* highlight kapag selected */
</style>

<script>
    (function () {
        const form      = document.getElementById('bulkDeleteForm');
        const openBtn   = document.getElementById('openBulkDelete');
        const cancelBtn = document.getElementById('cancelBulkDelete');
        const selectAll = document.getElementById('selectAllPeriods');
        const countEl   = document.getElementById('selectedCount');
        const delBtn    = document.getElementById('confirmBulkDelete');
        const checks    = () => [...document.querySelectorAll('.period-check')];

        function refresh() {
            const n = checks().filter(c => c.checked).length;
            countEl.textContent = n + ' selected';
            delBtn.disabled = n === 0;
            delBtn.style.opacity = n === 0 ? .5 : 1;
            selectAll.checked = n > 0 && n === checks().length;
        }

        openBtn.addEventListener('click', () => {
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });
        cancelBtn.addEventListener('click', () => { form.style.display = 'none'; });

        selectAll.addEventListener('change', () => {
            checks().forEach(c => c.checked = selectAll.checked);
            refresh();
        });
        checks().forEach(c => c.addEventListener('change', refresh));

        form.addEventListener('submit', (e) => {
            const n = checks().filter(c => c.checked).length;
            if (!confirm(`Sigurado ka bang buburahin ang ${n} payroll batch(es)? Hindi na ito mababalik.`)) {
                e.preventDefault();
            }
        });
    })();
</script>
