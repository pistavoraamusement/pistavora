<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Separates "Undertime" (worked a short/incomplete shift) from "Late"
     * (arrived past the shift's grace period). These used to be lumped
     * into the same `deduction` column and payslip line.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('payroll', 'undertime_deduction')) {
            Schema::table('payroll', function (Blueprint $table) {
                $table->decimal('undertime_deduction', 10, 2)->default(0)->after('deduction');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('payroll', 'undertime_deduction')) {
            Schema::table('payroll', function (Blueprint $table) {
                $table->dropColumn('undertime_deduction');
            });
        }
    }
};