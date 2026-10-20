{{--
    resources/views/user/booking-receipt.blade.php
    Route: GET /app/bookings/{booking}/receipt -> user.bookings.receipt
    Controller: App\Http\Controllers\User\BookingController@receipt

    Same voucher-ticket look as the booking wizard and My Bookings.
    The voucher code is only shown once the booking is confirmed.

    Expects from the controller:
      $booking -> ['id','reference','service','service_name','package_name','items','addons','discount',
                   'date','time','pax','price','payment_method','status','status_label','voucher_code']
--}}
@extends('layouts.user')

@section('title', 'Booking Receipt')
@section('page-title', 'Booking Receipt')
@section('page-subtitle', in_array($booking['status'], ['confirmed', 'done'], true) ? 'Payment verified — your booking is confirmed' : 'Payment received — waiting for approval')
@section('body-class', 'page-uniform')

@push('styles')
<style>
.mt-wrap{ --mt-surface:#fff; --mt-soft:#FBF8FB; --mt-text:var(--ink); --mt-sub:var(--ink-soft); --mt-line:var(--line); max-width:760px; margin:0 auto; }
[data-theme="dark"] .mt-wrap{ --mt-surface:var(--d-surface); --mt-soft:var(--d-surface-soft); --mt-text:var(--d-text); --mt-sub:var(--d-text-soft); --mt-line:var(--d-border); }
.mt-btn{ border:none; cursor:pointer; border-radius:14px; padding:13px 22px; font-weight:800; font-size:14px; font-family:inherit; text-decoration:none; display:inline-block; text-align:center; transition:all .18s var(--ease); }
.mt-btn:hover{ transform:translateY(-2px); }
.mt-btn.go{ background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; box-shadow:0 10px 20px -10px rgba(184,40,80,.7); }
.mt-btn.block{ display:block; margin-top:18px; }
.mt-ticket{ position:relative; background:var(--mt-surface); color:var(--mt-text); border-radius:24px; border:1px solid var(--mt-line); box-shadow:var(--shadow-pop); overflow:hidden; margin-top:6px; }
.mt-top{ padding:22px 24px 18px; display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; background:linear-gradient(135deg,var(--pink-deep),#F2762E); color:#fff; }
.mt-top small{ opacity:.9; font-weight:800; letter-spacing:.08em; font-size:11px; text-transform:uppercase; }
.mt-top h3{ margin:4px 0 0; font-size:24px; color:#fff; }
.mt-when{ opacity:.92; font-size:13.5px; margin-top:4px; }
.mt-pill{ display:inline-flex; align-items:center; gap:8px; padding:7px 16px; border-radius:999px; font-weight:800; font-size:12.5px; background:var(--amber-pale); color:var(--amber-deep); }
.mt-pill::before{ content:""; width:9px; height:9px; border-radius:50%; background:currentColor; animation:mtPulse 1.4s ease-in-out infinite; }
.mt-pill.ok{ background:var(--teal-pale); color:var(--teal-deep); }
.mt-pill.ok::before{ animation:none; }
@keyframes mtPulse{ 0%,100%{ opacity:1; transform:scale(1);} 50%{ opacity:.45; transform:scale(.7);} }
.mt-mid{ padding:16px 24px; font-size:14px; }
.mt-row{ display:flex; justify-content:space-between; gap:12px; padding:6px 0; }
.mt-row small{ display:block; color:var(--mt-sub); font-size:11.5px; }
.mt-row.disc{ color:#0C8B80; font-weight:700; }
.mt-row.sum{ border-top:2px dashed var(--mt-line); margin-top:8px; padding-top:12px; font-size:19px; font-weight:800; }
.mt-row.sum span:last-child{ color:var(--pink-deep); }
.mt-row.sum small{ font-weight:600; }
.mt-perf{ position:relative; border-top:3px dotted var(--mt-line); margin:0 18px; }
.mt-perf::before,.mt-perf::after{ content:""; position:absolute; top:-15px; width:26px; height:26px; border-radius:50%; background:var(--bg); }
.mt-perf::before{ left:-31px; } .mt-perf::after{ right:-31px; }
[data-theme="dark"] .mt-perf::before,[data-theme="dark"] .mt-perf::after{ background:var(--d-bg); }
.mt-bot{ padding:18px 24px 26px; text-align:center; }
.mt-vlabel{ font-size:11px; font-weight:800; letter-spacing:.12em; color:var(--mt-sub); }
.mt-vwrap{ position:relative; margin:10px auto 4px; display:inline-block; }
.mt-vcode{ display:inline-block; font-family:'Courier New',monospace; font-size:34px; font-weight:800; letter-spacing:.2em; padding:10px 18px; border-radius:14px; background:var(--gold-pale); color:#6b3b00; user-select:all; }
[data-theme="dark"] .mt-vcode{ background:rgba(255,201,72,.12); color:#FFD98A; }
.mt-vcode.lock{ filter:blur(7px); user-select:none; pointer-events:none; }
.mt-lock{ position:absolute; inset:0; display:grid; place-items:center; font-size:30px; pointer-events:none; }
.mt-note{ font-size:13px; color:var(--mt-sub); margin:8px 0 0; line-height:1.6; }
.mt-copy{ margin-top:12px; border:none; cursor:pointer; border-radius:999px; padding:8px 18px; font-weight:800; font-size:12.5px; background:var(--teal-pale); color:var(--teal-deep); font-family:inherit; }
.mt-line{ display:flex; align-items:flex-start; justify-content:center; margin:18px 0 4px; }
.mt-line div{ flex:1; text-align:center; font-size:11.5px; font-weight:700; color:var(--mt-sub); position:relative; }
.mt-line div::before{ content:""; display:block; width:16px; height:16px; margin:0 auto 6px; border-radius:50%; background:var(--mt-line); position:relative; z-index:1; }
.mt-line div::after{ content:""; position:absolute; top:7px; left:-50%; width:100%; height:3px; background:var(--mt-line); }
.mt-line div:first-child::after{ display:none; }
.mt-line div.on{ color:var(--mt-text); }
.mt-line div.on::before,.mt-line div.on::after{ background:var(--teal); }
.mt-line div.cur::before{ background:var(--gold); animation:mtPulse 1.4s ease-in-out infinite; }
.mt-tips{ margin:16px 0 0; padding:16px 20px 16px 40px; list-style:disc; border-radius:20px; background:var(--mt-surface); border:1px solid var(--mt-line); color:var(--mt-sub); font-size:13px; line-height:1.9; }
</style>
@endpush

@section('content')
    @php
        $approved = in_array($booking['status'], ['confirmed', 'done'], true);
        $emoji    = ['dino_adventure' => '🦖', 'rollerfever' => '🛼', 'field_of_rides' => '🎡'][$booking['service'] ?? ''] ?? '🎟️';
        $guests   = $booking['pax'] . ' ' . ((int) $booking['pax'] === 1 ? 'guest' : 'guests');
        $items    = collect($booking['items'] ?? []);
        $addons   = collect($booking['addons'] ?? []);
        $money    = fn ($n) => '₱' . number_format((float) $n, 2);
    @endphp

    <div class="mt-wrap">
        <article class="mt-ticket">
            <div class="mt-top">
                <div>
                    <small>{{ $emoji }} Pistavora Admit Pass &middot; {{ $booking['service_name'] }}</small>
                    <h3>{{ $booking['reference'] }}</h3>
                    <div class="mt-when">📅 {{ $booking['date'] }}{{ $booking['time'] ? ' · ' . $booking['time'] : '' }} · {{ $guests }}</div>
                </div>
                <span class="mt-pill {{ $approved ? 'ok' : '' }}">{{ $approved ? 'Confirmed' : 'Waiting for approval' }}</span>
            </div>

            <div class="mt-mid">
                @forelse ($items as $item)
                    <div class="mt-row">
                        <span>{{ $item['name'] }}<small>× {{ $item['qty'] }}@if (!empty($item['free_units'])) &middot; 🎁 {{ $item['free_units'] }} free @endif</small></span>
                        <span>{{ $money($item['line_total']) }}</span>
                    </div>
                @empty
                    {{-- bookings made with the older form have no line items --}}
                    <div class="mt-row">
                        <span>{{ $booking['package_name'] }}<small>{{ $guests }}</small></span>
                        <span>{{ $booking['price'] }}</span>
                    </div>
                @endforelse

                @foreach ($addons as $addon)
                    <div class="mt-row">
                        <span>{{ $addon['name'] ?? 'Add-on' }}<small>× {{ $addon['qty'] ?? 1 }}</small></span>
                        <span>{{ $money($addon['line_total'] ?? 0) }}</span>
                    </div>
                @endforeach

                @if (($booking['discount'] ?? 0) > 0)
                    <div class="mt-row disc"><span>🎉 Deals applied</span><span>&minus;{{ $money($booking['discount']) }}</span></div>
                @endif

                <div class="mt-row sum">
                    <span>Amount Paid @if ($booking['payment_method'])<small>via {{ $booking['payment_method'] }}</small>@endif</span>
                    <span>{{ $booking['price'] }}</span>
                </div>
            </div>

            <div class="mt-perf"></div>

            <div class="mt-bot">
                <div class="mt-vlabel">YOUR VOUCHER CODE</div>
                @if (!empty($booking['voucher_code']))
                    <div class="mt-vwrap"><span class="mt-vcode">{{ $booking['voucher_code'] }}</span></div>
                    <p class="mt-note">Show this code to the cashier at the counter to claim your booking.</p>
                    <button type="button" class="mt-copy" data-code="{{ $booking['voucher_code'] }}">Copy code</button>
                @else
                    {{-- decorative placeholder only — the real code is never sent until approved --}}
                    <div class="mt-vwrap">
                        <span class="mt-vcode lock" aria-hidden="true">{{ strtoupper(\Illuminate\Support\Str::random(8)) }}</span>
                        <span class="mt-lock">🔒</span>
                    </div>
                    <p class="mt-note">Our team is checking your proof of payment. Your voucher unlocks as soon as it's approved. ✨<br>Until then you can still reschedule.</p>
                @endif

                <div class="mt-line">
                    <div class="on">Submitted</div>
                    <div class="{{ $approved ? 'on' : 'on cur' }}">In review</div>
                    <div class="{{ $approved ? 'on' : '' }}">Approved</div>
                </div>
            </div>
        </article>

        <ul class="mt-tips">
            <li>Valid for your booked date: {{ $booking['date'] }}</li>
            <li>Present your voucher code at the counter upon arrival (available after approval)</li>
            <li>One redemption per booking</li>
        </ul>

        <a href="{{ route('user.bookings') }}" class="mt-btn go block">&larr; Back to My Bookings</a>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (e) {
        var b = e.target.closest('.mt-copy');
        if (!b) return;
        if (navigator.clipboard) navigator.clipboard.writeText(b.getAttribute('data-code'));
        b.textContent = 'Copied \u2713';
        setTimeout(function () { b.textContent = 'Copy code'; }, 1600);
    });
</script>
@endpush
