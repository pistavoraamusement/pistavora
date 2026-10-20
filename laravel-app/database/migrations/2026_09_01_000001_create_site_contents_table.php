<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('section');   // 'hero', 'split', 'contact', 'footer'
            $table->string('field_key'); // 'title', 'description', 'video_path', etc.
            $table->longText('value')->nullable();
            $table->timestamps();

            $table->unique(['section', 'field_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
