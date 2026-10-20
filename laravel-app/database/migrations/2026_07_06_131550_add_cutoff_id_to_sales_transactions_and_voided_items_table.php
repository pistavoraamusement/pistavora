<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // sales_transactions: add cutoff_id if missing
        if (!Schema::hasColumn('sales_transactions', 'cutoff_id')) {
            Schema::table('sales_transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('cutoff_id')->nullable()->after('transaction_status');
                $table->foreign('cutoff_id')->references('id')->on('sales_cutoffs')->nullOnDelete();
            });
        }

        // voided_items: add all missing columns used by logVoid()
        Schema::table('voided_items', function (Blueprint $table) {
            if (!Schema::hasColumn('voided_items', 'item_name')) {
                $table->string('item_name');
            }
            if (!Schema::hasColumn('voided_items', 'price')) {
                $table->decimal('price', 10, 2);
            }
            if (!Schema::hasColumn('voided_items', 'qty')) {
                $table->integer('qty');
            }
            if (!Schema::hasColumn('voided_items', 'amount')) {
                $table->decimal('amount', 10, 2);
            }
            if (!Schema::hasColumn('voided_items', 'cashier_name')) {
                $table->string('cashier_name')->nullable();
            }
            if (!Schema::hasColumn('voided_items', 'authorized_by')) {
                $table->string('authorized_by')->nullable();
            }
        });

        if (!Schema::hasColumn('voided_items', 'cutoff_id')) {
            Schema::table('voided_items', function (Blueprint $table) {
                $table->unsignedBigInteger('cutoff_id')->nullable();
                $table->foreign('cutoff_id')->references('id')->on('sales_cutoffs')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sales_transactions', 'cutoff_id')) {
            Schema::table('sales_transactions', function (Blueprint $table) {
                $table->dropForeign(['cutoff_id']);
                $table->dropColumn('cutoff_id');
            });
        }

        Schema::table('voided_items', function (Blueprint $table) {
            $columns = ['item_name', 'price', 'qty', 'amount', 'cashier_name', 'authorized_by'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('voided_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        if (Schema::hasColumn('voided_items', 'cutoff_id')) {
            Schema::table('voided_items', function (Blueprint $table) {
                $table->dropForeign(['cutoff_id']);
                $table->dropColumn('cutoff_id');
            });
        }
    }
};