<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_cards', function (Blueprint $table) {
            // 'fa'    -> use the `icon` column (Font Awesome icon name)
            // 'image' -> use `icon_image_path` (uploaded, auto-resized icon)
            $table->string('icon_type', 10)->default('fa')->after('icon');
            $table->string('icon_image_path')->nullable()->after('icon_type');
        });
    }

    public function down(): void
    {
        Schema::table('site_cards', function (Blueprint $table) {
            $table->dropColumn(['icon_type', 'icon_image_path']);
        });
    }
};