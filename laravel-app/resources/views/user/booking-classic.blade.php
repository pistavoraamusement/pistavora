{{--
    resources/views/user/booking.blade.php
    Route: GET  /user/booking  ->  user.booking
           POST /user/booking  ->  user.booking.store
    Controller: App\Http\Controllers\User\BookingController@create / store

    Expects from the controller:
      $packages -> array keyed by service, then by package code, each
        ['name','desc','image','badge','category','tiers'] — already
        sorted ascending by starting price (lowest tier first) via
        BookingController::sortedPackages().
      $packageCategories -> ['solo' => 'Solo', 'bundle' => 'Bundle', 'packages' => 'Packages']

    Layout: two-column 50/50 split.
      - LEFT  column: service switch + package cards, filterable by
        category (Solo / Bundle / Packages) and paginated, plus the
        "+ Booking" submit button at the bottom. Packages are already
        price-sorted ascending, so the cheapest option in the active
        filter always shows first.
      - RIGHT column: a big, always-visible (no popup) calendar + time
        slots, so the visit date/time being booked is clearly visible
        at all times.

    Date & time are picked straight from the inline calendar (built client
    side in JS, always reflects the real current date/month). Visit duration
    depends on the selected package (see PACKAGE_DURATIONS in the script
    below) instead of a flat 3 hours:
      - Weekdays (Mon-Fri): open 9:00 AM  - 9:00 PM
      - Weekends (Sat-Sun): open 10:00 AM - 9:00 PM
--}}
@extends('layouts.user')

@section('title', 'Booking')
@section('page-title', 'Booking')
@section('page-subtitle', 'Pick a date and package')
@section('body-class', 'page-uniform')

@push('styles')
    <style>
        @media (max-width: 768px) {
    #bookingForm > .booking-submit-btn {
        width: 100%;
        margin-top: 24px;
    }
}
        /* loading state for the booking submit button */
        .booking-submit-btn.is-loading {
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
            to {
                transform: rotate(360deg);
            }
        }

        /* ---------- overall page rhythm ---------- */
       .booking-split {
    gap: 32px !important;
    align-items: flex-start; /* bago — para di na mag stretch */
}
.booking-modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, .55);
    z-index: 200;
    align-items: center;
    justify-content: center;
}
.booking-modal-overlay.active {
    display: flex;
}
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
.booking-modal-row span {
    color: #64748B;
}
.booking-modal-row b {
    color: #0F172A;
    text-align: right;
}
.booking-modal-actions {
    display: flex;
    gap: 10px;
}
.booking-modal-actions button {
    flex: 1;
    padding: 11px 16px;
    border-radius: 10px;
    font-weight: 600;
    font-size: .86rem;
    border: none;
    cursor: pointer;
}
.btn-cancel {
    background: #F1F5F9;
    color: #475569;
}
.btn-cancel:hover {
    background: #E2E8F0;
}
.btn-confirm {
    background: var(--pink-deep, #4F46E5);
    color: #fff;
}
.btn-confirm:hover {
    background: var(--pink-dark, #4338CA);
}

/* pagination controls */
.promo-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding-top: 4px;
}
.promo-pagination[hidden] { display: none; }
.promo-pagination button {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: 1.5px solid var(--border, #eee);
    background: #fff;
    color: var(--ink-soft);
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background .15s var(--ease), color .15s var(--ease);
}
.promo-pagination button:hover:not(:disabled) {
    background: var(--pink-deep);
    color: #fff;
    border-color: var(--pink-deep);
}
.promo-pagination button:disabled { opacity: .35; cursor: not-allowed; }
.promo-pagination button.active {
    background: var(--pink-deep);
    color: #fff;
    border-color: var(--pink-deep);
}
.promo-pagination .promo-page-info {
    font-size: 12px;
    font-weight: 700;
    color: var(--muted);
    min-width: 46px;
    text-align: center;
}
        .booking-col-pkg {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }
        .service-packages {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .service-packages[hidden] { display: none; }

        /* ---------- category filter tabs (All / Solo / Bundle / Packages) ---------- */
        /* ---------- category filter tabs (All / Solo / Bundle / Packages) ---------- */
.cat-filter {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    padding: 5px;
    background: var(--bg);
    border: 1px solid var(--line, #eee);
    border-radius: 14px;
}
.cat-filter button {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    border-radius: 10px;
    border: 1.5px solid transparent;
    background: #fd81d8ee;
    color: var(--ink-deep);
    font-size: 12px;
    font-weight: 700;
    letter-spacing: .01em;
    cursor: pointer;
    transition: background .15s var(--ease), color .15s var(--ease),
                box-shadow .15s var(--ease), transform .1s var(--ease),
                border-color .15s var(--ease);
}
.cat-filter button svg {
    width: 15px;
    height: 15px;
    flex-shrink: 0;
    opacity: .7;
    transition: opacity .15s var(--ease);
}
.cat-filter button .cat-count {
    font-size: 10px;
    font-weight: 800;
    color: var(--muted);
    background: var(--bg);
    border-radius: 999px;
    padding: 1px 6px;
    line-height: 1.4;
}
.cat-filter button:hover {
    background: var(--pink-pale, #EEF2FF);
    color: var(--pink-dark);
    border-color: var(--pink-deep);
    box-shadow: var(--shadow-card);
    transform: translateY(-1px);
}
.cat-filter button:hover svg { opacity: 1; }
.cat-filter button:hover .cat-count {
    background: #fff;
    color: var(--pink-dark);
}
.cat-filter button.active {
    background: var(--pink-deep);
    color: #fff;
    border-color: var(--red-deep);
    box-shadow: 0 6px 16px -8px rgba(99,102,241,.55);
    transform: translateY(-1px);
}
.cat-filter button.active svg { opacity: 1; }
.cat-filter button.active .cat-count {
    background: rgba(255,255,255,.22);
    color: #fff;
}

[data-theme="dark"] .cat-filter {
    background: var(--d-surface-soft);
    border-color: var(--d-border);
}
[data-theme="dark"] .cat-filter button {
    background: var(--d-surface);
    color: var(--d-text-soft);
    border-color: transparent;
}
[data-theme="dark"] .cat-filter button .cat-count {
    background: var(--d-surface-soft);
    color: var(--d-muted);
}
[data-theme="dark"] .cat-filter button:hover {
    background: rgba(99,102,241,.16);
    color: #fff;
    border-color: var(--pink-deep);
}
[data-theme="dark"] .cat-filter button:hover .cat-count {
    background: rgba(255,255,255,.14);
    color: #fff;
}
[data-theme="dark"] .cat-filter button.active {
    background: var(--pink-deep);
    color: #fff;
}

        .promo-empty-filter {
            font-size: 12.5px;
            color: var(--muted);
            text-align: center;
            padding: 28px 0;
            margin: 0;
        }

        /* give the promo grid + pkgSummary/service-switch breathing room
           without relying on scattered margin-top values */
        #pkgSummary { margin: 0; }
        #serviceSwitch { margin: 0; }
        .promo-grid { margin: 0 !important; }

        /* calendar column: consistent gap instead of ad hoc margins on
           the summary bar / hidden inputs / submit button */
        #calSelectedBar { margin: 0; }
        #bookingSubmitBtn { margin: 0; }

      @media (max-width: 768px) {
    #bookingForm > .booking-submit-btn {
        width: 100%;
        margin-top: 24px;
    }
    .booking-col-cal {
        position: static;
        top: auto;
    }
}

        /* ===== Mobile: calendar floats over the packages as a bottom sheet ===== */
        .cal-sheet-head, .cal-sheet-done, .cal-sheet-backdrop, .cal-open-btn { display: none; }

        @media (max-width: 768px) {
            .booking-split .booking-col-cal {
                position: fixed;
                left: 0; right: 0; bottom: 0; top: auto;
                z-index: 80;                      /* above bottom nav (20) and chat button (25-30) */
                max-height: 88vh;
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                background: #fff;
                border-radius: 22px 22px 0 0;
                box-shadow: 0 -18px 40px -12px rgba(26,21,35,.35);
                padding: 0 14px calc(14px + env(safe-area-inset-bottom, 0px));
                transform: translateY(105%);
                visibility: hidden;
                transition: transform .28s ease, visibility 0s linear .28s;
            }
            .booking-split .booking-col-cal.sheet-open {
                transform: translateY(0);
                visibility: visible;
                transition: transform .28s ease, visibility 0s;
            }
            .booking-col-cal .cal-embed { box-shadow: none; border: 0; }
            .booking-col-cal #calSelectedBar { margin-top: 8px; }

            .cal-sheet-head {
                display: flex; align-items: center; justify-content: space-between;
                position: sticky; top: 0; z-index: 2; background: #fff;
                padding: 14px 2px 8px;
            }
            .cal-sheet-head::before {
                content: ""; position: absolute; top: 6px; left: 50%;
                width: 40px; height: 4px; margin-left: -20px;
                border-radius: 4px; background: var(--line, #E2E8F0);
            }
            .cal-sheet-title { font-weight: 800; font-size: 15px; color: var(--ink, #0F172A); }
            .cal-sheet-close {
                width: 32px; height: 32px; border-radius: 10px; border: 1.5px solid var(--line, #E2E8F0);
                background: #fff; color: var(--ink-soft, #555); font-size: 18px; line-height: 1; cursor: pointer;
            }
            .cal-sheet-done {
                display: block; width: 100%; margin-top: 10px; padding: 13px 16px;
                border: 0; border-radius: 14px; cursor: pointer;
                font-weight: 700; font-size: 14px; color: #fff;
                background: linear-gradient(120deg, var(--pink-dark, #4338CA), var(--pink-deep, #4F46E5));
            }
            .cal-sheet-done:disabled { opacity: .45; cursor: not-allowed; }

            .cal-sheet-backdrop {
                display: block; position: fixed; inset: 0; z-index: 70;
                background: rgba(26,21,35,.45);
                opacity: 0; pointer-events: none; transition: opacity .25s ease;
            }
            .cal-sheet-backdrop.show { opacity: 1; pointer-events: auto; }

            /* shortcut button under "Selected: ..." to reopen the calendar */
            .cal-open-btn {
                display: flex; align-items: center; gap: 8px; width: 100%;
                margin: 8px 0 0; padding: 11px 14px; border-radius: 14px; cursor: pointer;
                border: 1.5px dashed var(--pink-deep, #4F46E5); background: #fff;
                color: var(--pink-deep, #4F46E5); font-weight: 700; font-size: 13px; text-align: left;
            }
            .cal-open-btn[hidden] { display: none; }
            .cal-open-btn svg { width: 16px; height: 16px; flex-shrink: 0; }

            body.cal-sheet-lock { overflow: hidden; }
        }
    </style>
@endpush

@section('content')

    <form method="POST" action="{{ route('user.booking.store') }}" id="bookingForm">
        @csrf

        <div class="booking-split">

            {{-- ================= LEFT: experience + filterable packages ================= --}}
            <div class="booking-col booking-col-pkg">
                <div class="service-switch" id="serviceSwitch">
                    @foreach ($services as $svcCode => $svc)
                        <button type="button" class="service-tab {{ $loop->first ? 'active' : '' }}" data-service="{{ $svcCode }}">
                            <span class="svc-ico" aria-hidden="true">{{ ['dino_adventure' => '🦕', 'rollerfever' => '🛼', 'field_of_rides' => '🎡'][$svcCode] ?? '🎟️' }}</span>
                            <span>
                                <b>{{ $svc['name'] }}</b>
                                <small>{{ $svc['tagline'] }}</small>
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="booking-info-banner">
                    <span class="booking-info-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:15px;height:15px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    </span>
                    <p>Please choose your preferred pass and session slot below to begin reservation.</p>
                </div>

                <div id="pkgSummary" class="promo-selection-summary empty">
                    No package selected yet
                </div>
                <button type="button" class="cal-open-btn" id="calOpenBtn" hidden>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"></rect><path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path></svg>
                    <span id="calOpenBtnText">Pick date &amp; time</span>
                </button>
                <input type="hidden" name="service" id="serviceInput" value="{{ array_key_first($services) }}" required>
                <input type="hidden" name="package" id="packageInput" required>
                <input type="hidden" name="tier" id="tierInput" required>

                @foreach ($packages as $svcCode => $servicePackages)
                @php
                    $categoriesInService = collect($servicePackages)->pluck('category')->unique()->values()->all();
                @endphp
                <div class="promo-panel service-packages" data-service-group="{{ $svcCode }}" {{ $loop->first ? '' : 'hidden' }}>

                    @if (count($categoriesInService) > 1)
                        @php
                            $catIcons = [
                                'all' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"></rect><rect x="14" y="3" width="7" height="7" rx="1.5"></rect><rect x="3" y="14" width="7" height="7" rx="1.5"></rect><rect x="14" y="14" width="7" height="7" rx="1.5"></rect></svg>',
                                'solo' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"></circle><path d="M4 21c0-4 3.5-7 8-7s8 3 8 7"></path></svg>',
                                'bundle' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="8" cy="9" r="3.2"></circle><circle cx="16" cy="9" r="3.2"></circle><path d="M2.5 20c0-3.4 2.7-6 5.5-6s5.5 2.6 5.5 6M10.5 20c0-3.4 2.7-6 5.5-6s5.5 2.6 5.5 6"></path></svg>',
                                'packages' => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8L12 3 3 8l9 5 9-5z"></path><path d="M3 8v8l9 5 9-5V8"></path><path d="M12 13v8"></path></svg>',];
                                 @endphp
                        <div class="cat-filter" data-cat-filter>
                            <button type="button" class="active" data-cat="all">
                                {!! $catIcons['all'] !!}
                                All
                                <span class="cat-count">{{ count($servicePackages) }}</span>
                            </button>
                            @foreach ($categoriesInService as $cat)
                                <button type="button" data-cat="{{ $cat }}">
                                    {!! $catIcons[$cat] ?? '' !!}
                                    {{ $packageCategories[$cat] ?? ucfirst($cat) }}
                                    <span class="cat-count">{{ collect($servicePackages)->where('category', $cat)->count() }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    <div class="promo-grid" data-promo-grid>
                    @foreach ($servicePackages as $code => $package)
                        <div class="promo-card" id="promoCard-{{ $svcCode }}-{{ $code }}" data-code="{{ $code }}" data-category="{{ $package['category'] }}">
                            <div class="promo-media">
                                <img src="{{ asset('images/'.$package['image']) }}" alt="{{ $package['name'] }}" loading="lazy">
                                <span class="promo-badge">{{ $package['badge'] }}</span>
                            </div>
                            <div class="promo-body">
                                @php
                                    $pkgTitle = $package['name'];
                                    $pkgSub = null;
                                    if (preg_match('/^(.*?)\s*\((.*)\)\s*$/', $pkgTitle, $m)) {
                                        $pkgTitle = $m[1];
                                        $pkgSub = $m[2];
                                    }
                                @endphp
                                <h4>{{ $pkgTitle }}</h4>
                                @if ($pkgSub)
                                    <span class="promo-subtitle">{{ $pkgSub }}</span>
                                @endif
                                <p>{{ $package['desc'] }}</p>

                                <div class="promo-price-row">
                                    <div>
                                        <span class="promo-price-label">Starts at</span>
                                        <span class="promo-price">₱{{ number_format(min($package['tiers'])) }}</span>
                                    </div>
                                    <button type="button" class="u-btn promo-select-btn" data-toggle="{{ $svcCode }}-{{ $code }}">Select package</button>
                                </div>

                                <button type="button" class="promo-details-link" data-toggle="{{ $svcCode }}-{{ $code }}">View pax options &amp; inclusions ›</button>

                                <div class="promo-tiers" id="promoTiers-{{ $svcCode }}-{{ $code }}" hidden>
                                    <p class="promo-tiers-title">{{ $pkgTitle }}</p>
                                    @foreach ($package['tiers'] as $pax => $price)
                                        <label class="promo-tier">
                                            <span style="display:flex;align-items:center;gap:8px;">
                                                <input type="radio" name="tier_choice" value="{{ $svcCode }}|{{ $code }}|{{ $pax }}" data-pkg-name="{{ $package['name'] }}" data-pax="{{ $pax }}" data-price="{{ $price }}">
                                                {{ $pax }} PAX
                                            </span>
                                            <b>₱{{ number_format($price) }}</b>
                                        </label>
                                    @endforeach

                                    <div class="promo-inclusions">
                                        <p class="promo-inclusions-title">What's included</p>
                                        <ul class="promo-inclusions-list" data-inclusions-list>
                                            @foreach (($inclusions[$svcCode][$code] ?? []) as $i => $item)
                                                <li {{ $i >= 3 ? 'data-extra hidden' : '' }}>{{ $item }}</li>
                                            @endforeach
                                        </ul>
                                        @if (count($inclusions[$svcCode][$code] ?? []) > 3)
                                            <button type="button" class="promo-seemore-btn" data-seemore>See more ({{ count($inclusions[$svcCode][$code]) - 3 }}) ›</button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>

                    <p class="promo-empty-filter" data-promo-empty hidden>No packages match this filter.</p>
                    <div class="promo-pagination" data-promo-pagination hidden></div>
                </div>
                @endforeach

            </div>

            {{-- ================= RIGHT: big inline calendar ================= --}}
            <div class="booking-col booking-col-cal" id="calSheet">
                <div class="cal-sheet-head">
                    <span class="cal-sheet-title">Pick date &amp; time</span>
                    <button type="button" class="cal-sheet-close" id="calSheetClose" aria-label="Close">&times;</button>
                </div>
                <div class="cal-embed" id="calEmbed">
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

                <div id="calSelectedBar" class="promo-selection-summary empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;flex-shrink:0;">
                        <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                        <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                    </svg>
                    <span id="dateTimeLabel" style="flex:1;">No date &amp; time selected yet</span>
                    <span id="dateTimeAvailableTag" class="tag green" style="display:none;white-space:nowrap;">Available</span>
                </div>
                <input type="hidden" name="visit_date" id="visitDateInput" required>
                <input type="hidden" name="visit_time" id="visitTimeInput" required>
                <button type="button" class="cal-sheet-done" id="calSheetDone" disabled>Done</button>
            </div>

        </div>
    </form>

    <div class="cal-sheet-backdrop" id="calSheetBackdrop"></div>

    <!-- Booking Confirmation Modal -->
    <div class="booking-modal-overlay" id="bookingModalOverlay">
        <div class="booking-modal">
            <div class="booking-modal-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <rect x="3" y="5" width="18" height="16" rx="2"></rect>
                    <path d="M16 3v4"></path><path d="M8 3v4"></path><path d="M3 11h18"></path>
                </svg>
            </div>
            <h3>Confirm Your Booking</h3>
            <p class="booking-modal-sub">Are you sure you want to book this package?</p>

            <div class="booking-modal-summary">
                <div class="booking-modal-row">
                    <span>Package</span>
                    <b id="confirmPackageText">—</b>
                </div>
                <div class="booking-modal-row">
                    <span>Date &amp; Time</span>
                    <b id="confirmDateTimeText">—</b>
                </div>
            </div>

            <div class="booking-modal-actions">
                <button type="button" class="btn-cancel" id="bookingModalCancel">Cancel</button>
                <button type="button" class="btn-confirm" id="bookingModalConfirm">Yes, Book Now</button>
            </div>
        </div>
    </div>

    <div class="booking-sticky-bar" id="bookingStickyBar">
        <div class="booking-sticky-info">
            <span id="stickyPkgCount">0 packages selected</span>
            <span class="booking-sticky-divider"></span>
            <span class="booking-sticky-total">
                <span class="booking-sticky-total-label">Total estimated:</span>
                <b id="stickyTotal">₱0.00</b>
            </span>
        </div>
        <button type="submit" form="bookingForm" class="u-btn booking-submit-btn" id="bookingSubmitBtn">+ Proceed to Booking</button>
    </div>

@endsection

@push('scripts')
<script>
(function () {
    var summaryEl = document.getElementById('pkgSummary');
    var serviceInput = document.getElementById('serviceInput');
    var packageInput = document.getElementById('packageInput');
    var tierInput = document.getElementById('tierInput');

    function peso(n) {
        return '₱' + Number(n).toLocaleString('en-PH');
    }

    function resetSelection() {
        packageInput.value = '';
        tierInput.value = '';
        document.querySelectorAll('.promo-tier').forEach(function (label) {
            label.classList.remove('selected');
        });
        document.querySelectorAll('.promo-card').forEach(function (card) {
            card.classList.remove('selected');
        });
        document.querySelectorAll('input[name="tier_choice"]').forEach(function (r) {
            r.checked = false;
        });
        summaryEl.classList.add('empty');
        summaryEl.textContent = 'No package selected yet';
        if (window.REKS_refreshSlots) window.REKS_refreshSlots();
    }

    document.querySelectorAll('.service-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (tab.classList.contains('active')) return;

            document.querySelectorAll('.service-tab').forEach(function (t) {
                t.classList.toggle('active', t === tab);
            });

            var svc = tab.dataset.service;
            serviceInput.value = svc;

            document.querySelectorAll('.service-packages').forEach(function (group) {
                group.hidden = group.dataset.serviceGroup !== svc;
            });

            // collapse any open tier panels from the previous service
            document.querySelectorAll('.promo-tiers').forEach(function (p) {
                p.setAttribute('hidden', '');
            });

            resetSelection();
        });
    });

    document.querySelectorAll('.promo-select-btn, .promo-details-link').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var key = btn.dataset.toggle;
            var panel = document.getElementById('promoTiers-' + key);
            var isHidden = panel.hasAttribute('hidden');

            // collapse any other open panel so only one is open at a time
            document.querySelectorAll('.promo-tiers').forEach(function (p) {
                if (p !== panel) p.setAttribute('hidden', '');
            });

            if (isHidden) {
                panel.removeAttribute('hidden');
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                panel.setAttribute('hidden', '');
            }
        });
    });

    document.querySelectorAll('input[name="tier_choice"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.promo-tier').forEach(function (label) {
                label.classList.remove('selected');
            });
            document.querySelectorAll('.promo-card').forEach(function (card) {
                card.classList.remove('selected');
            });
            radio.closest('.promo-tier').classList.add('selected');

            var parts = radio.value.split('|');
            var svc = parts[0];
            var code = parts[1];
            var pax = parts[2];

            document.getElementById('promoCard-' + svc + '-' + code).classList.add('selected');
            packageInput.value = code;
            tierInput.value = pax;
            if (window.REKS_refreshSlots) window.REKS_refreshSlots();

            summaryEl.classList.remove('empty');
            summaryEl.innerHTML = 'Selected: <b>' + radio.dataset.pkgName + '</b> · ' +
                pax + ' PAX · <b>' + peso(radio.dataset.price) + '</b>';
        });
    });

    // Sticky bottom bar (package count + running total) — separate,
    // additive listener; doesn't touch the selection logic above.
    var stickyCount = document.getElementById('stickyPkgCount');
    var stickyTotal = document.getElementById('stickyTotal');
    document.querySelectorAll('input[name="tier_choice"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (stickyCount) stickyCount.textContent = '1 package selected';
            if (stickyTotal) stickyTotal.textContent = peso(radio.dataset.price);
        });
    });
    document.querySelectorAll('.service-tab').forEach(function (tab) {
        tab.addEventListener('click', function () {
            if (stickyCount) stickyCount.textContent = '0 packages selected';
            if (stickyTotal) stickyTotal.textContent = '₱0.00';
        });
    });

    document.querySelectorAll('[data-seemore]').forEach(function (btn) {
        var originalLabel = btn.textContent;
        btn.addEventListener('click', function () {
            var list = btn.previousElementSibling;
            var expanded = btn.classList.toggle('expanded');
            list.querySelectorAll('[data-extra]').forEach(function (li) {
                li.hidden = !expanded;
            });
            btn.textContent = expanded ? 'See less ‹' : originalLabel;
        });
    });

    // ---------------- category filter (replaces prev/next pagination) ----------------
    // Packages already arrive price-sorted ascending from the controller,
    // so filtering just shows/hides cards — no re-sorting needed here.
   function initCategoryFilter(group) {
    var filterBar  = group.querySelector('[data-cat-filter]');
    var grid       = group.querySelector('[data-promo-grid]');
    var emptyMsg   = group.querySelector('[data-promo-empty]');
    var pagination = group.querySelector('[data-promo-pagination]');
    if (!grid) return;

    var PAGE_SIZE = 2; // 1 row of 2 — matches the narrower packages column width
    var cards = Array.prototype.slice.call(grid.children);
    var matched = cards;
    var currentPage = 1;

    function goToPage(p) {
        currentPage = p;
        renderPage();
        grid.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    function renderPage() {
        var totalPages = Math.max(1, Math.ceil(matched.length / PAGE_SIZE));
        if (currentPage > totalPages) currentPage = totalPages;

        var start = (currentPage - 1) * PAGE_SIZE;
        var end = start + PAGE_SIZE;

        cards.forEach(function (card) { card.style.display = 'none'; });
        matched.slice(start, end).forEach(function (card) { card.style.display = ''; });

        if (emptyMsg) emptyMsg.hidden = matched.length > 0;

        if (!pagination) return;

        pagination.hidden = matched.length <= PAGE_SIZE;
        pagination.innerHTML = '';
        if (totalPages <= 1) return;

        var prev = document.createElement('button');
        prev.type = 'button';
        prev.innerHTML = '&lsaquo;';
        prev.setAttribute('aria-label', 'Previous page');
        prev.disabled = currentPage <= 1;
        prev.addEventListener('click', function () {
            if (currentPage > 1) goToPage(currentPage - 1);
        });
        pagination.appendChild(prev);

        for (var p = 1; p <= totalPages; p++) {
            (function (p) {
                var num = document.createElement('button');
                num.type = 'button';
                num.textContent = p;
                num.classList.toggle('active', p === currentPage);
                num.addEventListener('click', function () { goToPage(p); });
                pagination.appendChild(num);
            })(p);
        }

        var next = document.createElement('button');
        next.type = 'button';
        next.innerHTML = '&rsaquo;';
        next.setAttribute('aria-label', 'Next page');
        next.disabled = currentPage >= totalPages;
        next.addEventListener('click', function () {
            if (currentPage < totalPages) goToPage(currentPage + 1);
        });
        pagination.appendChild(next);
    }

    function applyFilter(cat) {
        matched = cards.filter(function (card) {
            return cat === 'all' || card.dataset.category === cat;
        });
        currentPage = 1;
        renderPage();
    }

    if (filterBar) {
        filterBar.querySelectorAll('button').forEach(function (btn) {
            btn.addEventListener('click', function () {
                filterBar.querySelectorAll('button').forEach(function (b) {
                    b.classList.toggle('active', b === btn);
                });
                applyFilter(btn.dataset.cat);
            });
        });
    }

    applyFilter('all');
}

    document.querySelectorAll('.service-packages').forEach(initCategoryFilter);
})();
</script>
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

    // Visit duration (in hours) per package code. null = all-day pass
    // (no fixed duration — just needs a lead time before closing).
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
        return 3; // fallback if nothing selected yet
    }

    function resetTimeSelection() {
        state.selectedTime = null;
        visitDateInput.value = '';
        visitTimeInput.value = '';
        bar.classList.add('empty');
        label.textContent = 'No date & time selected yet';
        var availTag = document.getElementById('dateTimeAvailableTag');
        if (availTag) availTag.style.display = 'none';
        renderSlots();
    }
    window.REKS_resetTimeSelection = resetTimeSelection;
    // Re-renders the time-slot grid for the currently selected package's
    // duration WITHOUT clearing the guest's already-chosen date/time —
    // used when switching service tabs or packages, so a selection made
    // earlier doesn't disappear. If the previously chosen time no longer
    // fits the new package duration, renderSlots() simply won't show it
    // as highlighted, but nothing is cleared.
    window.REKS_refreshSlots = function () {
        if (state.selectedDate) renderSlots();
    };

    var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
    var DAYS_SHORT = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];

    function pad(n) { return n < 10 ? '0' + n : '' + n; }

    function isWeekend(date) {
        var d = date.getDay(); // 0 = Sun, 6 = Sat
        return d === 0 || d === 6;
    }

    // Operating hours for the day.
    // Store closes at 9:00 PM every day — weekday closing time was
    // previously 10:00 PM, which produced booking slots that ran past
    // actual closing time (e.g. 7:00 PM - 10:00 PM for a 3-hour package).
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

    function renderCalendar() {
        monthLbl.textContent = MONTHS[state.viewMonth.getMonth()] + ' ' + state.viewMonth.getFullYear();
        daysEl.innerHTML = '';

        var firstOfMonth = new Date(state.viewMonth.getFullYear(), state.viewMonth.getMonth(), 1);
        // Monday-first offset (0 = Monday ... 6 = Sunday)
        var offset = (firstOfMonth.getDay() + 6) % 7;
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
        var availTagOnDateChange = document.getElementById('dateTimeAvailableTag');
        if (availTagOnDateChange) availTagOnDateChange.style.display = 'none';
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
        var duration = getSelectedDurationHours(); // hours, or null = all-day
        var lastStartHour = duration === null
            ? hours.closeHour - 1   // all-day pass: allow entry up to 1hr before closing
            : hours.closeHour - duration;
        var isToday = state.selectedDate.getTime() === today.getTime();
        var now = new Date();
        var hasSlots = false;

        for (var h = hours.openHour; h <= lastStartHour; h++) {
            [0, 30].forEach(function (m) {
                // don't go past the last allowed start time
                if (h === lastStartHour && m > 0) return;

                // hide past slots if the selected date is today
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
        var availTagOnCommit = document.getElementById('dateTimeAvailableTag');
        if (availTagOnCommit) availTagOnCommit.style.display = 'inline-block';
        document.dispatchEvent(new CustomEvent('reks:slot-committed'));
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

    // render immediately — calendar is always visible now, no open/close popup
    renderCalendar();
    renderSlots();
})();
</script>
<script>
// ── Mobile: calendar as a bottom sheet that floats over the packages ──
(function () {
    var sheet    = document.getElementById('calSheet');
    var backdrop = document.getElementById('calSheetBackdrop');
    var closeBtn = document.getElementById('calSheetClose');
    var doneBtn  = document.getElementById('calSheetDone');
    var openBtn  = document.getElementById('calOpenBtn');
    var openTxt  = document.getElementById('calOpenBtnText');
    var timeIn   = document.getElementById('visitTimeInput');
    var dateIn   = document.getElementById('visitDateInput');
    var label    = document.getElementById('dateTimeLabel');
    if (!sheet || !backdrop) return;

    var mq = window.matchMedia('(max-width: 768px)');
    var autoCloseTimer = null;

    function isMobile() { return mq.matches; }

    function openSheet() {
        // no JS width check: the CSS only turns .sheet-open into a bottom sheet
        // on mobile widths, so on desktop this class is harmless.
        sheet.classList.add('sheet-open');
        backdrop.classList.add('show');
        document.body.classList.add('cal-sheet-lock');
    }
    function closeSheet() {
        clearTimeout(autoCloseTimer);
        sheet.classList.remove('sheet-open');
        backdrop.classList.remove('show');
        document.body.classList.remove('cal-sheet-lock');
    }
    function syncOpenBtn() {
        if (!openBtn) return;
        var hasPkg = !!document.getElementById('packageInput').value;
        openBtn.hidden = !hasPkg;
        if (dateIn.value && timeIn.value) {
            openTxt.innerHTML = label.innerHTML.replace('Selected:', 'Change:');
        } else {
            openTxt.textContent = 'Pick date & time';
        }
        if (doneBtn) doneBtn.disabled = !(dateIn.value && timeIn.value);
    }

    // open the sheet as soon as a package/tier is chosen.
    // Delegated on document so it still works if the package cards are
    // re-rendered/moved by the category filter or pagination.
    document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t || t.name !== 'tier_choice') return;
        // let the main selection handler finish first (it sets packageInput)
        setTimeout(function () {
            syncOpenBtn();
            // collapse the tier panel so the sheet isn't covering an open overlay
            document.querySelectorAll('.promo-tiers').forEach(function (p) { p.setAttribute('hidden', ''); });
            openSheet();
        }, 0);
    });

    // switching service tab clears the package -> hide the shortcut + close
    document.addEventListener('click', function (e) {
        if (e.target.closest && e.target.closest('.service-tab')) {
            closeSheet(); setTimeout(syncOpenBtn, 0);
        }
    });

    // once a time slot is picked, update the shortcut and close the sheet shortly after
    document.addEventListener('reks:slot-committed', function () {
        syncOpenBtn();
        clearTimeout(autoCloseTimer);
        autoCloseTimer = setTimeout(closeSheet, 700);
    });

    if (closeBtn) closeBtn.addEventListener('click', closeSheet);
    if (doneBtn)  doneBtn.addEventListener('click', closeSheet);
    backdrop.addEventListener('click', closeSheet);
    if (openBtn)  openBtn.addEventListener('click', openSheet);

    // leaving mobile width: make sure nothing stays locked
    var onChange = function () { if (!isMobile()) closeSheet(); };
    if (mq.addEventListener) mq.addEventListener('change', onChange); else mq.addListener(onChange);

    syncOpenBtn();
})();
</script>
<script>
// ── Confirmation modal + loading state on submit ──
(function () {
    var form = document.getElementById('bookingForm');
    var submitBtn = document.getElementById('bookingSubmitBtn');
    var overlay = document.getElementById('bookingModalOverlay');
    var confirmBtn = document.getElementById('bookingModalConfirm');
    var cancelBtn = document.getElementById('bookingModalCancel');
    var confirmPackageText = document.getElementById('confirmPackageText');
    var confirmDateTimeText = document.getElementById('confirmDateTimeText');
    if (!form || !submitBtn || !overlay) return;

    var originalLabel = submitBtn.textContent;
    var isConfirmed = false;

    function openModal() {
        var pkgSummary = document.getElementById('pkgSummary');
        var dateBar = document.getElementById('dateTimeLabel');
        confirmPackageText.textContent = pkgSummary ? pkgSummary.textContent.trim() : '—';
        confirmDateTimeText.textContent = dateBar ? dateBar.textContent.trim() : '—';
        overlay.classList.add('active');
    }

    function closeModal() {
        overlay.classList.remove('active');
    }

    form.addEventListener('submit', function (e) {
        // Pangalawang beses na — kumpirmado na, papayagan nating tuluyan
        if (isConfirmed) {
            if (submitBtn.disabled) {
                e.preventDefault();
                return;
            }
            submitBtn.disabled = true;
            submitBtn.classList.add('is-loading');
            submitBtn.innerHTML = '<span class="btn-spinner"></span> Booking...';

            setTimeout(function () {
                if (submitBtn.disabled) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('is-loading');
                    submitBtn.textContent = originalLabel;
                }
            }, 15000);
            return;
        }

        // Unang beses — i-validate muna, tapos ipakita ang modal sa halip na direktang mag-submit
        e.preventDefault();

        var visitDate = document.getElementById('visitDateInput').value;
        var visitTime = document.getElementById('visitTimeInput').value;
        var pkg = document.getElementById('packageInput').value;
        var tier = document.getElementById('tierInput').value;

        if (!visitDate || !visitTime || !pkg || !tier) {
            alert('Please pick a date, time, and package before booking.');
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