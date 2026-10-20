#!/usr/bin/env bash
set -e

# patch_user_pages.sh
# Run from your Laravel app root (same folder as artisan):
#   bash patch_user_pages.sh
#
# Updates 3 blade views with the confirmation-modal + loading-spinner
# pattern already used in booking.blade.php. Backs up existing files
# first (safe to re-run).

BASE="resources/views/user"

if [ ! -d "$BASE" ]; then
  echo "ERROR: Could not find $BASE — run this from your Laravel app root (same folder as artisan)." >&2
  exit 1
fi

TS=$(date +%Y%m%d_%H%M%S)

backup() {
  if [ -f "$1" ]; then
    cp "$1" "$1.bak.$TS"
    echo "Backed up: $1 -> $(basename "$1").bak.$TS"
  fi
}

backup "$BASE/reschedule.blade.php"
cat > "$BASE/reschedule.blade.php" << 'PATCHEOF'
{{--
    resources/views/user/reschedule.blade.php
    Route: GET  /user/bookings/{booking}/reschedule    -> user.bookings.reschedule.edit
           POST /user/bookings/{booking}/reschedule    -> user.bookings.reschedule
    Controller: App\Http\Controllers\User\BookingController@editReschedule / reschedule

    Expects from the controller:
      $booking      -> App\Models\Booking (service, package, tier, visit_date, visit_time)
      $packageLabel -> string, the display name of the package
--}}
@extends('layouts.user')

@section('title', 'Reschedule Booking')
@section('page-title', 'Reschedule')
@section('page-subtitle', 'Pick a new date and time')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card">
        <h4>{{ $packageLabel }}</h4>
        <p>Currently set for {{ $booking->visit_date->format('M j, Y') }} · {{ $booking->tier }} pax</p>
    </div>

    <form method="POST" action="{{ route('user.bookings.reschedule', $booking->id) }}" id="rescheduleForm">
        @csrf

        <div class="cal-embed" id="calEmbed" style="margin-top:14px;">
            <div class="cal-embed-inner">
                <div class="cal-month">
                    <div class="cal-month-head">
                        <button type="button" id="calPrev" aria-label="Previous month">&lsaquo;</button>
                        <span id="calMonthLabel"></span>
                        <button type="button" id="calNext" aria-label="Next month">&rsaquo;</button>
                    </div>
                    <div class="cal-weekdays">
                        <span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span><span>Su</span>
                    </div>
                    <div class="cal-days" id="calDays"></div>
                </div>

                <div class="cal-times">
                    <div class="cal-times-head">
                        <span id="calSelectedDayLabel">Pick a date</span>
                        <div class="cal-fmt-toggle">
                            <button type="button" data-fmt="12" class="active">12h</button>
                            <button type="button" data-fmt="24">24h</button>
                        </div>
                    </div>
                    <div class="cal-slots" id="calSlots">
                        <p class="cal-slots-empty">Select a date first</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="calSelectedBar" class="promo-selection-summary empty" style="margin-top:14px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;flex-shrink:0;">
                <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
            </svg>
            <span id="dateTimeLabel">No date &amp; time selected yet</span>
        </div>

        <input type="hidden" name="visit_date" id="visitDateInput" required>
        <input type="hidden" name="visit_time" id="visitTimeInput" required>
        <input type="hidden" id="packageInput" value="{{ $booking->package }}">

        <button type="submit" class="u-btn" id="saveRescheduleBtn" style="margin-top:14px;" disabled>Save New Date &amp; Time</button>
    </form>

    <a href="{{ route('user.bookings') }}" class="u-btn ghost" style="margin-top:10px;">Cancel</a>

    <!-- Reschedule Confirmation Modal -->
    <div class="booking-modal-overlay" id="rescheduleModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                </svg>
            </div>
            <h3>Confirm New Schedule</h3>
            <p class="booking-modal-sub">Are you sure you want to move this booking to the date &amp; time below?</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b id="confirmPackageText">{{ $packageLabel }}</b>
                </div>
                <div class="booking-modal-row">
                    <span>New Date &amp; Time</span>
                    <b id="confirmDateTimeText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="rescheduleModalCancel">Cancel</button>
                <button type="button" class="btn-confirm" id="rescheduleModalConfirm">Yes, Reschedule</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    #saveRescheduleBtn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        filter: grayscale(60%);
        pointer-events: none;
    }

    /* ── confirmation modal + loading state (shared pattern with booking.blade.php) ── */
    .booking-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        z-index: 200;
        align-items: center;
        justify-content: center;
    }
    .booking-modal-overlay.active { display: flex; }
    .booking-modal {
        background: #fff;
        border-radius: 16px;
        padding: 30px 26px;
        width: 90%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,.25);
        animation: bookingModalPop .18s ease;
    }
    @keyframes bookingModalPop {
        from { transform: scale(.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .booking-modal-icon {
        width: 52px;
        height: 52px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: var(--pink-pale, #ffe6ee);
        color: var(--pink-deep, #b82850);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .booking-modal h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #1a1523;
    }
    .booking-modal-sub {
        font-size: .85rem;
        color: #635c72;
        margin-bottom: 18px;
    }
    .booking-modal-summary {
        background: #f8f6f9;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
        text-align: left;
    }
    .booking-modal-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .82rem;
        padding: 5px 0;
    }
    .booking-modal-row span { color: #9c94ab; }
    .booking-modal-row b { color: #1a1523; text-align: right; }
    .booking-modal-actions { display: flex; gap: 10px; }
    .booking-modal-actions button {
        flex: 1;
        padding: 11px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .86rem;
        border: none;
        cursor: pointer;
    }
    .btn-cancel { background: #f1eef2; color: #635c72; }
    .btn-cancel:hover { background: #e5e0e8; }
    .btn-confirm { background: var(--pink-deep, #b82850); color: #fff; }
    .btn-confirm:hover { background: var(--pink-dark, #d63e63); }

    .is-loading {
        opacity: .75;
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        display: inline-block;
        animation: btnSpin .7s linear infinite;
    }
    @keyframes btnSpin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var today = new Date();
    today.setHours(0, 0, 0, 0);

    var state = {
        viewMonth: new Date(today.getFullYear(), today.getMonth(), 1),
        selectedDate: null,   // Date, midnight
        selectedTime: null,   // 'HH:mm' 24h internal value
        fmt: '12'
    };

    var monthLbl  = document.getElementById('calMonthLabel');
    var daysEl    = document.getElementById('calDays');
    var slotsEl   = document.getElementById('calSlots');
    var dayLbl    = document.getElementById('calSelectedDayLabel');
    var bar       = document.getElementById('calSelectedBar');
    var label     = document.getElementById('dateTimeLabel');
    var visitDateInput = document.getElementById('visitDateInput');
    var visitTimeInput = document.getElementById('visitTimeInput');
    var packageInput = document.getElementById('packageInput');
    var saveBtn   = document.getElementById('saveRescheduleBtn');

    // Visit duration (in hours) per package code. null = all-day pass
    // (no fixed duration — just needs a lead time before closing).
    // Keep this in sync with the same map in booking.blade.php.
    var PACKAGE_DURATIONS = {
        non_exclusive: 3,
        exclusive_weekdays: 3,
        exclusive_weekends: 3,
        walkin_1hour: 1,
        walkin_2hour: 2,
        walkin_allday: null,
        weekday: 3,
        weekend: 3,
        group_bundle_1hour: 1,
        group_bundle_2hour: 2
    };

    function getSelectedDurationHours() {
        var code = packageInput.value;
        if (code && PACKAGE_DURATIONS.hasOwnProperty(code)) {
            return PACKAGE_DURATIONS[code]; // number of hours, or null = all-day
        }
        return 3; // fallback for packages without a fixed duration (e.g. per-ride tickets)
    }

    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var DAYS_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function isWeekend(date) {
        var d = date.getDay(); // 0 = Sun, 6 = Sat
        return d === 0 || d === 6;
    }

    // Store closes at 9:00 PM every day.
    function hoursFor(date) {
        return isWeekend(date)
            ? { openHour: 10, closeHour: 21 } // 10:00 AM - 9:00 PM
            : { openHour: 9,  closeHour: 21 }; // 9:00 AM - 9:00 PM
    }

    function formatTime(hour, minute) {
        if (state.fmt === '24') {
            return pad(hour) + ':' + pad(minute);
        }
        var period = hour >= 12 ? 'PM' : 'AM';
        var h12 = hour % 12;
        if (h12 === 0) h12 = 12;
        return h12 + ':' + pad(minute) + ' ' + period;
    }

    function updateSaveButtonState() {
        var ready = !!(visitDateInput.value && visitTimeInput.value);
        saveBtn.disabled = !ready;
    }

    function renderCalendar() {
        monthLbl.textContent = MONTHS[state.viewMonth.getMonth()] + ' ' + state.viewMonth.getFullYear();
        daysEl.innerHTML = '';

        var firstOfMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth(), 1);
        var offset = (firstOfMonth.getDay() + 6) % 7; // Monday-first
        var daysInMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth() + 1, 0).getDate();

        for (var i = 0; i < offset; i++) {
            var empty = document.createElement('span');
            empty.className = 'cal-day empty';
            daysEl.appendChild(empty);
        }

        for (var day = 1; day <= daysInMonth; day++) {
            var cellDate = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth(), day);
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cal-day';
            btn.textContent = day;

            var isPast = cellDate < today;
            if (isPast) {
                btn.classList.add('disabled');
                btn.disabled = true;
            }
            if (cellDate.getTime() === today.getTime()) {
                btn.classList.add('today');
            }
            if (state.selectedDate && cellDate.getTime() === state.selectedDate.getTime()) {
                btn.classList.add('selected');
            }

            (function (d) {
                btn.addEventListener('click', function () { selectDate(d); });
            })(cellDate);

            daysEl.appendChild(btn);
        }
    }

    function selectDate(date) {
        state.selectedDate = date;
        state.selectedTime = null;
        visitDateInput.value = '';
        visitTimeInput.value = '';
        bar.classList.add('empty');
        label.textContent = 'No date & time selected yet';
        updateSaveButtonState();
        renderCalendar();
        renderSlots();
    }

    function renderSlots() {
        slotsEl.innerHTML = '';

        if (!state.selectedDate) {
            dayLbl.textContent = 'Pick a date';
            var empty = document.createElement('p');
            empty.className = 'cal-slots-empty';
            empty.textContent = 'Select a date first';
            slotsEl.appendChild(empty);
            return;
        }

        dayLbl.textContent = DAYS_SHORT[state.selectedDate.getDay()] + ', ' +
            MONTHS[state.selectedDate.getMonth()] + ' ' + state.selectedDate.getDate();

        var hours = hoursFor(state.selectedDate);
        var duration = getSelectedDurationHours();
        var lastStartHour = duration === null
            ? hours.closeHour - 1
            : hours.closeHour - duration;
        var isToday = state.selectedDate.getTime() === today.getTime();
        var now = new Date();
        var hasSlots = false;

        for (var h = hours.openHour; h <= lastStartHour; h++) {
            [0, 30].forEach(function (m) {
                if (h === lastStartHour && m > 0) return;

                if (isToday) {
                    var slotDate = new Date(state.selectedDate);
                    slotDate.setHours(h, m, 0, 0);
                    if (slotDate <= now) return;
                }

                hasSlots = true;
                var value = pad(h) + ':' + pad(m);
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'cal-slot';
                btn.textContent = duration === null
                    ? formatTime(h, m)
                    : formatTime(h, m) + ' – ' + formatTime((h + duration) % 24, m);
                btn.dataset.value = value;
                if (state.selectedTime === value) btn.classList.add('selected');

                btn.addEventListener('click', function () {
                    state.selectedTime = value;
                    commitSelection();
                    renderSlots();
                });

                slotsEl.appendChild(btn);
            });
        }

        if (!hasSlots) {
            var none = document.createElement('p');
            none.className = 'cal-slots-empty';
            none.textContent = 'No more slots available this day';
            slotsEl.appendChild(none);
        }
    }

    function commitSelection() {
        if (!state.selectedDate || !state.selectedTime) return;

        var y = state.selectedDate.getFullYear();
        var m = pad(state.selectedDate.getMonth() + 1);
        var d = pad(state.selectedDate.getDate());
        visitDateInput.value = y + '-' + m + '-' + d;
        visitTimeInput.value = state.selectedTime;

        var parts = state.selectedTime.split(':');
        var timeLabel = formatTime(parseInt(parts[0], 10), parseInt(parts[1], 10));
        label.innerHTML = 'Selected: <b>' + DAYS_SHORT[state.selectedDate.getDay()] + ', ' +
            MONTHS[state.selectedDate.getMonth()] + ' ' + d + ', ' + y + '</b> · <b>' + timeLabel + '</b>';
        bar.classList.remove('empty');
        updateSaveButtonState();
    }

    document.getElementById('calPrev').addEventListener('click', function () {
        state.viewMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth() - 1, 1);
        renderCalendar();
    });
    document.getElementById('calNext').addEventListener('click', function () {
        state.viewMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth() + 1, 1);
        renderCalendar();
    });

    document.querySelectorAll('.cal-fmt-toggle button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            state.fmt = btn.dataset.fmt;
            document.querySelectorAll('.cal-fmt-toggle button').forEach(function (b) {
                b.classList.toggle('active', b === btn);
            });
            renderSlots();
            if (state.selectedTime) commitSelection();
        });
    });

    updateSaveButtonState();
    renderCalendar();
    renderSlots();
})();
</script>
<script>
// ── Confirmation modal + loading state on submit (same pattern as booking.blade.php) ──
(function () {
    var form = document.getElementById('rescheduleForm');
    var submitBtn = document.getElementById('saveRescheduleBtn');
    var overlay = document.getElementById('rescheduleModalOverlay');
    var confirmBtn = document.getElementById('rescheduleModalConfirm');
    var cancelBtn = document.getElementById('rescheduleModalCancel');
    var confirmDateTimeText = document.getElementById('confirmDateTimeText');
    if (!form || !submitBtn || !overlay) return;

    var originalLabel = submitBtn.textContent;
    var isConfirmed = false;

    function openModal() {
        var dateBar = document.getElementById('dateTimeLabel');
        confirmDateTimeText.textContent = dateBar ? dateBar.textContent.trim() : '—';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
    }

    form.addEventListener('submit', function (e) {
        // Second time around — already confirmed, let it through.
        if (isConfirmed) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitBtn.innerHTML = '<span class="btn-spinner"></span> Saving...';

            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitBtn.textContent = originalLabel;
                }
            }, 15000);
            return;
        }

        // First time — validate, then show the confirmation modal instead of submitting directly.
        e.preventDefault();

        var visitDate = document.getElementById('visitDateInput').value;
        var visitTime = document.getElementById('visitTimeInput').value;

        if (!visitDate || !visitTime) {
            alert('Please pick a new date and time before saving.');
            return;
        }

        openModal();
    });

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        isConfirmed = true;
        closeModal();
        if (form.requestSubmit) {
            form.requestSubmit(submitBtn);
        } else {
            form.submit();
        }
    });
})();
</script>
@endpush
PATCHEOF
echo "Updated:   reschedule.blade.php"

backup "$BASE/bookings.blade.php"
cat > "$BASE/bookings.blade.php" << 'PATCHEOF'
{{--
    resources/views/user/bookings.blade.php
    Route: GET  /user/bookings                        -> user.bookings
           POST /user/bookings/{booking}/cancel        -> user.bookings.cancel
           POST /user/bookings/{booking}/reschedule    -> user.bookings.reschedule
    Controller: App\Http\Controllers\User\BookingController@index / cancel / reschedule

    Expects from the controller:
      $bookings -> LengthAwarePaginator of
        ['id', 'package', 'date', 'pax', 'status_label', 'status_class']
        status_class is one of: green | amber | rose
--}}
@extends('layouts.user')

@section('title', 'My Bookings')
@section('page-title', 'My Bookings')
@section('page-subtitle', 'Manage your reservations')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card" style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
        <div>
            <h4 style="margin:0 0 4px;">My Bookings</h4>
            <p style="margin:0;">Manage upcoming &amp; past visits</p>
        </div>
        <a href="{{ route('user.booking') }}"
           class="u-btn ghost"
           style="width:auto;padding:9px 16px;font-size:12px;white-space:nowrap;">
            + New Booking
        </a>
    </div>

    <div class="u-card" style="margin-top:20px;padding:0;overflow:hidden;font-size:14px;">
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:640px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(0,0,0,0.08);">
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Package</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Date</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Pax</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Status</th>
                        <th style="text-align:left;padding:16px 20px;font-size:11px;font-weight:600;letter-spacing:.04em;color:var(--muted);text-transform:uppercase;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($bookings as $booking)
                    <tr style="{{ !$loop->last ? 'border-bottom:1px solid rgba(0,0,0,0.06);' : '' }}">
                        <td style="padding:20px;vertical-align:middle;font-size:14px;font-weight:600;">
                            {{ $booking['package'] }}
                        </td>
                        <td style="padding:20px;vertical-align:middle;white-space:nowrap;font-size:13px;color:var(--muted);">
                            {{ $booking['date'] }}
                        </td>
                        <td style="padding:20px;vertical-align:middle;white-space:nowrap;font-size:13px;color:var(--muted);">
                            {{ $booking['pax'] }} pax
                        </td>
                        <td style="padding:20px;vertical-align:middle;">
                            <span class="tag {{ $booking['status_class'] }}">{{ $booking['status_label'] }}</span>
                        </td>
                        <td style="padding:20px;vertical-align:middle;">
                            @if ($booking['status_class'] !== 'green')
                                <div style="display:flex;flex-direction:column;gap:8px;min-width:200px;">

                                    @if ($booking['status_class'] === 'amber')
                                        <a href="{{ route('user.bookings.review', $booking['id']) }}"
                                           class="u-btn"
                                           style="width:100%;padding:8px 8px;font-size:11px;font-weight:600;text-align:center;white-space:nowrap;">
                                            Review Booking
                                        </a>
                                    @endif

                                    <div style="display:flex;gap:8px;">
                                        <a href="{{ route('user.bookings.reschedule.edit', $booking['id']) }}"
                                           class="u-btn ghost"
                                           style="flex:1;padding:8px 6px;font-size:11px;font-weight:600;text-align:center;white-space:nowrap;">
                                            Reschedule
                                        </a>

                                        <form method="POST"
                                              action="{{ route('user.bookings.cancel', $booking['id']) }}"
                                              style="flex:1;"
                                              id="cancelForm-{{ $booking['id'] }}"
                                              class="cancel-form">
                                            @csrf
                                            <button type="button"
                                                    class="u-btn ghost cancel-trigger"
                                                    data-form-id="cancelForm-{{ $booking['id'] }}"
                                                    data-package="{{ $booking['package'] }}"
                                                    data-date="{{ $booking['date'] }}"
                                                    style="width:100%;padding:8px 6px;font-size:11px;font-weight:600;color:#e24b4a;border-color:#e24b4a;white-space:nowrap;">
                                                Cancel
                                            </button>
                                        </form>
                                    </div>

                                </div>
                            @else
                                <span style="font-size:13px;color:var(--muted);">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:24px 20px;text-align:center;font-size:13px;color:var(--muted);">
                            You don't have any bookings yet.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Custom pagination, styled to match the .pg-btn theme already used for packages --}}
    @if ($bookings instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $bookings->hasPages())
        <div class="promo-pagination" style="margin-top:16px;justify-content:flex-start;">

            <button type="button"
                    class="pg-btn pg-prev"
                    @if (!$bookings->onFirstPage()) onclick="window.location='{{ $bookings->previousPageUrl() }}'" @else disabled @endif>
                &larr;
            </button>

            @php
                $start = max(1, $bookings->currentPage() - 2);
                $end = min($bookings->lastPage(), $bookings->currentPage() + 2);
            @endphp

            @if ($start > 1)
                <button type="button" class="pg-btn pg-num" onclick="window.location='{{ $bookings->url(1) }}'">1</button>
                @if ($start > 2)
                    <span style="color:var(--muted);font-size:12px;padding:0 2px;">&hellip;</span>
                @endif
            @endif

            @for ($page = $start; $page <= $end; $page++)
                <button type="button"
                        class="pg-btn pg-num {{ $page === $bookings->currentPage() ? 'active' : '' }}"
                        onclick="window.location='{{ $bookings->url($page) }}'">
                    {{ $page }}
                </button>
            @endfor

            @if ($end < $bookings->lastPage())
                @if ($end < $bookings->lastPage() - 1)
                    <span style="color:var(--muted);font-size:12px;padding:0 2px;">&hellip;</span>
                @endif
                <button type="button" class="pg-btn pg-num" onclick="window.location='{{ $bookings->url($bookings->lastPage()) }}'">
                    {{ $bookings->lastPage() }}
                </button>
            @endif

            <button type="button"
                    class="pg-btn pg-next"
                    @if ($bookings->hasMorePages()) onclick="window.location='{{ $bookings->nextPageUrl() }}'" @else disabled @endif>
                &rarr;
            </button>

        </div>
    @endif

    <!-- Cancel Confirmation Modal -->
    <div class="booking-modal-overlay" id="cancelModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon" style="background:#ffe6e6;color:#e24b4a;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M12 8v4"></path><path d="M12 16h.01"></path>
                </svg>
            </div>
            <h3>Cancel This Booking?</h3>
            <p class="booking-modal-sub">This action cannot be undone.</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b id="confirmCancelPackageText">—</b>
                </div>
                <div class="booking-modal-row">
                    <span>Date</span>
                    <b id="confirmCancelDateText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="cancelModalDismiss">Keep Booking</button>
                <button type="button" class="btn-confirm danger" id="cancelModalConfirm">Yes, Cancel</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    /* ── confirmation modal + loading state (shared pattern with booking.blade.php) ── */
    .booking-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        z-index: 200;
        align-items: center;
        justify-content: center;
    }
    .booking-modal-overlay.active { display: flex; }
    .booking-modal {
        background: #fff;
        border-radius: 16px;
        padding: 30px 26px;
        width: 90%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,.25);
        animation: bookingModalPop .18s ease;
    }
    @keyframes bookingModalPop {
        from { transform: scale(.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .booking-modal-icon {
        width: 52px;
        height: 52px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: var(--pink-pale, #ffe6ee);
        color: var(--pink-deep, #b82850);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .booking-modal h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #1a1523;
    }
    .booking-modal-sub {
        font-size: .85rem;
        color: #635c72;
        margin-bottom: 18px;
    }
    .booking-modal-summary {
        background: #f8f6f9;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
        text-align: left;
    }
    .booking-modal-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .82rem;
        padding: 5px 0;
    }
    .booking-modal-row span { color: #9c94ab; }
    .booking-modal-row b { color: #1a1523; text-align: right; }
    .booking-modal-actions { display: flex; gap: 10px; }
    .booking-modal-actions button {
        flex: 1;
        padding: 11px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .86rem;
        border: none;
        cursor: pointer;
    }
    .btn-cancel { background: #f1eef2; color: #635c72; }
    .btn-cancel:hover { background: #e5e0e8; }
    .btn-confirm { background: var(--pink-deep, #b82850); color: #fff; }
    .btn-confirm:hover { background: var(--pink-dark, #d63e63); }
    .btn-confirm.danger { background: #e24b4a; }
    .btn-confirm.danger:hover { background: #c93f3e; }

    .is-loading {
        opacity: .75;
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        display: inline-block;
        animation: btnSpin .7s linear infinite;
    }
    @keyframes btnSpin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
// ── Cancel confirmation modal + loading state (same pattern as booking.blade.php) ──
(function () {
    var overlay = document.getElementById('cancelModalOverlay');
    var confirmBtn = document.getElementById('cancelModalConfirm');
    var dismissBtn = document.getElementById('cancelModalDismiss');
    var packageText = document.getElementById('confirmCancelPackageText');
    var dateText = document.getElementById('confirmCancelDateText');
    if (!overlay || !confirmBtn) return;

    var activeForm = null;

    function openModal(trigger) {
        activeForm = document.getElementById(trigger.dataset.formId);
        packageText.textContent = trigger.dataset.package || '—';
        dateText.textContent = trigger.dataset.date || '—';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
        activeForm = null;
    }

    document.querySelectorAll('.cancel-trigger').forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            openModal(trigger);
        });
    });

    dismissBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        if (!activeForm) return;

        confirmBtn.disabled = true;
        confirmBtn.classList.add('is-loading');
        confirmBtn.innerHTML = '<span class="btn-spinner"></span> Cancelling...';

        if (activeForm.requestSubmit) {
            activeForm.requestSubmit();
        } else {
            activeForm.submit();
        }
    });
})();
</script>
@endpush
PATCHEOF
echo "Updated:   bookings.blade.php"

backup "$BASE/booking-review.blade.php"
cat > "$BASE/booking-review.blade.php" << 'PATCHEOF'
{{--
    resources/views/user/booking-review.blade.php
    Route: GET  /user/bookings/{booking}/review              -> user.bookings.review
           POST /user/bookings/{booking}/payment              -> user.bookings.payment
    Controller: App\Http\Controllers\User\BookingController@review / confirmPayment

    Expects from the controller:
      $booking -> ['id', 'package', 'date', 'pax', 'status_label', 'status_class']
--}}
@extends('layouts.user')

@section('title', 'Review Booking')
@section('page-title', 'Review Booking')
@section('page-subtitle', 'Confirm the details below before payment')
@section('body-class', 'page-uniform')

@section('content')

    {{-- Booking summary --}}
    <div class="u-card">
        <h4>{{ $booking['package'] }}</h4>
        <p>{{ $booking['date'] }} &middot; {{ $booking['pax'] }} pax</p>
    </div>

    <div class="pkg" style="margin-top:10px;">
        <div>
            <b>Status</b>
            <span>Current booking status</span>
        </div>
        <span class="tag {{ $booking['status_class'] }}">{{ $booking['status_label'] }}</span>
    </div>

    <form method="POST" action="{{ route('user.bookings.payment', $booking['id']) }}" enctype="multipart/form-data" id="paymentForm">
        @csrf

        {{-- Payment method selection --}}
        <div class="u-card" style="margin-top:20px;">
            <h4>Payment method</h4>
            <p>Choose how you'd like to pay for this booking.</p>
        </div>

        <div class="payment-options" style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">

            <label class="pkg payment-option" data-method="qrph" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div>
                    <b>QR Ph</b>
                    <span>Scan with any GCash, Maya, or bank app</span>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="qrph" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

            <label class="pkg payment-option" data-method="cash" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div>
                    <b>Cash on-site</b>
                    <span>Pay at the counter upon arrival</span>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="cash" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

        </div>

        {{-- QR Ph instructions --}}
        <div id="qrphPanel" class="u-card" style="margin-top:14px;display:none;">
            <h4>Scan to pay</h4>
            <p>Open your GCash, Maya, or your bank's app, then scan this QR Ph code.</p>

            <div style="text-align:center;margin:14px 0 4px;">
                <img src="{{ asset('images/qrph.png') }}" alt="QR Ph code" style="max-width:220px;width:100%;border-radius:12px;">
            </div>

            <p style="font-size:11.5px;color:var(--muted);margin-top:8px;">
                After paying, upload a screenshot or photo of your payment receipt below, then tap "Send Receipt". We'll verify and confirm your booking shortly.
            </p>

            <div style="margin-top:10px;display:flex;flex-direction:column;gap:6px;">
                <label for="receiptInput" style="font-size:11.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.04em;">Payment receipt</label>
                <input type="file" name="receipt" id="receiptInput" accept="image/*">
                <span id="receiptFileName" style="font-size:11.5px;color:var(--muted);"></span>
            </div>
        </div>

        {{-- Cash instructions --}}
        <div id="cashPanel" class="u-card" style="margin-top:14px;display:none;">
            <h4>Pay at the counter</h4>
            <p>Please settle payment upon arrival at WonderPark Amusement Com Inc. &mdash; Lipa Branch. Your slot will remain reserved as <b>"Pending payment"</b> until then.</p>
        </div>

        <button type="submit" class="u-btn" id="paymentSubmitBtn" style="margin-top:18px;width:100%;" disabled>
            <span id="submitLabel">Select a payment method</span>
        </button>
    </form>

    <a href="{{ route('user.bookings') }}" class="u-btn ghost" style="margin-top:10px;display:block;text-align:center;">
        Back to My Bookings
    </a>

    <!-- Payment Confirmation Modal -->
    <div class="booking-modal-overlay" id="paymentModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                </svg>
            </div>
            <h3 id="paymentModalTitle">Confirm Payment</h3>
            <p class="booking-modal-sub" id="paymentModalSub">Are you sure you want to proceed with this payment method?</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b>{{ $booking['package'] }}</b>
                </div>
                <div class="booking-modal-row">
                    <span>Payment Method</span>
                    <b id="confirmPaymentMethodText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="paymentModalCancel">Cancel</button>
                <button type="button" class="btn-confirm" id="paymentModalConfirm">Yes, Confirm</button>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    #paymentSubmitBtn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
        filter: grayscale(60%);
        pointer-events: none;
    }

    /* ── confirmation modal + loading state (shared pattern with booking.blade.php) ── */
    .booking-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, .55);
        z-index: 200;
        align-items: center;
        justify-content: center;
    }
    .booking-modal-overlay.active { display: flex; }
    .booking-modal {
        background: #fff;
        border-radius: 16px;
        padding: 30px 26px;
        width: 90%;
        max-width: 380px;
        text-align: center;
        box-shadow: 0 20px 50px rgba(0,0,0,.25);
        animation: bookingModalPop .18s ease;
    }
    @keyframes bookingModalPop {
        from { transform: scale(.95); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }
    .booking-modal-icon {
        width: 52px;
        height: 52px;
        margin: 0 auto 14px;
        border-radius: 50%;
        background: var(--pink-pale, #ffe6ee);
        color: var(--pink-deep, #b82850);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .booking-modal h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #1a1523;
    }
    .booking-modal-sub {
        font-size: .85rem;
        color: #635c72;
        margin-bottom: 18px;
    }
    .booking-modal-summary {
        background: #f8f6f9;
        border-radius: 12px;
        padding: 14px 16px;
        margin-bottom: 20px;
        text-align: left;
    }
    .booking-modal-row {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: .82rem;
        padding: 5px 0;
    }
    .booking-modal-row span { color: #9c94ab; }
    .booking-modal-row b { color: #1a1523; text-align: right; }
    .booking-modal-actions { display: flex; gap: 10px; }
    .booking-modal-actions button {
        flex: 1;
        padding: 11px 16px;
        border-radius: 10px;
        font-weight: 600;
        font-size: .86rem;
        border: none;
        cursor: pointer;
    }
    .btn-cancel { background: #f1eef2; color: #635c72; }
    .btn-cancel:hover { background: #e5e0e8; }
    .btn-confirm { background: var(--pink-deep, #b82850); color: #fff; }
    .btn-confirm:hover { background: var(--pink-dark, #d63e63); }

    .is-loading {
        opacity: .75;
        cursor: not-allowed;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-spinner {
        width: 14px;
        height: 14px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, .4);
        border-top-color: #fff;
        display: inline-block;
        animation: btnSpin .7s linear infinite;
    }
    @keyframes btnSpin {
        to { transform: rotate(360deg); }
    }
</style>
@endpush

@push('scripts')
<script>
    function togglePaymentPanels(method) {
        document.getElementById('qrphPanel').style.display = method === 'qrph' ? 'block' : 'none';
        document.getElementById('cashPanel').style.display = method === 'cash' ? 'block' : 'none';

        document.querySelectorAll('.payment-option').forEach(function (el) {
            const isSelected = el.dataset.method === method;
            el.style.borderColor = isSelected ? 'currentColor' : '';
            el.style.boxShadow = isSelected ? '0 0 0 1px currentColor' : 'none';
        });

        updateSubmitState(method);
    }

    function updateSubmitState(method) {
        const btn = document.getElementById('paymentSubmitBtn');
        const label = document.getElementById('submitLabel');
        const receiptInput = document.getElementById('receiptInput');

        if (method === 'cash') {
            label.textContent = 'Confirm Cash Payment';
            btn.disabled = false;
        } else if (method === 'qrph') {
            label.textContent = 'Send Receipt';
            btn.disabled = !(receiptInput && receiptInput.files && receiptInput.files.length > 0);
        } else {
            label.textContent = 'Select a payment method';
            btn.disabled = true;
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const checked = document.querySelector('input[name="payment_method"]:checked');
        if (checked) {
            togglePaymentPanels(checked.value);
        } else {
            updateSubmitState(null);
        }

        const receiptInput = document.getElementById('receiptInput');
        receiptInput?.addEventListener('change', function () {
            const nameEl = document.getElementById('receiptFileName');
            nameEl.textContent = this.files && this.files.length ? this.files[0].name : '';
            const checkedNow = document.querySelector('input[name="payment_method"]:checked');
            updateSubmitState(checkedNow ? checkedNow.value : null);
        });
    });
</script>
<script>
// ── Confirmation modal + loading state on submit (same pattern as booking.blade.php) ──
(function () {
    var form = document.getElementById('paymentForm');
    var submitBtn = document.getElementById('paymentSubmitBtn');
    var overlay = document.getElementById('paymentModalOverlay');
    var confirmBtn = document.getElementById('paymentModalConfirm');
    var cancelBtn = document.getElementById('paymentModalCancel');
    var confirmPaymentMethodText = document.getElementById('confirmPaymentMethodText');
    var paymentModalSub = document.getElementById('paymentModalSub');
    if (!form || !submitBtn || !overlay) return;

    var isConfirmed = false;

    function methodLabel(method) {
        if (method === 'qrph') return 'QR Ph';
        if (method === 'cash') return 'Cash on-site';
        return '—';
    }

    function openModal(method) {
        confirmPaymentMethodText.textContent = methodLabel(method);
        paymentModalSub.textContent = method === 'qrph'
            ? 'Your uploaded receipt will be sent for verification.'
            : 'Your slot stays reserved as "Pending payment" until you pay at the counter.';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
    }

    form.addEventListener('submit', function (e) {
        // Second time around — already confirmed, let it through.
        if (isConfirmed) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            var loadingLabel = document.querySelector('input[name="payment_method"]:checked')?.value === 'qrph'
                ? 'Sending Receipt...'
                : 'Confirming...';
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitBtn.innerHTML = '<span class="btn-spinner"></span> ' + loadingLabel;

            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                }
            }, 15000);
            return;
        }

        // First time — validate, then show the confirmation modal instead of submitting directly.
        e.preventDefault();

        var checked = document.querySelector('input[name="payment_method"]:checked');
        if (!checked) {
            alert('Please select a payment method first.');
            return;
        }

        if (checked.value === 'qrph') {
            var receiptInput = document.getElementById('receiptInput');
            if (!receiptInput || !receiptInput.files || !receiptInput.files.length) {
                alert('Please upload your payment receipt first.');
                return;
            }
        }

        openModal(checked.value);
    });

    cancelBtn.addEventListener('click', closeModal);
    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    confirmBtn.addEventListener('click', function () {
        isConfirmed = true;
        closeModal();
        if (form.requestSubmit) {
            form.requestSubmit(submitBtn);
        } else {
            form.submit();
        }
    });
})();
</script>
@endpush
PATCHEOF
echo "Updated:   booking-review.blade.php"

echo ""
echo "Done. All 3 files now use the confirmation modal + loading spinner pattern."
