<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('ot_multiplier', 5, 2)->default(1.25);
            $table->decimal('holiday_ot_multiplier', 5, 2)->default(2.60);
            $table->integer('minutes_per_day')->default(480);
            $table->decimal('late_rate_multiplier', 5, 2)->default(1.00);
            $table->timestamps();
        });

        // Iisang row lang ang table na ito — sinisimulan agad para may
        // laging bases ang app kahit hindi pa na-e-edit ng user.
        DB::table('payroll_settings')->insert([
            'ot_multiplier' => 1.25,
            'holiday_ot_multiplier' => 2.60,
            'minutes_per_day' => 480,
            'late_rate_multiplier' => 1.00,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};