<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;   // 👈 kulang ito

return new class extends Migration
{
    public function up(): void
    {
        // Palawakin ang transaction_status para ma-allow ang 'Voided'
        DB::statement("ALTER TABLE sales_transactions 
            MODIFY COLUMN transaction_status 
            ENUM('Completed','Voided') NOT NULL DEFAULT 'Completed'");

        Schema::table('sales_transactions', function ($table) {
            $table->string('void_reason')->nullable()->after('transaction_status');
            $table->string('voided_by')->nullable()->after('void_reason');
            $table->timestamp('voided_at')->nullable()->after('voided_by');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function ($table) {
            $table->dropColumn(['void_reason', 'voided_by', 'voided_at']);
        });

        DB::statement("ALTER TABLE sales_transactions 
            MODIFY COLUMN transaction_status 
            ENUM('Completed') NOT NULL DEFAULT 'Completed'");
    }
};