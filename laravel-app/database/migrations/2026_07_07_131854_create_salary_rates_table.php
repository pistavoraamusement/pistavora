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
    Schema::create('salary_rates', function (Blueprint $table) {
        $table->id();
        $table->string('position')->unique();
        $table->decimal('daily_rate', 10, 2);
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('salary_rates');
}
};
