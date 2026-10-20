{{--
    resources/views/user/booking-review.blade.php
    Route: GET  /user/bookings/{booking}/review              -> user.bookings.review
           POST /user/bookings/{booking}/payment              -> user.bookings.payment
    Controller: App\Http\Controllers\User\BookingController@review / confirmPayment

    Expects from the controller:
      $booking -> ['id', 'category', 'package', 'date', 'time', 'pax',
                   'price', 'status_label', 'status_class']
--}}
@extends('layouts.user')

@section('title', 'Review Booking')
@section('page-title', 'Review Booking')
@section('page-subtitle', 'Confirm the details below before payment')
@section('body-class', 'page-uniform')

@section('content')

    {{-- Booking summary --}}
    <div class="u-card rb-summary-card">
        <div class="rb-summary-top">
            <div class="rb-summary-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:22px;height:22px;">
                    <path d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 0 0-2 2v3a2 2 0 1 1 0 4v3a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-3a2 2 0 1 1 0-4V7a2 2 0 0 0-2-2H5z"/>
                </svg>
            </div>
            <div style="flex:1;min-width:200px;">
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <h4 style="margin:0;">{{ $booking['package'] }}</h4>
                    <span class="rb-badge rb-badge-teal">Confirmed after payment check</span>
                    <span class="rb-badge rb-badge-neutral">{{ $booking['category'] }}</span>
                </div>
                <p style="margin:4px 0 0;font-size:13px;color:var(--muted);">
                    {{ $booking['date'] }} &middot; {{ $booking['pax'] }} pax
                    @if (!empty($booking['time']))
                        <span style="color:var(--line);padding:0 4px;">|</span> {{ $booking['time'] }}
                    @endif
                </p>
            </div>
            <span class="tag {{ $booking['status_class'] }}" style="white-space:nowrap;">{{ $booking['status_label'] }}</span>
        </div>

        <div class="rb-price-grid">
            <div class="rb-price-box">
                <div class="rb-price-label">Guests</div>
                <div class="rb-price-value">{{ $booking['pax'] }} pax</div>
            </div>
            <div class="rb-price-box rb-price-box-highlight">
                <div class="rb-price-label rb-price-label-highlight">Total Amount Due</div>
                <div class="rb-price-value rb-price-value-highlight">{{ $booking['price'] }}</div>
                <div class="rb-price-sub">Inclusive of VAT and local taxes</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('user.bookings.payment', $booking['id']) }}" enctype="multipart/form-data" id="paymentForm">
        @csrf

        {{-- Payment method selection --}}
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:20px;">
            <div>
                <h4 style="margin:0 0 2px;">Payment method</h4>
                <p style="margin:0;font-size:12.5px;color:var(--muted);">Choose how you'd like to pay for this booking.</p>
            </div>
            <span class="rb-step-chip">Step 2 of 3</span>
        </div>

        <div class="payment-options" style="display:flex;flex-direction:column;gap:10px;margin-top:10px;">

            <label class="pkg payment-option" data-method="qrph" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="rb-method-icon rb-method-icon-rose">QR<span style="color:#2E5BFF;">Ph</span></div>
                    <div>
                        <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                            <b>QR Ph (National QR Standard)</b>
                            <span class="rb-badge rb-badge-pink">Recommended</span>
                            <span class="rb-badge rb-badge-teal">Zero Convenience Fee</span>
                        </div>
                        <span>Scan with any GCash, Maya, ShopeePay, BPI, BDO, UnionBank, or bank app</span>
                    </div>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="qrph" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

            <label class="pkg payment-option" data-method="gcash" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="rb-method-icon rb-method-icon-blue">GCash</div>
                    <div>
                        <b>GCash Express Merchant</b>
                        <span>Scan our QR with your GCash app</span>
                    </div>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="gcash" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

            <label class="pkg payment-option" data-method="maya" style="cursor:pointer;align-items:center;transition:box-shadow .15s ease, border-color .15s ease;">
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="rb-method-icon rb-method-icon-teal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;">
                            <rect x="2" y="5" width="20" height="14" rx="2"/>
                            <line x1="2" y1="10" x2="22" y2="10"/>
                        </svg>
                    </div>
                    <div>
                        <b>Maya</b>
                        <span>Scan our QR with your Maya app</span>
                    </div>
                </div>
                <div class="price">
                    <input type="radio" name="payment_method" value="maya" onchange="togglePaymentPanels(this.value)" style="width:18px;height:18px;">
                </div>
            </label>

        </div>

        {{-- QR Ph instructions --}}
        <div id="qrphPanel" class="u-card" style="margin-top:14px;display:none;">
            <div class="rb-scan-layout">
                <div class="rb-scan-info">
                    <div class="rb-live-chip"><span class="rb-live-dot"></span> Live Gateway Terminal</div>
                    <h4 style="margin:10px 0 4px;">Scan to pay</h4>
                    <p style="font-size:13px;color:var(--ink-soft);line-height:1.6;">
                        Open your <b>GCash</b>, <b>Maya</b>, or any preferred banking app (BDO, BPI, UnionBank, RCBC), choose <span style="color:var(--pink-deep);font-weight:700;">Scan QR</span>, and point your camera at this QR Ph code.
                    </p>

                    <div class="rb-merchant-box">
                        <div class="rb-merchant-row">
                            <span>Merchant Name:</span>
                            <b>PISTAVORA AMUSEMENT PARK</b>
                        </div>
                        <div class="rb-merchant-row">
                            <span>Booking Reference:</span>
                            <code class="rb-ref-code">#WP-{{ $booking['id'] }}</code>
                        </div>
                        <div class="rb-merchant-row">
                            <span>Exact Amount:</span>
                            <b style="font-size:14px;">{{ $booking['price'] }}</b>
                        </div>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                        <span class="rb-timer-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Please complete payment within <b id="qrCountdown">15:00</b>
                        </span>
                    </div>
                </div>

                <div class="rb-scan-qr-frame">
                    <div class="rb-scan-qr-header">
                        <span>NATIONAL STANDARD</span>
                        <span class="rb-scan-qr-verified">QR Ph Verified</span>
                    </div>
                    <img src="{{ asset('images/instapay-qr.png') }}" alt="QR Ph code" style="width:100%;max-width:320px;height:auto;aspect-ratio:1/1;object-fit:contain;border-radius:10px;display:block;margin:0 auto;">
                    <div style="text-align:center;margin-top:10px;">
                        <span style="font-size:11px;font-weight:700;color:var(--ink);display:block;">PISTAVORA PH</span>
                        <span style="font-size:10px;color:var(--muted);">Scan via Any Banking App</span>
                    </div>
                </div>
            </div>

            <div class="rb-upload-zone" data-dropzone="qrph">
                <p style="font-size:11.5px;color:var(--muted);margin:0 0 10px;">
                    After paying, upload a screenshot or photo of your payment receipt below, then tap "Send Receipt". We'll verify and confirm your booking shortly.
                </p>
                <div class="rb-dropzone" id="qrphDropzone">
                    <div class="rb-dropzone-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;">
                            <path d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div style="font-size:13px;font-weight:600;">Drag &amp; drop your payment proof here, or <label for="receiptInput" style="color:var(--pink-deep);text-decoration:underline;cursor:pointer;">Browse files</label></div>
                    <p style="font-size:11px;color:var(--muted);margin:4px 0 0;">Supports PNG, JPG, or PDF (up to 5 MB)</p>
                </div>
                <input type="file" name="receipt" id="receiptInput" accept="image/*" disabled style="display:none;">
                <div class="rb-file-status">
                    <span id="receiptFileName">No file chosen yet</span>
                    <label for="receiptInput" style="color:var(--pink-deep);font-weight:700;cursor:pointer;">Choose File</label>
                </div>
            </div>
        </div>

        {{-- GCash instructions --}}
        <div id="gcashPanel" class="u-card" style="margin-top:14px;display:none;">
            <div class="rb-scan-layout">
                <div class="rb-scan-info">
                    <div class="rb-live-chip"><span class="rb-live-dot"></span> Live Gateway Terminal</div>
                    <h4 style="margin:10px 0 4px;">Scan to pay with GCash</h4>
                    <p style="font-size:13px;color:var(--ink-soft);line-height:1.6;">
                        Open your <b>GCash</b> app, choose <span style="color:var(--pink-deep);font-weight:700;">Scan QR</span>, and point your camera at this QR code. You can also scan it with <b>Maya</b> or any banking app that supports InstaPay.
                    </p>

                    <div class="rb-merchant-box">
                        <div class="rb-merchant-row">
                            <span>Merchant Name:</span>
                            <b>PISTAVORA AMUSEMENT PARK</b>
                        </div>
                        <div class="rb-merchant-row">
                            <span>Booking Reference:</span>
                            <code class="rb-ref-code">#WP-{{ $booking['id'] }}</code>
                        </div>
                        <div class="rb-merchant-row">
                            <span>Exact Amount:</span>
                            <b style="font-size:14px;">{{ $booking['price'] }}</b>
                        </div>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                        <span class="rb-timer-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Please complete payment within <b id="gcashCountdown">15:00</b>
                        </span>
                    </div>
                </div>

                <div class="rb-scan-qr-frame">
                    <div class="rb-scan-qr-header">
                        <span>INSTAPAY</span>
                        <span class="rb-scan-qr-verified">GCash Payment</span>
                    </div>
                    <img src="{{ asset('images/instapay-qr.png') }}" alt="GCash / InstaPay QR code" style="width:100%;max-width:320px;height:auto;aspect-ratio:1/1;object-fit:contain;border-radius:10px;display:block;margin:0 auto;">
                    <div style="text-align:center;margin-top:10px;">
                        <span style="font-size:11px;font-weight:700;color:var(--ink);display:block;">PISTAVORA PH</span>
                        <span style="font-size:10px;color:var(--muted);">Scan via GCash or Any Banking App</span>
                    </div>
                </div>
            </div>

            <div class="rb-upload-zone" data-dropzone="gcash">
                <p style="font-size:11.5px;color:var(--muted);margin:0 0 10px;">
                    After paying, upload a screenshot or photo of your GCash payment confirmation below, then tap "Send Receipt". We'll verify and confirm your booking shortly.
                </p>
                <div class="rb-dropzone" id="gcashDropzone">
                    <div class="rb-dropzone-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;">
                            <path d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div style="font-size:13px;font-weight:600;">Drag &amp; drop your payment proof here, or <label for="gcashReceiptInput" style="color:var(--pink-deep);text-decoration:underline;cursor:pointer;">Browse files</label></div>
                    <p style="font-size:11px;color:var(--muted);margin:4px 0 0;">Supports PNG, JPG, or PDF (up to 5 MB)</p>
                </div>
                <input type="file" name="receipt" id="gcashReceiptInput" accept="image/*" disabled style="display:none;">
                <div class="rb-file-status">
                    <span id="gcashReceiptFileName">No file chosen yet</span>
                    <label for="gcashReceiptInput" style="color:var(--pink-deep);font-weight:700;cursor:pointer;">Choose File</label>
                </div>
            </div>
        </div>

        {{-- Maya instructions --}}
        <div id="mayaPanel" class="u-card" style="margin-top:14px;display:none;">
            <div class="rb-scan-layout">
                <div class="rb-scan-info">
                    <div class="rb-live-chip"><span class="rb-live-dot"></span> Live Gateway Terminal</div>
                    <h4 style="margin:10px 0 4px;">Scan to pay with Maya</h4>
                    <p style="font-size:13px;color:var(--ink-soft);line-height:1.6;">
                        Open your <b>Maya</b> app, choose <span style="color:var(--pink-deep);font-weight:700;">Scan QR</span>, and point your camera at this QR code. You can also scan it with <b>GCash</b> or any banking app that supports InstaPay.
                    </p>

                    <div class="rb-merchant-box">
                        <div class="rb-merchant-row">
                            <span>Merchant Name:</span>
                            <b>PISTAVORA AMUSEMENT PARK</b>
                        </div>
                        <div class="rb-merchant-row">
                            <span>Booking Reference:</span>
                            <code class="rb-ref-code">#WP-{{ $booking['id'] }}</code>
                        </div>
                        <div class="rb-merchant-row">
                            <span>Exact Amount:</span>
                            <b style="font-size:14px;">{{ $booking['price'] }}</b>
                        </div>
                    </div>

                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;">
                        <span class="rb-timer-chip">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;">
                                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                            </svg>
                            Please complete payment within <b id="mayaCountdown">15:00</b>
                        </span>
                    </div>
                </div>

                <div class="rb-scan-qr-frame">
                    <div class="rb-scan-qr-header">
                        <span>INSTAPAY</span>
                        <span class="rb-scan-qr-verified">Maya Payment</span>
                    </div>
                    <img src="{{ asset('images/instapay-qr.png') }}" alt="Maya / InstaPay QR code" style="width:100%;max-width:320px;height:auto;aspect-ratio:1/1;object-fit:contain;border-radius:10px;display:block;margin:0 auto;">
                    <div style="text-align:center;margin-top:10px;">
                        <span style="font-size:11px;font-weight:700;color:var(--ink);display:block;">PISTAVORA PH</span>
                        <span style="font-size:10px;color:var(--muted);">Scan via Maya or Any Banking App</span>
                    </div>
                </div>
            </div>

            <div class="rb-upload-zone" data-dropzone="maya">
                <p style="font-size:11.5px;color:var(--muted);margin:0 0 10px;">
                    After paying, upload a screenshot or photo of your Maya payment confirmation below, then tap "Send Receipt". We'll verify and confirm your booking shortly.
                </p>
                <div class="rb-dropzone" id="mayaDropzone">
                    <div class="rb-dropzone-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="width:22px;height:22px;">
                            <path d="M7 16a4 4 0 0 1-.88-7.903A5 5 0 1 1 15.9 6L16 6a5 5 0 0 1 1 9.9M15 13l-3-3m0 0l-3 3m3-3v12" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div style="font-size:13px;font-weight:600;">Drag &amp; drop your payment proof here, or <label for="mayaReceiptInput" style="color:var(--pink-deep);text-decoration:underline;cursor:pointer;">Browse files</label></div>
                    <p style="font-size:11px;color:var(--muted);margin:4px 0 0;">Supports PNG, JPG, or PDF (up to 5 MB)</p>
                </div>
                <input type="file" name="receipt" id="mayaReceiptInput" accept="image/*" disabled style="display:none;">
                <div class="rb-file-status">
                    <span id="mayaReceiptFileName">No file chosen yet</span>
                    <label for="mayaReceiptInput" style="color:var(--pink-deep);font-weight:700;cursor:pointer;">Choose File</label>
                </div>
            </div>
        </div>

        <div class="rb-confirm-bar">
            <div style="display:flex;align-items:center;gap:12px;">
                <div class="rb-confirm-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;">
                        <path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0 1 12 2.944a11.955 11.955 0 0 1-8.618 3.04A12.02 12.02 0 0 0 3 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div style="font-size:13px;font-weight:700;">Guaranteed Admission</div>
                    <div style="font-size:11.5px;color:var(--muted);">Tickets will be sent to your account immediately after verification</div>
                </div>
            </div>

            <button type="submit" class="u-btn" id="paymentSubmitBtn" disabled style="width:auto;padding:12px 24px;white-space:nowrap;">
                <span id="submitLabel">Select a payment method</span>
            </button>
        </div>
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
        border-radius: 10px;
        padding: 12px 14px;
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
    function togglePaymentPanels(method) {
        document.getElementById('qrphPanel').style.display = method === 'qrph' ? 'block' : 'none';
        document.getElementById('gcashPanel').style.display = method === 'gcash' ? 'block' : 'none';
        document.getElementById('mayaPanel').style.display = method === 'maya' ? 'block' : 'none';

        // All three file inputs share name="receipt" so only one is ever
        // actually submitted — disable the inactive ones so the browser
        // doesn't send empty/duplicate "receipt" fields.
        document.getElementById('receiptInput').disabled = method !== 'qrph';
        document.getElementById('gcashReceiptInput').disabled = method !== 'gcash';
        document.getElementById('mayaReceiptInput').disabled = method !== 'maya';

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
        const inputByMethod = {
            qrph: document.getElementById('receiptInput'),
            gcash: document.getElementById('gcashReceiptInput'),
            maya: document.getElementById('mayaReceiptInput'),
        };
        const activeInput = inputByMethod[method];

        if (activeInput) {
            label.textContent = 'Send Receipt';
            btn.disabled = !(activeInput.files && activeInput.files.length > 0);
        } else {
            label.textContent = 'Select a payment method';
            btn.disabled = true;
        }
    }

    // Generic drag-and-drop wiring — works for any of the three dropzones,
    // forwarding the dropped/picked file into its paired hidden <input>.
    function initDropzone(dropzoneId, inputId, fileNameId) {
        const zone = document.getElementById(dropzoneId);
        const input = document.getElementById(inputId);
        const nameEl = document.getElementById(fileNameId);
        if (!zone || !input) return;

        function showFile() {
            nameEl.textContent = input.files && input.files.length ? input.files[0].name : 'No file chosen yet';
            const checkedNow = document.querySelector('input[name="payment_method"]:checked');
            updateSubmitState(checkedNow ? checkedNow.value : null);
        }

        zone.addEventListener('click', function () { input.click(); });
        input.addEventListener('change', showFile);

        ['dragenter', 'dragover'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                zone.classList.add('rb-dropzone-active');
            });
        });
        ['dragleave', 'drop'].forEach(function (evt) {
            zone.addEventListener(evt, function (e) {
                e.preventDefault();
                zone.classList.remove('rb-dropzone-active');
            });
        });
        zone.addEventListener('drop', function (e) {
            if (e.dataTransfer.files && e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                showFile();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const checked = document.querySelector('input[name="payment_method"]:checked');
        if (checked) {
            togglePaymentPanels(checked.value);
        } else {
            updateSubmitState(null);
        }

        initDropzone('qrphDropzone', 'receiptInput', 'receiptFileName');
        initDropzone('gcashDropzone', 'gcashReceiptInput', 'gcashReceiptFileName');
        initDropzone('mayaDropzone', 'mayaReceiptInput', 'mayaReceiptFileName');

        // Cosmetic reminder countdown on the QR Ph panel — not an enforced
        // session expiry, just a soft nudge to pay promptly.
        var countdownEls = ['qrCountdown', 'gcashCountdown', 'mayaCountdown']
            .map(function (id) { return document.getElementById(id); })
            .filter(Boolean);
        if (countdownEls.length) {
            var seconds = 15 * 60;
            setInterval(function () {
                if (seconds <= 0) return;
                seconds--;
                var m = Math.floor(seconds / 60);
                var s = seconds % 60;
                countdownEls.forEach(function (el) {
                    el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
                });
            }, 1000);
        }
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
        if (method === 'gcash') return 'GCash';
        if (method === 'maya') return 'Maya';
        return '—';
    }

    function openModal(method) {
        confirmPaymentMethodText.textContent = methodLabel(method);
        paymentModalSub.textContent = 'Your uploaded receipt will be sent for verification.';
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
            var loadingLabel = 'Sending Receipt...';
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

        if (checked.value === 'gcash') {
            var gcashReceiptInput = document.getElementById('gcashReceiptInput');
            if (!gcashReceiptInput || !gcashReceiptInput.files || !gcashReceiptInput.files.length) {
                alert('Please upload your GCash payment receipt first.');
                return;
            }
        }

        if (checked.value === 'maya') {
            var mayaReceiptInput = document.getElementById('mayaReceiptInput');
            if (!mayaReceiptInput || !mayaReceiptInput.files || !mayaReceiptInput.files.length) {
                alert('Please upload your Maya payment receipt first.');
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
