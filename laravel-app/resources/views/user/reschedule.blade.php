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
        background: var(--pink-pale, #EEF2FF);
        color: var(--pink-deep, #4F46E5);
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .booking-modal h3 {
        font-size: 1.05rem;
        font-weight: 700;
        margin-bottom: 6px;
        color: #0F172A;
    }
    .booking-modal-sub {
        font-size: .85rem;
        color: #475569;
        margin-bottom: 18px;
    }
    .booking-modal-summary {
        background: #F8FAFC;
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
    .booking-modal-row span { color: #64748B; }
    .booking-modal-row b { color: #0F172A; text-align: right; }
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
    .btn-cancel { background: #F1F5F9; color: #475569; }
    .btn-cancel:hover { background: #E2E8F0; }
    .btn-confirm { background: var(--pink-deep, #4F46E5); color: #fff; }
    .btn-confirm:hover { background: var(--pink-dark, #4338CA); }

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
