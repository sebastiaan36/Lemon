<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('homepage_contents', function (Blueprint $table) {
            $table->id();

            // Hero
            $table->string('hero_title')->default('Credible Creativity');
            $table->text('hero_body_text')->nullable();
            $table->string('hero_bg_image')->nullable();
            $table->json('hero_floating_images')->nullable();

            // Services intro
            $table->text('services_intro_text')->nullable();

            // Services (Brand / Experience / Employee) als JSON
            $table->json('services')->nullable();

            // Team-avatars
            $table->json('team_avatars')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_contents');
    }
};
