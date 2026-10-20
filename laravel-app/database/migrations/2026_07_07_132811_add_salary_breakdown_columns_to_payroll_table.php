<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->string('cutoff_type')->nullable()->after('payroll_period_end');
            $table->decimal('basic_salary', 10, 2)->default(0)->after('cutoff_type');
            $table->decimal('sss_deduction', 10, 2)->default(0)->after('basic_salary');
            $table->decimal('philhealth_deduction', 10, 2)->default(0)->after('sss_deduction');
            $table->decimal('pagibig_deduction', 10, 2)->default(0)->after('philhealth_deduction');
            $table->decimal('withholding_tax', 10, 2)->default(0)->after('pagibig_deduction');
            $table->decimal('deduction', 10, 2)->default(0)->after('withholding_tax');
            $table->decimal('net_salary', 10, 2)->default(0)->after('deduction');
        });
    }

    public function down(): void
    {
        Schema::table('payroll', function (Blueprint $table) {
            $table->dropColumn([
                'cutoff_type', 'basic_salary', 'sss_deduction',
                'philhealth_deduction', 'pagibig_deduction',
                'withholding_tax', 'deduction', 'net_salary',
            ]);
        });
    }
};