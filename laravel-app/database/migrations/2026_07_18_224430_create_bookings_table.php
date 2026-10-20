<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            // Customer-facing booking flow (BookingController)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('service')->nullable();
            $table->unsignedInteger('tier')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->date('visit_date')->nullable();
            $table->string('visit_time')->nullable();
            $table->string('payment_method')->nullable();

            // Admin reservations flow (ReservationController)
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('customer_name')->nullable();
            $table->string('customer_contact')->nullable();
            $table->unsignedInteger('pax')->nullable();
            $table->date('reservation_date')->nullable();
            $table->string('reservation_time')->nullable();

            // Shared by both flows
            $table->string('package')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', [
                'pending', 'confirmed', 'paid', 'cancelled',        // admin flow
                'pending_payment', 'awaiting_verification', 'done', // customer flow
            ])->default('pending_payment');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};