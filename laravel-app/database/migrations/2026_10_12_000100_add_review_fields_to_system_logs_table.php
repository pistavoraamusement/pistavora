<?php
// Ilagay sa: database/migrations/2026_10_12_000100_add_review_fields_to_system_logs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('system_logs', 'review_status')) {
                $table->string('review_status', 20)->nullable();   // confirmed | suspicious
            }
            if (!Schema::hasColumn('system_logs', 'reviewed_by')) {
                $table->unsignedBigInteger('reviewed_by')->nullable();
            }
            if (!Schema::hasColumn('system_logs', 'reviewed_at')) {
                $table->timestamp('reviewed_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('system_logs', function (Blueprint $table) {
            foreach (['review_status', 'reviewed_by', 'reviewed_at'] as $col) {
                if (Schema::hasColumn('system_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
