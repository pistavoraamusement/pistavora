<?php
/**
 * patch_booking_layout_v2.php
 *
 * Run from your laravel-app root:
 *   php patch_booking_layout_v2.php
 *
 * What it does:
 *  - Swaps booking-col-pkg to the LEFT and booking-col-cal to the RIGHT
 *  - Moves the "+ Booking" submit button + its anchor span so they sit
 *    at the bottom of the package column, under the pagination
 *  - Leaves all CSS/JS untouched
 *
 * This version matches the CURRENT file content (with the All/Solo/
 * Packages filter tabs and prev/next pagination already in place).
 */

$path = __DIR__ . '/resources/views/user/booking.blade.php';

if (!file_exists($path)) {
    fwrite(STDERR, "ERROR: file not found at $path\n");
    fwrite(STDERR, "Run this script from your laravel-app root folder.\n");
    exit(1);
}

$content = file_get_contents($path);

$oldOpen = <<<'OLD'
    <form method="POST" action="{{ route('user.booking.store') }}" id="bookingForm">
        @csrf

        <div class="booking-split">

            {{-- ================= LEFT: big inline calendar ================= --}}
            <div class="booking-col booking-col-cal">
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
                    <span id="dateTimeLabel">No date &amp; time selected yet</span>
                </div>
                <input type="hidden" name="visit_date" id="visitDateInput" required>
                <input type="hidden" name="visit_time" id="visitTimeInput" required>
                <button type="submit" class="u-btn booking-submit-btn" id="bookingSubmitBtn">+ Booking</button>
                 <span id="bookingBtnAnchor" style="display:none;"></span>
            </div>

            {{-- ================= RIGHT: experience + filterable packages ================= --}}
            <div class="booking-col booking-col-pkg">
OLD;

$newOpen = <<<'NEW'
    <form method="POST" action="{{ route('user.booking.store') }}" id="bookingForm">
        @csrf

        <div class="booking-split">

            {{-- ================= LEFT: experience + filterable packages ================= --}}
            <div class="booking-col booking-col-pkg">
NEW;

if (strpos($content, $oldOpen) === false) {
    fwrite(STDERR, "ERROR: expected opening block not found — no changes made.\n");
    exit(1);
}
$content = str_replace($oldOpen, $newOpen, $content);

$oldClose = <<<'OLD'
                    <p class="promo-empty-filter" data-promo-empty hidden>No packages match this filter.</p>
                    <div class="promo-pagination" data-promo-pagination hidden>
                        <button type="button" data-page-prev aria-label="Previous page">&lsaquo;</button>
                        <span class="promo-page-info" data-page-info>1 / 1</span>
                        <button type="button" data-page-next aria-label="Next page">&rsaquo;</button>
                    </div>
                </div>
                @endforeach

            </div>

        </div>
    </form>
OLD;

$newClose = <<<'NEW'
                    <p class="promo-empty-filter" data-promo-empty hidden>No packages match this filter.</p>
                    <div class="promo-pagination" data-promo-pagination hidden>
                        <button type="button" data-page-prev aria-label="Previous page">&lsaquo;</button>
                        <span class="promo-page-info" data-page-info>1 / 1</span>
                        <button type="button" data-page-next aria-label="Next page">&rsaquo;</button>
                    </div>
                </div>
                @endforeach

                <button type="submit" class="u-btn booking-submit-btn" id="bookingSubmitBtn">+ Booking</button>
                <span id="bookingBtnAnchor" style="display:none;"></span>
            </div>

            {{-- ================= RIGHT: big inline calendar ================= --}}
            <div class="booking-col booking-col-cal">
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
                    <span id="dateTimeLabel">No date &amp; time selected yet</span>
                </div>
                <input type="hidden" name="visit_date" id="visitDateInput" required>
                <input type="hidden" name="visit_time" id="visitTimeInput" required>
            </div>

        </div>
    </form>
NEW;

if (strpos($content, $oldClose) === false) {
    fwrite(STDERR, "ERROR: expected closing block not found — aborting before writing anything.\n");
    exit(1);
}
$content = str_replace($oldClose, $newClose, $content);

file_put_contents($path, $content);

echo "Done. booking.blade.php patched:\n";
echo "  - package column is now LEFT, calendar column is now RIGHT\n";
echo "  - '+ Booking' button moved under the package pagination\n";
