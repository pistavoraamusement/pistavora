<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_cards', function (Blueprint $table) {
            $table->string('badge_color', 20)->nullable()->after('badge_label');
        });
    }

    public function down(): void
    {
        Schema::table('site_cards', function (Blueprint $table) {
            $table->dropColumn('badge_color');
        });
    }
};