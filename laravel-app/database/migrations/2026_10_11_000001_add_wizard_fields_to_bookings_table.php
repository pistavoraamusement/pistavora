<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'items')) {
                $table->json('items')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'party_type')) {
                $table->string('party_type', 20)->nullable();
            }
            if (!Schema::hasColumn('bookings', 'discount_total')) {
                $table->decimal('discount_total', 10, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach (['items', 'party_type', 'discount_total'] as $col) {
                if (Schema::hasColumn('bookings', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
