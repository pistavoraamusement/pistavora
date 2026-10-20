<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'approval_status')) {
                $table->string('approval_status')->default('pending');
            }
            if (!Schema::hasColumn('bookings', 'reject_reason')) {
                $table->string('reject_reason')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'reject_note')) {
                $table->text('reject_note')->nullable();
            }
            // baka naidagdag na ito ng groupmate mo para sa cashier
            if (!Schema::hasColumn('bookings', 'voucher_used_at')) {
                $table->timestamp('voucher_used_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'reject_reason', 'reject_note']);
        });
    }
};