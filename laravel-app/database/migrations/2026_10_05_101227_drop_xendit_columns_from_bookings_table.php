<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'xendit_payment_request_id')) {
                $table->dropColumn('xendit_payment_request_id');
            }
            if (Schema::hasColumn('bookings', 'xendit_reference_id')) {
                $table->dropColumn('xendit_reference_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('xendit_payment_request_id')->nullable();
            $table->string('xendit_reference_id')->nullable();
        });
    }
};