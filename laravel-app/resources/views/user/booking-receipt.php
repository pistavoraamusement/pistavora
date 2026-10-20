{{--
    resources/views/user/booking-receipt.blade.php
    Route: GET /user/bookings/{booking}/receipt -> user.bookings.receipt
    Controller: App\Http\Controllers\User\BookingController@receipt

    Shown right after the customer signs the waiver. Payment already
    confirmed the booking (no admin verification step), so this is the
    final receipt with the voucher code the cashier will look up at the
    counter (see PosController@lookupBooking / the "Booking Code" button
    in the cashier POS).

    Expects from the controller:
      $booking -> ['id','service_name','package_name','date','time','pax',
                   'price','payment_method','voucher_code']
--}}
@extends('layouts.user')

@section('title', 'Booking Receipt')
@section('page-title', 'Booking Receipt')
@section('page-subtitle', 'Payment received — your booking is confirmed')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card">
        <h4>{{ $booking['service_name'] }}</h4>
        <p>{{ $booking['package_name'] }}</p>
    </div>

    <div class="pkg">
        <div>
            <b>{{ $booking['date'] }}{{ $booking['time'] ? ' · ' . $booking['time'] : '' }}</b>
            <span>{{ $booking['pax'] }} pax</span>
        </div>
        <span class="tag green">PAID</span>
    </div>

    <div class="u-card" style="margin-top:14px;">
        <h4 style="margin-bottom:2px;">Amount Paid</h4>
        <p style="font-size:22px;font-weight:700;margin:0;">{{ $booking['price'] }}</p>
        @if ($booking['payment_method'])
            <p style="margin-top:2px;">via {{ $booking['payment_method'] }}</p>
        @endif
    </div>

    <div class="u-card" style="margin-top:14px;text-align:center;background:#f6f6f8;">
        <span style="font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);">Voucher Code</span>
        <div style="font-family:monospace;font-size:30px;font-weight:700;letter-spacing:3px;margin:8px 0;">{{ $booking['voucher_code'] }}</div>
        <p style="font-size:12px;color:var(--muted);margin:0;">Show this code to the cashier at the counter to claim your booking.</p>
    </div>

    <div class="u-card" style="margin-top:14px;">
        <ul style="margin:0;padding-left:18px;font-size:12.5px;color:var(--muted);line-height:1.7;">
            <li>Valid for your booked date: {{ $booking['date'] }}</li>
            <li>Present this code at the counter upon arrival</li>
            <li>One redemption per booking</li>
        </ul>
    </div>

    <a href="{{ route('user.bookings') }}" class="u-btn" style="margin-top:14px;display:block;text-align:center;">Back to My Bookings</a>

@{{--
    resources/views/user/booking-receipt.blade.php
    Route: GET /user/bookings/{booking}/receipt -> user.bookings.receipt
    Controller: App\Http\Controllers\User\BookingController@receipt

    Shown right after the customer signs the waiver. Payment already
    confirmed the booking (no admin verification step), so this is the
    final receipt with the voucher code the cashier will look up at the
    counter (see PosController@lookupBooking / the "Booking Code" button
    in the cashier POS).

    Expects from the controller:
      $booking -> ['id','service_name','package_name','date','time','pax',
                   'price','payment_method','voucher_code']
--}}
@extends('layouts.user')

@section('title', 'Booking Receipt')
@section('page-title', 'Booking Receipt')
@section('page-subtitle', 'Payment received — your booking is confirmed')
@section('body-class', 'page-uniform')

@section('content')

    <div class="u-card">
        <h4>{{ $booking['service_name'] }}</h4>
        <p>{{ $booking['package_name'] }}</p>
    </div>

    <div class="pkg">
        <div>
            <b>{{ $booking['date'] }}{{ $booking['time'] ? ' · ' . $booking['time'] : '' }}</b>
            <span>{{ $booking['pax'] }} pax</span>
        </div>
        <span class="tag green">PAID</span>
    </div>

    <div class="u-card" style="margin-top:14px;">
        <h4 style="margin-bottom:2px;">Amount Paid</h4>
        <p style="font-size:22px;font-weight:700;margin:0;">{{ $booking['price'] }}</p>
        @if ($booking['payment_method'])
            <p style="margin-top:2px;">via {{ $booking['payment_method'] }}</p>
        @endif
    </div>

    <div class="u-card" style="margin-top:14px;text-align:center;background:#f6f6f8;">
        <span style="font-size:10.5px;letter-spacing:.5px;text-transform:uppercase;color:var(--muted);">Voucher Code</span>
        <div style="font-family:monospace;font-size:30px;font-weight:700;letter-spacing:3px;margin:8px 0;">{{ $booking['voucher_code'] }}</div>
        <p style="font-size:12px;color:var(--muted);margin:0;">Show this code to the cashier at the counter to claim your booking.</p>
    </div>

    <div class="u-card" style="margin-top:14px;">
        <ul style="margin:0;padding-left:18px;font-size:12.5px;color:var(--muted);line-height:1.7;">
            <li>Valid for your booked date: {{ $booking['date'] }}</li>
            <li>Present this code at the counter upon arrival</li>
            <li>One redemption per booking</li>
        </ul>
    </div>

    <a href="{{ route('user.bookings') }}" class="u-btn" style="margin-top:14px;display:block;text-align:center;">Back to My Bookings</a>

@endsection