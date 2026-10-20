<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Booking extends Model
{
        protected $fillable = [
    'user_id', 'service', 'tier', 'price', 'addons',
    'visit_date', 'visit_time', 'payment_method',
    'customer_id', 'customer_name', 'customer_contact',
    'pax', 'reservation_date', 'reservation_time',
    'package', 'notes', 'status', 'approval_status',
    'reject_reason', 'reject_note',
    'receipt_path', 'voucher_code', 'voucher_used_at',
    'payment_proof_path', 'payment_submitted_at', 'payment_verified_at',
    'payment_rejection_reason',
    'xendit_payment_request_id', 'xendit_reference_id',
    'items', 'party_type', 'discount_total',
];


    protected $casts = [
        'visit_date' => 'date',
        'reservation_date' => 'date',
        'addons' => 'array',
        'items' => 'array',
        'payment_submitted_at' => 'datetime',
        'payment_verified_at' => 'datetime',
    ];
    

    /**
     * Auto-generates a unique voucher_code for every new booking. Was
     * missing from this model entirely (confirmed via `grep -A 12
     * "function boot"` returning nothing) — that's why every booking,
     * old and new, ended up with voucher_code = NULL despite the column
     * existing and the backfill migration reporting "Ran".
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->voucher_code)) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (self::where('voucher_code', $code)->exists());

                $booking->voucher_code = $code;
            }
        });
    }

    // Customer-facing booking flow (BookingController)
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id', 'user_id');
    }

    // Admin reservations flow (ReservationController) — points to `users`,
    // NOT the empty `customers` table.
    public function customer()
    {
        return $this->belongsTo(\App\Models\User::class, 'customer_id', 'user_id');
    }

    /**
     * Unified accessors — a booking may come from either flow, so these
     * fall back to whichever set of columns actually has data. Use these
     * in views instead of reading the raw columns directly.
     */
    public function getDisplayCustomerAttribute()
    {
        return $this->customer ?? $this->user;
    }

    public function getDisplayDateAttribute()
    {
        return $this->reservation_date ?? $this->visit_date;
    }

    public function getDisplayTimeAttribute()
    {
        return $this->reservation_time ?? $this->visit_time;
    }

    public function getDisplayPaxAttribute()
    {
        return $this->pax ?? $this->tier;
    }
}
