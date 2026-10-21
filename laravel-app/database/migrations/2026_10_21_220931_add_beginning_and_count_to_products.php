<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('beginning_stock')->nullable()->after('stock');
            $table->integer('actual_count')->nullable()->after('beginning_stock');
            $table->timestamp('counted_at')->nullable()->after('actual_count');
        });

        // Starting point: kasalukuyang stock ang beginning
        DB::table('products')->update(['beginning_stock' => DB::raw('stock')]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['beginning_stock', 'actual_count', 'counted_at']);
        });
    }
};