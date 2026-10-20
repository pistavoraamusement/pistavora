<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_cards', function (Blueprint $table) {
            $table->id();
            $table->string('type');       // 'pass', 'attraction', 'service', 'step'
            $table->string('slug');       // 'pass-dino', 'ride-vikings', 'party', 'step-book'
            $table->string('badge_label')->nullable();   // "Dino Adventure", "Most Popular"
            $table->string('icon')->nullable();          // emoji or fa-icon class
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('price_display')->nullable(); // "₱599", "Promo"
            $table->string('image_path')->nullable();
            $table->string('button_text')->nullable();
            $table->string('button_link')->nullable();
            $table->json('features')->nullable();   // bullet list / ride meta tags
            $table->json('modal_list')->nullable();  // modal price-list line items
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_cards');
    }
};
