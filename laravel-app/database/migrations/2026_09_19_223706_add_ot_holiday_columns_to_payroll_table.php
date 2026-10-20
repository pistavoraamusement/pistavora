<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->integer('present_days')->default(0)->after('total_days');
            $table->decimal('daily_rate', 10, 2)->default(0)->after('present_days');
            $table->decimal('holiday_pay', 10, 2)->default(0)->after('basic_salary');
            $table->decimal('overtime_pay', 10, 2)->default(0)->after('holiday_pay');
            $table->decimal('holiday_ot_pay', 10, 2)->default(0)->after('overtime_pay');
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->dropColumn(['present_days', 'daily_rate', 'holiday_pay', 'overtime_pay', 'holiday_ot_pay']);
        });
    }
};