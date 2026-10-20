<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Kung nagawa mo na ang table sa Workbench, laktawan na lang.
        if (Schema::hasTable('attendance_imports')) {
            return;
        }

        Schema::create('attendance_imports', function (Blueprint $table) {
            $table->id();
            $table->date('period_start');
            $table->date('period_end');
            $table->dateTime('imported_at');

            $table->unique(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_imports');
    }
};
