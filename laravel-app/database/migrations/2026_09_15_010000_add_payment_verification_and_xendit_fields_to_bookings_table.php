<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Booking model's $fillable list already expects these columns
 * (voucher_code, payment_proof_path, payment_submitted_at,
 * payment_verified_at, payment_rejection_reason,
 * xendit_payment_request_id, xendit_reference_id) but the migration
 * that was supposed to add them to the `bookings` table never actually
 * ran/existed on this machine — confirmed via `php artisan migrate:status`
 * showing no such migration at all. Each column is added only if it's
 * not already there, so this is safe to run no matter which of these
 * columns (if any) already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'voucher_code')) {
                $table->string('voucher_code')->nullable()->after('status');
            }
            if (!Schema::hasColumn('bookings', 'payment_proof_path')) {
                $table->string('payment_proof_path')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'payment_submitted_at')) {
                $table->timestamp('payment_submitted_at')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'payment_verified_at')) {
                $table->timestamp('payment_verified_at')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'payment_rejection_reason')) {
                $table->string('payment_rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'xendit_payment_request_id')) {
                $table->string('xendit_payment_request_id')->nullable();
            }
            if (!Schema::hasColumn('bookings', 'xendit_reference_id')) {
                $table->string('xendit_reference_id')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            foreach ([
                'voucher_code',
                'payment_proof_path',
                'payment_submitted_at',
                'payment_verified_at',
                'payment_rejection_reason',
                'xendit_payment_request_id',
                'xendit_reference_id',
            ] as $column) {
                if (Schema::hasColumn('bookings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
