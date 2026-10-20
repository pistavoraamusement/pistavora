<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'pending_password_hash')) {
                $table->string('pending_password_hash')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'password_change_code')) {
                $table->string('password_change_code', 10)->nullable()->after('pending_password_hash');
            }
            if (!Schema::hasColumn('users', 'password_change_expires_at')) {
                $table->timestamp('password_change_expires_at')->nullable()->after('password_change_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['pending_password_hash', 'password_change_code', 'password_change_expires_at']);
        });
    }
};