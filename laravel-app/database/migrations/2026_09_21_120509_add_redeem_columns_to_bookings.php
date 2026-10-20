<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'redeemed_at')) {
                $table->timestamp('redeemed_at')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'redeemed_by')) {
                $table->unsignedBigInteger('redeemed_by')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'redeemed_transaction_id')) {
                $table->unsignedBigInteger('redeemed_transaction_id')->nullable();
            }
        });
        $col = DB::selectOne(
            "SELECT IS_NULLABLE AS is_nullable, COLUMN_TYPE AS column_type
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = 'transaction_items'
               AND COLUMN_NAME = 'product_id'"
        );

        if ($col && strtoupper($col->is_nullable) === 'NO') {
            DB::statement("ALTER TABLE transaction_items MODIFY product_id {$col->column_type} NULL");
        }
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['redeemed_at', 'redeemed_by', 'redeemed_transaction_id'] as $c) {
                if (Schema::hasColumn('bookings', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};