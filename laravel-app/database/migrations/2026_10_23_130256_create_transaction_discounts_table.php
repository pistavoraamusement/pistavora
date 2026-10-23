<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * One row per discount component on a transaction.
 * A transaction with both a manual discount and a Senior discount gets 2 rows.
 *
 * discount_type examples: Manual, Senior Citizen, PWD, Seasons, Unspecified
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id')->index();
            $table->string('discount_type', 60)->index();
            $table->decimal('discount_rate', 6, 2)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
        });

        // Backfill old transactions. We never stored the breakdown before, so the
        // type is pwd_senior_type when present, otherwise "Unspecified".
        DB::statement("
            INSERT INTO transaction_discounts
                (transaction_id, discount_type, discount_rate, amount, created_at, updated_at)
            SELECT transaction_id,
                   COALESCE(NULLIF(pwd_senior_type, ''), 'Unspecified'),
                   NULL,
                   discount_amount,
                   created_at,
                   created_at
            FROM sales_transactions
            WHERE discount_amount > 0
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_discounts');
    }
};