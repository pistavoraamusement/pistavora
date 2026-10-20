<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->string('pwd_senior_type', 20)->nullable()->after('discount_amount');
            $table->string('pwd_senior_name')->nullable()->after('pwd_senior_type');
            $table->string('pwd_senior_id_number', 50)->nullable()->after('pwd_senior_name');
            $table->decimal('vatable_sales', 12, 2)->nullable()->after('vat_amount');
            $table->decimal('vat_exempt_sales', 12, 2)->nullable()->after('vatable_sales');
        });
    }

    public function down(): void
    {
        Schema::table('sales_transactions', function (Blueprint $table) {
            $table->dropColumn([
                'pwd_senior_type',
                'pwd_senior_name',
                'pwd_senior_id_number',
                'vatable_sales',
                'vat_exempt_sales',
            ]);
        });
    }
};