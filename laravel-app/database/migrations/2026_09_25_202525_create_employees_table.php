<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('employees', function (Blueprint $table) {
        $table->string('employee_name')->unique()->after('id');
        $table->enum('category', ['Manager', 'Team Leader', 'Staff'])->after('employee_name');
        $table->string('pin_code')->nullable()->after('category');
        $table->string('qr_token')->nullable()->unique()->after('pin_code');
        $table->date('date_hired')->nullable()->after('qr_token');
        $table->date('date_ended')->nullable()->after('date_hired');
        $table->enum('status', ['active', 'inactive'])->default('active')->after('date_ended');
        $table->softDeletes();
    });
}
public function down(): void
{
    Schema::table('employees', function (Blueprint $table) {
        $table->dropColumn([
            'employee_name', 'category', 'pin_code', 'qr_token',
            'date_hired', 'date_ended', 'status', 'deleted_at',
        ]);
    });
}
};