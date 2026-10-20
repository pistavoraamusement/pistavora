<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_collections', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();      // matches cards.type
            $table->string('title');
            $table->string('description', 255);
            $table->string('icon')->default('fa-solid fa-layer-group');
            $table->string('unit_label')->default('items'); // "passes", "rides", ...
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_system')->default(false);   // system rows can't be deleted
            $table->timestamps();
        });

        // Seed the four collections that were previously hardcoded in the Blade.
        DB::table('cms_collections')->insert([
            [
                'slug' => 'pass', 'title' => 'Day Passes',
                'description' => 'Dino Adventure, Roller Fever, and Field of Rides pricing cards with price-list modals.',
                'icon' => 'fa-solid fa-ticket', 'unit_label' => 'passes',
                'sort_order' => 1, 'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'attraction', 'title' => 'Attractions',
                'description' => 'The ride cards shown in the Attractions carousel, including policies.',
                'icon' => 'fa-solid fa-ferris-wheel', 'unit_label' => 'rides',
                'sort_order' => 2, 'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'service', 'title' => 'Guest Services',
                'description' => 'Party packages, reservations, booking channels, payment, and the snack bar.',
                'icon' => 'fa-solid fa-concierge-bell', 'unit_label' => 'services',
                'sort_order' => 3, 'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'slug' => 'step', 'title' => 'How It Works',
                'description' => 'The 3-step Book to Scan to Ride sequence.',
                'icon' => 'fa-solid fa-list-ol', 'unit_label' => 'steps',
                'sort_order' => 4, 'is_system' => true,
                'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_collections');
    }
};