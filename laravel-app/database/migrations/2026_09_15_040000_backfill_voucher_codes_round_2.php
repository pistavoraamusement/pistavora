<?php

use App\Models\Booking;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Str;

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
        // Not reversible.
    }
};
