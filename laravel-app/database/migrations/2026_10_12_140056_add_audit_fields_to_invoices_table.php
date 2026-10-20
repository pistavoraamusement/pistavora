<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            // Void: void_reason, voided_by, voided_at already exist
            // (2026_09_07_145003_add_void_fields_to_sales_transactions)

            // Refund
            if (! Schema::hasColumn('sales_transactions', 'refund_amount')) {
                $table->decimal('refund_amount', 10, 2)->nullable();
            }
            if (! Schema::hasColumn('sales_transactions', 'refund_reason')) {
                $table->string('refund_reason')->nullable();
            }
            if (! Schema::hasColumn('sales_transactions', 'refunded_by')) {
                $table->unsignedBigInteger('refunded_by')->nullable();
            }
            if (! Schema::hasColumn('sales_transactions', 'refunded_at')) {
                $table->timestamp('refunded_at')->nullable();
            }

            // Manual discount
            if (! Schema::hasColumn('sales_transactions', 'discount_reason')) {
                $table->string('discount_reason')->nullable();
            }

            // Reprint
            if (! Schema::hasColumn('sales_transactions', 'print_count')) {
                $table->unsignedInteger('print_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'refund_amount', 'refund_reason', 'refunded_by', 'refunded_at',
                'discount_reason', 'print_count',
            ]);
        });
    }
};