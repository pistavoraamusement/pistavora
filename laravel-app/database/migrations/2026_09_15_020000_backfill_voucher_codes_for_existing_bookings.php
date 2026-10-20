<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

/**
 * One-time backfill: assign a unique voucher_code to every existing
 * booking that doesn't have one yet (bookings created before the
 * voucher_code column existed). New bookings already get one
 * automatically via Booking::boot()'s creating() hook — this migration
 * only catches the old rows.
 *
 * Written as a migration instead of a `php artisan tinker --execute`
 * one-liner because Git Bash (MINGW64) on Windows can mangle the
 * backslashes in namespaced class names / escaped `$` when passing a
 * long command straight to php.exe — running it as a plain PHP file
 * via `php artisan migrate` sidesteps that entirely.
 */
return new class extends Migration
{
    public function up(): void
    {
        Booking::whereNull('voucher_code')
            ->orWhere('voucher_code', '')
            ->get()
            ->each(function (Booking $booking) {
                do {
                    $code = strtoupper(Str::random(8));
                } while (Booking::where('voucher_code', $code)->exists());

                $booking->update(['voucher_code' => $code]);
            });
    }

    public function down(): void
    {
        // Not reversible — we don't know which bookings had a null
        // voucher_code before this ran, so there's nothing to undo.
    }
};
