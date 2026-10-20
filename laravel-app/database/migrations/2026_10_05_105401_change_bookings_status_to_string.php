<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE bookings MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending_payment'");
    }

    public function down(): void
    {
        // Intentionally empty: converting back to ENUM could truncate
        // rows that already use newer status values.
    }
};